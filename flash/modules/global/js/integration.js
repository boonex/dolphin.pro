function openRayWidget(sModule, sApp) {
	if(aRayApps[sModule][sApp] == undefined)return;
	
	var aInfo = aRayApps[sModule][sApp];
	var sUrl = sRayUrl + "index.php?module=" + sModule + "&app=" + sApp;			
	for(var i=0; i<arguments.length - 2; i++)
		sUrl += "&" + aInfo["params"][i] + "=" + arguments[i + 2];
		
	var popupWindow = window.open(sUrl, 'Ray_' + sModule + '_' + sApp + parseInt(Math.random()*100000), 'top=' + aInfo["top"] + ',left=' + aInfo["left"] + ',width=' + aInfo["width"] + ',height=' + aInfo["height"] + ',toolbar=0,directories=0,menubar=0,status=0,location=0,scrollbars=0,resizable=' + aInfo["resizable"]);
	
	if( popupWindow == null )
		alert( "You should disable your popup blocker software" );
}

function getRayFlashObject(sModule, sApp) {
	if(navigator.appName.indexOf("Microsoft") != -1) 
        return window["ray_flash_" + sModule + "_" + sApp + "_object"];
    else 
        return document["ray_flash_" + sModule + "_" + sApp + "_embed"];
}