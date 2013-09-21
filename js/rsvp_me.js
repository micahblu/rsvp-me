/**
 * Main js file
 * 
 * @dep jquery
 */

var rsvpMe = {
	
	lb : {}, //lightbox object
	self : this,
	clone : null,
	
	showEvent : function(id){      

		$.post(ajaxurl, { action: 'rsvp_event_form', id : id }, function(data){
			//check for leading '0' that wp adds, if there remove
			if(data.slice(-1) == "0"){
				data = data.slice(0, -1);
			}
			var jsondata = $.parseJSON(data);
			rsvpMe.buildRSVPMeForm(jsondata);
			return;
		});
	},
	
	/**
	 * Build an RSVP Event Form from json object
	 *
	 * @param  event Object
	 * @return null
	 * @since  1.5.0
	 */
	buildRSVPMeForm : function(event){
		
		//first make sure we're not already on that event's page
		if(document.getElementById("rsvp_form_" + event.id)){
			return false;
		}
		if(!this.clone){
			this.clone = $("#event_form_wrapper").clone();
		}

		var tmpl = this.clone.html();
		var reg;

		for(field in event){
			reg = new RegExp("{:" + field + "}");
			tmpl = tmpl.replace(reg, event[field]);
		}
		//for whatever reason.. its' skipping id... 
		// when ran a 2nd time it gets it?
		for(field in event){
			reg = new RegExp("{:" + field + "}");
			tmpl = tmpl.replace(reg, event[field]);
		}

		var tmpl = "<div class='rsvp-me-form-wrapper'>" + tmpl + "</div>";
		var html = $("#event_form_wrapper").html(tmpl);

    self.lb = $(html);
    $(self.lb).lightbox_me();
	},

	showMultipleEvents : function(events){
	},
	
	showMultiEvent : function(sel){
	},

	cancel : function(){
		$(self.lb).trigger('close');
	},
	
	submitRsvp : function(id){

		var valid=true;
		var selected = 0;
		var fields = {};
		//var form = document.getElementById("rsvp_form_"+id);

		$("#rsvp_form_"+id + " input").each(function(index){
			fields[this.name] = this.value;
			if(this.className == "reqd"){
				if(this.value==""){
					//alert(this.name + " = " + this.value);
					$(this).css("background", "#ffffcc");
					valid=false;
				}else{
					//let's make sure the bg is back to default
					$(this).css("background", "#ffffff");
				}	
			}
			if(this.type == "radio"){
				if(this.checked){
					selected++;
				}
			}
		});

		if(!valid || selected < 1){
			alert("Please fill all required fields");
			return false;
		}
		
		//If all looks good.. let's go
		// submit data via an ajax request 
		//var preloader = new Image();
		//preloader.src = plugin_path + "/images/ajax-loader-inline.gif";

		var data = {
			action : 'submit_rsvp',
			fname : escape(fields["fname"]),
			lname : escape(fields["lname"]),
			email : escape(fields["email"]),
			response : fields["response"],
			msg : escape(fields["msg"]),
			event_id : fields["event_id"]
		};

		if ( $('.rsvp-me-form-wrapper').length > 0) { 
		  $(".rsvp-me-form-wrapper").html("<div style='padding:65px' id='rsvp_msg'><h2>Sending RSVP...</h2></div>");	
			$(".rsvp-me-form-wrapper").css("position", "fixed");
			$(".rsvp-me-form-wrapper").css("top", ($(window).height() / 2 ) - ($(".rsvp-me-form-wrapper").height() / 2 ) + "px"); 
		}
	
		
		$.post(ajaxurl, data, function(data){
	
			if(data.slice(-1) == "0"){
				data = data.slice(0, -1);
			}
			var response = $.parseJSON(data);

			if(response.success){
				$("#rsvp_msg").html("<p class='alert-box success'>woohoo you're RSVP'd!</p>");
			}else if(response.error == "duplicate"){
				$("#rsvp_msg").html("<p class='alert-box alert'>We already have a reservation for that email</p>");
			}else{
				$("#rsvp_msg").html("<p class='alert-box alert'>There was an unidentified error. Please try again later</p>");
			}
			
			if ( $('.rsvp-me-form-wrapper').length > 0) {
				$(".rsvp-me-form-wrapper").css("position", "fixed");
				$(".rsvp-me-form-wrapper").css("top", ($(window).height() / 2 ) - ($(".rsvp-me-form-wrapper").height() / 2 ) + "px"); 
		
				setTimeout("$('#event_form_wrapper').trigger('close')", 3000);
				//setTimeout("$('.rsvp-me-form-wrapper').trigger('close')", 3000);
			}else{
				$(document).scrollTop(0);
				setTimeout("$('.alert-box').fadeOut();", 3000);
			}

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