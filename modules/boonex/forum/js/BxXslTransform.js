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
 * xml/xsl transformation
 */

/**
 * constructor
 *		url_xml	- url with xml data to open
 *		url_xsl	- url with xsl data to merge with xml
 *		h		- user handler function
 */
function BxXslTransform(url_xml, url_xsl, h)
{	
	var r_xsl;
	var r_xml;
	var no_xsl = 0;

    var safari = navigator.vendor && navigator.vendor.search('Apple') > -1 ? true : false;
    var konq = navigator.vendor && navigator.vendor.search('KDE') > -1 ? true : false;
	
    if (xsl_mode != 'client')
    {
    	if (xsl_mode == 'server' || (!window.ActiveXObject && !window.XSLTProcessor) || window.opera || safari || konq)
    	{
    		if (url_xml.indexOf ("?") == -1)
    		url_xml += "?trans=1";
    		else
    		url_xml += "&trans=1";

    		no_xsl = 1;
    	}
    }


	// xml load handler
	var h_xml = function (r)
	{
		if (r)  // Mozilla
		{
			r_xml = r;
		}

		if (r_xml.readyState == 4) // IE
		{
			if (200 == r_xml.status || (r_xml.parseError && !r_xml.parseError.errorCode))
			{
//				new BxError("xml load failed", r_xml.parseError.reason);
				if ((r_xsl && r_xsl.readyState == 4) || no_xsl)
					h_res (r_xml, r_xsl);
			}
		}
	}

	// xsl load handler
	var h_xsl = function (r)
	{
		if (r) // Mozilla
		{
			r_xsl = r;
		}
		
		if (r_xsl.readyState == 4) // IE
		{
			if (200 == r_xsl.status || (r_xsl.parseError && !r_xsl.parseError.errorCode))
			{
//				new BxError("xsl load failed", r_xsl.parseError.reason);
				if (r_xml && r_xml.readyState == 4) 
					h_res (r_xml, r_xsl);
			}
		}
	}


	// it fires after both (xml and xsl handlers) functions called
	var h_res = function (r_xml, r_xsl)
	{
	    var f;


        if (no_xsl)
        {        	        	
			if (window.XMLSerializer && r_xml.responseXML && r_xml.responseXML.firstChild)
			{                
    			f = ((new XMLSerializer()).serializeToString(r_xml.responseXML));                
			}
			else
            {
				f = r_xml.responseText;
            }        	
        }
        else
		// IE
	    if(window.ActiveXObject)
		{
			try
			{
		        f = r_xml.transformNode (r_xsl);
			}
			catch (e)
			{
				var ee = new BxError(e.message, e.description);
			}
		}
		// Mozilla
	    else if (window.XSLTProcessor)
		{
	        var x = new XSLTProcessor();
		    x.importStylesheet(r_xsl.responseXML);


	        var ff = x.transformToFragment(r_xml.responseXML, window.document);            
			if (XMLSerializer)
			{
				f = ((new XMLSerializer()).serializeToString(ff));                
			}
			else
				new BxError("[L[xml serialization failed]]", "[L[please upgrade your browser]]");
		}

		// call user defined handler function
		h (f);		
	}


    // other browsers
    if (no_xsl)
    {
        new BxXmlRequest (url_xml, h_xml, true);
    }
    else
	// IE
	if (window.ActiveXObject)
	{     
		var b = new ActiveXObject("MSXML2.DOMDocument");
		r_xml = b;
		b.async = true;
		b.load (url_xml);
		b.onreadystatechange = h_xml;

		b = new ActiveXObject("MSXML2.DOMDocument");
		r_xsl = b;
		b.async = true;
		b.load (url_xsl);
		b.onreadystatechange = h_xsl;
	}
	// Mozilla
	else if (window.XSLTProcessor)
	{
		new BxXmlRequest (url_xml, h_xml, true);
		new BxXmlRequest (url_xsl, h_xsl, true);
	}	


}   
