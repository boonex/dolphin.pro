<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolBrowse');
bx_import('BxDolPaginate');
bx_import('BxTemplSearchProfile');

class BxBaseBrowse extends BxDolBrowse
{
    var $bShowBothCoupleProfiles = true;

    // for block devider ;
    var $aParameters;

    var $aAdditionalParameters;

    // link on search profile ;
    var $oSearchProfileTmpl;

    // array with some visible parameters (page, per_page ....);
    var $aDisplaySettings;

    // add this string to all sql queries
    var $_sSqlActive = "`Profiles`.`Status` = 'Active'";
    

    /**
     * Class constructor ;
     *
     * @param : $aFilteredSettings (array) ;
     *                     :     sex (string) - set filter by sex,
     *                     :     age (string) - set filter by age,
     *                     :     country (string) - set filter by country,
     *                     :     photos_only (string) - set filter 'with photo only',
     *                     :     online_only (string) - set filter 'online only',
     * @param : $aDisplaySettings (array) ;
     *                     : page (integer) - current page,
     *                     : per_page (integer) - number ellements for per page,
     *                     : sort (string) - sort parameters for SQL instructions,
     *                     : mode (mode) - switch mode to extended and simple,
     * @param : $sPageName (string) - page name (need for page builder);
     */
    function __construct( &$aFilteredSettings, &$aDisplaySettings, $sPageName )
    {
        if(!$this->bShowBothCoupleProfiles)
            $this->_sSqlActive .= " AND (`Profiles`.`Couple` = 0 or `Profiles`.`Couple` > `Profiles`.`ID`)";

        if ( isset($aFilteredSettings['sex']) and $aFilteredSettings['sex'] == 'all' )
            $aFilteredSettings['sex'] = null;

        if ( isset($aFilteredSettings['age']) and $aFilteredSettings['age'] == 'all' )
            $aFilteredSettings['age'] = null;

        if ( isset($aFilteredSettings['country']) and $aFilteredSettings['country'] == 'all' )
            $aFilteredSettings['country'] = null;

        $this -> aParameters = array
        (
            'sex'         => process_db_input($aFilteredSettings['sex'], BX_TAGS_STRIP),
            'age'         => process_db_input($aFilteredSettings['age'], BX_TAGS_STRIP),
            'country'     => process_db_input($aFilteredSettings['country'], BX_TAGS_STRIP),
        );

        $this -> aAdditionalParameters = array
        (
            'photos_only' => process_db_input($aFilteredSettings['photos_only'], BX_TAGS_STRIP),
            'online_only' => process_db_input($aFilteredSettings['online_only'], BX_TAGS_STRIP),
        );

        $this -> aDisplaySettings = &$aDisplaySettings;

        // fill sKeyName with parameters for search into cache file ;
        $this -> sKeyName .= ($this -> aParameters['sex']) ? $this -> aParameters['sex'] . '|' : '';
        $this -> sKeyName .= ($this -> aParameters['age']) ? $this -> aParameters['age'] . '|' : '';

        $this -> sKeyName .= ($this -> aParameters['country']) ? $this -> aParameters['country'] . '|' : '';

        $this -> sKeyName .= ($this -> aAdditionalParameters['photos_only']) ? 'photo|'    : '';
        $this -> sKeyName .= ($this -> aAdditionalParameters['online_only']) ? 'online|' : '';

        $this -> sKeyName = preg_replace("|\|$|", '', $this -> sKeyName);

        parent::__construct($sPageName);

        // fill global array with the needed parameters ;

        $this -> _getGlobalStatistics
        (
            $this -> aParameters['sex'],
            $this -> aParameters['age'],
            $this -> aParameters['country'],
            $aFilteredSettings['photos_only'],
            $aFilteredSettings['online_only']
        );

        $this -> oSearchProfileTmpl = new BxTemplSearchProfile();
    }

    /**
     * Function will generate the browse link ;
     *
     * @param  : $sName      (string) - link's name;
     * @param  : $sValue     (string) - link's extended value;
     * @param  : $sLink      (string) - link's location;
     * @param  : $sIco       (string) - link's icon;
     * @param  : $bImgDetect (boolean) - if isset this param img path will detect with "getTemplateIcon";
     * @return : Html presentation data;
     */
    function genBrowseLink( $sName, $sValue, $sLink, $sIcon, $bImgDetect = true )
    {
        // try to find link's icon

        if ( $bImgDetect )
            $sIcon = getTemplateIcon($sIcon);

        $sValue = ( $sValue ) ? '<span>(' . $sValue . ')</span>' : null;

        return '
            <div class="linkSection">
                <table>
                    <tr>
                        <td>
                            <img src="' . $sIcon . '" alt="' . $sName . '" />
                        </td>
                        <td>
                            <a href="' . $sLink . '">' . $sName . '</a>
                            ' . $sValue . '
                        </td>
                    </tr>
                </table>
            </div>
        ';
    }

    /**
     * Function will generate block with members global statistics ;
     *
     * @return : Html presentation data ;
     */
    function getBlockCode_SettingsBlock()
    {
        // lang keys ;
        $sSexCaption      = _t( '_By Sex' );
        $sAgeCaption      = _t( '_By Age' );
        $sCountryCaption  = _t( '_By Country' );

        // init some variables ;

        $sSexSection      = '';
        $sAgeSection      = '';
        $sCountrySection  = '';

        $sCurrentKey = ( $this -> sKeyName ) ? $this -> sKeyName : 'public';

        // ** INTERNAL FUNCTIONS ;

        /**
         * Function will generate sex block ;
         *
         * @param        : $rObject (resource) - link on created object;
         * @param        : $sCurrentKey (string) - key name, need for search into cacje file;
         * @return       : Html presentation data;
        */
        function _genSexSection( &$oObject, $sCurrentKey )
        {
            global $aPreValues;

            // init some variables ;
            $sSexSection    = '';
            $aCurrentArray  = '';
            $sExtendedCss   = '';

            $aUsedTemplates = array
            (
                'browse_settings_section.html'
            );

            $iIndex  = 0; // ** need for dividers ;

            if ( isset($oObject -> aMembersInfo[$sCurrentKey]['sex']) ) {
                $aCurrentArray    = &$oObject -> aMembersInfo[$sCurrentKey]['sex'];
            } elseif ( $oObject -> aParameters['sex'] ) {
                // ** if param `Sex` was selected ;
                // ** load data again ;

                $aCurrentArray = $oObject -> _getGlobalStatistics
                (
                    null,
                    $oObject -> aParameters['age'],
                    $oObject -> aParameters['country'],
                    $oObject -> aAdditionalParameters['photos_only'],
                    $oObject -> aAdditionalParameters['online_only'],
                    'sex'
                );
            }

            // hide selected block ;

            if ( $oObject -> aParameters['sex'] ) {
                $aCurrentArray['all'] = _t( '_All' );
                $sExtendedCss = 'hidden_block';
            }

            $iArrayCount = count($aCurrentArray);

            if ( is_array($aCurrentArray) and !empty($aCurrentArray) )
                foreach( $aCurrentArray AS $sKey => $sValue ) {
                    $iDivider   = $iIndex % 2;

                    if ( $sKey != 'all' )
                        $sValue = ( $oObject -> aParameters['sex'] ) ? 0 : $sValue;

                    switch ($sKey) {
                        case 'male'   :
                            $sSexSection .= $oObject  -> genBrowseLink( _t( '_By Male' ), $sValue, $oObject -> genLinkLocation( 'sex', 'male'), 'male.png');
                            break;
                        case 'female' :
                            $sSexSection .= $oObject  -> genBrowseLink( _t( '_By Female' ), $sValue, $oObject -> genLinkLocation( 'sex', 'female'), 'female.png');
                            break;
                        case 'all' :
                            $sSexSection .= $oObject  -> genBrowseLink( $sValue, null, $oObject -> genLinkLocation( 'sex', $sKey), 'post_featured.png');
                            break;
                        default :
                            $sSexSection .= $oObject  -> genBrowseLink( _t($aPreValues['Sex'][$sKey]['LKey']), $sValue, $oObject -> genLinkLocation( 'sex', $sKey), 'tux.png');
                            break;
                    }

                    if ( $iDivider )
                        $sSexSection  .=
                        '
                            <div class="clear_both"></div>
                        ';

                    $iIndex++;

                    if ( $iDivider and $iIndex >= 2 and $iArrayCount > 2 and ($iIndex <= $iArrayCount - 1) )
                        $sSexSection  .=
                        '
                            <div class="devider"></div>
                        ';
                }

            if ( !$sSexSection )
                $sSexSection = MsgBox(_t( '_Empty' ));

            // fill array with template's keys ;
            $aTemplateKeys = array
            (
                'section_id'     => 'sex_section',
                'extended_css'   => $sExtendedCss,
                'section_data'   => $sSexSection,
            );

            $sSexSection = $GLOBALS['oSysTemplate'] -> parseHtmlByName( $aUsedTemplates[0], $aTemplateKeys );

            // return builded template ;
            return $sSexSection;
        }

        /**
         * Function will generate the `Age` block ;
         *
         * @param        : $oObject (object) - link on created object;
         * @param        : $sCurrentKey (string) - key name, needed for search into cache file;
         * @return       : Html presentation data;
        */
        function _genAgeSection( &$oObject, $sCurrentKey )
        {
            global $aCurrentPointer;

            // init some variables ;
            $sAgeSection    = '';
            $sExtendedCss   = '';
            $aCurrentArray  = '';

            $aUsedTemplates = array
            (
                'browse_settings_section.html'
            );

            $iIndex  = 0; // ** need for dividers ;

            if ( isset($oObject -> aMembersInfo[$sCurrentKey]['age']) ) {
                $aCurrentArray    = &$oObject -> aMembersInfo[$sCurrentKey]['age'];
            } elseif ( $oObject -> aParameters['age'] ) {
                // ** if param `Age` was selected ;
                // ** load data again ;

                $aCurrentArray = $oObject -> _getGlobalStatistics
                (
                    $oObject -> aParameters['sex'],
                    null,
                    $oObject -> aParameters['country'],
                    $oObject -> aAdditionalParameters['photos_only'],
                    $oObject -> aAdditionalParameters['online_only'],
                    'age'
                );
            }

            // hide selected block ;
            if ( $oObject -> aParameters['age'] ) {
                $aCurrentArray['all'] = _t( '_All' );
                $sExtendedCss = 'hidden_block';
            }

            $iArrayCount = count($aCurrentArray);

            if ( is_array($aCurrentArray) and $iArrayCount ) {
                foreach( $aCurrentArray AS $sKey => $sValue ) {
                    $iDivider   = $iIndex % 2;

                    if ( $sKey != 'all')
                        $sValue = ( $sExtendedCss ) ? 0 : $sValue;

                    if ( $sKey == 'all' )
                        $sAgeSection .= $oObject -> genBrowseLink( $sValue, null, $oObject -> genLinkLocation( 'age', $sKey), 'post_featured.png');
                    else
                        $sAgeSection .= $oObject -> genBrowseLink( $sKey, $sValue, $oObject -> genLinkLocation( 'age', $sKey), 'birthday.png');

                    if ( $iDivider )
                        $sAgeSection  .=
                        '
                            <div class="clear_both"></div>
                        ';

                    $iIndex++;

                    if ( $iDivider and $iIndex >= 2 and $iArrayCount > 2 and ($iIndex <= $iArrayCount - 1) )
                        $sAgeSection  .=
                        '
                            <div class="devider"></div>
                        ';
                }
            }

            if ( !$sAgeSection )
                $sAgeSection = MsgBox(_t( '_Empty' ));

            // fill array with template's keys ;
            $aTemplateKeys = array
            (
                'section_id'     => 'age_section',
                'extended_css'   => $sExtendedCss,
                'section_data'   => $sAgeSection,
            );

            $sAgeSection = $GLOBALS['oSysTemplate'] -> parseHtmlByName( $aUsedTemplates[0], $aTemplateKeys );

            // return builded template ;
            return $sAgeSection;
        }

        /**
         * Function will generate country block ;
         *
         * @param        : $oObject (object) - link on created object;
         * @param        : $sCurrentKey (string) - key name, need for search into cacje file;
         * @return       : Html presentation data;
        */
        function _genCountrySection( &$oObject, $sCurrentKey  )
        {
            global $site;

            // init some variables ;
            $sCountrySection     = '';
            $sExtendedCss        = '';
            $aCurrentArray       = array();

            $aUsedTemplates = array
            (
                'browse_settings_section.html'
            );

            $iIndex  = 0; // ** need for dividers ;

            if ( isset($oObject -> aMembersInfo[$sCurrentKey]['country']) ) {
                $aCurrentArray    = &$oObject -> aMembersInfo[$sCurrentKey]['country'];
            } elseif ( $oObject -> aParameters['country'] ) {
                // ** if param `Country` was selected;
                // ** load data again;

                $aCurrentArray = $oObject -> _getGlobalStatistics
                (
                    $oObject -> aParameters['sex'],
                    $oObject -> aParameters['age'],
                    null,
                    $oObject -> aAdditionalParameters['photos_only'],
                    $oObject -> aAdditionalParameters['online_only'],
                    'country'
                );
            }

            // hide selected block ;
            if ( $oObject -> aParameters['country'] ) {
                $aCurrentArray['all'] = _t( '_All' );
                $sExtendedCss = 'hidden_block';
            }

            $iArrayCount = count($aCurrentArray);

            if ( is_array($aCurrentArray) and !empty($aCurrentArray) ) {
                foreach( $aCurrentArray AS $sKey => $sValue ) {
                    $iDivider   = $iIndex % 2;

                    if ( $sKey != 'all' )
                        $sValue = ( $sExtendedCss ) ? 0 : $sValue;

                    if ( $sKey == 'all' )
                        $sCountrySection .= $oObject -> genBrowseLink( $sValue, null, $oObject -> genLinkLocation( 'country', $sKey), 'post_featured.png');
                    else {
                        $sCountryName = strtolower($sKey);
                        $sImagePath = $site['flags'] . $sCountryName . '.gif';
                        $sCountrySection .= $oObject -> genBrowseLink( $sKey, $sValue, $oObject -> genLinkLocation( 'country', $sCountryName), $sImagePath, false );
                    }

                    if ( $iDivider )
                        $sCountrySection  .=
                        '
                            <div class="clear_both"></div>
                        ';

                    $iIndex++;

                    if ( $iDivider and $iIndex >= 2 and $iArrayCount > 2 and ($iIndex <= $iArrayCount - 1) )
                        $sCountrySection  .=
                        '
                            <div class="devider"></div>
                        ';
                }
            }

            if ( !$sCountrySection )
                $sCountrySection = MsgBox(_t( '_Empty' ));

            // fill array with template's keys ;
            $aTemplateKeys = array
            (
                'section_id'     => 'country_section',
                'extended_css'   => $sExtendedCss,
                'section_data'   => $sCountrySection,
            );

            $sCountrySection = $GLOBALS['oSysTemplate'] -> parseHtmlByName( $aUsedTemplates[0], $aTemplateKeys );

            // return builded template ;
            return $sCountrySection;
        }

        /**
        * Function will generate js control box (toggle block) ;
        *
        * @param        : $sCaption (string) - Block's caption ;
        * @param        : $sBlockID (string) - Block's ID ;
        * @param        : $bIsClosed (boolean) - if isset this param, that block will be hidde ;
        * @return       : Html presentation data ;
        */
        function _genJsControlBox( $sCaption, $sBlockID, $bIsHidden = false )
        {
            // lang keys
            $sToggleCaption = _t( '_Show' ) . ' / ' . _t( '_Hide' );

            // extended parameters
            $sVisibleParam = ( !$bIsHidden ) ? 'closed_toggle_block' : null;

            $sJsControlBlock =
            '
                <div class="caption_section">
                    ' . $sCaption . '
                </div>
                <div class="js_control_section ' . $sVisibleParam . '" bxchild="' . $sBlockID . '" title="' . $sToggleCaption . '">
                    &nbsp;
                </div>
                <div class="clear_both"></div>
            ';

            return $sJsControlBlock;
        }

        /**
        * Function will generate container for browse settings ;
        *
        * @param        : $sCaption (string) - Block's caption ;
        * @param        : $sContent (string) - Block's content ;
        * @return         : Html presentation data ;
        */
        function _genSubDesignBox( $sCaption, $sContent )
        {
            $sOutputHtml =
            '
                <div class="sub_design_box_head">
                    ' . $sCaption . '
                </div>
                ' . $sContent . '
            ';

            return $sOutputHtml;
        }

        // gen sex section ;
        $sSexSection  = _genSexSection( $this, $sCurrentKey );
        if ( $sSexSection ) {
            $sVisibleParam = ( $this -> aParameters['sex'] ) ? false : true;

            $sSexSection  =  _genSubDesignBox(
                _genJsControlBox($sSexCaption, 'sex_section', $sVisibleParam) ,
                $sSexSection
             );
        }

        // gen Age section ;
        $sAgeSection  = _genAgeSection( $this, $sCurrentKey );
        if ( $sAgeSection ) {
            $sVisibleParam = ( $this -> aParameters['age'] ) ? false : true;

            $sAgeSection  =  _genSubDesignBox(
                _genJsControlBox($sAgeCaption, 'age_section', $sVisibleParam),
                $sAgeSection
             );
        }

        // gen County section ;
        $sCountrySection  = _genCountrySection( $this, $sCurrentKey );
        if ( $sCountrySection ) {
            $sVisibleParam = ( $this -> aParameters['country'] ) ? false : true;

            $sCountrySection  =  _genSubDesignBox(
                _genJsControlBox($sCountryCaption, 'country_section', $sVisibleParam),
                $sCountrySection
             );
        }

        $sJsEventInit =
        '
            <script type="text/javascript">
                $(".js_control_section").click( function() { oBrowsePage.ShowHideToggle(this) } );
            </script>
        ';

        $sContent =
        '
            <div class="main_settings bx-def-bc-margin">
                <div class="devider"></div>
                ' . $sSexSection . '
                ' . $sAgeSection . '
                ' . $sCountrySection . '
            </div>
                ' . $sJsEventInit . '
        ';

        return DesignBoxContent( _t( '_Browse' ),  $sContent, 1);
    }

    /**
    * function will generate location for browse link ;
    * @param        : $sType (string) - type of link ;
    * @param        : $sTypeValue (string) - value of link ;
    * @return        : (string) location string ;
    */
    function genLinkLocation( $sType, $sTypeValue )
    {
        $sLocation = null;

        foreach( $this -> aParameters AS $sKey => $sValue ) {
            if ( $this -> bPermalinkMode ) {
                if ( $sType == $sKey )
                    $sLocation .= '/' . $sTypeValue;
                else
                    $sLocation .= ( $sValue ) ? '/' . $sValue : '/all';
            } else {
                if ( $sType == $sKey )
                    $sLocation .= '&' . $sKey . '=' . $sTypeValue;
                else
                    $sLocation .= ( $sValue ) ? '&' . $sKey . '=' . $sValue : '&' . $sKey . '=all';
            }
        }

        $sLocation = ($this->bPermalinkMode) ? BX_DOL_URL_ROOT . 'browse' . $sLocation : 'browse.php?' . $sLocation;

        // concatenate some    get visible params ;
        if ( isset($this -> aAdditionalParameters['photos_only']) && $this -> aAdditionalParameters['photos_only'])
            $sLocation .= "&amp;photos_only=on";

        if ( isset($this -> aAdditionalParameters['online_only']) && $this -> aAdditionalParameters['online_only'])
            $sLocation .= "&amp;online_only=on";

        if ( isset($this -> aDisplaySettings['mode']) && $this -> aDisplaySettings['mode'])
            $sLocation .= "&amp;mode=" . $this -> aDisplaySettings['mode'];

        return $sLocation;
    }

    /**
    * Function will find all needed members by some criteria ;
    *
    * @return  : Html presentation data ;
    */
    function getBlockCode_SearchedMembersBlock()
    {
        // lang keys ;
        $sOutputHtml = '';

        $sPhotoCaption  = _t( '_With photos only' );
        $sOnlineCaption = _t( '_online only' );
        $sSimpleCaption = _t( '_Simple' );
        $sExtendCaption = _t( '_Extended' );

        $aUsedTemplates = array
        (
            'browse_searched_block.html'
        );

        // collect the SQL parameters ;

        $aWhereParam = array();
        $aWhereParam[] = ( $this -> aParameters['sex'] )
            ? '`Profiles`.`Sex` = "' . $this -> aParameters['sex'] . '"'
            : null;

        if ( $this -> aParameters['age'] ) {
            $aAgeTemp = explode('-',  $this -> aParameters['age']);
            $iFrom = ( isset($aAgeTemp[0]) )
                ? (int) $aAgeTemp[0]
                : 0;

            $iTo = ( isset($aAgeTemp[1]) )
                ? (int) $aAgeTemp[1]
                : 0;

            unset($aAgeTemp);

            if ($iFrom) {
                $sSign = $iTo ? '>=' : '=';
                $aWhereParam[] =
                "
                    ((YEAR(CURDATE())-YEAR(`Profiles`.`DateOfBirth`)) - (RIGHT(CURDATE(),5)<RIGHT(`Profiles`.`DateOfBirth`,5))) {$sSign} {$iFrom}
                ";
            }

            if($iTo) {
                $sSign = $iFrom ? '<=' : '=';
                $aWhereParam[] =
                "
                    ((YEAR(CURDATE())-YEAR(`Profiles`.`DateOfBirth`)) - (RIGHT(CURDATE(),5)<RIGHT(`Profiles`.`DateOfBirth`,5))) {$sSign} {$iTo}
                ";
            }
        }

        $aWhereParam[] = ( $this -> aParameters['country'] )
            ? '`Profiles`.`Country` = "' . $this -> aParameters['country'] . '"'
            : null;

        if ( $this -> aAdditionalParameters['photos_only'] )
            $aWhereParam[] = '`Profiles`.`Avatar` <> 0';

        if ( $this -> aAdditionalParameters['online_only'] )
            $aWhereParam[] = "(`Profiles`.`DateLastNav` > SUBDATE(NOW(), INTERVAL " . $this -> iMemberOnlineTime . " MINUTE)) ";

        $sWhereParam = null;
        foreach( $aWhereParam AS $sValue )
            if ( $sValue )
                $sWhereParam .= ' AND ' . $sValue;

        // make search ;
        $sQuery = "SELECT COUNT(*) AS `Cnt` FROM `Profiles` WHERE {$this->_sSqlActive} {$sWhereParam}";
        $iTotalNum = db_value($sQuery);

        if( !$iTotalNum )
            $sOutputHtml = MsgBox(_t( '_Empty' ));

        // init some pagination parameters ;

        $iPerPage = $this -> aDisplaySettings['per_page'];
        $iCurPage = $this -> aDisplaySettings['page'];

        if( $iCurPage < 1 )
            $iCurPage = 1;

        $sLimitFrom = ( $iCurPage - 1 ) * $iPerPage;
        $sqlLimit = "LIMIT {$sLimitFrom}, {$iPerPage}";

        // switch template for `simle` and `advanced` mode ;

        $sTemplateName = ($this->aDisplaySettings['mode'] == 'extended') ? 'search_profiles_ext.html' : 'search_profiles_sim.html';

        // select sorting parameters ;
        $sSortParam = '`Profiles`.`DateLastNav` DESC';
        if ( isset($this -> aDisplaySettings['sort']) ) {
            switch($this -> aDisplaySettings['sort']) {
                case 'date_reg' :
                    $sSortParam = ' `Profiles`.`DateReg` DESC';
                    break;
                case 'rate' :
                    $sSortParam = ' `Profiles`.`Rate` DESC, `Profiles`.`RateCount` DESC';
                    break;
                default :
                    $this -> aDisplaySettings['sort'] = 'activity';
                case 'activity' :
                    $sSortParam = ' `Profiles`.`DateLastNav` DESC';
                    break;
            }
        } else
            $this -> aDisplaySettings['sort'] = 'activity';

        // status uptimization
        $iOnlineTime = (int)getParam( "member_online_time" );
        $sIsOnlineSQL = ", if(`DateLastNav` > SUBDATE(NOW(), INTERVAL {$iOnlineTime} MINUTE ), 1, 0) AS `is_online`";

        $sQuery  =
        "
            SELECT
                `Profiles`.* {$sIsOnlineSQL}
            FROM
                `Profiles`
            WHERE
                {$this->_sSqlActive}
                {$sWhereParam}
            ORDER BY
                {$sSortParam}
            {$sqlLimit}
        ";

        $rResult = db_res($sQuery);
        $iIndex = 0;

        // need for the block divider ;
        $aExtendedCss = array(
            'ext_css_class' => $this->aDisplaySettings['mode'] == 'extended' ? 'search_filled_block' : ''
        );
        while( true == ($aRow = $rResult->fetch()) ) {
            // generate the `couple` thumbnail ;
            if ( $aRow['Couple']) {
                $aCoupleInfo = getProfileInfo( $aRow['Couple'] );
                $sOutputHtml .= $this -> oSearchProfileTmpl -> PrintSearhResult($aRow, $aCoupleInfo, ($iIndex % 2 ? $aExtendedCss : array()), $sTemplateName);
            } else
                $sOutputHtml .= $this -> oSearchProfileTmpl -> PrintSearhResult($aRow, array(), ($iIndex % 2 ? $aExtendedCss : array()), $sTemplateName);
            $iIndex++;
        }
        // # end of search generation ;

        // work with link pagination ;
        if ( $this -> bPermalinkMode ) {
            preg_match("|([^\?\&]*)|", $_SERVER['REQUEST_URI'], $aMatches);
            if ( isset( $aMatches[1] ) and $aMatches[1] )
                $sRequest  = $aMatches[1] . '?';

            // need for additional parameters ;
            $aGetParams = array('photos_only', 'online_only', 'sort', 'mode');
            foreach($aGetParams AS $sValue )
                if ( isset($_GET[$sValue]) ) {
                    $sRequest .= '&' . $sValue . '=' . rawurlencode($_GET[$sValue]);
                }
        } else {
            $sRequest = BX_DOL_URL_ROOT . 'browse.php?';

            // need for additional parameters ;
            $aGetParams = array('sex', 'age', 'country','photos_only', 'online_only', 'sort', 'mode');
            foreach($aGetParams AS $sValue )
                if ( isset($_GET[$sValue]) ) {
                    $sRequest .= '&' . $sValue . '=' . rawurlencode($_GET[$sValue]);
                }
        }

        // cutted al aunecessary parameters ;
        $sRequest = getClearedParam( 'sort',  $sRequest);

        $sRequest = $sRequest . '&page={page}&per_page={per_page}&sort={sorting}';

        // gen pagination block ;
        $oPaginate = new BxDolPaginate
        (
            array
            (
                'page_url'   => $sRequest,
                'count'      => $iTotalNum,
                'per_page'   => $iPerPage,
                'sorting'    => $this -> aDisplaySettings['sort'],
                'page'       => $iCurPage,
            )
        );

        $sPagination = $oPaginate -> getPaginate();

        // gen per page block ;
        $sPerPageBlock = $oPaginate -> getPages( $iPerPage );

        // prepare to output ;
        $sOutputHtml .=
        '
            <div class="clear_both"></div>
        ';

        $sRequest = str_replace('{page}', '1', $sRequest);
        $sRequest = str_replace('{per_page}', $iPerPage, $sRequest);
        $sRequest = str_replace('{sorting}', $this -> aDisplaySettings['sort'], $sRequest);

        // fill array with sorting params
        $aSortingParam = array(
            'activity' => _t('_Latest activity'),
            'date_reg' => _t('_FieldCaption_DateReg_View'),
        );
        if (getParam('votes')) $aSortingParam['rate'] = _t('_Rate');

        // gen sorting block ( type of : drop down ) ;

        $sSortBlock = $oPaginate -> getSorting($aSortingParam);

        // init some visible parameters ;

        $sPhotosChecked = ($this -> aAdditionalParameters['photos_only']) ? 'checked="checked"' : null;

        $sOnlineChecked = ($this -> aAdditionalParameters['online_only']) ? 'checked="checked"' : null;

        // ** cutting all unnecessary get parameters ;

        // link for photos section ;

        $sPhotoLocation = getClearedParam( 'photos_only',  $sRequest);

        // link for online section ;

        $sOnlineLocation = getClearedParam( 'online_only',  $sRequest);

        // link for `mode switcher` ;

        $sModeLocation = getClearedParam( 'mode',  $sRequest);
        $sModeLocation = getClearedParam( 'per_page',  $sModeLocation);

        // ** gen header part - with some display options ;

        // fill array with template's keys ;
        bx_import('BxDolMemberInfo');
        $oMemberInfo = BxDolMemberInfo::getObjectInstance(getParam('sys_member_info_thumb'));

        $sTopControls = $GLOBALS['oSysTemplate']->parseHtmlByName('browse_sb_top_controls.html', array(
            'sort_block' => $sSortBlock,
            'bx_if:show_with_photos' => array(
                'condition' => $oMemberInfo ? $oMemberInfo->isAvatarSearchAllowed() : false,
                'content' => array(
                    'photo_checked'   => $sPhotosChecked,
                    'photo_location'  => $sPhotoLocation,
                    'photo_caption'   => $sPhotoCaption,
                )
            ),
            'online_checked' => $sOnlineChecked,
            'online_location' => $sOnlineLocation,
            'online_caption' => $sOnlineCaption,
            'per_page_block' => $sPerPageBlock,
        ));

        // build template ;
        $sOutputHtml = $GLOBALS['oSysTemplate'] -> parseHtmlByName( $aUsedTemplates[0], array(
            'top_controls' => $sTopControls,
            'bx_if:show_sim_css' => array (
                'condition' => $this->aDisplaySettings['mode'] != 'extended',
                'content' => array()
            ),
            'bx_if:show_ext_css' => array (
                'condition' => $this->aDisplaySettings['mode'] == 'extended',
                'content' => array()
            ),
            'searched_data'   => $sOutputHtml,
            'pagination'      => $sPagination,
        ));

        // generate toggle ellements ;
        $aToggleItems = array (
            ''             =>  _t( '_Simple' ),
            'extended'     =>    _t( '_Extended' ),
        );

        foreach( $aToggleItems AS $sKey => $sValue ) {
            $aToggleEllements[$sValue] = array (
                'href' => $sModeLocation . '&mode=' . $sKey,
                'dynamic' => false,
                'active' => ($this -> aDisplaySettings['mode'] == $sKey ),
            );
        }

        return array($sOutputHtml, $aToggleEllements, array(), true);
    }
}
