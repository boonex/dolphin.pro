<?php
/***************************************************************************
*
* IMPORTANT: This is a commercial product made by BoonEx Ltd. and cannot be modified for other than personal usage.
* This product cannot be redistributed for free or a fee without written permission from BoonEx Ltd.
* This notice may not be removed from the source code.
*
***************************************************************************/

$aXmlTemplates = array (
    "status" => array (
        1 => '<status current="#1#" />',
        3 => '<status id="#1#" image="#2#"><![CDATA[#3#]]></status>'
    ),

    "message" => array (
        3 => '<message id="#1#" sender="#2#"><![CDATA[#3#]]></message>',
        10 => '<message id="#1#" sender="#2#" nick="#4#" sex="#5#" age="#6#" image="#7#" profile="#8#" music="#9#" video="#10#"><![CDATA[#3#]]></message>'
    ),

    "user" => array (
        2 => '<user id="#1#" online="#2#" />',
        11 => '<user id="#1#" sex="#3#" age="#4#" image="#5#" profile="#6#" online="#7#" friend="#8#" music="#9#" video="#10#"><nick><![CDATA[#2#]]></nick><desc><![CDATA[#11#]]></desc></user>'
    ),

    "result" => array (
        1 => '<result value="#1#" />',
        2 => '<result value="#1#" status="#2#" />',
        3 => '<result value="#1#" status="#2#" password="#3#" />'
    )
);
