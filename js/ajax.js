// JavaScript Document
var request= null;
function createRequest () 
{
  try {
		request = new XMLHttpRequest();
		if (request.overrideMimeType) {
			request.overrideMimeType('text/xml');
		}
		if (!request) {
			alert('Cannot create XMLHTTP instance');
			return false;
		}
	} catch (trymicrosoft) {
	  try {
		   request = new ActiveXObject("msxml2.XMLHTTP");
	} catch (othermicrosoft) {
	  try {
		   request = new ActiveXObject("Microsoft.XMLHTTP");
	} catch (failed) {
		   request = null;
		 }
	   }
	}
	if (request == null){
	  alert("Error creating request object!");
	}
}//ends createRequest function
	
function ajaxGet(url, obj, callback){
	/*
	This is the ajax invoking method
	*/
	createRequest();
	
	//prepare url with data appended from obj
	url = url + "?" + makeQueryString(obj);	
	
	request.open("GET", url, true);
	
	request.onreadystatechange = function() { sendback = updateDiv(obj, callback); }
	
	request.send(null);
}

function updateDiv(obj, callback){
	
	if (request.readyState == 4) {
		
		var data = request.responseText;
		
		
		if(typeof callback == 'object'){
			//look for a callback function in this obj
			for(prop in callback){
									
				if(typeof callback[prop] =='function'){
		
					callback[prop](data);	
				}
			}
		}
		else if(typeof callback == 'function'){
			callback(data);
		}
	}
	
	return false;
}

function makeQueryString(obj){
	
	var query = [];
	
	for(var key in obj){
		
		if(typeof (obj[key]) == "string" || typeof (obj[key]) == "number"){
		
			query.push(key + "=" + encodeURIComponent(obj[key]));
		
		}
														 
	}

	return query.join('&');

}

function processPosChange() {
    //page loaded "complete"
    if (pos.readyState == 4) {
        // page is "OK"
        if (pos.status == 200){
			if(grabPosXML("posStatus") == 'NOTOK') { 
				alert('There were problems Sending Email. Please check back in a couple minutes');
			}
		}
	}
	return false;
}