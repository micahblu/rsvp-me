// Simple Calender Creator
// By Brian Gosselin of http://scriptasylum.com

// Usage:  buildCal(month, year, main_css_classname, header_css_classname, Sat-Sun_css_classname, days_css_classname, border_thickness, starting_day, date_onclick_handler)
//         Pass the result to the document.write() method. ex: document.write( buildCal(<parameters>) );

// Release History:
// V1.0 - Initial Release
// V1.1 - Added revisions made by DynamicDrive.com (changes denoted by the letters "DD").
// V1.2 - Added ability to specify the starting day of the week (Sun, Mon, Tue, etc)
// V1.3 - Added ability to execute a function when a date is clicked. Fixed a minor visual bug in Mozilla.

var cal = {};
//default properties
cal.mnth=new Date().getMonth()+1;
cal.year=new Date().getFullYear();
cal.strtD=1;

cal.el = "";
cal.onstage = false;
cal.field = "";
cal.path = "";

cal.buildCal = function(m, y, cM, cH, cDW, cD, brdr, sDay, action){
var mn=['January','February','March','April','May','June','July','August','September','October','November','December'];
var dim=[31,0,31,30,31,30,31,31,30,31,30,31];

if(sDay<1 || sDay>7) { sDay=1; }

var oD = new Date(y, m-1, 1); //DD replaced line to fix date bug when current day is 31st
oD.od=oD.getDay()+1; //DD replaced line to fix date bug when current day is 31st

var tmpod=oD.od-sDay;
if(tmpod<0) { tmpod=7+tmpod; }
oD.od=tmpod+1;

var wkstr="SMTWTFS".substr(sDay-1,"SMTWTFS".length)+"SMTWTFS".substr(0,sDay-1);

var todaydate=new Date(); //DD
var curM = (todaydate.getMonth()+1);
var curY = todaydate.getFullYear();
var curD = todaydate.getDate();
var scanfortoday = curY + "-" + curM + "-" + curD; //DD

var selectable = false;
var dayclass = "nonselectable"; //default for selectable days
var c = ""; //this var will hold the day's content

dim[1] = (((oD.getFullYear()%100!==0) && (oD.getFullYear()%4===0))||(oD.getFullYear()%400===0)) ? 29 : 28;

var t = "<div id='dyncal'>\n";

t+="<div style='position:absolute; top:-5px; left: -5px;' onclick='cal.remove()'><img src='"+this.path+"/images/x.png' alt='close' /></div>\n";
t+='<div class="'+cM+'">\n';
t+='<table class="'+cM+'" cellpadding="3" border="0" cellspacing="0">\n';

t+='<tr align="center"><td colspan="7" align="center" valign="top" class="'+cH+'">\n';
t+='<table width="100%" cellpadding="0" cellspacing="0" class="'+cM+'">\n';
t+='<tr><td>\n';
t+='<a href="" onclick="cal.adjM(0); return false;" class="monthArrow" style="float:left;"><b> &laquo; </b></a>\n';
t+='</td><td align="center"><span class="calmonth">\n';
t+= mn[m-1]+' - '+y;
t+='</span></td><td>\n';
t+='<a href="" onclick="cal.adjM(1); return false;" class="monthArrow" style="float:right;"><b> &raquo; </b></a>\n';
t+='</td></tr></table>\n';
t+='</td></tr>\n';

t+='<tr align="center">\n';
for(var s=0;s<7;s++) { t+='<td class="'+cDW+'">'+wkstr.substr(s,1)+'</td>\n'; }
t+='</tr><tr align="center">\n';
for(var i=1;i<=42;i++){
	var day = i-oD.od+1;
	var ymd = y + "-" + m + "-" + day;
	//check for today
	if (ymd == scanfortoday){
		//Today has been found. from here we'll allow dates to be selectable
		selectable = true;
		dayclass = "selectable";
		c = '<span class="today" onmouseup="'+action+'('+m+','+day+','+y+')">'+day+'</span>\n';
	}//DD
	else{
		//make sure all future months and years are selectable
		if(y == curY){ if(m > curM){ selectable = true; dayclass = "selectable" } }
		if(y > curY){ selectable = true; dayclass = "selectable" }
		
		//prepare inner cell div
		c =( (i-oD.od>=0) && (i-oD.od<dim[m-1]) ) ? 
		'<div style="width:100%" class="'+dayclass+'" '+((action!='' && selectable ) ?
		'onclick="'+action+'('+m+','+day+','+y+')' 
		: day)+'">'+day+'</div>' 
		: '&nbsp;\n';
	}

	t+='<td width="14.28%" class="'+cD+'">'+c+'</td>\n';
	if(((i)%7===0)&&(i<36)) { t+='</tr><tr align="center">\n'; }
}
t +='</tr></table></div>\n';
return t;
};

//ADJUSTED MONTH FUNCTION THAT WILL TRIGGER NEXT AND PREVIOUS YEARS. BY MICAH BLU WWW.BLUPRINTSMEDIA.NET
cal.adjM = function(I){
if(I){
this.mnth=(this.mnth==12)?1:this.mnth+1;
	if(this.mnth == 1){
	this.adjY(1);
	}
}else{
	this.mnth=(this.mnth==1)?12:this.mnth-1;
	if(this.mnth == 12){
	this.adjY(0);
	}
}
this.changeHTML();
}

cal.adjY = function(I){
if(I){
this.year=(this.year==2030)?1970:this.year+1;
}else{
this.year=(this.year==1970)?2030:this.year-1;
}
this.changeHTML();
}

cal.adjD = function(){
this.strtD=(this.strtD==7)?1:this.strtD+1;
document.forms["f"].daybut.value="Day 1 = "+["Sun","Mon","Tue","Wed","Thu","Fri","Sat"][this.strtD-1];
this.changeHTML();
}

//FUNCTION TO FIND NESTED NN4 LAYERS BY MIKE HALL OF WWW.BRAINJAR.COM
cal.findlayer = function(name,doc){
	var i,layer;
	for(i=0;i<doc.layers.length;i++){
		layer=doc.layers[i];
		if(layer.name==name)return layer;
		if(layer.document.layers.length>0)
		if((layer=findlayer(name,layer.document))!=null)
		return layer;
	}
	return null;
}

cal.changeHTML = function(){
	//var el=(document.layers)? this.findlayer("dyncal",document):(document.all)?document.all["dyncal"]:document.getElementById("dyncal");
	var el = this.el;
	var html=this.buildCal(this.mnth,this.year,"calMAIN","calHEADER","calDW","calDAYS",1,1,'cal.updateField');
	if(document.layers){
		el.document.open();
		el.document.write('<div class="abc">'+html+'</div>');
		el.document.close();
	}else el.innerHTML=html;
}

cal.zfill = function(n){
return (n.toString().length<2)?"0"+n:""+n;
}

cal.updateField = function(mm,dd,y){
	var date = y + "-" + mm + "-" + dd;
	this.field.value = date;
	this.remove();
}



cal.appendCalendar = function( el, w, h, path){
	
	if(this.onstage) return false;
	
	cal.path = path;
	
	var newel = document.createElement("div");
	
	newel.id = "mydiv";
	newel.style.background = "white";
	newel.style.padding = "8px";
	
	var wrapper = document.createElement("div");
	
	wrapper.style.position = "relative";
	//wrapper.style.width = w+"px";
	//wrapper.style.height = h+"px";
	
	newel.style.position = "absolute";
	newel.style.zIndex = 999;
	newel.style.top = "0px";
	newel.style.left = "0px";	
	
	wrapper.appendChild(newel);
	el.parentNode.appendChild(wrapper);
	
	
	newel.innerHTML = cal.buildCal(this.mnth,this.year,"calMAIN","calHEADER","calDW","calDAYS",1,1,"cal.updateField");
	
	//adjustPage(newel);
	
	this.onstage = true;
	this.el = newel;
	this.field = el;
	
}

cal.remove = function(){
	this.el.parentNode.removeChild(this.el);
	this.el = "";
	this.onstage = false;
}