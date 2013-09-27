/**
 * Main js file
 * 
 * @dep jquery
 */
var rsvpMe; // put our namespace in global scope
(function($){

	rsvpMe = {
		lb : {}, //lightbox object
		self : this,
		clones : [],
		
		showEvent : function(event){

			// do not show an overlay for this event if current page is this event
			if(document.getElementById("event_form_"+event.id)) return false;

			event.featured_image = '<img src="' + event.featured_image_src + '" alt="" />';
			rsvpMe.buildRSVPMeForm(event);
		},
		
		/**
		 * Build an RSVP Event Form from json object
		 *
		 * @param  event Object
		 * @return null
		 * @since  1.5.0
		 */
		buildRSVPMeForm : function(event){

			if(!this.clones['rsvp_form']){
				this.clones['rsvp_form'] = $("#event_form_wrapper").clone();
			}

			var tmpl = this.clones['rsvp_form'].html();
			var reg;

			tmpl = renderTemplate(tmpl, event);

			var tmpl = "<div class='rsvp-me-form-wrapper'>" + tmpl + "</div>";
			var html = $("#event_form_wrapper").html(tmpl);

	    self.lb = $(html);
	    $(self.lb).lightbox_me();
		},

		showMultipleEvents : function(events){
	
			if(!this.clones['single_event_overview_tmpl']){
				this.clones['single_event_overview_tmpl'] = $("#single_event_overview_tmpl").clone();
			}

			var tmpl = this.clones["single_event_overview_tmpl"].html();
			var html = '';
			
			for(obj in events){
				events[obj].featured_image = '<img src="' + events[obj].featured_image_src + '" alt="" />';
				html += renderTemplate(tmpl, events[obj]);
			}
		
			html = "<div style='padding:65px; background:white'><h1>Multiple Events</h1>" + html + "</div>";
		
			self.lb = $(html);
			$(self.lb).lightbox_me();

			/*
			$(".rsvp-me-event").click(function(e){
				
			})*/
		},

		cancel : function(){
			$(self.lb).trigger('close');
		},
		
		submitRsvp : function(id){
			
			var valid=true;
			var selected = 0;
			var fields = {};
			//alert($"#rsvp_form_"+id));
			//var form = document.getElementById("rsvp_form_"+id);
			$("#rsvp_form_"+id + " input").each(function(index){
				
				if(this.name == "rsvp_event_id"){
					fields[this.name] = this.value;
				}
				if(this.className == "reqd"){
					if(this.value==""){
						//alert(this.name + " = " + this.value);
						$(this).css("background", "#ffffcc");
						valid=false;
					}else{
						//let's make sure the bg is back to default
						$(this).css("background", "#ffffff");
						fields[this.name] = this.value;
					}	
				}
				if(this.type == "radio"){
					if(this.checked){
						fields[this.name] = this.value;
						selected++;
					}
				}
			});

			if(!valid || selected < 1){
				alert("Please fill all required fields");
				return false;
			}
			// all looks good...
			// prep data for ajax call
			var data = {
				action : 'submit_rsvp',
				fname : escape(fields["fname"]),
				lname : escape(fields["lname"]),
				email : escape(fields["email"]),
				response : fields["response"],
				msg : escape(document.getElementById("rsvp_form_"+id)["msg"].value),
				event_id : escape(fields["rsvp_event_id"])
			};

			if ( $('.rsvp-me-form-wrapper').length > 0) { 
			  $(".rsvp-me-form-wrapper").html("<div style='padding:65px' class='rsvp_msg'><h2>Sending RSVP...</h2></div>");	
				$(".rsvp-me-form-wrapper").css("position", "fixed");
				
				$(".rsvp-me-form-wrapper").css("top", ($(window).height() / 2 ) - ($(".rsvp-me-form-wrapper").height() / 2 ) + "px"); 				
				$(".rsvp-me-form-wrapper").css("left", ($(window).width() / 2 ) - ($(".rsvp-me-form-wrapper").width() / 2 ) + "px"); 
				
				// Set the width to it's current size so it won't change with the following server response msg
				$(".rsvp-me-form-wrapper").css("width", $(".rsvp-me-form-wrapper").width());
				$(".rsvp-me-form-wrapper").css("height", $(".rsvp-me-form-wrapper").height());
			}
					
			$.post(ajaxurl, data, function(data){

				if(data.slice(-1) == "0"){
					data = data.slice(0, -1);
				}
				var response = $.parseJSON(data);

				for(field in response){
					//alert(field + " = " + response[field]);
				}

				if(response.success){

					$(".rsvp_msg").html("<p class='rsvp-me-alert-box success'>woohoo you're RSVP'd!</p>");
				}else if(response.error == "duplicate"){

					$(".rsvp_msg").html("<p class='rsvp-me-alert-box alert'>We already have a reservation for that email</p>");
				}else{

					$(".rsvp_msg").html("<p class='rsvp-me-alert-box alert'>There was an unidentified error. Please try again later</p>");
				}
				
				if ( $('.rsvp-me-form-wrapper').length > 0) {
					$(".rsvp-me-form-wrapper").css("position", "fixed");
					$(".rsvp-me-form-wrapper").css("top", ($(window).height() / 2 ) - ($(".rsvp-me-form-wrapper").height() / 2 ) + "px"); 
			
					setTimeout("jQuery('#event_form_wrapper').trigger('close')", 3000);
					//setTimeout("$('.rsvp-me-form-wrapper').trigger('close')", 3000);
				}else{
					setTimeout("jQuery('.alert-box').fadeOut();", 3000);
				}
				$(document).scrollTop(0);

				return false;

			});
			return false;
		},
		
		rememberUser :function(){

		},
		
		nextMonth : function(){
			if(!this.curmonth){
				var date = new Date();
				this.curmonth = date.getMonth() + 1; //javascript starts at 0 for january
				this.curyear = date.getFullYear();
			}
			this.curmonth = (this.curmonth < 12) ? (this.curmonth + 1) : 1;
			this.curyear = (this.curmonth == 1) ? (this.curyear + 1 ) : this.curyear;
			this.updateMonth();
		},
		
		prevMonth : function(){
			if(!this.curmonth){	
				var date = new Date();
				this.curmonth = date.getMonth() + 1;
				this.curyear = date.getFullYear();
			}
			this.curmonth = (this.curmonth > 1) ? (this.curmonth - 1) : 12;
			this.curyear = (this.curmonth == 12) ? (this.curyear - 1) : this.curyear;
			this.updateMonth();
		},
		
		updateMonth : function(){
			
			var data = {
				month : this.curmonth,
				year : this.curyear,
				action : "update_calendar"
			};
			//prepare preloader
			var preloader = new Image();
			preloader.src = plugin_path + "/images/ajax-loader-inline.gif";
			
			var calmonth = document.getElementById("rsvp_calendar_month");
			
			calmonth.innerHTML = "";
			
			calmonth.appendChild(preloader);
			
			$.get(ajaxurl, data, function(data){
				
				if(data.slice(-1) == "0"){
					data = data.slice(0, -1);
				}

				if(/error/.test(data)){
						alert("error occurred");
				}else{
					var calwrap = document.getElementById("rsvp_me_event_calendar");
					var parent = calwrap.parentNode;
					calwrap.parentNode.removeChild(calwrap);
					parent.innerHTML = data;
				}
			});
		}
	}

	/**
	 * Template Engine, renders template, replaces template placeholders with object vars
	 *
	 * @param tmpl (String) Template html as a string object
	 * @param obj (Object) object with field=>value pairings to match template placeholders
	 * @since 1.9
	 */
	function renderTemplate(tmpl, obj){
		var reg;
		//alert(tmpl);
		var maxattempts = 50;
		var str = "";
		var i;
		while(/{:(.*)}/.test(tmpl)){
			for(field in obj){
				//alert(field + " = " + obj[field]);
				//str += field + " = " + obj[field] + "\n";
				reg = new RegExp("{:" + field + "}");
				tmpl = tmpl.replace(reg, obj[field]);
			}
			//return false;
			//i++;
			//if(i == maxattempts) return;
		}
		return tmpl;
	}
	function stripslashes (str) {
	  // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	  // +   improved by: Ates Goral (http://magnetiq.com)
	  // +      fixed by: Mick@el
	  // +   improved by: marrtins
	  // +   bugfixed by: Onno Marsman
	  // +   improved by: rezna
	  // +   input by: Rick Waldron
	  // +   reimplemented by: Brett Zamir (http://brett-zamir.me)
	  // +   input by: Brant Messenger (http://www.brantmessenger.com/)
	  // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
	  // *     example 1: stripslashes('Kevin\'s code');
	  // *     returns 1: "Kevin's code"
	  // *     example 2: stripslashes('Kevin\\\'s code');
	  // *     returns 2: "Kevin\'s code"
	  return (str + '').replace(/\\(.?)/g, function (s, n1) {
	    switch (n1) {
	    case '\\':
	      return '\\';
	    case '0':
	      return '\u0000';
	    case '':
	      return '';
	    default:
	      return n1;
	    }
	  });
	}
})(jQuery);