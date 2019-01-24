<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_INC . 'db.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'profiles.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'utils.inc.php');

define('BX_DOL_TAGS_DIVIDER', ';,');

class BxDolTags
{
    var $oDb;

    var $iViewer;

    var $sCacheFile;
    var $sNonParseParams;

    var $sCacheTable;
    var $sTagTable;
    var $aTagFields;
    var $iTagLength;

    var $aTagObjects = array();

    var $sTagsDivider = BX_DOL_TAGS_DIVIDER;
    var $bToLower = true;

    function __construct()
    {
        $this->oDb = BxDolDb::getInstance();
        $this->iViewer = getLoggedId();

        $this->sCacheFile = 'sys_objects_tag';
        $this->sNonParseParams = 'tags_non_parsable';

        $this->sCacheTable = 'sys_objects_tag';
        $this->sTagTable = 'sys_tags';
        $this->aTagFields = array(
            'id' => 'ObjID',
            'type' => 'Type',
            'tag' => 'Tag',
            'date' => 'Date'
        );
        $this->iTagLength = 32;

        $this->aObjFields = array(
            'id' => 'ID',
            'name' => 'ObjectName',
            'query' => 'Query',
            'perm_param' => 'PermalinkParam',
            'perm_enable' => 'EnabledPermalink',
            'perm_disable' => 'DisabledPermalink',
            'lang_key' => 'LangKey'
        );
    }

    function getTagObjectConfig ($aParam = array())
    {
        if (!empty($aParam)) {
            $sqlQuery = "SELECT obj.*
                FROM  `{$this->sCacheTable}` obj LEFT JOIN  `{$this->sTagTable}` tgs
                ON obj.`{$this->aObjFields['name']}` = tgs.`{$this->aTagFields['type']}` " .
                $this->_getSelectCondition($aParam) . " GROUP BY obj.`{$this->aObjFields['name']}` ORDER BY obj.`ID`";
            $this->aTagObjects = $this->oDb->getAllWithKey($sqlQuery, 'ObjectName');
        } else
            $this->aTagObjects = $this->oDb->fromCache($this->sCacheFile, 'getAllWithKey',
                "SELECT * FROM `{$this->sCacheTable}`", 'ObjectName');
    }

    function implodeTags ($aTags)
    {
        if(empty($aTags) || !is_array($aTags))
            return '';

        return implode(',', $aTags);
    }

    function explodeTags ($sText)
    {
        $aTags = preg_split( '/['.$this->sTagsDivider.']/', $sText, 0, PREG_SPLIT_NO_EMPTY );
        foreach( $aTags as $iInd => $sTag ) {
            if( strlen( $sTag ) < 3 )
                unset( $aTags[$iInd] );
            else
                $aTags[$iInd] = trim($this->bToLower ? mb_strtolower( $sTag , 'UTF-8') : $sTag);
        }
        $aTags = array_unique($aTags);
        $sTagsNotParsed = getParam( $this->sNonParseParams );
        $aTagsNotParsed = preg_split( '/[, ]/', $sTagsNotParsed, 0, PREG_SPLIT_NO_EMPTY );

        $aTags = array_diff( $aTags, $aTagsNotParsed ); //drop non parsable tags
        return $aTags;
    }

    function reparseObjTags( $sType, $iID )
    {
        $this->getTagObjectConfig();

        $iID = (int)$iID;
        if($iID <= 0 || !array_key_exists($sType, $this->aTagObjects) || !isset($this->aTagObjects[$sType]['Query'])) 
            return;

        $this->oDb->query("DELETE FROM `{$this->sTagTable}` WHERE `{$this->aTagFields['id']}` = $iID AND `{$this->aTagFields['type']}` = '$sType'");

        $sQuerySelect = str_replace('{iID}', $iID, $this->aTagObjects[$sType]['Query']);
        $sTags = $this->oDb->getOne($sQuerySelect);
        if(!strlen($sTags))
            return;

        $sResult = $this->_insertTags(array(
            'id' => $iID,
            'type' => $sType,
            'tagString' => $sTags,
            'date' => time()
        ));

        if(empty($sResult))
            return;

        $aField = $this->oDb->fetchField($sQuerySelect, 0);
        if($aField && ($i = mb_stripos($sQuerySelect, 'WHERE')) !== false && mb_stripos($sQuerySelect, 'JOIN') === false)
            $this->oDb->query("UPDATE `{$aField['table']}` SET `{$aField['name']}` = '" . process_db_input($sResult) . "' " . mb_substr($sQuerySelect, $i));
    }

    function getTagList($aParam)
    {
        $sLimit = '';
        $sJoin = isset($aParam['admin']) ? $this->_getProfileJoin(isset($aParam['admin'])) : '';
        $sGroupBy = "GROUP BY `{$this->aTagFields['tag']}`";

        if (isset($aParam['limit'])) {
            $sLimit = 'LIMIT ';
            if (isset($aParam['start']))
                $sLimit .= $aParam['start'] . ', ';
            $sLimit .= $aParam['limit'];
        }

        $sCondition = $this->_getSelectCondition($aParam);

        if (isset($aParam['orderby'])) {
            if ($aParam['orderby'] == 'popular')
                $sGroupBy .= " ORDER BY `count` DESC, `{$this->aTagFields['tag']}` ASC";
            else if ($aParam['orderby'] == 'recent')
                $sGroupBy .= " ORDER BY `{$this->aTagFields['date']}` DESC, `{$this->aTagFields['tag']}` ASC";
        }

        $sqlQuery = "SELECT
            `tgs`.`{$this->aTagFields['tag']}` as `{$this->aTagFields['tag']}`,
            `tgs`.`{$this->aTagFields['date']}` as `{$this->aTagFields['date']}`,
            COUNT(`tgs`.`{$this->aTagFields['id']}`) AS `count`
            FROM `{$this->sTagTable}` `tgs` $sJoin $sCondition $sGroupBy $sLimit";
        $aTotalTags = $this->oDb->getPairs($sqlQuery, $this->aTagFields['tag'], 'count');

        if(isset($aParam['orderby']) && $aParam['orderby'] == 'popular')
            ksort($aTotalTags);

        return $aTotalTags;
    }

    function getTagsCount($aParam)
    {
        $sCondition = $this->_getSelectCondition($aParam);
        $sJoin = isset($aParam['admin']) ? $this->_getProfileJoin(isset($aParam['admin'])) : '';
        $sqlQuery = "SELECT count(DISTINCT `tgs`.`{$this->aTagFields['tag']}`) AS `count` FROM
            `{$this->sTagTable}` `tgs` $sJoin {$sCondition}";

        return $this->oDb->getOne($sqlQuery);
    }

    function getFirstObject()
    {
        if ($this->aTagObjects) {
            $aKeys = array_keys($this->aTagObjects);
            return $aKeys[0];
        }

        return '';
    }

    function getHrefWithType($sType)
    {
        $aCurrent = $this->aTagObjects[$sType];
        $bPermalinks = getParam($aCurrent['PermalinkParam'])=='on' ? true : false;

        return $bPermalinks ? $aCurrent['EnabledPermalink'] : $aCurrent['DisabledPermalink'];
    }

    function _getSelectCondition($aParam)
    {
        $sCondition = "WHERE `tgs`.`{$this->aTagFields['tag']}` IS NOT NULL";

        if (!$aParam)
            return $sCondition;

        if (isset($aParam['type']) && $aParam['type'])
            $sCondition .= " AND `tgs`.`{$this->aTagFields['type']}` = '{$aParam['type']}'";

        if (isset($this->aTagFields['owner'])) {
            $sCondition .= ' AND ';

            if (isset($aParam['admin'])) {
                if ($aParam['admin'])
                    $sCondition .= '(`profiles`.`Role` & ' . BX_DOL_ROLE_ADMIN . ')';
                else
                    $sCondition .= 'NOT (`profiles`.`Role` & ' . BX_DOL_ROLE_ADMIN . ')';
            } else
                $sCondition .= "`tgs`.`{$this->aTagFields['owner']}` <> 0";
        }

        if (isset($aParam['filter']) && $aParam['filter'])
            $sCondition .= " AND `tgs`.`{$this->aTagFields['tag']}` LIKE '%{$aParam['filter']}%'";

        if (isset($aParam['date']) && $aParam['date'])
            $sCondition .= " AND DATE(`tgs`.`{$this->aTagFields['date']}`) = DATE('{$aParam['date']['year']}-{$aParam['date']['month']}-{$aParam['date']['day']}')";

        return $sCondition;
    }

    function _getProfileJoin($bAdmin)
    {
        if (isset($this->aTagFields['owner']) && $bAdmin)
            return "INNER JOIN `Profiles` `profiles` ON `tgs`.`{$this->aTagFields['owner']}` = `profiles`.`ID`";

        return '';
    }

    function _insertTags ($aTagsSet)
    {
        $aTags = $this->explodeTags( $aTagsSet['tagString'] );
        if( !$aTags )
            return;

        $bUpdate = false;
        foreach( $aTags as $iKey => $sTag ) {
            $sTag = trim($sTag);
            if(get_mb_len($sTag) > $this->iTagLength) {
                $sTag = get_mb_substr($sTag, 0, $this->iTagLength);

                $aTags[$iKey] = $sTag;
                $bUpdate = true;
            }
            $sTag = process_db_input($sTag, BX_TAGS_STRIP, BX_SLASHES_NO_ACTION);

            $sQuery = "SELECT COUNT(*) FROM `sys_tags` WHERE " . $this->oDb->arrayToSQL(array(
                $this->aTagFields['id'] => $aTagsSet['id'],
                $this->aTagFields['type'] => $aTagsSet['type'],
                $this->aTagFields['tag'] => $sTag,
            ), ' AND ');

            if(!$this->oDb->getOne($sQuery))
                $this->oDb->query("INSERT INTO `{$this->sTagTable}` SET " . $this->oDb->arrayToSQL(array(
                    $this->aTagFields['id'] => $aTagsSet['id'],
                    $this->aTagFields['type'] => $aTagsSet['type'],
                    $this->aTagFields['tag'] => $sTag,
                    $this->aTagFields['date'] => $aTagsSet['date']
                )));
        }

        $sResult = '';
        if($bUpdate)
            $sResult = $this->implodeTags(array_unique($aTags));

        return $sResult;
    }
}
