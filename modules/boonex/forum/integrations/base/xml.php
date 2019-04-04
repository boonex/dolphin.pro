<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

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
