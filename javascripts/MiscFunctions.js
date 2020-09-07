function makeAlert(e, t) {
	theme = localStorage.Theme;
	document.getElementById("mask").style["display"] = "inline";
	html = '<div id="dialog_header"><img class="dialog_image" src="css/' + theme + '/images/help.png" />' + t + '</div><img style="float: left;vertical-align:middle" src="css/' + theme + '/images/alert.png" /><div id="dialog_main">' + e;
	html = html + '</div><div id="dialog_buttons"><input type="submit" class="okButton" value="OK" onClick="hideAlert()" /></div>';
	document.getElementById("dialog").innerHTML = html;
	document.getElementById("dialog").style.marginTop = -document.getElementById("dialog").offsetHeight + "px";
	document.getElementById("dialog").style.marginLeft = -(document.getElementById("dialog").offsetWidth / 2) + "px";
	return false
}

function hideAlert() {
	document.getElementById("dialog").innerHTML = "";
	document.getElementById("mask").style["display"] = "none";
	return true
}

function MakeConfirm(e, t, n) {
	url = n.href;
	th = localStorage.Theme;
	document.getElementById("mask").style["display"] = "inline";
	h = '<div id="dialog_header"><img class="dialog_image" src="css/' + th + '/images/help.png" />' + t + '</div><div id="dialog_main">' + e;
	h = h + '</div><div id="dialog_buttons"><input type="submit" class="okButton" value="Cancel" onClick="hideConfirm(\'\')" />';
	h = h + '<a class="ButtonLink" href="' + url + '" ><input type="submit" class="okButton" value="OK" onClick="hideConfirm(\'OK\')" /></a></div></div>';
	document.getElementById("dialog").innerHTML = h;
	document.getElementById("dialog").style.marginTop = -document.getElementById("dialog").offsetHeight + "px";
	document.getElementById("dialog").style.marginLeft = -(document.getElementById("dialog").offsetWidth / 2) + "px";
	return false
}

function hideConfirm(e) {
	if (e == "") {
		document.getElementById("dialog").innerHTML = "";
		document.getElementById("mask").style["display"] = "none"
	}
	return true
}

function expandTable(e) {
	parent=e.cells[0].innerHTML;
	table=e.parentNode;
	for (var r = 1, i; i = table.rows[r]; r++) {
		if (i.cells[3].innerHTML == parent) {
			i.className='visible striped_row';
		}
	}
	e.onclick=function onclick(event) {collapseTable(this)};
	return true
}

function collapseTable(e) {
	parent=e.cells[0].innerHTML;
	table=e.parentNode;
	for (var r = 1, i; i = table.rows[r]; r++) {
		if (i.cells[3].innerHTML == parent) {
			i.className='invisible';
		}
	}
	e.onclick=function onclick(event) {expandTable(this)};
	return true
}

function isInteger(e) {
	return e.toString().search(/^-?[0-9]+$/) == 0
}

function validateEmail(e) {
	var t = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	return t.test(e)
}

function ReloadForm(e) {
	e.click()
}

function clickInputByValue(value) {
    var allInputs = document.getElementsByTagName("input");
    var results = [];
    for(var x=0;x<allInputs.length;x++)
        if(allInputs[x].value == value)
            results.push(allInputs[x]);
    results[0].click();
}

function ShowTable(e) {
	document.getElementById(e).style["display"] = "table"
}

function HideTable(e) {
	document.getElementById(e).style["display"] = "none"
}

function rTN(e) {
	if (window.event) k = window.event.keyCode;
	else if (e) k = e.which;
	else return true;
	kC = String.fromCharCode(k);
	if (k == null || k == 0 || k == 8 || k == 9 || k == 13 || k == 27) return true;
	else if ("0123456789.,-".indexOf(kC) > -1) return true;
	else return false
}

function rTI(e) {
	if (window.event) k = window.event.keyCode;
	else if (e) k = e.which;
	else return true;
	kC = String.fromCharCode(k);
	if (k == null || k == 0 || k == 8 || k == 9 || k == 13 || k == 27) return true;
	else if ("0123456789".indexOf(kC) > -1) return true;
	else return false
}

function assignComboToInput(e, t) {
	t.value = e.value
}

function inArray(e, t, n) {
	for (i = 0; i < t.length; i++) {
		if (e.value == t[i].value) {
			return true
		}
	}
	makeAlert(n, "Error");
	return false
}

function isDate(e, t) {
	var n = e.match(/^(\d{1,2})(\/|-|.)(\d{1,2})(\/|-|.)(\d{4})$/);
	if (n == null) {
		makeAlert("Please enter the date in the format " + t, "Date Error");
		return false
	}
	if (t == "d/m/Y") {
		d = n[1];
		m = n[3]
	} else {
		d = n[3];
		m = n[1]
	}
	y = n[5];
	if (m < 1 || m > 12) {
		makeAlert("Month must be between 1 and 12", "Date Error");
		return false
	}
	if (d < 1 || d > 31) {
		makeAlert("Day must be between 1 and 31", "Date Error");
		return false
	}
	if ((m == 4 || m == 6 || m == 9 || m == 11) && d == 31) {
		makeAlert("Month " + m + " doesn`t have 31 days", "Date Error");
		return false
	}
	if (m == 2) {
		var r = y % 4 == 0;
		if (d > 29 || d == 29 && !r) {
			makeAlert("February " + y + " doesn`t have " + d + " days", "Date Error");
			return false
		}
	}
	return true
}

function eitherOr(e, t) {
	if (e.value != "") t.value = "";
	else if (e.value == "NaN") e.value = ""
}

function Calendar(e, t) {
	iF = document.getElementsByName(e).item(0);
	pB = iF;
	x = pB.offsetLeft;
	y = pB.offsetTop + pB.offsetHeight;
	var n = pB;
	while (n.offsetParent) {
		n = n.offsetParent;
		x += n.offsetLeft;
		y += n.offsetTop
	}
	dt = convertDate(iF.value, t);
	nN = document.createElement("div");
	nN.setAttribute("id", dateDivID);
	nN.setAttribute("style", "visibility:hidden;");
	document.body.appendChild(nN);
	cD = document.getElementById(dateDivID);
	cD.style.position = "absolute";
	cD.style.left = x + "px";
	cD.style.top = y + "px";
	cD.style.visibility = cD.style.visibility == "visible" ? "hidden" : "visible";
	cD.style.display = cD.style.display == "block" ? "none" : "block";
	cD.style.zIndex = 1e4;
	drawCalendar(e, dt.getFullYear(), dt.getMonth(), dt.getDate(), t)
}

function drawCalendar(e, t, n, r, s) {
	var o = new Date;
	if (n >= 0 && t > 0) o = new Date(t, n, 1);
	else {
		r = o.getDate();
		o.setDate(1)
	}
	TR = "<tr>";
	xTR = "</tr>";
	TD = "<td class='dpTD' onMouseOut='this.className=\"dpTD\";' onMouseOver='this.className=\"dpTDHover\";'";
	xTD = "</td>";
	html = '<table class="dpTbl">' + TR + '<th colspan="3">' + months[o.getMonth()] + " " + o.getFullYear() + "</th>" + '<td colspan="2">' + getButtonCode(e, o, -1, "<", s) + xTD + '<td colspan="2">' + getButtonCode(e, o, 1, ">", s) + xTD + xTR + TR;
	for (i = 0; i < days.length; i++) html += "<th>" + days[i] + "</th>";
	html += xTR + TR;
	for (i = 0; i < o.getDay(); i++) html += TD + " " + xTD;
	do {
		dN = o.getDate();
		TD_onclick = " onclick=\"postDate('" + e + "','" + formatDate(o, s) + "');\">";
		if (dN == r) html += "<td" + TD_onclick + "<div class='dpDayHighlight'>" + dN + "</div>" + xTD;
		else html += TD + TD_onclick + dN + xTD; if (o.getDay() == 6) html += xTR + TR;
		o.setDate(o.getDate() + 1);
	} while (o.getDate() > 1);
	if (o.getDay() > 0)
		for (i = 6; i > o.getDay(); i--) html += TD + " " + xTD;
	html += "</table>";
	document.getElementById(dateDivID).innerHTML = html
}

function getButtonCode(e, t, n, r, i) {
	nM = (t.getMonth() + n) % 12;
	nY = t.getFullYear() + parseInt((t.getMonth() + n) / 12, 10);
	if (nM < 0) {
		nM += 12;
		nY += -1
	}
	return "<button onClick='drawCalendar(\"" + e + '",' + nY + "," + nM + "," + 1 + ',"' + i + "\");'>" + r + "</button>"
}

function formatDate(e, t) {
	ds = String(e.getDate());
	ms = String(e.getMonth() + 1);
	d = ("0" + e.getDate()).substring(ds.length - 1, ds.length + 1);
	m = ("0" + (e.getMonth() + 1)).substring(ms.length - 1, ms.length + 1);
	y = e.getFullYear();
	switch (t) {
	case "d/m/Y":
		return d + "/" + m + "/" + y;
	case "d.m.Y":
		return d + "." + m + "." + y;
	case "Y/m/d":
		return y + "/" + m + "/" + d;
	case "Y-m-d":
		return y + "-" + m + "-" + d;
	default:
		return m + "/" + d + "/" + y
	}
}

function convertDate(dS, dF) {
	// Converts a date in DefaultDateFormat into a javascript date-object.
	// dS: Date to convert.
	// dF: Date format. Formats: "d/m/Y", "d.m.Y", "m/d/Y", "Y-m-d", "Y/m/d".
	var y, m, d;
	switch(dF) {
		case "d/m/Y":
			dA = dS.split("/");
			d = parseInt(dA[0], 10);
			m = parseInt(dA[1], 10)-1;
			y = parseInt(dA[2], 10);
			break;
		case "d.m.Y":
			dA = dS.split(".");
			d = parseInt(dA[0], 10);
			m = parseInt(dA[1], 10)-1;
			y = parseInt(dA[2], 10);
			break;
		case "m/d/Y":
			dA = dS.split("/");
			m = parseInt(dA[0], 10)-1;
			d = parseInt(dA[1], 10);
			y = parseInt(dA[2], 10);
			break;
		case "Y-m-d":
			dA = dS.split("-");
			y = parseInt(dA[0], 10);
			m = parseInt(dA[1], 10)-1;
			d = parseInt(dA[2], 10);
			break;
		case "Y/m/d":
			dA = dS.split("/");
			y = parseInt(dA[0], 10);
			m = parseInt(dA[1], 10)-1;
			d = parseInt(dA[2], 10);
			break;
		default:
			alert("Unknown date format " + dF);
			return false;
	}
return new Date(y, m, d);
}

function postDate(e, t) {
	var n = document.getElementsByName(e).item(0);
	n.value = t;
	var r = document.getElementById(dateDivID);
	r.style.visibility = "hidden";
	r.style.display = "none";
	n.focus()
}

function clickDate() {
	Calendar(this.name, localStorage.DateFormat)
}

function changeDate() {
	isDate(this.value, localStorage.DateFormat)
}

function SortSelect() {
	selElem = this;
	var e = new Array;
	th = localStorage.Theme;
	columnText = selElem.innerHTML;
	TableHeader = selElem.parentNode;
	TableBodyElements = TableHeader.parentNode.parentNode.getElementsByTagName('tbody');
	table = TableBodyElements[0];
	i = TableHeader;
	for (var t = 0, n; n = i.cells[t]; t++) {
		if (i.cells[t].innerHTML == columnText) {
			columnNumber = t;
			s = getComputedStyle(i.cells[t], null);
			if (s.cursor == "s-resize") {
				i.cells[t].style.cursor = "n-resize";
				i.cells[t].style.backgroundImage = "url('css/" + th + "/images/descending.png')";
//				i.cells[t].style.backgroundPosition = "right center";
				i.cells[t].style.backgroundRepeat = "no-repeat";
				i.cells[t].style.backgroundSize = "12px";
				direction = "a"
			} else {
				i.cells[t].style.cursor = "s-resize";
				i.cells[t].style.backgroundImage = "url('css/" + th + "/images/ascending.png')";
//				i.cells[t].style.backgroundPosition = "right center";
				i.cells[t].style.backgroundRepeat = "no-repeat";
				i.cells[t].style.backgroundSize = "12px";
				direction = "d"
			}
		}
	}
	for (var r = 0, i; i = table.rows[r]; r++) {
		var o = new Array;
		for (var t = 0, n; n = i.cells[t]; t++) {
			if (i.cells[t].tagName == "TD") {
				o[t] = i.cells[t].innerHTML;
				columnClass = i.cells[columnNumber].className
			}
		}
		e[r] = o
	}
	e.sort(function (e, t) {
		if (direction == "a") {
			if (columnClass == "number") {
				return parseFloat(e[columnNumber].replace(/[,.]/g, '')) - parseFloat(t[columnNumber].replace(/[,.]/g, ''))
			} else if (columnClass == "date") {
				if (e[columnNumber] !== undefined) {
					da = new Date(convertDate(e[columnNumber], localStorage.DateFormat));
				} else {
					da = new Date(e[columnNumber]);
				}
				db = new Date(convertDate(t[columnNumber], localStorage.DateFormat));
				return da > db
			} else {
				return e[columnNumber].localeCompare(t[columnNumber])
			}
		} else {
			if (columnClass == "number") {
				return parseFloat(t[columnNumber].replace(/[,.]/g, '')) - parseFloat(e[columnNumber].replace(/[,.]/g, ''))
			} else if (columnClass == "date") {
				if (e[columnNumber] !== undefined) {
					da = new Date(convertDate(e[columnNumber], localStorage.DateFormat));
				} else {
					da = new Date(e[columnNumber]);
				}
				db = new Date(convertDate(t[columnNumber], localStorage.DateFormat));
				return da <= db
			} else {
				return t[columnNumber].localeCompare(e[columnNumber])
			}
		}
	});
	for (var r = 0, i; i = table.rows[r]; r++) {
		var o = new Array;
		o = e[r];
		for (var t = 0, n; n = i.cells[t]; t++) {
			if (i.cells[t].tagName == "TD") {
				i.cells[t].innerHTML = o[t]
			}
		}
	}
	return
}

function remSelOpt(e, t) {
	len1 = t.options.length;
	for (i = 0; i < len1; i++) {
		if (t.options[i].value == e) {
			t.options[i] = null;
			break
		}
	}
}

function AddScript(e, t) {
	theme = localStorage.Theme;
	document.getElementById("favourites").innerHTML = document.getElementById("favourites").innerHTML + '<option value="' + e + '">' + t + "</option>";
	document.getElementById("PlusMinus").src = "css/" + theme + "/images/subtract.png";
	document.getElementById("PlusMinus").setAttribute("onClick", "javascript: RemoveScript('" + e + "', '" + t + "');");
	UpdateFavourites(e, t)
}

function RemoveScript(e, t) {
	theme = localStorage.Theme;
	remSelOpt(e, document.getElementById("favourites"));
	document.getElementById("PlusMinus").src = "css/" + theme + "/images/add.png";
	document.getElementById("PlusMinus").setAttribute("onClick", "javascript: AddScript('" + e + "', '" + t + "');");
	UpdateFavourites(e, t)
}

function UpdateFavourites(e, t) {
	Target = "UpdateFavourites.php?Script=" + e + "&Title=" + t;
	if (window.XMLHttpRequest) {
		xmlhttp = new XMLHttpRequest
	} else {
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP")
	}
	xmlhttp.open("GET", Target, true);
	xmlhttp.send();
	return false
}

function AddAmount(t,Target) {
	if (t.checked) {
		document.getElementById(Target).value=parseFloat(document.getElementById(Target).value)+parseFloat(t.value);
	} else {
		document.getElementById(Target).value=parseFloat(document.getElementById(Target).value)-parseFloat(t.value);
	}
}

function Scheduler() {
	if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
	} else {// code for IE6, IE5
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState==4 && xmlhttp.status==200) {
			document.getElementById('HiddenOutput').innerHTML=xmlhttp.responseText;
		}
	}
	xmlhttp.open("GET",'RunScheduledJobs.php',true);
	xmlhttp.send();
	return false;
}
function Redirect(e) {
	alert(e.getAttribute("href"));
}
function NotificationMessages() {
	/* Notification messages */

	/* Move messages from footer div into header div */
	document.getElementById('MessageContainerHead').appendChild(
    document.getElementById('MessageContainerFoot')
	);

	/* Show footer div after it has been moved to header div */
	document.getElementById('MessageContainerFoot').style["display"] = "block";

	/* Close button dynamic styling*/
	var close = document.getElementsByClassName("MessageCloseButton");
	var i;
	for (i = 0; i < close.length; i++) {
		close[i].onclick = function(){
			var div = this.parentElement;
			div.style.opacity = "0";
			setTimeout(function(){ div.style.display = "none"; }, 600);
		}
	}
}
function SetSortingEvent() {
	var n = document.getElementsByTagName("th");
	for (i = 0; i < n.length; i++) {
		if (n[i].className == "SortedColumn") {
			n[i].onclick = SortSelect
		}
	}
}
function ToggleCheckbox(element) {
	if (element.checked==true) {
		element.checked=false;
	} else {
		element.checked=true;
	}
}
function initial() {
	Scheduler();
	if (document.getElementsByTagName) {
		var e = document.getElementsByTagName("a");
		for (i = 0; i < e.length; i++) {
			var t = e[i];
			if (t.getAttribute("href") && t.getAttribute("rel") == "external") t.target = "_blank"
//			e[i].onclick = function () {Redirect(this); return false};
		}
	}
	var n = document.getElementsByTagName("input");
	for (i = 0; i < n.length; i++) {
		if (n[i].className == "date") {
			n[i].onclick = clickDate;
			n[i].onchange = changeDate;
		}
		if (n[i].className == "number") n[i].onkeypress = rTN;
		if (n[i].className == "integer") n[i].onkeypress = rTI;
		if (n[i].type == "tel") n[i].pattern = "[0-9 +s()]*";
		if (n[i].type == "email") n[i].pattern = "^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,4})$"
	}
	ScriptName=(location.pathname.substring(location.pathname.lastIndexOf("/") + 1)).slice(0, -4);
	var n = document.getElementsByTagName("field");
	for (i = 0; i < n.length; i++) {
		n[i].className = n[i].className+" "+ScriptName;
		SubElements=n[i].children;
		for (j = 0; j < SubElements.length; j++) {
			if (SubElements[j].tagName == "INPUT") {
				ElementName = SubElements[j].name;
				n[i].className = n[i].className+"_"+ElementName;
			}
		}
	}

	SetSortingEvent();

	NotificationMessages();
}
days = new Array("Su", "Mo", "Tu", "We", "Th", "Fr", "Sa");
months = new Array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");
dateDivID = "calendar";
function AddAmount(t,Target,d) {
	if (t.checked) {
		document.getElementById(Target).value=Number(t.value) + Number(document.getElementById(Target).value);
		if(d) document.getElementById(d).required="required";
	} else {
		document.getElementById(Target).value=Number(document.getElementById(Target).value)-Number(t.value);
		if(d) document.getElementById(d).required="";
	}
}
window.onload = initial
