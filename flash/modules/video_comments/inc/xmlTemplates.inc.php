<?php
/***************************************************************************
*
* IMPORTANT: This is a commercial product made by BoonEx Ltd. and cannot be modified for other than personal usage.
* This product cannot be redistributed for free or a fee without written permission from BoonEx Ltd.
* This notice may not be removed from the source code.
*
***************************************************************************/

$aXmlTemplates = array (
    "result" => array (
        1 => '<result value="#1#" />',
        2 => '<result value="#1#" status="#2#" />',
    ),

    "file" => array (
        5 => '<file id="#1#" file="#2#" save="#3#" image="#4#" time="#5#" />',
        6 => '<file id="#1#" file="#2#" save="#3#" image="#4#" time="#5#"><saveName><![CDATA[#6#]]></saveName></file>'
    ),

    "user" => array (
        5 => '<user id="#1#" nick="#2#" profile="#3#" all="#4#" approval="#5#" />'
    )
);
