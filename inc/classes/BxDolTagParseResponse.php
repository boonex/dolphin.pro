<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolAlerts.php' );

class BxDolTagParseResponse extends BxDolAlertsResponse
{
    var $aParseList = array(
        'tag' => array(
            'class' => 'BxDolTags',
            'file' => 'inc/classes/BxDolTags.php',
            'method' => 'reparseObjTags({sType}, {iId})'
        ),
        'category' => array(
            'class' => 'BxDolCategories',
            'file' => 'inc/classes/BxDolCategories.php',
            'method' => 'reparseObjTags({sType}, {iId})'
        )
    );

    var $aCurrent = array();

    function response ($oTag)
    {
        foreach ($this->aParseList as $sKey => $aValue) {
            if (!class_exists($aValue['class']))
               require_once(BX_DIRECTORY_PATH_ROOT . $aValue['file']);
            $oParse = new $aValue['class']();
            $sMethod = $aValue['method'];

            $sMethod = str_replace('{sType}', "'".$oTag->sUnit."'", $sMethod);
            $sMethod = str_replace('{iId}', $oTag->iObject, $sMethod);
            $sMethod = str_replace('{iId}', $oTag->iObject, $sMethod);
            $sFullComm = '$oParse->'.$sMethod.'; ';
            eval($sFullComm);
        }
    }
}
