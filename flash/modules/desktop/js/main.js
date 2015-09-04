var sUrl = String(document.location);
var flashVars = sUrl.split("?")[1];
var sXmlUrl = flashVars.split("&url=")[1];
var sRayUrl = sXmlUrl.substring(0, sXmlUrl.lastIndexOf("/")+1);
var sModulesUrl = sRayUrl + "modules/";
var sNick = "";
if(flashVars.indexOf("&nick=") > -1)
	sNick = flashVars.split("&nick=")[1].split("&")[0];

document.write('<script src="' + sModulesUrl + 'global/data/integration.dat" type="text/javascript"></script>');
document.write('<script src="' + sModulesUrl + 'global/js/integration.js" type="text/javascript"></script>');

var sBase = sModulesUrl + sModule + "/";

AC_FL_RunContent(
	'codebase','http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab',
	'width',width,
	'height',height,
	'id','flash',
	'base',sBase,
	'align','middle',
	'src',sModulesUrl + 'global/app/holder_as3',
	'quality','high',
	'bgcolor','#FFFFFF',
	'name','flash',
	'allowscriptaccess','all',
	'pluginspage','http://www.macromedia.com/go/getflashplayer',
	'flashvars',flashVars + "&module=" + sModule + "&app=" + sApp + "&url=" + sXmlUrl,
	'movie',sModulesUrl + 'global/app/holder_as3'
);

function resizeWindow()
{
	var frameWidth = 0;
	var frameHeight = 0;
		
	if(window.innerWidth)
	{
		frameWidth = window.innerWidth;
		frameHeight = window.innerHeight;
	}
	else if (document.documentElement)
	{
		if(document.documentElement.clientHeight)
		{
			frameWidth = document.documentElement.clientWidth;
			frameHeight = document.documentElement.clientHeight;
		}
	}
	else if (document.body)
	{
		frameWidth = document.body.offsetWidth;
		frameHeight = document.body.offsetHeight;
	}
		
	var e = document.getElementsByTagName('embed')[0];
		
	if(e != null){
		e.width = (frameWidth < width) ? width : frameWidth;
		e.height = (frameHeight < height) ? height : frameHeight;
	}
}