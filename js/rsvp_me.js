/**
 * Main js file
 * 
 * @dep jquery
 */

var rsvpMe = {
	
	nspace : 'rsvp_me_',
	
	showEvent : function(id){
		
		//triggered when mousing over calendar event
		
		var html = "<div id='"+id+"' class='rsvp-form'>" + $("#rsvp_form_"+id).html() + "</div>";
		
		mybox.overlay(html, $("#rsvp_form_"+id).width(), $("#rsvp_form_"+id).height());
		
		//now that our overlay is up let's check to see if we remember this user
		var theform = document.getElementById("rsvp_form_"+id);
		
		if(rsvpCookie.fname){
			theform["fname"].value = rsvpCookie.fname;
			theform["lname"].value = rsvpCookie.lname;
			theform["email"].value = rsvpCookie.email;
		}
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
	
		mybox.overlay(html, $("#rsvp_form_"+events[0].id).width() + 100, $("#rsvp_form_"+events[0].id).height() + 100); //arbitrary 100 added as padding, obviously not ideal, but will do for now 
		
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
		
		var fields = {
						fname : theform["fname"],
					 	lname : theform["lname"],
						email : theform["email"],
						response : theform["response"],
						msg : theform["msg"],
						event_id : theform["event_id"]
					};
		
		//validate the fields
		var invalid = false;
		
		for(field in fields){
			
			if(field == "msg"){ continue; }
			
			if(fields[field].value == ""){
				fields[field].style.background = "#ffffcc";
				fields[field].style.border = "2px solid #f2f2f2";
				invalid = true;
			}
		}
		
		//make sure the radio button response is selected 
		var selcount = 0;
		var response = '';
		for(var i=0; i < fields.response.length; i++){
			if(fields.response[i].checked) {
				selcount++;
				
				response = fields.response[i].value;
			}
		}
				
		if(selcount < 1 ){ invalid = true; };			
			
		if(invalid){
			alert("please be sure to fill out all required information");	
		}
		else{
			
			/* submit data via an ajax request */
			var preloader = new Image();
			preloader.src = plugin_path + "/images/ajax-loader-inline.gif";
			
			$("#submit_cancel_"+fields.event_id.value).html("");
			
			$("#submit_cancel_"+fields.event_id.value).append(preloader);
			
			//theform["submit"].parentNode.insertBefore(preloader, theform["submit"].nextSibling);
			
			//theform["submit"].style.display = "none";
			
			var data = {
				action : 'submit_rsvp',
				fname : escape(fields.fname.value),
				lname : escape(fields.lname.value),
				email : escape(fields.email.value),
				response : response,
				msg : escape(fields.msg.value),
				event_id : fields.event_id.value
			};
			
			//let's set our cookie so that we remember this user!
			rsvpCookie.fname = fields.fname.value;
			rsvpCookie.lname = fields.lname.value;
			rsvpCookie.email = fields.email.value;
			
			rsvpCookie.store();
			
			var obj = {
				callback : function(response){
					
					var parts = response.split("|"); //separate our response from the wordpress response
				    
					var wp_response = parts[1];
					
					var our_response = parts[0];
										
					//remove preloader
					this.preloader.parentNode.removeChild(this.preloader);
					
					if(/error/.test(our_response)){
						
						var error = our_response.split("=")[1];
						
						switch(error){
							
							case "duplicate":
								
							var msg ="<p align='center'><strong>"+this.name+", we already have you down for this event<br /></strong> <a href='Javascript: mybox.closebox()'>Close</a></p>";
													
							break;
							
						}
						
					}
					else{					
						//successfull return!
						var msg ="<p align='center'><strong>"+this.name+", your RSVP was successfull!</strong><br /><a href='Javascript: mybox.closebox()'>Close</a></p>";						
					}
					
					//output message
					$("#submit_cancel_"+this.id).html(msg);
				},
				
				id : data.event_id,
				
				theform : theform,
				
				preloader : preloader,
				
				name : data.fname
			}
			
			ajaxGet(ajaxurl, data, obj);
			
		}
		
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
		alert(plugin_path);
		//prepare preloader
		var preloader = new Image();
		preloader.src = plugin_path + "/images/ajax-loader-inline.gif";
		
		var calmonth = document.getElementById("rsvp_calendar_month");
		
		calmonth.innerHTML = "";
		
		calmonth.appendChild(preloader);
		
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
	}

}

var mybox = {
	
	overlayDiv : {},
	
	contentDiv : {},
	
	clickedArea : "",
	
	overlay : function(html, width, height){
		
		this.overlayDiv = document.createElement("div");
		
		this.overlayDiv.style.position = "absolute";
		this.overlayDiv.style.top = "0px";
		this.overlayDiv.style.left = "0px";
		this.overlayDiv.style.width = $(document).width() + "px";
		this.overlayDiv.style.height = $(document).height() + "px";
		this.overlayDiv.style.zIndex = "99999";
		this.overlayDiv.style.background = "url(" + plugin_path + "/images/overlay_bg.png) repeat top left";
		
		this.overlayDiv.onclick = function(){
			if(mybox.clickedArea == "content"){
				mybox.clickedArea = ""; //reset clicked area
			}else{
				mybox.closebox();
			}
		}
		
		document.body.insertBefore(this.overlayDiv, document.body.firstChild);	
		
		//now prepare the centered content div
		this.contentDiv = document.createElement("div");
		this.contentDiv.className = "overlay-content";
		this.contentDiv.style.width = width + "px";
		this.contentDiv.style.height = height + "px";
		this.contentDiv.style.background = "white";
		this.contentDiv.style.padding = "35px";
		this.contentDiv.style.position = "relative";
		this.contentDiv.style.margin = "0px auto";
		this.overlayDiv.style.zIndex = "99999999";
		this.contentDiv.style.marginTop = ( (getWindowHeight() - height) / 2 ) + "px";
		
		this.contentDiv.innerHTML = html;
		
		this.contentDiv.onclick = function(){
			mybox.clickedArea = "content";
			//mybox.closebox();
		}
		
		this.overlayDiv.appendChild(this.contentDiv);
	},
	
	closebox : function(){

		this.overlayDiv.removeChild(this.contentDiv);
		
		document.body.removeChild(this.overlayDiv);
	}
}


/* Some very important utility functions */

function getScrollXY() {
	
  var scrOfX = 0, scrOfY = 0;
  if( typeof( window.pageYOffset ) == 'number' ) {
    //Netscape compliant
    scrOfY = window.pageYOffset;
    scrOfX = window.pageXOffset;
  } else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
    //DOM compliant
    scrOfY = document.body.scrollTop;
    scrOfX = document.body.scrollLeft;
  } else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
    //IE6 standards compliant mode
    scrOfY = document.documentElement.scrollTop;
    scrOfX = document.documentElement.scrollLeft;
  }
  //return [ scrOfX, scrOfY ];
  return scrOfX;
}


function getWindowSize(){
	
	var w = getWindowWidth();
	
	var h = getWindowHeight();
	
	return [w, h];
	
}

function getWindowWidth(){
	var w = 0;

	if (self.innerHeight)	  {
		  w = self.innerWidth;
	}
	
	else if (document.documentElement && document.documentElement.clientHeight){
		  w = document.documentElement.clientWidth;
	}
	
	else if (document.body){
		  w = document.body.clientWidth;
	}
	
	return w;	
}


function getWindowHeight(){

	  var h = 0;

	  if (self.innerHeight){
			  h = self.innerHeight;
	  }
	  else if (document.documentElement && document.documentElement.clientHeight){
			  h = document.documentElement.clientHeight;
	  }

	  else if (document.body){
			  h = document.body.clientHeight;
	  }
	  
	  return h;
}


function adjustPage(e){
	
	var eHeight = e.offsetHeight;
	
	var wHeight = getScroll() > 0 ? (getScroll() + getWindowHeight()) : getWindowHeight();
	
	var pos = findPos(e);

	var diff = wHeight - pos[1]; //pos[0] is x and pos[1] is y
	
	if(diff < eHeight){
		
		//alert("wHeight = " + wHeight + " and curY = " + pos[1] + " and diff = " + diff + " and scrolled amount = " + getScroll());
		
		var scrollby = getScroll() > 0 ? (getScroll() + (eHeight - diff) + 60) : (eHeight - diff) + 60; //60 for margin
		
		window.scroll(0, scrollby);
	}
	
}
