// Cookie Class

function Cookie(name){
	this.$name = name; // Remember the name of this cookie
	
	var allcookies = document.cookie;
			
	if(allcookies=="") { return };
		
	var cookies = allcookies.split(";"); //split multiple cookies if there are more than 1

	var cookie = null;

	for(var i=0; i < cookies.length; i++){
		//Look for the specified cookie name		
		if(cookies[i].trim().substr(0, name.length+1) == (name + "=")){
			cookie = cookies[i].trim(); //found existing cookie by our passed name !! Bug Fixed by triming the cookie, was adding whitespace!
			break;
		}
	}
	
	if(cookie == null) { return false }; //this must be a new cookie. we can leave now
	
	var cookieval = cookie.substring(name.length+1);
	
	var a = cookieval.split("&"); // break name value/pairs
	for(var i=0; i < a.length; i++){
		a[i] = a[i].split(":");	
	}
	
	for(var i=0; i < a.length; i ++){
		this[a[i][0]] = decodeURIComponent(a[i][1]);
	}
}

/* Cookie Store Method */
Cookie.prototype.store = function(daysToLive, path, domain, secure){
	//check for illegal chars
	var cookieval = ""; 
	for(var prop in this){
		//Ignore properties with names that start with '$' as well as methods
		if((prop.charAt(0)=='$') || ((typeof this[prop]) == 'function')){ continue; }
		if(cookieval != "") {  cookieval += '&'; }
		//alert("storing ... " + prop);
		cookieval += prop + ':' + encodeURIComponent(this[prop]);
	}
	var data = '';
	
	for(var prop in this){
		if(typeof this[prop] == "function") continue;
		data += "["+prop+"] = " + this[prop] + "\n";
	}
	
	var cookie = this.$name + '=' + cookieval;
	
	if(daysToLive || daysToLive ==0){
		cookie += "; max-age=" + (daysToLive*24*60*60);
	}
	
	if(path) { cookie += "; path=" + path; }
	if(domain) { cookie += "; domain=" + domain; }
	if(secure) { secure += "; secure=" + secure; }
	
	//now finally store the cookie
	document.cookie = cookie;
}

/* Cookie Remove Method */
Cookie.prototype.remove = function(path, domain, secure){
	//Delete the properties of the cookie
	for(var prop in this){
		if(prop.charAt(0) != '$' && typeof this[prop] != 'function'){
			delete this[prop];	
		}
	}
	//now store the cookie with a lifetime of 0
	this.store(0, path, domain, secure);
}

/* Cookie Enabled Check */
Cookie.prototype.enabled = function(){
	// try navigator
	if(navigator.cookieEnabled != undefined) { return navigator.cookieEnabled; }
	
	//If we've already cached a value - use that
	if(Cookie.enabled.cache != undefined) { return Cookie.enabled.cache; }
	
	//Otherwise create a test cookie with a lifetime
	document.cookie = "testcookie=test; max-age=10000"; 
	
	//now check to see if the cookie was saved
	var cookies = document.cookie;
	if(cookies.indexOf("testcookie=test") == -1){
		//the cookie was not saved
		return Cookie.enabled.cache = false;	
	}
	else{
		//Cookie was saved - remove test cookie and return true
		document.cookie = "testcookie=test; max-age=0";
		return Cookie.enabled.cache = true;	
	}
}
