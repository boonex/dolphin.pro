<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolPaginate');

class BxDolEmailTemplates
{
    var $iDefaultLangId;

    /**
     * Class constructor.
     */
    function __construct()
    {
        $sLang = getParam('lang_default');
        if(empty($sLang))
            $sLang = 'en';

        $this->iDefaultLangId = $GLOBALS['MySQL']->getOne("SELECT `ID` FROM `sys_localization_languages` WHERE `Name`='" . $sLang . "' LIMIT 1");
    }

    /**
     * Update existing or create new template ;
     *
     * @param $sTemplateName (string)   - name of template ;
     * @param $sTemplateSubj (string)   - subject of template ;
     * @param $sTemplateBody (string)   - text of template ;
     * @param $iLangID (integer)        - needed language's ID;
     * @return HTML presentation data ;
     */
    function setTemplate( $sTemplateName, $sTemplateSubj, $sTemplateBody, $iLangID )
    {
        if ( !db_value("SELECT `ID` FROM `sys_email_templates` WHERE `Name` = '" . process_db_input($sTemplateName) . "'  AND `LangID` = '{$iLangID}'") ) {
            $sQuery =
            "
                INSERT INTO
                    `sys_email_templates` (`Name`, `Subject`, `Body`, `LangID`)
                VALUES
                    (
                        '" . process_db_input($sTemplateName) . "',
                        '" . process_db_input($sTemplateSubj) . "',
                        '" . process_db_input($sTemplateBody) . "',
                        '" . (int) $iLangID . "'
                    )
            ";

            $sMessage = 'Template was created';
        } else {
            $sQuery =
            "
                UPDATE
                    `sys_email_templates`
                SET
                    `Subject` = '" . process_db_input($sTemplateSubj) . "',
                    `Body` = '" . process_db_input($sTemplateBody) . "'
                WHERE
                    `Name`   = '" . process_db_input($sTemplateName) . "'
                        AND
                    `LangID` = '" . (int) $iLangID . "'
                LIMIT 1
            ";

            $sMessage = 'Template was updated';
        }

        db_res($sQuery);
        return $this ->  genTemplatesForm( $sTemplateName, $iLangID, $sMessage ) ;
    }

    /**
     * Function will return array of needed template ;
     *
     * @param  string  $sTemplateName - name of necessary template.
     * @param  integer $iMemberId     - ID of registered member.
     * @return array   with template subject and its body.
     */
    function getTemplate($sTemplateName, $iMemberId = 0 )
    {
        if($iMemberId != 0) {
            $aProfile = getProfileInfo($iMemberId);
            $iUseLang = $aProfile['LangID'] ? $aProfile['LangID'] : $this->iDefaultLangId;
        } else {
            $iUseLang = $this->iDefaultLangId;
        }

        $sSql = "SELECT `Subject`, `Body` FROM `sys_email_templates` WHERE `Name`= ? AND (`LangID` = ? OR `LangID` = ?) ORDER BY `LangID` DESC LIMIT 1";
        return $GLOBALS['MySQL']->getRow($sSql, [$sTemplateName, $iUseLang, 0]);
    }

    function parseTemplate($sTemplateName, $aTemplateKeys, $iMemberId = 0)
    {
        $aTemplate = $this->getTemplate($sTemplateName, $iMemberId);

        return array(
           'subject' => $this->parseContent($aTemplate['Subject'], $aTemplateKeys, $iMemberId),
           'body' => $this->parseContent($aTemplate['Body'], $aTemplateKeys, $iMemberId)
        );
    }
    function parseContent($sContent, $aKeys, $iMemberId = 0)
    {
        $aResultKeys = $this->getDefaultKeys();
        if($iMemberId != 0) {
            $aProfile = getProfileInfo($iMemberId);

            $aResultKeys = array_merge($aResultKeys, array(
                'recipientID' => $aProfile['ID'],
                'RealName'    => getNickName($aProfile['ID']),
                'NickName'	  => getNickName($aProfile['ID']),
                'Username'    => getUsername($aProfile['ID']),
                'RecipientUrl'=> getProfileLink($aProfile['ID']),
                'Email'       => $aProfile['Email'],
                'Password'    => $aProfile['Password'],
                'SiteName'	  => getParam('site_title'),
            ));
        }
        if(is_array($aKeys))
            $aResultKeys = array_merge($aResultKeys, $aKeys);

        return $GLOBALS['oSysTemplate']->parseHtmlByContent($sContent, $aResultKeys, array('<', '>'));
    }

    function getDefaultKeys()
    {
        return array(
            'Domain' => $GLOBALS['site']['url'],
            'SiteName' => $GLOBALS['site']['title'],
            'BoonexEmailFooter' => getParam('enable_dolphin_footer') ? _t('_powered_by_Dolphin') : '',
        );
    }
}
