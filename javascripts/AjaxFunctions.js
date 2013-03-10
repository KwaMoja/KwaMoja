function SubmitForm(FormName, Element) {
	Target=FormName.action;
	var PostData='';
	for(var i=0,fLen=FormName.length;i<fLen;i++){
		if(FormName.elements[i].type=='checkbox' && !FormName.elements[i].checked) {
			FormName.elements[i].value=null;
		}
		PostData=PostData+FormName.elements[i].name+'='+FormName.elements[i].value+'&';
	}
	if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
	} else {// code for IE6, IE5
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState==4 && xmlhttp.status==200) {
			document.getElementById(Elerment).innerHTML='<div id="SubWindow"><div id="contents">'+xmlhttp.responseText+'</div></div>';
		}
	}
	xmlhttp.open("POST",Target,true);
	xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	xmlhttp.setRequestHeader("Cache-Control","no-store, no-cache, must-revalidate");
	xmlhttp.setRequestHeader("Pragma","no-cache");
	xmlhttp.send(PostData);
	return false;
}
