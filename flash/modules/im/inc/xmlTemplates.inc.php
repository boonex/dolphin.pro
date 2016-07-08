<?php
/***************************************************************************
*
* IMPORTANT: This is a commercial product made by BoonEx Ltd. and cannot be modified for other than personal usage.
* This product cannot be redistributed for free or a fee without written permission from BoonEx Ltd.
* This notice may not be removed from the source code.
*
***************************************************************************/

$aXmlTemplates = array (
    "message" => array (
        1 => '<message><text><![CDATA[#1#]]></text></message>',
        4 => '<msg sender="#1#" nick="#2#" profile="#3#"><![CDATA[#4#]]></msg>',
        6 => '<msg color="#2#" bold="#3#" underline="#4#" italic="#5#" smileset="#6#"><![CDATA[#1#]]></msg>',
        9 => '<message id="#1#" color="#3#" bold="#4#" underline="#5#" italic="#6#" size="#7#" font="#8#" smileset="#9#"><text><![CDATA[#2#]]></text></message>'
    ),

    "user" => array (
        1 => '<user online="#1#" />',
        2 => '<user id="#1#"><nick><![CDATA[#2#]]></nick></user>',
        6 => '<user id="#1#" sex="#3#" age="#4#" img="#5#" profile="#6#"><nick><![CDATA[#2#]]></nick></user>',
        8 => '<user id="#1#" sex="#3#" age="#4#" photo="#6#" profile="#7#" online="#8#"><nick><![CDATA[#2#]]></nick><desc><![CDATA[#5#]]></desc></user>'
    ),

    "file" => array (
        2 => '<file file="#1#"><name><![CDATA[#2#]]></name></file>'
    ),

    "result" => array (
        1 => '<result value="#1#" />',
        2 => '<result value="#1#" status="#2#" />',
        6 => '<result value="#1#" uId="#3#" uNick="#4#" uImg="#5#" uProfile="#6#"><![CDATA[#2#]]></result>'
    ),

    "smileset" => array (
        2 => '<properties current="#1#" url="#2#" />',
        3 => '<smileset folder="#1#" config="#2#"><![CDATA[#3#]]></smileset>'
    )
);
