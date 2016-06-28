<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( BX_DIRECTORY_PATH_BASE . 'scripts/BxBaseProfileView.php' );

define('BX_BLOCK_GENERALINFO', 17);
define('BX_BLOCK_ADDITIONALINFO', 20);

class BxDolXMLRPCProfileView extends BxBaseProfileGenerator
{
    var $_iViewerId;

    function __construct($iProfileId, $iViewerId = 0)
    {
        BxBaseProfileGenerator::__construct ((int)$iProfileId);
        $this->_iViewerId = $iViewerId;
    }

    function getProfileInfoExtra()
    {
        $oPrivacy = new BxDolPrivacy('sys_page_compose_privacy', 'id', 'user_id');
        $aRet = array();
        $r = db_res ("SELECT `pc`.`Caption`, `pc`.`Content`, `pc`.`Func`, `pc`.`ID` AS `BlockID`
            FROM `sys_profile_fields` AS `pf` 
            INNER JOIN `sys_page_compose` AS `pc` 
            ON ((`pc`.`Func` = 'PFBlock' AND `pc`.`Content` = `pf`.`ID`) OR (`pc`.`Func` = 'GeneralInfo' AND " . BX_BLOCK_GENERALINFO . " = `pf`.`ID`) OR (`pc`.`Func` = 'AdditionalInfo' AND " . BX_BLOCK_ADDITIONALINFO . " = `pf`.`ID`))
            WHERE `pc`.`Page` = 'profile_info' AND `pf`.`Type` = 'block' AND `pc`.`Column` != 0 
            ORDER BY `pc`.`Column`, `pc`.`Order`");
        while ($a = $r->fetch()) {
            $iPrivacyId = (int)$GLOBALS['MySQL']->getOne("SELECT `id` FROM `sys_page_compose_privacy` WHERE `user_id`='" . $this->_iProfileID . "' AND `block_id`='" . $a['BlockID'] . "' LIMIT 1");
            if ($iPrivacyId != 0 && !$oPrivacy->check('view_block', $iPrivacyId, $this->_iViewerId))
                continue;

            switch ($a['Func']) {
                case 'GeneralInfo': $i = BX_BLOCK_GENERALINFO; break;
                case 'AdditionalInfo': $i = BX_BLOCK_ADDITIONALINFO; break;
                default: $i = $a['Content'];
            }
            $aBlock = $this->getProfileInfoBlock ($a['Caption'], $i);
            if (false === $aBlock) continue;
            $aRet[] = $aBlock;
        }

        if ($this->_iViewerId == $this->_iProfileID) {

            $aOwnInfo[] = new xmlrpcval (array (
                'Caption' => new xmlrpcval (_t('_E-mail')),
                'Type' => new xmlrpcval ('text'),
                'Value1' => new xmlrpcval ($this->_aProfile['Email']),
            ), "struct");

            $aOwnInfo[] = new xmlrpcval (array (
                'Caption' => new xmlrpcval (_t('_Membership2')),
                'Type' => new xmlrpcval ('text'),
                'Value1' => new xmlrpcval (strip_tags(GetMembershipStatus($this->_iProfileID, false, false))),
            ), "struct");

            $aOwnInfo[] = new xmlrpcval (array (
                'Caption' => new xmlrpcval (_t('_Status')),
                'Type' => new xmlrpcval ('text'),
                'Value1' => new xmlrpcval (_t('__' . $this->_aProfile['Status'])),
            ), "struct");

            $aRet[] = new xmlrpcval (array (
                'Info' => new xmlrpcval ($aOwnInfo, "array"),
                'Title' => new xmlrpcval (_t('_Account Info')),
            ), "struct");
        }

        return new xmlrpcval ($aRet, "array");
    }

    function getProfileInfoBlock($sCaption, $sContent)
    {
        global $site;

        $iBlockID = (int)$sContent;

        if( !isset( $this->aPFBlocks[$iBlockID] ) or empty( $this->aPFBlocks[$iBlockID]['Items'] ) )
            return false;

        $aItems = $this->aPFBlocks[$iBlockID]['Items'];

        $aRet = array ();
        foreach( $aItems as $aItem ) {

            $sValue1 = htmlspecialchars_decode($this->oPF->getViewableValue($aItem, $this->_aProfile[$aItem['Name']]), ENT_COMPAT);

            if ($aItem['Name'] == 'Age')
                $sValue1 = (isset($this->_aProfile['DateOfBirth'])) ? age($this->_aProfile['DateOfBirth']) : _t("_uknown");

            if( !$sValue1 ) //if empty, do not draw
                continue;

            $aStruct = array ();

            $aStruct['Caption'] = new xmlrpcval (strip_tags(_t($aItem['Caption'])));
            $aStruct['Type'] = new xmlrpcval ($aItem['Type']);
            $aStruct['Value1'] = new xmlrpcval (strip_tags($sValue1));

            if ($this->bCouple) {
                if (!in_array( $aItem['Name'], $this->aCoupleMutualItems)) {
                    $sValue2 = htmlspecialchars_decode($this->oPF->getViewableValue($aItem, $this->_aCouple[$aItem['Name']]), ENT_COMPAT);
                    if ($aItem['Name'] == 'Age')
                        $sValue2 = (isset($this->_aCouple['DateOfBirth'])) ? age($this->_aCouple['DateOfBirth']) : _t("_uknown");
                    $aStruct['Value2'] = new xmlrpcval (strip_tags($sValue2));
                }
            }

            $aRet[] = new xmlrpcval ($aStruct, "struct");
        }

        return new xmlrpcval (array (
            'Info' => new xmlrpcval ($aRet, "array"),
            'Title' => new xmlrpcval (_t($sCaption)),
        ), "struct");
    }
}
