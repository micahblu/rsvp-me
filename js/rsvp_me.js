/**
 * Main js file
 * 
 * @dep jquery
 */

var rsvpMe = {
	
	lb : {}, //lightbox object
	self : this,
	
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
	
		var form = "<div id='rsvp_form_" + event.id +"_wrapper' class='rsvp-me-form-wrapper'>\n";	
		form += "<div id='rsvp_form_content'>\n";

		// begin form header
		form += "<header>\n";
    form += "<h2>" + event.title + "</h2>\n";
    form += "<p>" + stripslashes(event.description) + "</p>\n";
    form += "<h3>Venue: " + stripslashes(event.venue) + "</h3>\n";
    form += "<p>" + event.address + "<br />\n";
    form += event.city + ", " + event.state + " " + event.zip + "</p>"; 
    form += "</header>\n";
        
    // Begin form section    
    form += "<h3>Are you coming? Then RSVP below!</h3>\n";
    form += "<form id='rsvp_form_" + event.id + "' action='' method='' onsubmit='return rsvpMe.submitRsvp(" + event.id + ")'>\n";
		form += "<input type='hidden' name='event_id' value='" +  event.id + "' />\n";
    
    // First Name
    form += "<p>\n"; 
    form += "<label for='fname'>First name</label>\n";
    form += "<input class='reqd' type='text'	 name='fname' value='' />\n";
    form += "</p>\n";
	  
	  // Last Name
		form += "<p>\n";
		form += "<label for='lname'>Last name</label>\n";
		form += "<input class='reqd' type='text' name='lname' value='' />\n";
		form += "</p>\n";
	      
    form += "<p>\n";
    form += "<label for='email'>Email</label>\n";
    form += "<input class='reqd' type='text' name='email' value='' />\n";
		form += "</p>\n";
	      
    form += "<p>\n";
    form += "<input type='radio' name='response' value='accepted' /> I'm Definitely coming!<br />\n";
    form += "<input type='radio' name='response' value='maybe' /> I might come.<br />\n";
    form += "<input type='radio' name='response' value='declined' /> Sorry can't make it.<br />\n";
    form += "</p>\n";

    form += "<p>Want to send an additional message?<br />\n";
    form += "<textarea name='msg'></textarea></p>\n";
     
   	form += "<p><span id='submit_cancel_" + event.id + "'>\n";
    form += "<input type='submit' name='submit' value='RSVP Me' /> or <a href='Javascript: rsvpMe.cancel()''>Cancel</a></span></p>\n";
	  form += "</form>\n";
    form += "</div>\n";
    form += "</div>\n";

    self.lb = $(form);
    $(self.lb).lightbox_me();

	},

	showMultipleEvents : function(events){
		//triggered when mousing over calendar event

		var html = "<div id='multipleRsvpEvents' class='rsvp-form'>\n";
		//build tabs
		html += "<div id='rsvpMultiTabHeader'>\n";
		html += "<h2>Multiple Events:</h2>\n";
		html += "<select onchange='rsvpMe.showMultiEvent(this)'>\n";
		for(var i=0; i < events.length; i++){
			html += "<option value='"  + events[i].id + "'>" + events[i].title + "</option>\n";
		}
		html += "</select>\n";
		html += "</div>\n";
		var first = true;
		for(var i=0; i < events.length; i++){
			
			html += "<div id='rsvpMultiEvent_"+events[i].id+"' class='rsvp-multi-event' style='display:" + (first==true ? 'block' : 'none') + "'>\n";
			html += $("#rsvp_form_"+events[i].id).html();
			html += "</div><!-- .rsvp-multi-event -->\n";
			first = false;
		}
	
		//mybox.overlay(html, $("#rsvp_form_"+events[0].id).width() + 100, $("#rsvp_form_"+events[0].id).height() + 100); //arbitrary 100 added as padding, obviously not ideal, but will do for now 
		
		//now that our overlay is up let's check to see if we remember this user
		var theform;
		if(rsvpCookie.fname){
			for(var i=0; i < events.length; i++){
				theform = document.getElementById("rsvp_form_"+events[i].id);
				theform["fname"].value = rsvpCookie.fname;
				theform["lname"].value = rsvpCookie.lname;
				theform["email"].value = rsvpCookie.email;
				
			}
		}
	},
	
	showMultiEvent : function(sel){
		var id = sel.options[sel.selectedIndex].value;
		
		$(".rsvp-multi-event").hide();
		$("#rsvpMultiEvent_"+id).show();
		//alert($("#rsvpMultiEvent_"+id).html());
		
	},
	
	cancel : function(){
		mybox.closebox();	
	},
	
	hideEvent : function(){
		//triggered when mousing out calendar event	
	},
	
	eventForm : function(){
		/* 
		triggered when user clicks on calendar event
		Will display event info (map maybe?) and rsvp form
		*/
	},
	
	submitRsvp : function(id){

		var theform = document.getElementById("rsvp_form_"+id);
		
		var valid=true;
		$(".reqd").each(function(index){
			if(this.value==""){
				$(this).css("background", "#ffffcc");
				valid=false;
			}else{
				//let's make sure the bg is back to default
				$(this).css("background", "#ffffff");
			}
		});
		if(!valid){
			alert("Please fill all required fields");
			return false;
		}

		//make sure the radio button response is selected 
		var response = null;
		for(var i=0; i < theform["response"].length; i++){		
			if(theform["response"][i].checked) {
				response = theform["response"][i].value;
			}
		}

		if(!response){ 
			alert("Please select an RSVP option");
			return false;
		}
		//If all looks good.. let's go

		// submit data via an ajax request 
		var preloader = new Image();
		preloader.src = plugin_path + "/images/ajax-loader-inline.gif";

		var data = {
			action : 'submit_rsvp',
			fname : escape(theform["fname"].value),
			lname : escape(theform["lname"].value),
			email : escape(theform["email"].value),
			response : response,
			msg : escape(theform["msg"].value),
			event_id : theform["event_id"].value
		};

	
		$(".rsvp-me-form-wrapper").html("<div style='padding:65px' id='rsvp_msg'><h2>Sending RSVP...</h2></div>");	
		$(".rsvp-me-form-wrapper").css("position", "fixed");
		$(".rsvp-me-form-wrapper").css("top", ($(window).height() / 2 ) - ($(".rsvp-me-form-wrapper").height() / 2 ) + "px"); 
	
		$.post(ajaxurl, data, function(data){
		
			if(data.slice(-1) == "0"){
				data = data.slice(0, -1);
			}
			var response = $.parseJSON(data);

			if(response.success){
				$("#rsvp_msg").html("<h2>woohoo you're RSVP'd!</h2>");
			}else if(response.error == "duplicate"){
				$("#rsvp_msg").html("<p>We already have a reservation for that email</p>");
			}else{
				$("#rsvp_msg").html("<p>There was an unidentified error. Please try again later</p>");
			}
			
			$(".rsvp-me-form-wrapper").css("position", "fixed");
			$(".rsvp-me-form-wrapper").css("top", ($(window).height() / 2 ) - ($(".rsvp-me-form-wrapper").height() / 2 ) + "px"); 
			
			setTimeout("$('.rsvp-me-form-wrapper').trigger('close')", 3000);

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
		
		/*
		var obj = {
			callback : function(response){
				
				var parts = response.split("|"); //separate our response from the wordpress response
				
				var wp_response = parts[1];
				var our_response = parts[0];
											
				if(/error/.test(our_response)){
					var error = our_response.split("=")[1];
					alert("error occurred : " + error);
				}else{
					
					var calwrap = document.getElementById("rsvp_me_event_calendar");
					var parent = calwrap.parentNode;
					calwrap.parentNode.removeChild(calwrap);
					parent.innerHTML = our_response;
				}
			}
		};
		ajaxGet(ajaxurl, data, obj);	
		*/
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





