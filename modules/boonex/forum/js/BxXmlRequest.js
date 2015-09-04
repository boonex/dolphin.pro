/**
*                            Orca Interactive Forum Script
*                              ---------------
*     Started             : Mon Mar 23 2006
*     Copyright           : (C) 2007 BoonEx Group
*     Website             : http://www.boonex.com
* This file is part of Orca - Interactive Forum Script
* Creative Commons Attribution 3.0 License
**/


/**
 * load xml data object
 */


/**
 * constructor
 *		url	- url with xml data to open
 *		h	- handler function
 */
function BxXmlRequest(url, h, async)
{	
	if (!url.length) return;

	/**
	 * local handler function
	 */
	var f = function (r, url, h)
	{
		if (r.readyState == 4) // only if req shows "loaded"
	    {
		    if (r.status == 200 || r.status == 304) // only if "OK"
			{
	            h (r);
		    }
			else
	        {
				var s = '';
				for (var i in r) s += i + "      ";
		        BxError("[L[XML read failed:]]" + r.status, "[L[There was a problem retrieving the XML data:]]\n" + url);
			}
	    }
	}

	var r;
	

	// IE
	if(window.ActiveXObject)
	{		

		try
		{			
			r = new ActiveXObject("Microsoft.XMLHTTP")

			// register handler function
			r.onreadystatechange = function(  ) 
			{
				f (r, url, h);
			}

			r.open("GET", url, async);
			r.send();  
		}
		catch(a)
		{
		}
	}
	else  if (window.XMLHttpRequest)
	{
		r = new XMLHttpRequest();
	
		// register handler function
		r.onload = function () 
		{
			f (r, url, h);
		}

		r.open("GET", url, async);
		r.send(null);  
	}	

	if (!r)
	{
		var e = new BxError("[L[httpxml object creation failed]]", "[L[please upgrade your browser]]");
	}
	else
	{
		this.request = r;
	}

}   





BxXmlRequest.prototype.getRetNodeValue = function (r_xml, tagname)
{
    var ret = '';

    if (r_xml.responseXML)
    {
        if (window.ActiveXObject)
	    {
		    var e = r_xml.responseXML.getElementsByTagName(tagname)[0];
    		if (e != undefined && e != null && e.firstChild)
	    	    ret = e.firstChild.nodeValue;
    	}
        else
    	{     
	    	var e = r_xml.responseXML.getElementsByTagName(tagname)[0];
		    ret = e.textContent;
    	}
    }

    if (ret == null || ret == undefined || !ret.length)
    {
        var r = new RegExp ('<'+tagname+'>([\\x00-\\xff]*)<\/'+tagname+'>');
        var a = r_xml.responseText.match (r);     
        if (a && a.length > 1)
            ret = a[1];
    }

	return  ret;
}
