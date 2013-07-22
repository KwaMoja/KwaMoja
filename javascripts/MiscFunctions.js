function defaultControl(c){
c.select();
c.focus();
}
function makeAlert(message, title) {
	theme=document.getElementById("Theme").value;
	document.getElementById("mask").style['display'] = "inline";
	html = '<div id="dialog_header"><img src="css/'+theme+'/images/help.png" />'+title+
		'</div><img style="float: left;vertical-align:middle" src="css/'+theme+'/images/alert.png" /><div id="dialog_main">'+message;
	html = html + '</div><div id="dialog_buttons"><input type="submit" class="okButton" value="OK" onClick="hideAlert()" /></div>'
	document.getElementById("dialog").innerHTML = html;
	document.getElementById("dialog").style.marginTop = -(document.getElementById('dialog').offsetHeight)+"px";
	document.getElementById("dialog").style.marginLeft = -(document.getElementById('dialog').offsetWidth/2)+"px";
	return false;
}
function hideAlert(){
	document.getElementById("dialog").innerHTML = '';
	document.getElementById("mask").style['display'] = "none";
	return true;
}
function MakeConfirm(m, t, l) {
url=l.href;
th=document.getElementById("Theme").value;
document.getElementById("mask").style['display'] = "inline";
h='<div id="dialog_header"><img src="css/'+th+'/images/help.png" />'+t+'</div><div id="dialog_main">'+m;
h=h+'</div><div id="dialog_buttons"><input type="submit" class="okButton" value="Cancel" onClick="hideConfirm(\'\')" />'
h=h+'<a href="'+url+'" ><input type="submit" class="okButton" value="OK" onClick="hideConfirm(\'OK\')" /></a></div></div>'
document.getElementById("dialog").innerHTML = h;
document.getElementById("dialog").style.marginTop = -(document.getElementById('dialog').offsetHeight)+"px";
document.getElementById("dialog").style.marginLeft = -(document.getElementById('dialog').offsetWidth/2)+"px";
return false;
}
function hideConfirm(result){
if (result=='') {
document.getElementById("dialog").innerHTML = '';
document.getElementById("mask").style['display'] = "none";
}
return true;
}
function isInteger(s) {
return (s.toString().search(/^-?[0-9]+$/) == 0);
}
function validateEmail(email) {
var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
return re.test(email);
}
function ReloadForm(fB){
fB.click();
}
function ShowTable(t){
	document.getElementById(t).style["display"] = "table";
}
function HideTable(t){
	document.getElementById(t).style["display"] = "none";
}
function rTN(event){
if (window.event) k=window.event.keyCode;
else if (event) k=event.which;
else return true;
kC=String.fromCharCode(k);
if ((k==null) || (k==0) || (k==8) || (k==9) || (k==13) || (k==27)) return true;
else if ((("0123456789.,-").indexOf(kC)>-1)) return true;
else return false;
}
function rTI(event){
if (window.event) k=window.event.keyCode;
else if (event) k=event.which;
else return true;
kC=String.fromCharCode(k);
if ((k==null) || (k==0) || (k==8) || (k==9) || (k==13) || (k==27)) return true;
else if ((("0123456789").indexOf(kC)>-1)) return true;
else return false;
}
function assignComboToInput(c,i){
	i.value=c.value;
}
function inArray(v,tA,m){
	for (i=0;i<tA.length;i++) {
		if (v==tA[i].value) {
			return true;
		}
	}
	makeAlert(m, 'Error');
	return false;
}
function isDate(dS,dF){
	var mA=dS.match(/^(\d{1,2})(\/|-|.)(\d{1,2})(\/|-|.)(\d{4})$/);
	if (mA==null){
		makeAlert("Please enter the date in the format "+dF, 'Date Error');
		return false;
	}
	if (dF=="d/m/Y"){
		d=mA[1];
		m=mA[3];
	}else{
		d=mA[3];
		m=mA[1];
	}
	y=mA[5];
	if (m<1 || m>12){
		makeAlert("Month must be between 1 and 12", 'Date Error');
		return false;
	}
	if (d<1 || d>31){
		makeAlert("Day must be between 1 and 31", 'Date Error');
		return false;
	}
	if ((m==4 || m==6 || m==9 || m==11) && d==31){
		makeAlert("Month "+m+" doesn`t have 31 days", 'Date Error');
		return false;
	}
	if (m==2){
		var isleap=(y%4==0);
		if (d>29 || (d==29 && !isleap)){
			makeAlert("February "+y+" doesn`t have "+d+" days", 'Date Error');
			return false;
		}
	}
	return true;
}
function eitherOr(o,t){
	if (o.value!='') t.value='';
	else if (o.value=='NaN') o.value='';
}
/*Renier & Louis (info@tillcor.com) 25.02.2007
Copyright 2004-2007 Tillcor International
*/
days=new Array('Su','Mo','Tu','We','Th','Fr','Sa');
months=new Array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
dateDivID="calendar";
function Calendar(md,dF){
	iF=document.getElementsByName(md).item(0);
	pB=iF;
	x=pB.offsetLeft;
	y=pB.offsetTop+pB.offsetHeight;
	var p=pB;
	while (p.offsetParent){
		p=p.offsetParent;
		x+=p.offsetLeft;
		y+=p.offsetTop;
	}
	dt=convertDate(iF.value,dF);
	nN=document.createElement("div");
	nN.setAttribute("id",dateDivID);
	nN.setAttribute("style","visibility:hidden;");
	document.body.appendChild(nN);
	cD=document.getElementById(dateDivID);
	cD.style.position="absolute";
	cD.style.left=x+"px";
	cD.style.top=y+"px";
	cD.style.visibility=(cD.style.visibility=="visible" ? "hidden" : "visible");
	cD.style.display=(cD.style.display=="block" ? "none" : "block");
	cD.style.zIndex=10000;
	drawCalendar(md,dt.getFullYear(),dt.getMonth(),dt.getDate(),dF);
}
function drawCalendar(md,y,m,d,dF){
	var tD=new Date();
	if ((m>=0) && (y>0)) tD=new Date(y,m,1);
	else{
		d=tD.getDate();
		tD.setDate(1);
	}
	TR="<tr>";
	xTR="</tr>";
	TD="<td class='dpTD' onMouseOut='this.className=\"dpTD\";' onMouseOver='this.className=\"dpTDHover\";'";
	xTD="</td>";
	html="<table class='dpTbl'>"+TR+"<th colspan=\"3\">"+months[tD.getMonth()]+" "+tD.getFullYear()+"</th>"+"<td colspan=\"2\">"+
	getButtonCode(md,tD,-1,"&lt;",dF)+xTD+"<td colspan=\"2\">"+getButtonCode(md,tD,1,"&gt;",dF)+xTD+xTR+TR;
	for(i=0;i<days.length;i++) html+="<th>"+days[i]+"</th>";
		html+=xTR+TR;
	for (i=0;i<tD.getDay();i++) html+=TD+"&nbsp;"+xTD;
	do{
		dN=tD.getDate();
		TD_onclick=" onclick=\"postDate('"+md+"','"+formatDate(tD,dF)+"');\">";
		if (dN==d) html+="<td"+TD_onclick+"<div class='dpDayHighlight'>"+dN+"</div>"+xTD;
		else html+=TD+TD_onclick+dN+xTD;
		if (tD.getDay()==6) html+=xTR+TR;
		tD.setDate(tD.getDate()+1);
	} while (tD.getDate()>1)
	if (tD.getDay()>0) for (i=6;i>tD.getDay();i--) html+=TD+"&nbsp;"+xTD;
		html+="</table>";
	document.getElementById(dateDivID).innerHTML=html;
}
function getButtonCode(mD,dV,a,lb,dF){
	nM=(dV.getMonth()+a)%12;
	nY=dV.getFullYear()+parseInt((dV.getMonth()+a)/12,10);
if (nM<0){
	nM+=12;
	nY+=-1;
}
return "<button onClick='drawCalendar(\""+mD+"\","+nY+","+nM+","+1+",\""+dF+"\");'>"+lb+"</button>";
}
function formatDate(dV,dF){
	ds=String(dV.getDate());
	ms=String(dV.getMonth()+1);
	d=("0"+dV.getDate()).substring(ds.length-1,ds.length+1);
	m=("0"+(dV.getMonth()+1)).substring(ms.length-1,ms.length+1);
	y=dV.getFullYear();
	switch (dF) {
		case "d/m/Y":
			return d+"/"+m+"/"+y;
		case "d.m.Y":
			return d+"."+m+"."+y;
		case "Y/m/d":
			return y+"/"+m+"/"+d;
		case "Y-m-d":
			return y+"-"+m+"-"+d;
		default :
			return m+"/"+d+"/"+y;
	}
}
function convertDate(dS,dF){
	var d,m,y;
	if (dF=="d.m.Y")
		dA=dS.split(".");
	else
		dA=dS.split("/");
	switch (dF){
		case "d/m/Y":
			d=parseInt(dA[0],10);
			m=parseInt(dA[1],10)-1;
			y=parseInt(dA[2],10);
			break;
	case "d.m.Y":
		d=parseInt(dA[0],10);
		m=parseInt(dA[1],10)-1;
		y=parseInt(dA[2],10);
		break;
	case "Y/m/d":
		d=parseInt(dA[2],10);
		m=parseInt(dA[1],10)-1;
		y=parseInt(dA[0],10);
		break;
	default :
		d=parseInt(dA[1],10);
		m=parseInt(dA[0],10)-1;
		y=parseInt(dA[2],10);
		break;
}
return new Date(y,m,d);
}
function postDate(mydate,dS){
var iF=document.getElementsByName(mydate).item(0);
iF.value=dS;
var cD=document.getElementById(dateDivID);
cD.style.visibility="hidden";
cD.style.display="none";
iF.focus();
}
function clickDate(){
	Calendar(this.name,this.alt);
}
function changeDate(){
	isDate(this.value,this.alt);
}
function VerifyForm(f) {
	Clean=true;
	Alert='';
	for(var i=0,fLen=f.length;i<fLen;i++){
		if(f.elements[i].type=='text') {
			var a=document.getElementsByName(f.elements[i].name);
			Class=a[0].getAttribute("class");
			if ((a[0].getAttribute("minlength")>f.elements[i].value.length)) {
				if (f.elements[i].value.length==0) {
					Alert=Alert+'You must input a value in the field '+a[0].getAttribute("name")+'<br />';
				} else {
					Alert=Alert+a[0].getAttribute("name")+' field must be at least '+a[0].getAttribute("minlength")+' characters long'+'<br />';
				}
				a[0].className=Class+' inputerror';
				Clean=false;
			} else {
				a[0].className=Class;
			}
		}
		if(f.elements[i].type=='select-one') {
			Class=f.elements[i].getAttribute("class");
			if ((f.elements[i].getAttribute("minlength")>0) && (f.elements[i].value.length==0)) {
				Alert=Alert+'You must make a selection in the field '+f.elements[i].getAttribute("name")+'<br />';
				f.elements[i].className=Class+' inputerror';
				Clean=false;
			}
		}
		if(f.elements[i].type=='password') {
			Class=f.elements[i].getAttribute("class");
			if ((f.elements[i].getAttribute("minlength")>0) && (f.elements[i].value.length==0)) {
				Alert=Alert+'You must make a selection in the field '+f.elements[i].getAttribute("name")+'<br />';
				f.elements[i].className=Class+' inputerror';
				Clean=false;
			}
		}
		if(f.elements[i].type=='email') {
			Class=f.elements[i].getAttribute("class");
			if ((f.elements[i].value.length>0) && (!validateEmail(f.elements[i].value))) {
				Alert=Alert+'You have not entered a valid email address <br />';
				f.elements[i].className=Class+' inputerror';
				Clean=false;
			}
		}
	}
	if (Alert!='') {makeAlert(Alert, 'Input Error');}
	return Clean;
}
function SortSelect(selElem) {
	var tmpArray = new Array();
	th=document.getElementById("Theme").value;
	columnText=selElem.innerHTML;
	parentElem=selElem.parentNode;
	table=parentElem.parentNode;
	row = table.rows[0];
	for (var j = 0, col; col = row.cells[j]; j++) {
		if (row.cells[j].innerHTML==columnText) {
			columnNumber=j;
			s=getComputedStyle(row.cells[j], null);
			if (s.cursor=="s-resize") {
				row.cells[j].style.cursor="n-resize";
				row.cells[j].style.backgroundImage="url('css/"+th+"/images/descending.png')";
				row.cells[j].style.backgroundPosition="right center";
				row.cells[j].style.backgroundRepeat="no-repeat";
				row.cells[j].style.backgroundSize="12px";
				direction="a";
			} else {
				row.cells[j].style.cursor="s-resize";
				row.cells[j].style.backgroundImage="url('css/"+th+"/images/ascending.png')";
				row.cells[j].style.backgroundPosition="right center";
				row.cells[j].style.backgroundRepeat="no-repeat";
				row.cells[j].style.backgroundSize="12px";
				direction="d";
			}
		}
	}
	for (var i = 1, row; row = table.rows[i]; i++) {
		var rowArray = new Array();
		for (var j = 0, col; col = row.cells[j]; j++) {
			if (row.cells[j].tagName == 'TD' ) {
				rowArray[j]=row.cells[j].innerHTML;
				columnClass=row.cells[columnNumber].className;
			}
		}
		tmpArray[i]=rowArray;
	}
	tmpArray.sort(
		function(a,b) {
			if (direction=="a") {
				if (columnClass=="number") {
					return parseFloat(a[columnNumber])-parseFloat(b[columnNumber]);
				} else if (columnClass=="date") {
					da=new Date(a[columnNumber]);
					db=new Date(b[columnNumber]);
					return da>db;
				} else {
					return a[columnNumber].localeCompare(b[columnNumber])
				}
			} else {
				if (columnClass=="number") {
					return parseFloat(b[columnNumber])-parseFloat(a[columnNumber]);
				} else if (columnClass=="date") {
					da=new Date(a[columnNumber]);
					db=new Date(b[columnNumber]);
					return da<=db;
				} else {
					return b[columnNumber].localeCompare(a[columnNumber])
				}
			}
		}
	);
	for (var i = 0, row; row = table.rows[i+1]; i++) {
		var rowArray = new Array();
		rowArray=tmpArray[i];
		for (var j = 0, col; col = row.cells[j]; j++) {
			if (row.cells[j].tagName == 'TD' ) {
				row.cells[j].innerHTML=rowArray[j];
			}
		}
	}
	return;
}
function remSelOpt(inp1, sel1){
	len1 = sel1.options.length;
	for (i=0;i<len1 ;i++ ){
		if (sel1.options[i].value == inp1){
			sel1.options[i] = null;
			break;
		}
	}
}
function AddScript(Script, Title) {
	theme=document.getElementById("Theme").value;
	document.getElementById("favourites").innerHTML=document.getElementById("favourites").innerHTML+'<option value="'+Script+'">'+Title+'</option>';
	document.getElementById("PlusMinus").src="css/"+theme+"/images/subtract.png";
	document.getElementById("PlusMinus").setAttribute( "onClick", "javascript: RemoveScript('"+Script+"', '"+Title+"');" );
	UpdateFavourites(Script, Title);
}
function RemoveScript(Script, Title) {
	theme=document.getElementById("Theme").value;
	remSelOpt(Script, document.getElementById("favourites"));
	document.getElementById("PlusMinus").src="css/"+theme+"/images/add.png";
	document.getElementById("PlusMinus").setAttribute( "onClick", "javascript: AddScript('"+Script+"', '"+Title+"');" );
	UpdateFavourites(Script, Title);
}

function UpdateFavourites(Script, Title) {
	Target='UpdateFavourites.php?Script='+Script+'&Title='+Title;
	if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
	} else {// code for IE6, IE5
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.open("GET",Target,true);
	xmlhttp.send();
	return false;
}
function initial(){
	if (document.getElementsByTagName){
		var as=document.getElementsByTagName("a");
		for (i=0;i<as.length;i++){
			var a=as[i];
			if (a.getAttribute("href") &&
				a.getAttribute("rel")=="external")
				a.target="_blank";
		}
	}
	var ds=document.getElementsByTagName("input");
	for (i=0;i<ds.length;i++){
		if (ds[i].className=="date"){
			ds[i].onclick=clickDate;
			ds[i].onchange=changeDate;
		}
		if (ds[i].className=="number") ds[i].onkeypress=rTN;
		if (ds[i].className=="integer") ds[i].onkeypress=rTI;
	}
}
window.onload=initial;