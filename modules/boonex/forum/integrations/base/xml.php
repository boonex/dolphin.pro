<?php
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
 *
 * Add xml contents to whole xml output
 * put xml content to $integration_xml variable
 *******************************************************************************/

global $site;
global $tmpl;
global $glHeader;
global $glFooter;
global $gConf;

if (isset($gConf['title']) && $gConf['title'])
    $glHeader = preg_replace('#<title>(.*)</title>#', "<title>" . str_replace(array('<![CDATA[', ']]>'), '', $gConf['title']) . "</title>", $glHeader);

$integration_xml .= '<url_dolphin>' . $site['url'] . '</url_dolphin>';
$integration_xml .= '<skin_dolphin>' . $tmpl . '</skin_dolphin>';
$integration_xml .= '<header><![CDATA[' . str_replace(array('<![CDATA[', ']]>'), '', $glHeader) . ']]></header>';
$integration_xml .= '<footer><![CDATA[' . str_replace(array('<![CDATA[', ']]>'), '', $glFooter) . ']]></footer>';
