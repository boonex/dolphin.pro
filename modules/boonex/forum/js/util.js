/**
*                            Orca Interactive Forum Script
*                              ---------------
*     Started             : Mon Mar 23 2006
*     Copyright           : (C) 2007 BoonEx Group
*     Website             : http://www.boonex.com
* This file is part of Orca - Interactive Forum Script
* Creative Commons Attribution 3.0 License
**/


function hoverEffects() {
	//get all elements (text inputs, passwords inputs, textareas)
	var elements = document.getElementsByTagName('input');
	var j = 0;
	var hovers = new Array();
	for (var i4 = 0; i4 < elements.length; i4++) {
		if((elements[i4].type=='text')||(elements[i4].type=='password')) {
			hovers[j] = elements[i4];
			++j;
		}
	}
	elements = document.getElementsByTagName('textarea');
	for (var i4 = 0; i4 < elements.length; i4++) {
		hovers[j] = elements[i4];
		++j;
	}
	
	//add focus effects
	for (var i4 = 0; i4 < hovers.length; i4++) {
		hovers[i4].onfocus = function() {this.className += "Hovered";}
		hovers[i4].onblur = function() {this.className = this.className.replace(/Hovered/g, "");}
	}
}


function correctPNG(id) 
{
    if (!/MSIE (5\.5|6\.)/.test(navigator.userAgent)) return;

	var e = document.getElementById (id);
	if (e)
	{
		var imgName = e.style.backgroundImage
		
		imgName = imgName.substring(0, imgName.length-1)
		imgName = imgName.substring(4)
		if (imgName.substring(imgName.length-3, imgName.length).toUpperCase() == "PNG")		
		{			
			e.style.backgroundImage = 'none'
			e.style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\'" + imgName + "\', sizingMethod='scale')"
		}
			
	}

   for (var i=0; i<document.images.length; i++)
   {
	  var img = document.images[i]
	  var imgName = img.src.toUpperCase()
	  if (imgName.substring(imgName.length-3, imgName.length) == "PNG")
      {
		 var imgID = (img.id) ? "id='" + img.id + "' " : ""
		 var imgClass = (img.className) ? "class='" + img.className + "' " : ""
		 var imgTitle = (img.title) ? "title='" + img.title + "' " : "title='" + img.alt + "' "
		 var imgStyle = "display:inline-block;" + img.style.cssText 
		 if (img.align == "left") imgStyle = "float:left;" + imgStyle
		 if (img.align == "right") imgStyle = "float:right;" + imgStyle
		 if (img.parentElement.href) imgStyle = "cursor:hand;" + imgStyle		
		 var strNewHTML = "<span " + imgID + imgClass + imgTitle
		 + " style=\"" + "width:" + img.width + "px; height:" + img.height + "px;" + imgStyle + ";"
	     + "filter:progid:DXImageTransform.Microsoft.AlphaImageLoader"
		 + "(src=\'" + img.src + "\', sizingMethod='scale');\"></span>" 
		 img.outerHTML = strNewHTML
		 i = i-1
      }
   }

}

function orca_html_decode (id_from, id_to)
{                                
    var s = document.getElementById(id_from).innerHTML;
    s = s.replace(/&#160;/gm, ' ');
    s = s.replace(/\x26gt;/gm, '\x3e');
    s = s.replace(/\x26lt;/gm, '\x3c');
    s = s.replace(/&amp;quot;/gm, '"');
    s = s.replace(/&quot;/gm, '"');
    document.getElementById(id_to).innerHTML = s;
}

function orca_get_xml_ret (s) 
{
    return orca_get_xml_val ('ret', s);
}

function orca_get_xml_val (tag, s) 
{
    if (!s || !s.length)
        return null;
    var r = new RegExp ('<' + tag + '>(.*)<\/' + tag + '>');
    var m = s.match(r);
    if (null == m || m.length < 2)
        return null;

    return m[1];
}

function orca_remove_editor (sId) 
{
    try {
        tinyMCE.execCommand('mceRemoveEditor', false, sId); 
    } catch(err) {};
}

function orca_add_editor (sId, sInitialText) 
{    
    if (document.getElementById(sId)) {
        if ('undefined' !== typeof(sInitialText) && sInitialText) {
            window.orcaInitInstance = function (inst) {	
                inst.setContent(sInitialText);
                window.orcaInitInstance = function (inst) {};
            }
        }
        jQuery('#' + sId).tinymce(glOrcaSettings);
    }
}
