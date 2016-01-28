<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

class BxDolXmlNode
{
    var $name     = '';
    var $value    = '';
    var $children = array();

    function __construct( $name1 = '', $value1 = '' )
    {
        $this->name  = $name1;
        $this->value = $value1;
    }
    function addChild( $node )
    {
        if ( is_a($node, 'BxDolXmlNode') )
            $this->children[] = $node;
    }
    function getXMLText()
    {
        $result = "<{$this->name}>";
        if ( empty($this->children) )
            $result .= $this->value;
        else
            foreach ( $this->children as $child )
                $result .= $child->getXMLText();
        $result .= "</{$this->name}>";
        return $result;
    }

    function GetXMLHtml()
    {
        $sRes = '<?xml version="1.0" encoding="UTF-8"?>' . $this->getXMLText();
        return $sRes;
    }
}
