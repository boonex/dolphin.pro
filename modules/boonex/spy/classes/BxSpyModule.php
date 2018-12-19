<?php
    /**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    bx_import('BxDolModuleDb');
    bx_import('BxDolModule');
    bx_import('BxDolPageView');

    require_once('BxSpyResponseContent.php');
    require_once('BxSpyResponseProfiles.php');
    require_once('BxSpySearch.php');

    /**
     * Spy module by BoonEx
     *
     * This module will show all system events recived by some of modules.
     * This is default module and Dolphin can not work properly without this module.
     *
     *
     *
     * Profile's Wall:
     * no wall events
     *
     *
     *
     * Spy:
     * no spy events
     *
     *
     *
     * Memberships/ACL:
     * no levels;
     *
     *
     *
     * Service methods:
     *
     * Process all alerts.
     * @see BxSpyModule::serviceResponse
     * BxDolService::call('spy', 'response', array($oAlert));
     *
     * This module will check for spy module alerts across all other Dolphin modules.
     * @see BxSpyModule::serviceUpdateHandlers
     * BxDolService::call('spy', 'update_handlers', array(sModuleUri, $bInstall));
     *
     * Function will get spy block;
     * @see BxSpyModule::serviceGetSpyBlock
     * BxDolService::call('spy', 'get_spy_block', array($sPage, $iProfileId));
     *
     * Function will generate content for member menu (latest activity);
     * @see BxSpyModule::serviceGetMemberMenuSpyData
     * BxDolService::call('spy', 'get_member_menu_spy_data', array());
     *
     * Function will generate content for member menu's bubbles;
     * @see BxSpyModule::serviceGetMemberMenuBubblesData
     * BxDolService::call('spy', 'get_member_menu_spy_data', array(iOldCount));
     *
     *
     * Alerts:
     * no alerts;
     *
     */
    class BxSpyModule extends BxDolModule
    {
        // contain some module information ;
        var $aModuleInfo;

        // contain path for current module;
        var $sPathToModule;

        // contain link on created search object;
        var $oSearch;

        var $sSpyMode;

        // logged member's Id;
        var $iMemberId;

        var $sEventsWrapper = 'spy_events';

        var $iPage = 1;
        var $iPerPage;

        /**
         * Class constructor ;
         *
         * @param   : $aModule (array) - contain some information about this module;
         *                  [ id ]           - (integer) module's  id ;
         *                  [ title ]        - (string)  module's  title ;
         *                  [ vendor ]       - (string)  module's  vendor ;
         *                  [ path ]         - (string)  path to this module ;
         *                  [ uri ]          - (string)  this module's URI ;
         *                  [ class_prefix ] - (string)  this module's php classes file prefix ;
         *                  [ db_prefix ]    - (string)  this module's Db tables prefix ;
         *                  [ date ]         - (string)  this module's date installation ;
         */
        function __construct(&$aModule)
        {
            parent::__construct($aModule);

            // prepare the location link ;
            $this -> sPathToModule  = BX_DOL_URL_ROOT . $this -> _oConfig -> getBaseUri();
            $this -> aModuleInfo    = $aModule;

            $this -> oSearch        = new BxSpySearch($this);

            // define current page's mode;
            $this -> sSpyMode   = isset($_GET['mode']) ? $_GET['mode'] : 'global';

            $this -> iMemberId = getLoggedId();

            $this -> iPage  = ( isset($_GET['page']) )
                ? (int) $_GET['page']
                : 1;

            $this -> iPerPage = ( isset($_GET['per_page']) )
                ? (int) $_GET['per_page']
                : $this -> _oConfig -> iPerPage;
        }

        /**
         * Function will generate the poll's admin page ;
         *
         * @return : (text) - Html presentation data ;
         */
        function actionAdministration()
        {
            $GLOBALS['iAdminPage'] = 1;

            if( !isAdmin() ) {
                header('location: ' . BX_DOL_URL_ROOT);
            }

            bx_import('BxDolAdminSettings');

            $aLanguageKeys = array(
                'premoderation' => _t('_bx_spy_admin'),
            );

            //-- define page's action --//
            $sAction = ( isset($_GET['action']) ) ? $_GET['action'] : null;
            $aMenu   = array();

            switch ($sAction) {
                default:
                    $aMenu['bx_spy_main']['active'] = 1;

                    $iCategoryId = $this-> _oDb -> getSettingsCategory('bx_spy_keep_rows_days');
                    if(!$iCategoryId) {
                        $sContent = MsgBox( _t('_Empty') );
                    } else {
                            $mixedResult = '';
                            if(isset($_POST['save']) && isset($_POST['cat'])) {
                                $oSettings = new BxDolAdminSettings($iCategoryId);
                                $mixedResult = $oSettings -> saveChanges($_POST);
                            }

                            $oSettings = new BxDolAdminSettings($iCategoryId);
                            $sResult = $oSettings->getForm();

                            if($mixedResult !== true && !empty($mixedResult))
                                $sResult = $mixedResult . $sResult;

                            $sContent = $GLOBALS['oAdmTemplate']
                                    -> parseHtmlByName( 'design_box_content.html', array('content' => $sResult) );

                    }
            }

            $this -> _oTemplate-> pageCodeAdminStart();
            echo $this -> _oTemplate -> adminBlock ($sContent, $aLanguageKeys['premoderation']);
            $this -> _oTemplate->pageCodeAdmin( _t('_bx_spy_module') );
        }

        /**
         * Function will set all profile's activity as read;
         *
         * @return : void;
         */
        function actionSetAsRead()
        {
           $this -> _oDb -> setViewedProfileActivity($this -> iMemberId);
        }

        /**
         * Function will get new activity by type;
         *
         * @param  : $sMode (string) - page's mode (possible values : global, friends_events);
         * @param  : $iLastActivityId (integer) - last event's Id;
         * @param  : $sType (string) - activity type;
         * @return : (text) - html presentation data;
         */
        function actionCheckUpdates($sMode = 'global', $iLastActivityId = 0, $sType = '', $iProfileId = 0)
        {
            $sPageUrl  = $this -> sPathToModule;
            $iLastActivityId = (int) $iLastActivityId;
            $iProfileId = (int) $iProfileId;

            // set filter;
            if($sType && $sType != 'all') {
                $this -> oSearch -> aCurrent['restriction']['type']['value'] = process_db_input($sType, BX_TAGS_STRIP);
            }

            if($iProfileId) {
                // get only profile's activity;
                $this -> oSearch -> aCurrent['restriction']['only_me']['value'] = $iProfileId;
            }

            switch($sMode) {
                case 'friends_events' :
                    //-- if member not logged function will draw login form --//;
                    if(!$this -> iMemberId ) {
                        exit( member_auth(0) );
                    }

                    $this -> oSearch -> aCurrent['join'][] = array(
                        'type'      => 'INNER',
                        'table'     => $this -> _oDb -> sTablePrefix . 'friends_data',
                        'mainField' => 'id',
                        'onField'   => 'event_id',
                        'joinFields' => array(),
                    );

                    $this -> oSearch -> aCurrent['restriction']['friends']['value'] = $this -> iMemberId;
                    $this -> oSearch -> aCurrent['restriction']['no_my']['value']   = $this -> iMemberId;
                    $this -> oSearch -> aCurrent['restriction']['over_id']['value'] = $iLastActivityId;

                    $sPageUrl .= '&mode=' . $this -> sSpyMode;
                break;

                default :
                    $this -> oSearch -> aCurrent['restriction']['id'] = array(
                        'field'     => 'id',
                        'operator'  => '>',
                        'value'     => $iLastActivityId,
                    );
            }

            // get data;
            $aActivites = $this -> oSearch -> getSearchData();
            $aProccesedActivites = $this -> _proccesActivites($aActivites, ' style="display:none" ', true);

            $aRet = array(
                'events'        => $aProccesedActivites,
                'last_event_id' => $this -> _oDb -> getLastActivityId($sType),
            );

            // draw builded data;
            echo json_encode($aRet);
        }

        /**
         * Function will get profile's spy block by ajax method;
         *
         * @param  : $iPage (integer) - current page;
         * @param  : $sActivityType (string) - type of activity;
         * @return : (text) html presentation data;
         */
        function actionGetMemberBlock($sActivityType = '')
        {
            $this -> serviceGetMemberSpyBlock(true, $sActivityType);
        }

        /**
         * Function will generate global spy's page (content activity only!);
         *
         * @param  : $sType (string) - type of activity;
         * @return : (text) - html presentation data;
         */
        function getActivityPage($iProfile = 0, $sType = '')
        {
            $sPageUrl = $this -> sPathToModule;
            if(!empty($this->sSpyMode))
                $sPageUrl .= '&mode=' . $this->sSpyMode;

            $aParams = array(
                'active' => true,
                'page_ajax' => false,
            	'page_url' => $this->_oConfig->getBaseUri(),
                'profile' => $iProfile
            );

            if($sType) {
                $aParams['type'] = $sType;
                $sPageUrl .= '&spy_type=' . $sType;
            }

            $aCode = $this->_getSpyBlock($aParams);

            $sOutputCode = $aCode[0];
            if(isset($aCode[2]))
                $sOutputCode .= $aCode[2];

            return $sOutputCode;
        }

        /**
         * Function will get the javascript code that will update spy page every times;
         *
         * @param  : $iProfileId (integer) - profile's Id;
         * @param  : $sType (string) - type of activity;
         * @return : (text) - javascript code;
         */
        function getInitPart($sType = '', $iProfileId = 0, $bActive = true)
        {
            $this -> _oTemplate  -> addJs('spy.js');

            // define some parameters;
            $iProfileId = (int)$iProfileId;
            if($sType)
                $sType = bx_js_string( strip_tags($sType));

            if($this -> sSpyMode == 'friends_events' && $iProfileId) {
                $iLastActivityId = $this -> _oDb -> getLastFriendsActivityId($iProfileId, $sType);
                $iActivityCount  = $this -> _oDb -> getFriendsActivityCount($iProfileId, $sType);
            } else {
                $iLastActivityId = $this -> _oDb -> getLastActivityId($sType);
                $iActivityCount  = $this -> _oDb -> getActivityCount($sType);
            }

            $sActive = $bActive ? 'true' : 'false';

            $sOutputCode  = <<<JS
                <script type="text/javascript">
                    $(document).ready(function () {
                        oSpy = new BxSpy();
                        oSpy.sPageReceiver = '{$this -> sPathToModule}';
                        oSpy.iUpdateTime = {$this -> _oConfig -> iUpdateTime};
                        oSpy.sEventsContainer = '{$this -> sEventsWrapper}';
                        oSpy.sPageMode = '{$this -> sSpyMode}';
                        oSpy.iLastEventId = {$iLastActivityId};
                        oSpy.iEventsCount = {$iActivityCount};
                        oSpy.iPerPage = {$this -> iPerPage};
                        oSpy.iSlideDown = {$this -> _oConfig -> iSpeedToggleDown};
                        oSpy.iSlideUp = {$this -> _oConfig -> iSpeedToggleUp};
                        oSpy.sActivityType = '{$sType}';
                        oSpy.iProfileId = {$iProfileId};
                        oSpy.bUpdateAllowed = {$sActive};

                        oSpy.PageUpdate();
                    });
                </script>
JS;

            return $sOutputCode;
        }

        /**
         * Function will generate activity types toggle elements;
         *
         * @param  : $sPageUrl (string) - nedded page's url;
         * @return : (text) - html presentation data;
         */
        function getActivityTypesToggles($sPageUrl = '')
        {
            //-- Generate the page toggle ellements -- //;
            $aToggleItems = array (
                'all'                =>  _t( '_bx_spy_all_activity' ),
                'content_activity'   =>  _t( '_bx_spy_content_updates' ),
                'profiles_activity'  =>  _t( '_bx_spy_profiles_updates' ),
            );

            // define page's mode;
            $sExtraParam = null;
            if( isset($_GET['mode']) ) {
                $sExtraParam = '&mode=' . $_GET['mode'];
            }

            //$sExtraParam = '?' . substr($sExtraParam,1);

            // define page's Url;
            $sRequest = (!$sPageUrl) ? $this -> sPathToModule . $sExtraParam : $sPageUrl . $sExtraParam  ;

            foreach( $aToggleItems AS $sKey => $sValue ) {
                $aTopToggleEllements[$sValue] = array
                (
                    'href' => $sRequest . '&spy_type=' . $sKey,
                    'dynamic' => false,
                    'active'  => ( (isset($_GET['spy_type']) && $_GET['spy_type'] == $sKey)
                                        || !isset($_GET['spy_type']) && $sKey == 'all' ) ? true : false,
                );
            }

            return BxDolPageView::getBlockCaptionItemCode(time(), $aTopToggleEllements );
        }

        /**
         * SERVICE METHODS
         * Process alert.
         *
         * @param BxDolAlerts $oAlert an instance with accured alert.
         */
        function serviceResponseContent($oAlert)
        {
            $oResponse = new BxSpyResponseContent($this);
            $oResponse -> response($oAlert);
        }

        function serviceResponseProfiles($oAlert)
        {
            $oResponse = new BxSpyResponseProfiles($this);
            $oResponse -> response($oAlert);
        }

        function serviceUpdateHandlers($sModuleUri = 'all', $bInstall = true)
        {
            $aModules = $sModuleUri == 'all' ? $this -> _oDb -> getModules() : array($this->_oDb->getModuleByUri($sModuleUri));

            foreach($aModules as $aModule) {
                if(!BxDolRequest::serviceExists($aModule, 'get_spy_data'))
                    continue;

                $aData = BxDolService::call($aModule['uri'], 'get_spy_data');
                if($bInstall)
                    $this -> _oDb -> insertData($aData);
                else
                    $this -> _oDb -> deleteData($aData);
            }

            BxDolAlerts::cache();
        }

        /**
         * Generate spy block
         *
         * @param $sPage string
         * @param $iProfileId integer
         * @return array
         */
        function serviceGetSpyBlock($sPage = 'index.php', $iProfileId = 0)
        {
            //-- process external vars --//
            return $this -> _getSpyBlock(array(
                'page_url' => rawurlencode($sPage),
                'profile' => (int)$iProfileId
            ));
        }

        function serviceGetSpyBlockFriends($iProfileId = 0)
        {
            $this->sSpyMode = 'friends_events';

            $sPageUrl = 'viewFriends.php?iUser=' . $iProfileId;
            return $this -> _getSpyBlock(array(
                'page_url' => $sPageUrl,
                'profile' => (int)$iProfileId
            ));
        }

        /**
         * Function will generate content for member menu;
         *
         * @return : (text) - html presentation data;
         */
        function serviceGetMemberMenuSpyData()
        {
            $sOutputCode = null;
            // define member's Id;
            $iProfileId = $this -> getUserId();

            if(!$iProfileId) {
                return;
            }

            // get only member's activity;
            $this -> oSearch -> aCurrent['restriction']['only_me']['value'] = $iProfileId;
            $this -> oSearch -> aCurrent['paginate']['limit'] = $this -> _oConfig -> iMemberMenuNotifyCount;

            // get data;
            $aActivites = $this -> oSearch -> getSearchData();

            // proccess recived data;
            if($aActivites) {
                // procces all activites;
                $sOutputCode = $this -> _proccesActivites($aActivites, '', false, false, 'spy_events_wrapper_menu_member');
                $sOutputCode .= $this->_oTemplate->parseHtmlByName('member_menu_link.html', array(
                    'link' => $this->_oConfig->getBaseUri(),
                    'caption' => _t('_bx_spy_view_all'),
                ));
            } else {
                $sOutputCode = MsgBox( _t('_Empty') );
            }

            echo $this -> _oTemplate  -> addCss('spy.css', true);
            echo $sOutputCode;
        }

        /**
         * Function will get bubbles data for member menu;
         *
         * @param  : $iOldCount (integer) - received old count of messages (if will difference will generate message)
         * @return : (array)
                [count]     - (integer) number of new notifications;
                [message]   - (string) notify's messages;
         */
        function serviceGetMemberMenuBubblesData($iOldCount)
        {
            global $oSysTemplate;

            $iOldCount = (int) $iOldCount;
            $iNewNotifyCount = 0;
            $aNotifyMessages = array();

            // define member's Id;
            $iProfileId = $this -> getUserId();

            if($iProfileId) {
                // get all profile's notifications;
                $this -> oSearch -> aCurrent['restriction']['only_me']['value'] = $iProfileId;
                $this -> oSearch -> aCurrent['restriction']['type']['value'] = '';
                // get all unviewed data;
                $this -> oSearch -> aCurrent['restriction']['viewed']['value']  = array(0);
                $this -> oSearch -> aCurrent['paginate']['unlimit'] = true;

                // get data;
                $aActivites = $this -> oSearch -> getSearchData();

                if($aActivites) {
                    $aActivites = array_reverse($aActivites);
                    $iNewNotifyCount = count($aActivites);

                    // if have some difference;
                    if ($iNewNotifyCount > $iOldCount) {
                        // generate notify messages;
                        for( $i = $iOldCount; $i < $iNewNotifyCount; $i++) {
                            $aTemp[] = $aActivites[$i];
                            $sMessage = $this -> _proccesActivites($aTemp);
                            unset($aTemp);
                            $aNotifyMessages[] = array(
                                'message' => $oSysTemplate -> parseHtmlByName('member_menu_notify_window.html', array('message' => $sMessage))
                            );
                        }
                    }
                }
            }

            $sCode = 'var _sRandom = Math.random(); $.get("' . $this -> sPathToModule . 'set_as_read&_random=" + _sRandom);';
            $aRetEval = array(
                'count'     => $iNewNotifyCount,
                'messages'  => $aNotifyMessages,
                'onlclick_script'  => $aActivites ? $sCode : '',
            );

            return $aRetEval;
        }

        /**
         * Function will replace all markers into recived string;
         *
         * @param  : $sKey        (string) - language key;
         * @param  : $aParameters (array)  - key's parameters;
         * @return : (string) - replaced string;
         */
        function _parseParameters($sKey, &$aParameters)
        {
            if( $aParameters and is_array($aParameters) ) {
                foreach($aParameters as $sArrayKey => $aItems) {
                    $sKey = str_replace('{' . $sArrayKey . '}', $aParameters[$sArrayKey], $sKey);
                }
            }

            return $sKey;
        }

        /**
         * Generate spy block
         *
         * @param $aVars array
         * 		$aVars[type] - string
         * 		$aVars[page_url] - string
         * 		$aVars[page] - integer
         * 		$aVars[profile] - integer
         * @return array
         */
        function _getSpyBlock($aVars)
        {
            if(!isset($aVars['active']))
                $aVars['active'] = false;
            if(!isset($aVars['dynamic']))
                $aVars['dynamic'] = bx_get('dynamic') !== false;
            if(!isset($aVars['type']))
                $aVars['type'] = bx_get('type') !== false ? bx_get('type') : 'all';
            if(!isset($aVars['page_ajax']))
                $aVars['page_ajax'] = true;
            if(!isset($aVars['page']))
                $aVars['page'] = bx_get('page') !== false ? (int) bx_get('page') : 1;
            $aVars['page']= $aVars['page'] > 0 ? $aVars['page'] : 1;

            //-- set search filter --//
            $this -> oSearch -> aCurrent['restriction']['viewed']['value']  = '';
            if($aVars['type'] != 'all')
                $this -> oSearch -> aCurrent['restriction']['type']['value'] = process_db_input($aVars['type'], BX_TAGS_STRIP);

            switch($this->sSpyMode) {
                case 'friends_events':
                    $this -> oSearch -> aCurrent['join']['friends_data'] = array(
                        'type'      => 'INNER',
                        'table'     => $this -> _oDb -> sTablePrefix . 'friends_data',
                        'mainField' => 'id',
                        'onField'   => 'event_id',
                        'joinFields' => array(),
                    );

                    $this -> oSearch -> aCurrent['restriction']['friends']['value'] = $aVars['profile'];
                    $this -> oSearch -> aCurrent['restriction']['no_my']['value'] = $aVars['profile'];
                    break;

                default:
                    //--- get only member's activity ---//
                    if($aVars['profile'])
                        $this -> oSearch -> aCurrent['restriction']['only_me']['value'] = $aVars['profile'];
            }

            //-- get data --//
            $aActivites = $this -> oSearch -> getSearchData();
            $sActivites = $this -> _proccesActivites($aActivites);

            $sOutputCode = $this->_oTemplate->getWrapper($this -> sEventsWrapper, $aActivites ? $sActivites : MsgBox(_t('_Empty')));

            //-- process pagination URL --//
            $sPaginate = '';
            if($this -> oSearch -> aCurrent['paginate']['totalNum'] > $this -> _oConfig -> iPerPage) {
                $aVars['page_url'] .= (strpos($aVars['page_url'], '?') === false ? '?' : '&amp;') . 'type=' . $aVars['type'] . '&page={page}&per_page={per_page}';

                $sOnClick = '';
                if($aVars['page_ajax'])
                    $sOnClick = 'return !loadDynamicBlock({id}, \'' . $aVars['page_url'] . '\')';

                $oPaginate = new BxDolPaginate(array(
                    'page_url' =>  $aVars['page_url'],
                    'count' => $this -> oSearch -> aCurrent['paginate']['totalNum'],
                    'per_page' => $this -> _oConfig -> iPerPage,
                    'page' =>  $aVars['page'],
                    'on_change_page' => $sOnClick,
                ));

                $sPaginate = $oPaginate -> getSimplePaginate(null, -1, -1, false);
            }

            if($aVars['dynamic'])
                header('Content-Type: text/html; charset=utf-8');
            else
                $this ->_oTemplate->addCss('spy.css');

            //-- check init part --//
            if($aVars['page'] == 1)
                $sOutputCode = $this -> getInitPart($aVars['type'], $aVars['profile'], $aVars['active']) . $sOutputCode;
            return array($sOutputCode, array(), $sPaginate, true);
        }

        /**
         * Function will procces recived activites;
         *
         * @param  : $aActivites (array);
         * @param  : $sExtraStyles (string) - extra css styles;
         * @param  : $bSetViewed  (boolean) - if isset this parameter activity will; set as viwed;
         * @return : (mixed) - html presentation data or array;
         */
        function _proccesActivites($aActivites, $sExtraStyles = '', $inArray = false, $bSetViewed = false, $sExtraCssClass = null)
        {
            $sOutputCode = null;
            $aProcessedActivites = array();

            if( is_array($aActivites) ) {
                foreach($aActivites as $iKey => $aItems) {
                    $aParams  = unserialize($aItems['params']);

                    if($bSetViewed) {
                        $this -> _oDb -> setViewed($aItems['id']);
                    }

                    // procces activity text;
                    $sActivity = $this -> _parseParameters( _t($aItems['lang_key']), $aParams );

                    // define activity's sender;
                    if($aItems['sender_id']) {
                        $aTemplateKeys = array(
                            'sender_thumb'    =>  get_member_icon($aItems['sender_id'], 'none'),
                            'event_caption'   => $sActivity,
                            'extra_styles'    => $sExtraStyles,
                            'extra_css_class' => $sExtraCssClass,
                            'date_add'        => getLocaleDate( strtotime($aItems['date']), BX_DOL_LOCALE_DATE),
                        );

                        $sTemplateName = 'activity.html';
                    } else {
                        $aTemplateKeys = array(
                            'event_caption' => $sActivity,
                            'extra_styles'  => $sExtraStyles,
                            'extra_css_class' => $sExtraCssClass,
                            'date_add'      => getLocaleDate( strtotime($aItems['date']), BX_DOL_LOCALE_DATE),
                        );

                        $sTemplateName = 'non_member_activity.html';
                    }

                   // build data;
                   if(!$inArray) {
                        $sOutputCode .= $this -> _oTemplate -> parseHtmlByName($sTemplateName, $aTemplateKeys);
                   } else {
                        $aProcessedActivites[] = array(
                            'event' => $this -> _oTemplate -> parseHtmlByName($sTemplateName, $aTemplateKeys),
                        );
                   }
                }
            }

            return (!$inArray) ? $sOutputCode : $aProcessedActivites;
        }
    }
