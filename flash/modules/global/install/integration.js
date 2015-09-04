//var url = String(document.location.href);
//var sRayUrl = url.substring(url.indexOf('?')+1,url.length) + ;
var sRayUrl = "[url_to_ray]";
var aRayApps = new Array();

var userId = getCookie("memberID");
updateRayUserStatus(userId);

function getCookie(name)
{		
	var leftPart = name + "=";
	var aCookies = document.cookie.split(";");

	for(var i=0; i<aCookies.length; i++)
	{
		var sCookie = aCookies[i];
		while(sCookie.charAt(0) == " ") sCookie = sCookie.substring(1, sCookie.length);
		if(sCookie.indexOf(leftPart) == 0) return sCookie.substring(leftPart.length, sCookie.length);
	}
	return "";
}

function updateRayUserStatus(sUserId)
{
	var XMLHttpRequestObject = false;		
	if(userId != "")
	{			
		var d = new Date();
		var url = sRayUrl + "XML.php?action=updateOnlineStatus&id=" + userId + "&_t=" + d.getTime();
		if(window.XMLHttpRequest)
		{
			XMLHttpRequestObject = new XMLHttpRequest();		
		}
		else if(window.ActiveXObject)
		{
			XMLHttpRequestObject = new ActiveXObject("Microsoft.XMLHTTP");			
		}	
		if(XMLHttpRequestObject)
		{
			XMLHttpRequestObject.open("GET", url);		
			XMLHttpRequestObject.send(null);	
		}
	}
}

function openRayWidget(sModule, sApp)
{
	if(aRayApps[sModule][sApp] == undefined)return;
	
	var aInfo = aRayApps[sModule][sApp];
	var sUrl = sRayUrl + "index.php?module=" + sModule + "&app=" + sApp;			
	for(var i=0; i<arguments.length - 2; i++)
		sUrl += "&" + aInfo["params"][i] + "=" + arguments[i + 2];
		
	var popupWindow = window.open(sUrl, 'Ray_' + sModule + '_' + sApp + parseInt(Math.random()*100000), 'top=' + aInfo["top"] + ',left=' + aInfo["left"] + ',width=' + aInfo["width"] + ',height=' + aInfo["height"] + ',toolbar=0,directories=0,menubar=0,status=0,location=0,scrollbars=0,resizable=' + aInfo["resizable"]);
	
	if( popupWindow == null )
		alert( "You should disable your popup blocker software" );
}

//base begin
aRayApps["global"] = new Array();
aRayApps["global"]["admin"] = {"params": new Array('nick', 'password'), "top": 0, "left": 0, "width": 800, "height": 600, "resizable": 0};
//base end