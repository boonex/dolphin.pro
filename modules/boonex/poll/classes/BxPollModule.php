<?php

    /**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    require_once(BX_DIRECTORY_PATH_CLASSES  . 'BxDolModule.php');
    require_once(BX_DIRECTORY_PATH_CLASSES  . 'BxDolAlerts.php');
    require_once(BX_DIRECTORY_PATH_CLASSES  . 'BxDolCategories.php');

    require_once('BxPollCalendar.php');
    require_once('BxPollPrivacy.php');
    require_once('BxPollSearch.php');

    define( 'BX_POLL_VOTE_UNIQ', 60*60*24*30); // within 1 month vote poll is uniq
    define( 'BX_POLL_ANS_DIVIDER', '<delim>');
    define( 'BX_POLL_RES_DIVIDER', ';');

    define( 'POLL_ERROR_OCCURED', _t( '_Error occured') );
    define( 'POLL_EMPTY_FIELDS',  _t( '_please_fill_next_fields_first') );
    define( 'POLL_CREATED',       _t( '_bx_poll_created') );
    define( 'POLL_EDITED',        _t( '_bx_poll_was_edited') );
    define( 'POLL_MAX_REACHED',   _t( '_bx_poll_max_reached') );
    define( 'POLL_NOT_ALLOW',     _t( '_bx_poll_not_available') );

    class BxCheckerPoll extends BxDolFormCheckerHelper
    {
        function checkAnswers($aItems, $iLenMin, $iLenMax)
        {
            if (count($aItems) < 2) {
                return false;
            }

            return parent::checkLength ($aItems, $iLenMin, $iLenMax);
        }

        function passAnswers ($a)
        {
            if (is_array($a))
                foreach ($a as $k => $v)
                    $a[$k] = $this->passXss ($v);
            return is_array($a) ? implode(',', $a) : $a;

        }
    }

    /**
     * Poll module by BoonEx
     *
     * This module allow users to create some of polls.
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
     * 'add poll' events are displayed on spy's page
     *
     *
     *
     * Memberships/ACL:
     * no levels here;
     *
     *
     *
     * Service methods:
     *
     * Generate polls list.
     * @see BxPollModule::serviceGetPolls
     * BxDolService::call('poll', 'get_polls', array($sAction, $iProfileId));
     *
     * Generate poll's link into member menu in 'profile' section (this link will show the number of profile's polls).
     * @see BxPollModule::serviceGetMemberMenuLink
     * BxDolService::call('poll', 'get_member_menu_link', array($iMemberId));
     *
     * Will draw edit poll's button ;
     * @see BxPollModule::serviceEditActionButton
     * BxDolService::call('poll', 'edit_action_button', array($iMemberId, $iPollId));
     *
     * Will draw delete poll's button ;
     * @see BxPollModule::serviceDeleteActionButton
     * BxDolService::call('poll', 'delete_action_button', array($iMemberId, $iPollId));
     *
     * will return all needed systems alerts for 'spy' module;
     * @see BxPollModule::serviceGetSpyData
     * BxDolService::call('poll', 'get_spy_data', array());
     *
     * will return sys alerts' answer on some action for 'spy' module;
     * @see BxPollModule::serviceGetSpyPost
     * BxDolService::call('poll', 'get_spy_post', array($sAction, $iObjectId, $iSenderId));
     *
     *
     *
     * Alerts:
     * Alerts type/unit - 'bx_poll'
     * The following alerts are rised
     *
     *  vote  - vote for some of poll
     *      $iPollID - poll's Id
     *      $iVoteNumber - vote number
     *
     *  delete_poll - delete poll
     *      $iPollId - poll's id
     *
     *  add - add new poll
     *      $iPollID - poll's Id
     *
     *  edit - edit poll
     *      $iPollId - poll's Id
     */
    class BxPollModule extends BxDolModule
    {
        var $sHomeUrl;

        // contain all needed templates name;
        var $aUsedTemplates;

        // contain some of needed poll's settings;
        var $aPollSettings;

        // contain some module information ;
        var $aModuleInfo;

        // contain answer for some member's action ;
        var $sActionAnswer = null;

        // contain path to current module;
        var $sPathToModule = null;

        // privacy object;
        var $oPrivacy = null;

        // title of poll's home page;
        var $sPollHomeTitleLenght = 70;

        // number of polls elements for  per line;
        var $iPollsForPerLine = 3;

        var $oSearch;

        /**
         * Constructor ;
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
         * @param   : $aPollSettings (array) - contain some needed poll's settings;
         *                  [ admin_mode ]   - (boolean)  check admin mode ;
         *                  [ member_id ]    - (integer)  logged member's id ;
         *                  [ page_columns ] - (integer)  number of poll's columns for per page ;
         *                  [ per_page ]     - (integer)  number of poll's elements for per page ;
         *                  [ page ]         - (integer)  current page ;
         *                  [ action ]       - (string)  contain some specific actions for pools ;
         */
        function __construct($aModule, $aPollSettings = array() )
        {
            global $logged;

            parent::__construct($aModule);
            $this -> sHomeUrl = $this ->_oConfig -> _sHomeUrl;

            $this -> aPollSettings = $aPollSettings;

            $this -> aPollSettings['question_min_length'] = 10;
            $this -> aPollSettings['question_max_length'] = 300;

            $this -> aPollSettings['answer_min_length'] = 1;
            $this -> aPollSettings['answer_max_length'] = 300;

            // init some pagination parameters;
            if( !$this -> aPollSettings['per_page'] )
                $this -> aPollSettings['per_page'] = 10;

            if ( $this -> aPollSettings['per_page'] > 100 )
                $this -> aPollSettings['per_page'] = 100;

            if( $this -> aPollSettings['page'] < 1 )
                $this -> aPollSettings['page'] = 1;

            // fill array with templates name;
            $this -> aUsedTemplates = array
            (
                'poll_init'              => 'poll_init.html',
                'poll_block'             => 'poll_block.html',
                'poll_view_block'        => 'poll_view_block.html',
                'poll_block_ajax'        => 'poll_block_ajax.html',
                'poll_questions_list'    => 'poll_questions_list.xml',
                'poll_results_list'      => 'poll_results_list.xml',
                'server_answer'          => 'server_answer.xml',
                'poll_creation_form'     => 'poll_creation_form.html',
                'poll_edit_form'         => 'poll_edit_form.html',
                'poll_actions'           => 'poll_actions.html',
                'poll_premoderation'     => 'poll_premoderation.html',
                'poll_owner'             => 'entry_view_block_info.html',
            );

            $this -> aModuleInfo = $aModule;

            // prepare the location link ;
            $this -> sPathToModule = BX_DOL_URL_ROOT . $this -> _oConfig -> getBaseUri();
                if ($this -> aPollSettings['action']) {
                $this -> sPathToModule .= '&action=' . rawurlencode($this -> aPollSettings['action']);
            }

            $this -> oPrivacy = new BxPollPrivacy($this);
            $this -> oSearch  = new BxPollSearch($this);
        }

        /**
         * Function will return path to current module;
         *
         * @return : (string) - path;
         */
        function getModulePath()
        {
            return BX_DOL_URL_ROOT . $this -> _oConfig -> getBaseUri();
        }

        /**
         * Function will generate information about the poll's onwer;
         *
         * @param  : $iPollOwner (integer) - poll's owner id;
         * @param  : $aPollInfo (array) - poll's information;
         * @return : (text) - Html presentation data;
         */
        function getOwnerBlock($iPollOwner, $aPollInfo)
        {
            $aMemberInfo = getProfileInfo($iPollOwner);
            $sThumbImg   = get_member_thumbnail($aMemberInfo['ID'], 'none', true);

            $aTemplateKeys = array(
                'author_unit'    => $sThumbImg,
                'author_username' => $aMemberInfo['NickName'],
                'author_url'      => getProfileLink($aMemberInfo['ID']),
                'date'            => getLocaleDate($aPollInfo['poll_date'], BX_DOL_LOCALE_DATE_SHORT),
                'date_ago'        => defineTimeInterval($aPollInfo['poll_date'], false),
                'tags'            => getLinkSet($aPollInfo['poll_tags'], $this -> getModulePath() . '&action=tag&tag=', BX_DOL_TAGS_DIVIDER),
                'fields'          => null,
                'cats'            => getLinkSet($aPollInfo['poll_categories'], $this -> getModulePath() . '&action=category&category=', CATEGORIES_DIVIDER),
            );

            $sOutpuCode = $this -> _oTemplate -> parseHtmlByName($this -> aUsedTemplates['poll_owner'], $aTemplateKeys);

            return array($sOutpuCode);
        }

        /**
         * Function will generate block of member's administration;
         *
         * @return : (text) - html presentation data;
         */
        function genMemberAdministration()
        {
            global $_page;

            switch($this -> aPollSettings['mode']) {

                case 'edit_poll' :
                    $sPageCaption = _t('_bx_poll_edit_poll');

                    $_page['header']        = $sPageCaption ;
                    $_page['header_text']   = $sPageCaption ;

                    $sOutputCode = $this -> getEditForm();
                break;

                case 'add' :
                    $sPageCaption = _t('_bx_poll_add');

                    $_page['header']        = $sPageCaption ;
                    $_page['header_text']   = $sPageCaption ;

                    $sOutputCode = $this -> getCreationForm();
                break;

                case 'manage' :
                case 'pending' :

                    $sPageCaption = _t($this->aPollSettings['mode'] == 'manage' ? '_bx_poll_manage_poll' : '_bx_poll_pending_poll');
                    $_page['header']        = $sPageCaption ;
                    $_page['header_text']   = $sPageCaption ;

                    if(!empty($_POST['poll_id']) && is_array($_POST['poll_id']))
                        foreach($_POST['poll_id'] as $iKey)
                            $this->deletePoll($iKey);

                    // select approvedn polls or not;
                    $sOutputCode  = $this -> getInitPollPage();
                    $sOutputCode .=  $this -> getManagePollsPage($this -> aPollSettings['mode'] == 'manage');
                break;

                case 'main' :
                default :
                    $sOutputCode = '<center class="bx-def-font-large">' . _t('_bx_poll_have_not_approval',
                        '<b>' . $this -> _oDb -> getUnApprovedPolls($this -> aPollSettings['member_id']) . '</b>') . '</center>';

                    $aTemplateKeys = array(
                        'content' => $sOutputCode,
                    );

                    $sOutputCode =  $GLOBALS['oSysTemplate'] -> parseHtmlByName('default_padding.html', $aTemplateKeys);
            }

            return DesignBoxContent(_t('_bx_poll_administration'), $sOutputCode, 1, $this->genToggleElements());
        }

        function genToggleElements()
        {
            // generate toggle ellements ;
            $aToggleItems = array (
                'main' => _t('_bx_poll_main'),
                'add' => _t('_bx_poll_add'),
                'manage' => _t('_bx_poll_manage'),
                'pending' => _t('_bx_poll_pending'),
            );

            // set default
            if(!$this->aPollSettings['mode'])
                $this->aPollSettings['mode'] = 'main';

            // add new toggle el;
            if($this->aPollSettings['mode'] == 'edit_poll')
                $aToggleItems['edit_poll'] = _t('_bx_poll_edit');

            $aItems = array();
            foreach($aToggleItems as $sKey => $sValue)
                $aItems[$sValue] = array('href' => $this->sPathToModule . '&mode=' . $sKey, 'active' => $this->aPollSettings['mode'] == $sKey ? 1 : 0);

            bx_import('BxDolPageView');
            return BxDolPageView::getBlockCaptionItemCode(0, $aItems);
        }

        /**
         * Function will get manage poll's page;
         *
         * @param  : $bApproval (boolean) - only approavl polls needed;
         * @return : (text) - html presentation data;
         */
        function getManagePollsPage($bApproval = true)
        {
            // try to get all approved polls;
            if($bApproval) {
                $this -> oSearch -> aCurrent['restriction']['my']['value'] = $this -> aPollSettings['member_id'];
                $this -> oSearch -> aCurrent['restriction']['activeStatus']['value'] = '';
                $sExtraParam = '&mode=manage';
                $sOutputCode =  $this -> showSearchResult(null, $sExtraParam, 0, true, 0, false);
            } else {
                // get all anapproved polls;
                $this -> oSearch -> aCurrent['restriction']['my']['value'] = $this -> aPollSettings['member_id'];
                $this -> oSearch -> aCurrent['restriction']['approvalStatus']['operator'] = '!=';
                $sExtraParam = '&mode=pending';
                $sOutputCode =  $this -> showSearchResult(null, $sExtraParam, 0, true, 0, false);
            }

            // draw manage elements;
            $aAdminSection = $this -> showAdminActionsPanel('manage_form', array( _t('_bx_poll_delete') ), 'poll_id');

            $sOutputCode =
            '
                <div id="pol_container">
                    <form id="manage_form" method="post">
                    ' . $sOutputCode . $aAdminSection . '
                    </form>
                </div>
            ';

            return $sOutputCode;
        }

        function showAdminActionsPanel($sWrapperId, $aButtons, $sCheckboxName = 'entry', $bSelectAll = true, $bSelectAllChecked = false)
        {
            $aUnit = $aBtns = array();
            if(is_array($aButtons) && !empty($aButtons))
                foreach ($aButtons as $k => $v) {
                    if(is_array($v)) {
                        $aBtns[] = $v;
                        continue;
                    }
                    $aBtns[] = array(
                        'type' => 'submit',
                        'name' => $k,
                        'value' => ('_' == $v[0] ? _t($v) : $v),
                        'onclick' => ''
                    );
                }
            $aUnit['bx_if:actionButtons'] = array(
                'condition' => !empty($aBtns),
                'content' => array(
                    'bx_repeat:buttons' => $aBtns
                )
            );
            $aUnit['bx_if:selectAll'] = array(
                'condition' => $bSelectAll,
                'content' => array(
                    'wrapperId' => $sWrapperId,
                    'checkboxName' => $sCheckboxName,
                    'checked' => ($bSelectAll && $bSelectAllChecked ? 'checked="checked"' : '')
                )
            );
            $aUnit['bx_if:customHTML'] = array(
                'condition' => false,
                'content' => array(),
            );

            return $GLOBALS['oSysTemplate']->parseHtmlByName('adminActionsPanel.html', $aUnit, array('{','}'));
        }

        /**
         * Function will generate private block;
         *
         * @param : $aPollInfo (array) - contain some poll's information ;
            [ id_poll ]     - (integer) poll's Id;
            [ id_profile ]  - (integer) poll's owner Id;
            [ PollDate ]    - (string)  poll's date creation;
            [ sec ]         - (integer) poll's date creation in seconds;
         * @return : (text) - html presentation data;
         */
        function getPrivatePollBlock(&$aPollInfo)
        {
            $aTemplateKeys = array ();
            return $this -> _oTemplate -> parseHtmlByName('poll_private_block.html', $aTemplateKeys);
        }

        /**
         * Function will generate the pool block;
         *
         * @param : $aPollInfo (array) - contain some poll's information ;
                        [ id_poll ]     - (integer) poll's Id;
                        [ id_profile ]  - (integer) poll's owner Id;
                        [ PollDate ]    - (string)  poll's date creation;
                        [ sec ]         - (integer) poll's date creation in seconds;
         * @param : $bAjaxQuery (boolean) - if isset this param that script will use different template;
         */
        function getPollBlock(&$aPollInfo, $bAjaxQuery = false, $bViewMode = false, $bAdminMode = false)
        {
            if(!$this -> oPrivacy -> check('view', $aPollInfo['id_poll'], $this -> aPollSettings['member_id']))
                return $this -> getPrivatePollBlock($aPollInfo);

            // ** init some needed variables ;
            $sPollActions   = null;
            $aMemberInfo    = array();

            // language keys ;
            $aLanguageKeys  = array (
                'results'   => _t('_bx_poll_result'),
                'delete'    => _t('_bx_poll_delete'),
                'edit'      => _t('_bx_poll_edit'),
                'active'    => _t('_bx_poll_active'),
                'sure'      => _t('_Are_you_sure'),
                'by'        => _t('_bx_poll_by' ),
                'poll'      => _t('_bx_poll'),

                'featured'        => _t('_bx_poll_featured'),
                'unfeatured'      => _t('_bx_poll_unfeatured'),
                'poll_approved'   => _t('_bx_poll_approved'),
                'poll_unapproved' => _t('_bx_poll_unapproved'),
            );

            // **  generate the some of poll's information ;
            $sDateAdd = defineTimeInterval($aPollInfo['poll_date']);

            // need to modify (move the top member's info to bottom)
            $aTemplateKeys = array (
                'bx_if:start_tag' => array (
                    'condition' => !$bAjaxQuery,
                    'content'   => array('uid' => $aPollInfo['id_poll']),
                ),

               'bx_if:manage_page' => array (
                    'condition' => $bAdminMode || (in_array($this->aPollSettings['mode'], array('manage', 'pending')) && $this->aPollSettings['member_id'] && $this->aPollSettings['member_id'] == $aPollInfo['id_profile']),
                    'content'   => array(
                    ),
                ),

                'bx_if:end_tag' => array (
                    'condition' => !$bAjaxQuery,
                    'content'   => array(),
                ),

                'uid'            => $aPollInfo['id_poll'],
                'poll_url'       => BX_DOL_URL_ROOT . $this -> _oConfig -> getBaseUri() . '&action=show_poll_info&id=' . $aPollInfo['id_poll'],
                'bx_if:show_link' => array(
                    'condition' => $aPollInfo['id_profile'],
                    'content' => array(
                        'link' => getProfileLink($aPollInfo['id_profile']),
                        'content' => getNickName($aPollInfo['id_profile'])
                    )
                ),
                'bx_if:show_text' => array(
                    'condition' => !$aPollInfo['id_profile'],
                    'content' => array(
                        'content' => _t( '_Admin' )
                    )
                ),
                'date_add'       => $sDateAdd ,
                'result'         => $aLanguageKeys['results'],
                'actions'        => $sPollActions,
                'by'             => $aLanguageKeys['by'],

                'bx_if:show_status' => array(
                    'condition' => $bAdminMode,
                    'content' => array(
                        'status' => $aPollInfo['poll_approval'] ? $aLanguageKeys['poll_approved'] : $aLanguageKeys['poll_unapproved'],
                        'featured' => $aPollInfo['poll_featured'] ? $aLanguageKeys['featured'] : $aLanguageKeys['unfeatured'],
                    )
                ),

                'bx_if:show_actions' => array(
                    'condition' => $bAdminMode,
                    'content' => array(
                        'url_edit' => $this -> sPathToModule . '&action=my&mode=edit_poll&edit_poll_id=' . $aPollInfo['id_poll'],
                    )
                ),

                // poll's corner's images ;
                'pool_down_src' => $this -> _oTemplate -> getIconUrl ( 'poll_down.png' ),
                'pool_up_src'   => $this -> _oTemplate -> getIconUrl ( 'poll_up.png' ),

                'spacer'   => $this -> _oTemplate -> getIconUrl ( 'spacer.gif' ),

                'back'    => $aLanguageKeys['poll'],
            );

            $sTemplateName = !$bViewMode ? $this->aUsedTemplates['poll_block'] : $this->aUsedTemplates['poll_view_block'];
            return $this->_oTemplate->parseHtmlByName($sTemplateName, $aTemplateKeys);
        }

        /**
         * Function will generate poll's init page ;
         *
         * @param $bDynamic boolean
         * @return : (text) - Html presentation data ;
         */
        function getInitPollPage($bDynamic = true)
        {
            global $site;
            $sJS = '';

            if($bDynamic) {
                $this -> _oTemplate -> addJs('profile_poll.js');
            } else {
                $sJS = $this -> _oTemplate -> addJs('profile_poll.js', true);
            }

            // language keys;
            $aLanguageKeys = array
            (
                'delete'        => _t('_bx_poll_delete'),
                'loading'       => _t('_bx_poll_loading') . '...',
                'poll_deleted'  => _t('_bx_poll_was_deleted'),
                'make_it'       => _t('_bx_poll_make_it'),
                'you_should'    => _t('_bx_poll_specify_least'),
            );

            $aTemplateKeys = array
            (
                // init some of needed poll's parameters ;
                'delete'         => $aLanguageKeys['delete'],
                'loading'        => $aLanguageKeys['loading'],
                'poll_deleted'   => $aLanguageKeys['poll_deleted'],
                'make_it'        => $aLanguageKeys['make_it'],
                'you_should'     => $aLanguageKeys['you_should'],
                'path_to_script' => $this -> sHomeUrl,
                'module_name'    => $this -> aModuleInfo['uri'],
                'site'           => $site['url'],
            );

            // generate init page ;
            $sInitPart = $this -> _oTemplate -> parseHtmlByName( $this -> aUsedTemplates['poll_init'], $aTemplateKeys );

            return $sJS . $sInitPart;
        }

        /**
         * Function will procces all recevied poll's data;
         */
        function proccesData()
        {
            $sPollAnsers   = null;
            $sPollResults  = null;

            if (isset($_POST['question'])
                    and ( $this -> aPollSettings['member_id'] or $this -> aPollSettings['admin_mode'] ) ) {

                // process the poll's question ;
                $sPollQuestion = strip_tags( trim($_POST['question']) );

                if( $_POST['answers'] and is_array($_POST['answers']) ) {
                    // procces the answers list ;
                    foreach( $_POST['answers'] as $iKey => $sValue ) {
                        if ($sValue) {
                            $sValue = strip_tags( trim($sValue) );

                            $sPollAnsers  .= $sValue . BX_POLL_ANS_DIVIDER;
                            $sPollResults .= '0;';
                        }
                    }
                } else {
                    // try define answer list as separate values;
                    foreach($_POST as $sKey => $sValue) {
                        if( strstr($sKey, 'answers_') ) {
                            $sPollAnsers  .= $sValue . BX_POLL_ANS_DIVIDER;
                        }
                    }
                }

                // procces recived tags;
                $sTags = null;
                if( isset($_POST['tags']) ) {
                   $sTags = strip_tags( trim($_POST['tags']) );
                }

                $sCategory = null;
                if( isset($_POST['Categories']) && is_array($_POST['Categories']) ) {
                    foreach($_POST['Categories'] as $iKey => $sValue) {
                        if($sValue) {
                            $sCategory .= strip_tags( trim($sValue, BX_TAGS_STRIP) ) . CATEGORIES_DIVIDER;
                        }
                    }
                }

                // define the privacy group value;
                $iCommentGroupValue = ( isset($_POST['allow_comment_to']) )
                    ? (int) $_POST['allow_comment_to']
                    : 3;

                $iVoteGroupValue = ( isset($_POST['allow_vote_to']) )
                    ? (int) $_POST['allow_vote_to']
                    : 3;

                $iViewGroupValue = ( isset($_POST['allow_view_to']) )
                    ? (int) $_POST['allow_view_to']
                    : 3;

                if ($_GET['mode'] == 'add' && $sPollAnsers) {
                    // create new poll ;
                    $this -> createPoll
                    (
                        $sPollQuestion,
                        $sPollAnsers,
                        $sPollResults,
                        $sTags,
                        $iCommentGroupValue,
                        $iVoteGroupValue,
                        $sCategory,
                        $iViewGroupValue
                    );
                } else if ($_GET['mode'] == 'edit_poll' && $sPollAnsers) {

                    $bActive  = ( isset($_POST['active']) ) ? true : false ;
                    $bApprove = ( isset($_POST['approve']) && isAdmin() ) ? true : false ;

                    $this -> editPoll
                    (
                        $this -> aPollSettings['edit_poll_id'],
                        $sPollQuestion,
                        $sPollAnsers,
                        $sCategory,
                        $bActive,
                        $bApprove,
                        $sTags,
                        $iCommentGroupValue,
                        $iVoteGroupValue,
                        $iViewGroupValue
                    );
                }
            }
        }

        /**
         * Function will generate the poll's creation form ;
         *
         * @return : (text) - Html presentation data ;
         */
        function getCreationForm()
        {
            // check membership;
            if(!$this -> isPollCreateAlowed($this -> aPollSettings['member_id'], false) ) {
                return MsgBox( _t('_bx_poll_access_denied') );
            }

            $iDefaultAnswerCount = 2;

            $aLanguageKeys = array
            (
                'create'     => _t('_bx_poll_create'),
                'tags'       => _t('_bx_poll_tags'),
                'tags_sep'   => _t('_sys_tags_note'),
                'generate'   => _t('_bx_poll_generate'),
                'question'   => _t('_bx_poll_question'),
                'answer'     => _t('_bx_poll_answer'),
                'add_answer' => _t('_bx_poll_add'),
                'max_pool'   => _t('_bx_poll_max_reached'),

                'question_length_req' => _t('_bx_poll_question_length_required',
                            $this -> aPollSettings['question_min_length'], $this -> aPollSettings['question_max_length']),

                'answer_length_req' => _t('_bx_poll_answer_length_required',
                            $this -> aPollSettings['answer_min_length'], $this -> aPollSettings['answer_max_length']),
            );

            $aForm = array (

                'form_attrs' => array (
                    'action' =>  $this -> sPathToModule . '&mode=' . $this -> aPollSettings['mode'],
                    'method' => 'post',
                    'name' => 'poll_creation_form'
                ),

                'params' => array (
                    'checker_helper' => 'BxCheckerPoll',
                    'db' => array(
                        'submit_name' => 'do_submit', // some filed name with non empty value to determine if the for was submitted,
                    ),
                ),

                'inputs' => array(
                    'question' => array (
                        'type'     => 'text',
                        'name'     => 'question',
                        'caption'  => $aLanguageKeys['question'],
                        'required' => true,

                        // checker params
                        'checker' => array (
                            'func'   => 'length',
                            'params' => array($this -> aPollSettings['question_min_length'], $this -> aPollSettings['question_max_length']),
                            'error'  => $aLanguageKeys['question_length_req'],
                        ),
                    ),

                    'answers' => array (
                        'type'     => 'text',
                        'name'     => 'answers[]',
                        'caption'  => $aLanguageKeys['answer'],
                        'required' => true,
                        'value' => array ('', ''),

                        'attrs' => array(
                            'multiplyable' => 'true',
                        ),

                        // checker params
                        'checker' => array (
                            'func'   => 'answers',
                            'params' => array($this -> aPollSettings['answer_min_length'], $this -> aPollSettings['answer_max_length']),
                            'error'  => $aLanguageKeys['answer_length_req'],
                        ),

                        'db' => array (
                            'pass' => 'Anwers',
                        ),
                    ),

                    'category' => array(),

                    'tags' => array (
                        'type'     => 'text',
                        'name'     => 'tags',
                        'caption'  => $aLanguageKeys['tags'],
                        'required' => false,
                        'info'     => $aLanguageKeys['tags_sep'],
                    ),

                    'allow_view_to' => $this -> oPrivacy -> getGroupChooser($this -> aPollSettings['member_id']
                                            , $this -> aModuleInfo['uri'], 'view', array(), _t('_bx_poll_allow_view') ),

                    'allow_comments_to' => $this -> oPrivacy -> getGroupChooser($this -> aPollSettings['member_id']
                                            , $this -> aModuleInfo['uri'], 'comment', array(), _t('_bx_poll_allow_comment') ),

                    'allow_vote_to' => $this -> oPrivacy -> getGroupChooser($this -> aPollSettings['member_id']
                                            , $this -> aModuleInfo['uri'], 'vote', array(), _t('_bx_poll_allow_vote') ),
                ),
            );

            // generate categories;
            $oCategories = new BxDolCategories();
            $oCategories -> getTagObjectConfig();
            $aForm['inputs']['category'] = $oCategories -> getGroupChooser ('bx_poll', $this -> aPollSettings['member_id'], true);

            // add submit button;
            $aForm['inputs'][] = array (
                'type' => 'submit',
                'name' => 'do_submit',
                'value' => $aLanguageKeys['generate'],
            );

            $oForm = new BxTemplFormView($aForm);
            $oForm -> initChecker();

            // create new poll
            if ( $oForm -> isSubmittedAndValid() ) {
                $this -> proccesData();
                $sOutputCode .= $this -> sActionAnswer;
            } else {
                $sOutputCode .= $oForm -> getCode();
            }

            return $this->_oTemplate->parseHtmlByName('default_margin.html', array('content' => $sOutputCode));
        }

        /**
         * Function will generate custom  Button;
         *
         */
        function getCustomActionButton()
        {
            $iProfileId = getLoggedId();

            if( $iProfileId && isMember() ) {
                $aOpt = array('only_menu' => 1, 'BaseUri' => $this -> _oConfig -> getBaseUri());
                $GLOBALS['oTopMenu'] -> setCustomSubActions($aOpt, 'bx_poll_title');
            }
        }

        /**
         * Function will generate the edit poll's form ;
         *
         * @return : (text) - Html presentation data ;
         */
        function getEditForm()
        {
            if($this -> aPollSettings['edit_poll_id']) {
               $aPollInfo = $this -> _oDb -> getPollInfo($this -> aPollSettings['edit_poll_id']);
               $aPollInfo = array_shift($aPollInfo);

                // check poll's permission;
                if ($this -> aPollSettings['admin_mode']
                       || $aPollInfo['id_profile'] == $this -> aPollSettings['member_id'] ) {

                    $aLanguageKeys = array
                    (
                        'question'            => _t('_bx_poll_question'),
                        'answer'              => _t('_bx_poll_answer'),
                        'save'                => _t('_bx_poll_save'),
                        'close'               => _t('_bx_poll_close'),
                        'active'              => _t('_bx_poll_active'),
                        'approve'             => _t('_bx_poll_approve'),
                        'tags'                => _t('_bx_poll_tags'),
                        'tags_sep'            => _t('_bx_poll_tags_separeted'),

                        'question_length_req' => _t('_bx_poll_question_length_required',
                                    $this -> aPollSettings['question_min_length'], $this -> aPollSettings['question_max_length']),

                        'answer_length_req' => _t('_bx_poll_answer_length_required',
                                    $this -> aPollSettings['answer_min_length'], $this -> aPollSettings['answer_max_length']),
                    );

                    // generate edit form;
                    $aForm = array (
                        'form_attrs' => array (
                            'action' =>  $this -> sPathToModule . '&mode=' . $this -> aPollSettings['mode'] . '&edit_poll_id=' . $this -> aPollSettings['edit_poll_id'],
                            'method' => 'post',
                            'name' => 'poll_edit_form'
                        ),

                        'params' => array (
                            'checker_helper' => 'BxCheckerPoll',
                            'db' => array(
                                'submit_name' => 'do_submit', // some filed name with non empty value to determine if the for was submitted,
                            ),
                        ),

                        'inputs' => array (
                            'question' => array (
                                'type'     => 'text',
                                'name'     => 'question',
                                'caption'  => $aLanguageKeys['question'],
                                'required' => true,
                                'value'    => $aPollInfo['poll_question'],

                                // checker params
                                'checker' => array (
                                    'func'   => 'length',
                                    'params' => array($this -> aPollSettings['question_min_length'], $this -> aPollSettings['question_max_length']),
                                    'error'  => $aLanguageKeys['question_length_req'],
                                ),
                            ),
                        ),
                    );

                    //  generate answers list ;
                    $aAnswers = explode(BX_POLL_ANS_DIVIDER, $aPollInfo['poll_answers']);
                    $iIndex = 0;

                    foreach($aAnswers as $iKey => $sValue) {
                        if ($sValue) {
                            $iIndex++;

                            $aForm['inputs'][] = array(
                                'type'     => 'text',
                                'name'     => 'answers_' . $iIndex,
                                'caption'  => $aLanguageKeys['answer'] . ' ' . $iIndex,
                                'required' => true,
                                'value'    => $sValue,

                                // checker params
                                'checker' => array (
                                    'func'   => 'length',
                                    'params' => array($this -> aPollSettings['answer_min_length'], $this -> aPollSettings['answer_max_length']),
                                    'error'  => $aLanguageKeys['answer_length_req'],
                                ),
                            );
                        }
                    }

                    // generate categories;
                    $oCategories = new BxDolCategories();
                    $oCategories -> getTagObjectConfig();
                    $aCurrentCategories = explode(CATEGORIES_DIVIDER, $aPollInfo['poll_categories']);

                    $aForm['inputs']['category'] = $oCategories -> getGroupChooser ('bx_poll', $this -> aPollSettings['member_id'], true, $aPollInfo['poll_categories']);
                    $aForm['inputs']['category']['value'] = $aCurrentCategories;

                    // generate tags el;
                    $aForm['inputs'][] = array (
                        'type'     => 'text',
                        'name'     => 'tags',
                        'caption'  => $aLanguageKeys['tags'],
                        'required' => false,
                        'info'     => $aLanguageKeys['tags_sep'],
                        'value'    => $aPollInfo['poll_tags'],
                    );

                    $aForm['inputs'] = array_merge($aForm['inputs'], array(
                        'allow_view_to' => $this -> oPrivacy -> getGroupChooser($this -> aPollSettings['member_id'], $this -> aModuleInfo['uri'], 'view', array(), _t('_bx_poll_allow_view') ),
                        'allow_comments_to' => $this -> oPrivacy -> getGroupChooser($this -> aPollSettings['member_id'], $this -> aModuleInfo['uri'], 'comment', array(), _t('_bx_poll_allow_comment') ),
                        'allow_vote_to' => $this -> oPrivacy -> getGroupChooser($this -> aPollSettings['member_id'], $this -> aModuleInfo['uri'], 'vote', array(), _t('_bx_poll_allow_vote') ),
                    ));

                    // add status checkbox;
                    $aForm['inputs'][] = array (
                        'type'    => 'checkbox',
                        'name'    => 'active',
                        'caption' => $aLanguageKeys['active'],

                        'attrs'    => array(
                            'checked' => ($aPollInfo['poll_status']) ? 'checked' : null,
                        ),
                    );

                    // add approve checkbox;
                    if(isAdmin())
                        $aForm['inputs'][] = array (
                            'type'    => 'checkbox',
                            'name'    => 'approve',
                            'caption' => $aLanguageKeys['approve'],

                            'attrs'    => array(
                                'checked' => ($aPollInfo['poll_approval']) ? 'checked' : null,
                            )
                        );

                    // add submit button;
                    $aForm['inputs'][] = array (
                        'type' => 'submit',
                        'name' => 'do_submit',
                        'value' => $aLanguageKeys['save'],
                    );

                    $aForm['inputs']['allow_view_to']['value']  = (string) $aPollInfo['allow_view_to'];
                    $aForm['inputs']['allow_comments_to']['value']  = (string) $aPollInfo['allow_comment_to'];
                    $aForm['inputs']['allow_vote_to']['value']      = (string) $aPollInfo['allow_vote_to'];

                    $oForm = new BxTemplFormView($aForm);
                    $oForm -> initChecker();

                    // create new poll
                    if ( $oForm -> isSubmittedAndValid() ) {
                            $this -> proccesData();
                            $sOutputCode = MsgBox( _t('_bx_poll_was_edited') );
                    } else {
                        $sOutputCode = $oForm -> getCode();
                    }
                }
            }

            return $this->_oTemplate->parseHtmlByName('default_margin.html', array('content' => $sOutputCode));
        }

        /**
         * Function will generate the poll's vote results list ;
         *
         * @param : $iPollID (integer) - poll's Id ;
         *
         */
        function actionResultsList($iPollID)
        {
            // ** init some needed variables ;

            $aPoints = array();
            $aNames  = array();

            // get information about received pool ;
            $aPoll = $this -> _oDb -> getPollInfo( (int) $iPollID );

            $aAnswersResult = explode(BX_POLL_RES_DIVIDER, $aPoll[0]['poll_results'] );
            $aAnswersNames  = explode(BX_POLL_ANS_DIVIDER, $aPoll[0]['poll_answers'] );
            $iTotalVotes    = $aPoll[0]['poll_total_votes'];

            foreach ($aAnswersResult as $value) {
                if  ( $value ) {
                    $aPoints[] = array
                    (
                        'point'  => round( (0 != $iTotalVotes ? (( $value / $iTotalVotes ) * 100) : 0), 1),
                        'number' => htmlspecialchars ( $value ),
                    );
                } else if ( $value != '' ) {
                    $aPoints[] = array
                    (
                        'point'  => 0,
                        'number' => 0,
                    );
                }
            }

            foreach ($aAnswersNames as $value) {
                if ( $value ) {
                    $aNames[] = array
                    (
                        'name' => htmlspecialchars ( $value ),
                    );
                }
            }

            // return the generated poll's results list ;
            header('Content-Type: application/xml');

            $aTemplateKeys = array
            (
                'bx_repeat:points' => $aPoints,
                'bx_repeat:names'  => $aNames,
            );

            $this -> _oTemplate -> _bCacheEnable = false;
            echo $this -> _oTemplate -> parseHtmlByName( $this -> aUsedTemplates['poll_results_list'], $aTemplateKeys );
        }

        /**
         * Function will generate list with questions ;
         *
         * @param   : $iPollID (integer) - poll's Id ;
         * @return  : - (xml) - xml data ;
         */
        function actionGetQuestions($iPollID)
        {
            // ** init some needed variables ;

            // contain processed answer list ;
            $aQuestionsList = array();

            // get information about received pool ;
            $aPoll = $this -> _oDb -> getPollInfo( (int) $iPollID );

            // processing received answer list ;
            if ($aPoll) {
                $aQuestions = explode(BX_POLL_ANS_DIVIDER, $aPoll[0]['poll_answers']);
                foreach ($aQuestions as $sValue) {
                    if ($sValue) {
                        $aQuestionsList[] = array
                        (
                            'answer' => htmlspecialchars($sValue),
                        );
                    }
                }
            } else {
                $aQuestionsList[] = array
                (
                    'answer' => _t( '_Empty' ),
                );
            }

            // prepare to output ;
            $aTemplateKeys = array
            (
                'question'          => ( isset($aPoll[0]['poll_question']) )
                    ? htmlspecialchars( $aPoll[0]['poll_question'] )
                    : null,

                'bx_repeat:answer_list' => $aQuestionsList,
            );

            header('Content-Type: application/xml; charset=utf-8');
            $this -> _oTemplate -> _bCacheEnable = false;
            echo $this -> _oTemplate -> parseHtmlByName( $this -> aUsedTemplates['poll_questions_list'], $aTemplateKeys );
        }

        /**
         * Function will receive and save the poll's vote result ;
         *
         * @param : $iPollID (integer) - poll's Id ;
         * @param : $iVoteNumber (integer) - poll's vote number ;
         */
        function actionSetAnswer($iPollID, $iVoteNumber)
        {
            if ( (int) $iVoteNumber >= 0 ) {
                // get information about received pool ;
                $aPoll = $this -> _oDb -> getPollInfo( (int) $iPollID );

                // explode all votes results ;
                $aVotes = explode(BX_POLL_RES_DIVIDER, $aPoll[0]['poll_results'] );
                $aVotes[$iVoteNumber]++;

                $iPoll_total_votes = array_sum($aVotes);
                $sVotes            = implode(';', $aVotes);

                if ( !isset($_COOKIE['profile_polls_question_' . $iPollID]) ) {
                    if ( $this -> _oDb -> setVotes( (int) $iPollID, $sVotes, $iPoll_total_votes) ) {
                        // if vote was created ;
                        $aUrl = parse_url($GLOBALS['site']['url']);
                        $sPath = isset($aUrl['path']) && !empty($aUrl['path']) ? $aUrl['path'] : '/';
                        setcookie( 'profile_polls_question_' . $iPollID, 1 , time() + BX_POLL_VOTE_UNIQ, $sPath );

                          // create system event
                        $oZ = new BxDolAlerts('bx_poll', 'answered',  $aPoll[0]['id_profile'], $this -> aPollSettings['member_id'], array('poll_id' => $iPollID, 'vote' => $iVoteNumber));
                        $oZ->alert();
                    }
                }
            }

            // return the poll's votes result ;
            header('Content-Type: application/xml; charset=utf-8');
            echo $this -> actionResultsList( $iPollID );
        }

        /**
         * Share poll
         *
         * @param $iEntryId integer
         * @return void
         */
        function actionSharePopup ($iEntryId = 0)
        {
            $iEntryId = (int)$iEntryId;
            if ( !($aDataEntry = $this -> _oDb -> getPollInfo($iEntryId)) ) {
                echo MsgBox(_t('_Empty'));
                exit;
            }

            $aDataEntry = array_shift($aDataEntry);
            require_once (BX_DIRECTORY_PATH_INC . "shared_sites.inc.php");

            $sEntryUrl = BX_DOL_URL_ROOT . $this -> _oConfig -> getBaseUri()
                . '&action=show_poll_info&id=' . $aDataEntry['id_poll'];

            $aSitesPrepare = getSitesArray ($sEntryUrl);
            $sIconsUrl = getTemplateIcon('digg.png');
            $sIconsUrl = str_replace('digg.png', '', $sIconsUrl);
            $aSites = array ();
            foreach ($aSitesPrepare as $k => $r) {
                $aSites[] = array (
                    'icon' => $sIconsUrl . $r['icon'],
                    'name' => $k,
                    'url' => $r['url'],
                );
            }

            $aVarsContent = array (
                'bx_repeat:sites' => $aSites,
            );
            $aVarsPopup = array (
                'title' => _t('_Share'),
                'content' => $this->_oTemplate->parseHtmlByName('popup_share.html', $aVarsContent),
            );

            header('Content-Type: text/html; charset=utf-8');
            echo $GLOBALS['oFunctions']->transBox($this->_oTemplate->parseHtmlByName('popup.html', $aVarsPopup), true);
            exit;
        }

        /**
         * Function will delete poll;
         *
         * @param : $iPollID (integer) - poll's Id ;
         */
        function actionDeletePoll($iPollId)
        {
            $iPollId = (int)$iPollId;
            $bResult = $this->deletePoll($iPollId);

            $aTemplateKeys = array(
                'answer' => $bResult ? 'ok' : _t('_Error Occured'),
            );

            header('Content-Type: application/xml');
            $this -> _oTemplate -> _bCacheEnable = false;
            echo $this -> _oTemplate -> parseHtmlByName( $this -> aUsedTemplates['server_answer'], $aTemplateKeys );
        }

        /**
         * Function will return generated poll's block ;
         *
         * @param  : $iPollId   (integer) - poll's block id;
         * @param  : $bViewMode (boolean) - view mode (need for single  poll block);
         * @return : Html presentation data ;
         */
        function actionGetPollBlock($iPollId, $bViewMode = false)
        {
            $aPollInfo = $this -> _oDb -> getPollInfo( (int) $iPollId );
            $aPoll = array_shift($aPollInfo);

            header('Content-Type: application/xml; charset=utf-8');
            echo $this -> getPollBlock($aPoll, true, $bViewMode) ;
        }

        /**
         * Function will generate page with nedded polls by date;
         */
        function actionViewCalendar($iYear = 0, $iMonth = 0, $iDay = 0)
        {
            global $_page;
            global $_page_cont;

            $sCaption = _t('_bx_poll_browse_by_day')
                . ': ' . getLocaleDate( strtotime("{$iYear}-{$iMonth}-{$iDay}"), BX_DOL_LOCALE_DATE_SHORT);

            $iIndex = 57;
            $_page['name_index']	= $iIndex;
            $_page['css_name']      = 'main.css';
            $_page['header']        = $sCaption ;
            $_page['header_text']   = $sCaption ;

            $sSearchResult = $this -> searchByDate($sCaption, $iYear, $iMonth, $iDay);
            $_page_cont[$iIndex]['page_main_code'] = $sSearchResult;

            PageCode($this -> _oTemplate);
        }

        function actionCalendar($iYear = null, $iMonth = null )
        {
            global $_page;

            $sPageCaption = _t('_bx_poll_calendar');

            $_page['header']        = $sPageCaption ;
            $_page['header_text']   = $sPageCaption ;

            $this -> getCustomActionButton();
            $oCalendar = new BxPollCalendar($iYear, $iMonth, $this->_oDb, $this->_oTemplate, $this->_oConfig);
            $sCode = $oCalendar->display();
            $this->_oTemplate->pageCode($this->aPageTmpl, array('page_main_code' => $sCode));
        }

        function actionCategories()
        {
            $this -> getCustomActionButton();
            bx_import('BxTemplCategoriesModule');

            $aParam = array(
                'type' => 'bx_poll'
            );

            $oCateg = new BxTemplCategoriesModule($aParam, '', BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'categories');
            $sCode = $oCateg->getCode();
            $this -> _oTemplate -> defaultPage( _t('_bx_poll_categories'), $sCode);
       }

        /**
         * Function will generate tag's search result;
         *
         * @param  : ($sTag) - tag
         * @return : (text)  - Html presentation data;
         */
        function actionTag($sTag = '')
        {
            $sProccessed = uri2title($sTag);
            $sExtraParam = 'tag/' . urlencode($sTag);

            $this -> _oTemplate -> addCss('main.css');
            echo $this -> _oTemplate -> defaultPage( _t('_bx_poll_view_tag')
                , $this -> searchTags($sProccessed, $sExtraParam ) );
        }

        /**
         * Function will generate page with all poll's tags list
         *
         * @return : (text) - html presentation data;
         */
        function actionTags()
        {
            $this -> getCustomActionButton();
            bx_import('BxTemplTagsModule');

            $aParam = array(
                'type'       => 'bx_poll',
                'pagination' => ( isset($_GET['per_page']) )
                    ? (int) $_GET['per_page']
                    : getParam('tags_perpage_browse'),
            );

            $oTags = new BxTemplTagsModule($aParam, '', BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'tags');
            $sCode = $oTags->getCode();
            $this -> _oTemplate -> defaultPage( _t('_bx_poll_tags'), $sCode);
        }

        function getAdminForm()
        {
            bx_import('BxTemplSearchResult');

            $oNewSearchresult = new BxTemplSearchResult();

            $aPolls     = array();
            $aPollsList = array();

            $iPage  = ( isset($_GET['page']) )
                ? (int) $_GET['page']
                : 1;

            $iPerPage = ( isset($_GET['per_page']) )
                ? (int) $_GET['per_page']
                : 6;

            if ( $iPerPage > 100 ) {
                $iPerPage = 100;
            }

            if ($iPerPage <= 0 ) {
                $iPerPage = 6;
            }

            if ( !$iPage ) {
                $iPage = 1;
            }

            // proccessed all post datas ;
            if ( isset($_POST['poll_id']) and is_array($_POST['poll_id']) ) {
                foreach($_POST['poll_id'] as $iKey => $iValue ) {
                    $iValue = (int) $iValue;

                    // set as approved;
                    if (isset($_POST['approve']) || isset($_POST['disapprove']))
					{
                        $this -> _oDb -> setOption($iValue);
                    } else if( isset($_POST['delete']))
					{
                        $this->deletePoll($iValue);
                    } else if(isset($_POST['featured']) || isset($_POST['unfeatured']))
			            $this -> _oDb -> setOption($iValue, 'featured');
            
                    $oTag = new BxDolTags();
                    $oTag -> reparseObjTags('bx_poll', $iValue);

                    $oCateg = new BxDolCategories();
                    $oCateg->reparseObjTags('bx_poll', $iValue);
                }
            }

            $aLanguageKeys = array
            (
                'premoderation'   => _t('_bx_poll_moderation'),
                'select_all'      => _t('_bx_poll_select_all'),
                'approve'         => _t('_bx_poll_approve'),
                'disapprove'      => _t('_bx_poll_disapprove'),

                'delete'          => _t('_bx_poll_delete'),
                'sure'            => _t('_Are_you_sure'),
                'featured'        => _t('_bx_poll_featured'),
                'unfeatured'      => _t('_bx_poll_unfeatured'),
            );

            // get only the member's polls ;
            $iTotalNum = $this -> _oDb -> getPollsCount(0, true);

            if(!$iTotalNum)
                $sOutputHtml = MsgBox(_t( '_Empty' ));

            $sLimitFrom = ( $iPage - 1 ) * $iPerPage;
            $sqlLimit = "LIMIT {$sLimitFrom}, {$iPerPage}";

            $aPolls  = $this->_oDb -> getAllPolls($sqlLimit, 0, true);
            foreach($aPolls as $iKey => $aItems)
                $aPollsList[] = array (
                    'poll' => $this->getPollBlock($aItems, false, false, true),
                );

            // generate init page ;
            $sInitPart = $this -> getInitPollPage();

            // generate page pagination ;
            $sRequest = $this -> sPathToModule . 'administration&amp;page={page}&amp;per_page={per_page}';
            $oPaginate = new BxDolPaginate
            (
                array
                (
                    'page_url'   => $sRequest,
                    'count'      => $iTotalNum,
                    'per_page'   => $iPerPage,
                    'sorting'    => null,
                    'page'       => $iPage,
                )
            );

            $sPagination = $oPaginate -> getPaginate();

            // generate needed buttons;
            $aButtons = array(
                'approve'       =>  $aLanguageKeys['approve'],
                'disapprove'    => $aLanguageKeys['disapprove'],
                'featured'      => $aLanguageKeys['featured'],
                'unfeatured'    => $aLanguageKeys['unfeatured'],

                'delete'        => array(
                    'type'  => 'submit',
                    'name'  => 'delete',
                    'value' => $aLanguageKeys['delete'],
                    'onclick' => 'onclick="return confirm(\'' . $aLanguageKeys['sure'] . '\')"',
                ),
            );

            $sButtons = $oNewSearchresult -> showAdminActionsPanel('poll_form', $aButtons, 'poll_id');

            if (isset($GLOBALS['oAdmTemplate'])) {
                $GLOBALS['oAdmTemplate']->addDynamicLocation($this->_oConfig->getHomePath(), $this->_oConfig->getHomeUrl());
                $GLOBALS['oAdmTemplate'] -> addCss('main.css');
            }

            // generate template ;
            $aTemplateKeys = array (
                'action'            =>  $this -> sPathToModule . 'administration&amp;page=' . $iPage . '&amp;per_page=' . $iPerPage,

                'init_js'           => $sInitPart,
                'js_file'           => $this -> _oTemplate -> addJs( 'profile_poll.js', true ),

                'bx_repeat:polls'   => $aPollsList,
                'pagination'        => $sPagination,
                'admin_panel'       => $sButtons,
            );

            $sOutputHtml  .= $this -> _oTemplate -> parseHtmlByName( $this -> aUsedTemplates['poll_premoderation'], $aTemplateKeys );

            $sOutputHtml = '<div id="pol_container">' . $sOutputHtml . '</div>';
            return $sOutputHtml;
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

            $aLanguageKeys = array(
                'premoderation' => _t('_bx_poll_tags_premoderation'),
            );

            $aMenu = array(
                'bx_poll_main'      => array('title' => _t('_bx_poll_main'), 'href' => $this -> sPathToModule . 'administration&action=main'),
                'bx_poll_settings'  => array('title' => _t('_bx_poll_settings'), 'href' => $this -> sPathToModule . 'administration&action=settings'),
            );

            $sAction = ( isset($_GET['action']) ) ? $_GET['action'] : null;

            switch ($sAction) {
                case 'main':
                    $aMenu['bx_poll_main']['active'] = 1;
                    $sContent = $this -> getAdminForm();
                    break;
                case 'settings':
                    $aMenu['bx_poll_settings']['active'] = 1;
                    $sContent = $this -> getSettingsForm();
                    break;
                default:
                    $aMenu['bx_poll_main']['active'] = 1;
                    $sContent = $this -> getAdminForm();
            }

            $this -> _oTemplate-> pageCodeAdminStart();
            echo $this -> _oTemplate -> adminBlock ($sContent, $aLanguageKeys['premoderation'], $aMenu);
            $this -> _oTemplate->pageCodeAdmin( _t('_bx_poll_module') );
        }
        
        /**
         * Method for ajax perform actions (approval/disaproval - featured/unfeatured)  from actions button ;
         *
         * @return : (text) - Html response ;
         */        
        function actionSetOption($iPollId, $sAction = 'approval')
        {
            $iPollId = (int)$iPollId;
            if ($iPollId)
            {
                $iActionerId = getLoggedId();
                $sJQueryJS = genAjaxyPopupJS($iPollId);
                if (isAdmin($iActionerId) || isModerator($iActionerId))
                {
                    if (!$this->_oDb ->setOption($iPollId, $sAction))
                        $sMsg = '_Error';
                    else
                        $sMsg = '_Saved';                        
                }
                else
                    $sMsg = '_Access denied';
                header('Content-Type: text/html; charset=UTF-8');
                echo MsgBox(_t($sMsg)) . $sJQueryJS;
                exit;
            }
        }
        /**
         * Function will generate global settings form ;
         *
         * @return : (text) - html presentation data;
         */
        function getSettingsForm()
        {
            $iId = $this-> _oDb -> getSettingsCategory('enable_poll');
            if(!$iId) {
                return MsgBox( _t('_Empty') );
            }

            bx_import('BxDolAdminSettings');

            $mixedResult = '';
            if(isset($_POST['save']) && isset($_POST['cat'])) {
                $oSettings = new BxDolAdminSettings($iId);
                $mixedResult = $oSettings -> saveChanges($_POST);
            }

            $oSettings = new BxDolAdminSettings($iId);
            $sResult = $oSettings->getForm();

            if($mixedResult !== true && !empty($mixedResult))
                $sResult = $mixedResult . $sResult;

            return $GLOBALS['oAdmTemplate']
                    -> parseHtmlByName( 'design_box_content.html', array('content' => $sResult) );
        }

        /**
         * Function will search poll used the recived tag;
         *
         * @param  : $sTag (string) - tag text;
         * @param  : $sExtraParam (string) - extra URI params;
         * @param  : $bUseInitPart (boolean) - if isset this param, function will add poll's js part;
         * @return : (text) - html presentation data;
         */
        function searchTags($sTag, $sExtraParam = '', $bUseInitPart = true)
        {
            $sOutputCode = '';

            //concat init part;
            if($bUseInitPart) {
                $sOutputCode = $this -> getInitPollPage();
            }

            $this -> oSearch -> aCurrent['restriction']['tag']['value'] = $sTag;

            !$sExtraParam ? $sExtraParam = '&tag=' . urlencode( title2uri($sTag) ) : '';
            $sOutputCode .= $this
                -> showSearchResult( _t('_bx_poll_browse_tag') . ': ' . htmlspecialchars_adv($sTag), $sExtraParam );

            return $sOutputCode;
        }

        /**
         * Function will search poll used the recived category name;
         *
         * @param  : $sCategory (string) - poll's category;
         * @param  : $sExtraParam (string) - extra URI params;
         * @param  : $bUseInitPart (boolean) - if isset this param, function will add poll's js part;
         * @return : (text) - html presentation data;
         */
        function searchCategories($sCategory, $sExtraParam = null, $bUseInitPart = true)
        {
            $sOutputCode = null;

            //concat init part;
            if($bUseInitPart) {
                $sOutputCode = $this -> getInitPollPage();
            }

            $this -> oSearch -> aCurrent['restriction']['category']['value'] = $sCategory;
            $sExtraParam .= '&category=' . urlencode( title2uri($sCategory) );
            $sOutputCode .= $this -> showSearchResult( _t('_bx_poll_browse_category')
                . ': ' . htmlspecialchars_adv($sCategory), $sExtraParam );

            return $sOutputCode;
        }

        /**
         * Function will search featured polls;
         *
         * @param  : $bUseInitPart (boolean) - if isset this param, function will add poll's js part;
         * @return : (text) - html presentation data;
         */
        function searchFeatured($bUseInitPart = true, $iLimit = 0)
        {
            $sOutputCode = null;

            //concat init part;
            if($bUseInitPart) {
                $sOutputCode = $this -> getInitPollPage();
            }

            $this -> oSearch -> aCurrent['restriction']['featured']['value'] = '1';
            $sOutputCode .= $this -> showSearchResult( _t('_bx_poll_featured') );

            return $sOutputCode;
        }

        /**
         * Function will search featured polls;
         *
         * @param  : $bUseInitPart (boolean) - if isset this param, function will add poll's js part;
         * @return : (text) - html presentation data;
         */
        function searchFeaturedHome($bUseInitPart = true)
        {
            $sOutputCode = null;

            //concat init part;
            if($bUseInitPart) {
                $sOutputCode = $this -> getInitPollPage();
            }

            $this -> oSearch -> aCurrent['restriction']['featured']['value'] = '1';
            $this -> oSearch -> aCurrent['paginate']['perPage'] = 1;

            $sOutputCode .= $this -> showSearchResult( _t('_bx_poll_featured'), null, 0, false, 1);

            return $sOutputCode;
        }

        /**
         * Function will search featured polls;
         *
         * @param  : $bUseInitPart (boolean) - if isset this param, function will add poll's js part;
         * @return : (text) - html presentation data;
         */
        function searchPopular($bUseInitPart = true)
        {
            $sOutputCode = null;

            //concat init part;
            if($bUseInitPart) {
                $sOutputCode = $this -> getInitPollPage();
            }

            $this -> oSearch -> aCurrent['sorting'] = 'popular';
            $sOutputCode .= $this -> showSearchResult( _t('_bx_poll_popular') );

            return $sOutputCode;
        }

        /**
         * Function will all profile's polls;
         *
         * @param  : $bUseInitPart (boolean) - if isset this param, function will add poll's js part;
         * @return : (text) - html presentation data;
         */
        function searchMy($bUseInitPart = true)
        {
            $sOutputCode   = null;
            $sMemberPanel  = $this -> genMemberAdministration();

            //concat init part;
            if($bUseInitPart) {
                $sOutputCode = $this -> getInitPollPage();
            }

            $this -> oSearch -> aCurrent['restriction']['my']['value'] = $this -> aPollSettings['member_id'];
            $this -> oSearch -> aCurrent['restriction']['activeStatus']['value'] = '';

            if($this->aPollSettings['mode'] != 'manage' && $this->aPollSettings['mode'] != 'pending')
                $sOutputCode .= $this -> showSearchResult( _t('_bx_poll_my') );

            return $sMemberPanel . $sOutputCode;
        }

        /**
         * Function will search all polls;
         *
         * @param  : $iLimit (integer)   - limit of returning polls;
         * @param  : $iPerLine (integer) - number elements for per line;
         * @param  : $bUseInitPart (boolean) - if isset this param, function will add poll's js part;
         * @return : (text) - html presentation data;
         */
        function searchAll($iLimit = 0, $iPerLine = 0, $bUseInitPart = true, $sCaption = null, $bShowEmptyMsg = true)
        {
            $sOutputCode   = null;

            $this -> oSearch -> aCurrent['restriction']['unfeatured']['value'] = 0;

            //concat init part;
            if($bUseInitPart) {
                $sOutputCode = $this -> getInitPollPage();
            }

            $sPageCaption = $sCaption ? _t($sCaption) : _t('_bx_poll_latest');

            $sOutputCode .= $this -> showSearchResult( $sPageCaption
                                , null, ($iLimit) ? $iLimit : 0, ($iLimit) ? false : true, $iPerLine, true, $bShowEmptyMsg);

            return  $sOutputCode;
        }

        /**
         * Function will search all polls;
         *
         * @param  : $iProfileId (integer) - profile's id;
         * @param  : $bUseInitPart (boolean) - if isset this param, function will add poll's js part;
         * @return : (text) - html presentation data;
         */
        function searchProfilePolls($iProfileId, $iLimit = 6, $bUseInitPart = true, $bShowEmptyMsg = true)
        {
            $sOutputCode   = null;

            //concat init part;
            if($bUseInitPart)
                $sOutputCode = $this -> getInitPollPage();

            $this -> oSearch -> aCurrent['restriction']['my']['value'] = $iProfileId;
            $sOutputCode .= $this -> showSearchResult( _t('_bx_polls_profile'), null, $iLimit, false, 1, true, $bShowEmptyMsg );

            return $sOutputCode;
        }

        /**
         * Function will search all profile's polls;
         *
         * @param  : $iProfileId (integer)   - profile's Id;
         * @param  : $iLimit (integer)   - limit of returning polls;
         * @param  : $iPerLine (integer) - number elements for per line;
         * @param  : $bUseInitPart (boolean) - if isset this param, function will add poll's js part;
         * @return : (text) - html presentation data;
         */
        function searchAllProfilePolls($iProfileId, $iLimit = 0, $iPerLine = 0, $bUseInitPart = true)
        {
            $sOutputCode = null;

            $this -> oSearch -> aCurrent['restriction']['my']['value'] = $iProfileId;

            //concat init part;
            if($bUseInitPart) {
                $sOutputCode = $this -> getInitPollPage();
            }

            $sNickName = isset($_GET['nickname']) ? $_GET['nickname'] : null;
            $sExtraParam = '&nickname=' .  $sNickName;

            $this -> oSearch -> aCurrent['sorting'] = 'popular';
            $sOutputCode .= $this -> showSearchResult( _t('_bx_poll_popular'), $sExtraParam );

            return $sOutputCode;
        }

        /**
         * Function will search all polls by date;
         *
         * @param $sCaption string
         * @param  : $iYear  (integer) - nedded year;
         * @param  : $iMonth (integer) - nedded month;
         * @param  : $iDay   (integer) - nedded day;
         * @param  : $bUseInitPart (boolean) - if isset this param, function will add poll's js part;
         * @return : (text) - html presentation data;
         */
        function searchByDate($sCaption, $iYear, $iMonth, $iDay, $bUseInitPart = true)
        {
            $sOutputCode   = null;

            //concat init part;
            if($bUseInitPart) {
                $sOutputCode = $this -> getInitPollPage();
            }

            $oCalendar = new BxPollCalendar($iYear, $iMonth, $this->_oDb, $this->_oTemplate, $this->_oConfig);

            $iYear    = (int) $iYear;
            $iMonth   = (int) $iMonth;
            $iDay     = (int) $iDay;
            $iNextDay = $iDay + 1;

            $this -> oSearch -> aCurrent['restriction']['calendar-min']['value'] = "UNIX_TIMESTAMP('{$iYear}-{$iMonth}-{$iDay}')";
            $this -> oSearch -> aCurrent['restriction']['calendar-max']['value'] = "UNIX_TIMESTAMP('{$iYear}-{$iMonth}-{$iNextDay}')-1";

            $sExtraParam  = $oCalendar -> sActionViewResult . "{$iYear}/{$iMonth}/{$iDay}";
            $sOutputCode .= $this -> showSearchResult( $sCaption, $sExtraParam );

            return $sOutputCode;
        }

        /**
         * Function will draw the search result;
         *
         * @param  : $sBlockCaption (string)  - block's caption;
         * @param  : $iPerPage      (integer) - per page value;
         * @param  : $bShowPagination (boolean) - if isset this parameter, that function will generate pagination block;
         * @param  : $iPerLine (integer) - number of elements for per line;
         * @param  : $bUseDesignBox (boolean) - if isset this parameter that rsult will return into design box;
         * @return : (text) Html presentation data;
         */
        function showSearchResult($sBlockCaption, $sExtraParam = null, $iPerPage = 5, $bShowPagination = true, $iPerLine = 0, $bUseDesignBox = true, $bShowEmptyMsg = true)
        {
            $sOutputCode = $sPaginate = '';

            $this -> oSearch -> aCurrent['paginate']['perPage'] = $iPerPage ? $iPerPage : 5;
            if(!$bShowPagination)
                $this -> oSearch -> aCurrent['paginate']['page'] = 1;

            $aPolls = $this -> oSearch -> getSearchData();

            //process recived data;
            if( $aPolls && is_array($aPolls) ) {
                $sOutputCode = $this -> genPollsList($aPolls);

                if(!empty($sOutputCode))
                    $sOutputCode = $this->_oTemplate->parseHtmlByName('default_margin.html', array(
                        'content' => $sOutputCode
                    ));

                if($bShowPagination)
                    $sPaginate = $this -> oSearch -> showPagination(array('module_path' => $this -> sPathToModule . $sExtraParam));
            } else
                $sOutputCode = $bShowEmptyMsg ? MsgBox( _t('_Empty') ) : '';

            return ($bUseDesignBox && $sOutputCode) ? DesignBoxContent($sBlockCaption, $sOutputCode, 1, '', $sPaginate) : $sOutputCode . $sPaginate;
        }

        /**
         * Function will create new poll ;
         *
         * @param   : $sPollQuestion (string)  - poll's qiestion ;
         * @param   : $sPollAnswers  (string)  - poll's answers list ;
         * @param   : $sPollResults  (string)  - poll's vote result list ;
         * @param   : $sTags         (string)  - poll's tags;
         * @param   : $iCommentGroupValue   (integer) - poll's comment privacy group value;
         * @param   : $iVoteGroupValue   (integer) - poll's vote privacy group value;
         * @param   : $iViewGroupValue   (integer) - poll's view privacy group value;
         * @return  : (integer) - number of affected rows ;
         */
        function createPoll($sPollQuestion, $sPollAnswers, $sPollResults, $sTags, $iCommentGroupValue, $iVoteGroupValue, $sCategory, $iViewGroupValue)
        {
            // check membership;
            if(!$this -> isPollCreateAlowed($this -> aPollSettings['member_id'], true) ) {
                return;
            }

            // ** init some needed variables ;
            $aPoolInfo = array();

            if ( !$sPollQuestion or !$sPollAnswers ) {
                $this -> sActionAnswer  = MsgBox(POLL_EMPTY_FIELDS);
            } else {
                $aPoolInfo = array
                (
                    'owner_id'      => $this -> aPollSettings['member_id'],
                    'question'      => $sPollQuestion,
                    'answers'       => $sPollAnswers,
                    'results'       => $sPollResults,
                    'tags'          => $sTags,
                    'allow_comment' => $iCommentGroupValue,
                    'allow_vote'    => $iVoteGroupValue,
                    'category'      => $sCategory,
                    'allow_view'    => $iViewGroupValue
                );

                $iResponse = $this -> _oDb -> createPoll($aPoolInfo, $this -> aPollSettings['admin_mode']);
                $iLastPoll = $this -> _oDb -> lastId();

                // define the action number ;
                switch($iResponse) {
                    case '0':
                        $this -> sActionAnswer  = MsgBox(POLL_NOT_ALLOW);
                        break;

                    case '1' :
                        $this -> sActionAnswer  = MsgBox(POLL_CREATED);
                        // create system event
                        $oZ = new BxDolAlerts('bx_poll', 'add', $iLastPoll);
                        $oZ->alert();

                        $oTag = new BxDolTags();
                        $oTag -> reparseObjTags('bx_poll', $iLastPoll);

                        $oCateg = new BxDolCategories();
                        $oCateg->reparseObjTags('bx_poll', $iLastPoll);
                        break;

                    case '2' :
                        $this -> sActionAnswer  = MsgBox(POLL_MAX_REACHED);
                        break;
                }
            }
        }

        /**
         * Function will edit poll's information ;
         *
         * @param   : $iPollId       (integer) - poll's Id ;
         * @param   : $sPollQuestion (string)  - poll's qiestion ;
         * @param   : $sPollAnswers  (string)  - poll's answers list ;
         * @param   : $bActive       (boolean) - is active or not ;
         * @param   : $bApprove      (boolean) - approve or not ;
         * @return  : (integer) - number of affected rows ;
         */
        function editPoll($iPollId, $sPollQuestion, $sPollAnswers, $sCategory, $bActive = true, $bApprove = false, $sTags = null, $iCommentGroupValue = 3, $iVoteGroupValue = 3, $iViewGroupValue = 3)
        {
            if ( !$sPollQuestion or !$sPollAnswers ) {
                $this -> sActionAnswer  = MsgBox(POLL_EMPTY_FIELDS);
            } else {
                // check poll's owner Id;
                $aPoolInfo =  $this -> _oDb -> getPollInfo( $iPollId );

                if ( $this -> aPollSettings['admin_mode']
                        or ( $this -> aPollSettings['member_id']
                                and $aPoolInfo[0]['id_profile'] == $this -> aPollSettings['member_id']) ) {

                    $aPoolInfo = array
                    (
                        'question'      => $sPollQuestion,
                        'answers'       => $sPollAnswers,
                        'status'        => ( $bActive )  ? 'active' : null,
                        'Id'            => $iPollId,
                        'tags'          => $sTags,
                        'allow_comment' => $iCommentGroupValue,
                        'allow_vote'    => $iVoteGroupValue,
                        'category'      => $sCategory,
                        'allow_view'    => $iViewGroupValue,
                    );

                    if (  $this -> aPollSettings['admin_mode'] ) {
                        $aPoolInfo['approve'] = ( $bApprove ) ? true : false;
                    }

                    $this -> _oDb -> editPoll($aPoolInfo);
                    $this -> sActionAnswer  = MsgBox(POLL_EDITED);

                    // create system event
                    $oZ = new BxDolAlerts('bx_poll', 'edit',  $iPollId);
                    $oZ->alert();

                    // reparse poll's tags;
                    $oTag = new BxDolTags();
                    $oTag -> reparseObjTags('bx_poll', $iPollId);

                    $oCateg = new BxDolCategories();
                    $oCateg->reparseObjTags('bx_poll', $iPollId);
                } else {
                    $this -> sActionAnswer  = MsgBox(POLL_NOT_ALLOW);
                }
            }
        }

        function deletePoll($iId)
        {
            $aPoll = $this->_oDb->getPollInfo($iId);
            if(empty($aPoll) || !is_array($aPoll))
                return false;

            $aPoll = array_shift($aPoll);
            if(!isLogged() || (!isAdmin() && $aPoll['id_profile'] != getLoggedId()))
                return false;

            $this->_oDb->deletePoll($iId);

            $oTag = new BxDolTags();
            $oTag -> reparseObjTags('bx_poll', $iId);

            $oCateg = new BxDolCategories();
            $oCateg->reparseObjTags('bx_poll', $iId);

            //delete all subscriptions
			$oSubscription = BxDolSubscription::getInstance();
			$oSubscription->unsubscribe(array('type' => 'object_id', 'unit' => 'bx_poll', 'object_id' => $iId));

            // create system event
            $oZ = new BxDolAlerts('bx_poll', 'delete_poll',  $iId);
            $oZ->alert();

            return true;
        }

        /**
         * Function will generate polls list ;
         *
         * @param  : $sAction (string) - action name ;
         * @param  : $iProfileId (integer) - profile's Id;
         * @return : (text) - Html presentation data ;
         */
        function serviceGetPolls($sAction, $iProfileId = 0)
        {
            // concat css file;
            $this -> _oTemplate -> addCss('main.css');

            switch($sAction) {

                // for profile's page;
                case 'get_profile_polls' :
                    if($iProfileId) {
                        $iRowsLimit  = $this -> _oConfig -> iProfilePagePollsCount;
                        $sOutputCode = $this -> searchProfilePolls($iProfileId, $iRowsLimit, true, false);
                    }
                break;

                // for index page;
                case 'get_polls' :
                    $aVis = array(BX_DOL_PG_ALL);
                    if ($this->getUserId())
                        $aVis[] = BX_DOL_PG_MEMBERS;
                    $this -> oSearch -> aCurrent['restriction']['allow_view']['value'] = $aVis;
                    $iRowsLimit  = $this ->_oConfig -> iIndexPagePollsCount;
                    $sOutputCode = $this -> searchAll($iRowsLimit, 1, true, '_bx_polls_public', false);
                break;
            }

            echo $sOutputCode;
        }

        function serviceGetMemberMenuLink($iMemberId = false)
        {
            if (false === $iMemberId)
                $iMemberId = getLoggedId();

            $oMemberMenu = bx_instance('BxDolMemberMenu');

            $aLinkInfo = array(
                'item_img_src'  => 'tasks',
                'item_img_alt'  => _t( '_bx_polls' ),
                'item_link'     => $this -> sPathToModule . '&action=my',
                'item_title'    => _t( '_bx_polls' ),
                'extra_info'    => count($this -> _oDb -> getAllPolls(null, $iMemberId)), // number of member's polls
            );

            return $oMemberMenu -> getGetExtraMenuLink($aLinkInfo);
        }

        function serviceGetMemberMenuLinkAddContent($iMemberId = false)
        {
            if (false === $iMemberId)
                $iMemberId = getLoggedId();

            if (!$this -> isPollCreateAlowed($iMemberId))
                return '';

            $oMemberMenu = bx_instance('BxDolMemberMenu');

            $aLinkInfo = array(
                'item_img_src'  => 'tasks',
                'item_img_alt'  => _t( '_bx_poll' ),
                'item_link'     => $this -> sPathToModule . '&action=my&mode=add',
                'item_title'    => _t( '_bx_poll' ),
            );

            return $oMemberMenu -> getGetExtraMenuLink($aLinkInfo);
        }

        /**
         * Function will check is member owner of recived poll script will return edit link;
         */
        function serviceEditActionButton($iMemberId, $iPollId)
        {
            $sEditLink = null;

            $aPollInfo = $this -> _oDb -> getPollInfo( (int) $iPollId);
            $aPollInfo = array_shift($aPollInfo);

            if($aPollInfo['id_profile'] == $iMemberId || isAdmin() ){
                $sEditLink = $this -> sPathToModule . '&action=my&mode=edit_poll&edit_poll_id=' . $iPollId;
            }

            return $sEditLink;
        }

        /**
         * Function will check is member owner of recived poll script will return edit link;
         */
        function serviceDeleteActionButton($iMemberId, $iPollId)
        {
            $sDeleteLink = null;

            $aPollInfo = $this -> _oDb -> getPollInfo( (int) $iPollId);
            $aPollInfo = array_shift($aPollInfo);

            if($aPollInfo['id_profile'] == $iMemberId || isAdmin() ){
                $sDeleteLink = $this -> sPathToModule . '&action=delete_poll&id=' . $iPollId;
            }

            return $sDeleteLink;
        }

        function serviceGetSubscriptionParams ($sAction, $iEntryId)
        {
            $aDataEntry = $this->_oDb->getPollInfo($iEntryId);
            $aDataEntry = array_shift($aDataEntry);
            if(empty($aDataEntry) || (int)$aDataEntry['poll_approval'] == 0) {
                return array('skip' => true);
            }

            $aActionList = array(
                'commentPost' => '_bx_poll_sbs_comment'
            );

            $sActionName = isset($aActionList[$sAction]) ? ' (' . _t($aActionList[$sAction]) . ')' : '';
            return array (
                'skip' => false,
                'template' => array (
                    'Subscription' => $aDataEntry['poll_question'] . $sActionName,
                    'ViewLink' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . '&action=show_poll_info&id=' . $aDataEntry['id_poll'],
                ),
            );
        }

        function serviceGetWallPost($aEvent)
        {
            $aItem = $this->_oDb->getPollInfo((int)$aEvent['object_id']);
            if(empty($aItem))
                return array('perform_delete' => true);

            $aItem = array_shift($aItem);

            $iOwner = 0;
            if(!empty($aEvent['owner_id']))
                $iOwner = (int)$aEvent['owner_id'];
    
            $iDate = 0;
            if(!empty($aEvent['date']))
                $iDate = (int)$aEvent['date'];
    
            $bItem = !empty($aItem) && is_array($aItem);
            if($iOwner == 0 && $bItem && !empty($aItem['id_profile']))
                $iOwner = (int)$aItem['id_profile'];
    
            if($iDate == 0 && $bItem && !empty($aItem['poll_date']))
                $iDate = (int)$aItem['poll_date'];

            if($iOwner == 0 || !$bItem)
                return '';

            $aProfile = getProfileInfo($iOwner);
            if(empty($aProfile) || (int)$aItem['poll_approval'] != 1 || !$this->oPrivacy->check('view', (int)$aEvent['object_id'], getLoggedId()))
                return '';

            $sJs = $sCss = $sInit = '';
            $sBaseUrl = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/';
            if($aEvent['js_mode']) {
                $sJs = $this->_oTemplate->addJs(array('profile_poll.js'), true);
                $sCss = $this->_oTemplate->addCss(array('main.css', 'wall_post.css'), true);
            } else {
                $this->_oTemplate->addJs(array('profile_poll.js'));
                $this->_oTemplate->addCss(array('main.css', 'wall_post.css'));
            }

            $sInit = $this->getInitPollPage();
            $sOwner = getNickName($iOwner);

            //--- Single public event
            $aItem['url'] = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . '&action=show_poll_info&id=' . $aItem['id_poll'];

            $sTextWallObject = _t('_bx_poll_wall_object');
            return array(
            	'owner_id' => $iOwner,
                'title' => _t('_bx_poll_wall_added_new_title', $sOwner, $sTextWallObject),
                'description' => $aItem['poll_question'],
                'content' => $sJs . $sCss . $sInit . $this->_oTemplate->parseHtmlByName('wall_post.html', array(
	                'cpt_user_name' => $sOwner,
	                'cpt_added_new' => _t('_bx_poll_wall_added_new'),
	                'cpt_object' => $sTextWallObject,
	                'cpt_item_url' => $aItem['url'],
	                'cnt_item_page' => $aItem['url'],
	                'cnt_item_title' => $aItem['poll_question'],
	                'cnt_item_id' => $aItem['id_poll'],
	                'post_id' => $aEvent['id'],
	            )),
	            'date' => $iDate
            );
        }

        function serviceGetWallPostOutline($aEvent)
        {
            $aItem = $this->_oDb->getPollInfo((int)$aEvent['object_id']);
            if(empty($aItem))
                return array('perform_delete' => true);

            $aItem = array_shift($aItem);
            $aProfile = getProfileInfo((int)$aEvent['owner_id']);
            if(empty($aProfile) || (int)$aItem['poll_approval'] != 1 || !$this->oPrivacy->check('view', (int)$aEvent['object_id'], getLoggedId()))
                return '';

            $sCss = '';
            $sPrefix = 'bx_' . $this->_oConfig->getUri();
            if($aEvent['js_mode'])
                $sCss = $this->_oTemplate->addCss('wall_outline.css', true);
            else
                $this->_oTemplate->addCss('wall_outline.css');

            $iOwner = (int)$aEvent['owner_id'];
            $sOwner = getNickName($iOwner);
            $sOwnerLink = getProfileLink($iOwner);

            //--- Single public event
            $aItem['poll_url'] = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . '&action=show_poll_info&id=' . $aItem['id_poll'];

            return array(
                'content' => $sCss . $this->_oTemplate->parseHtmlByName('modules/boonex/wall/|outline_item_text.html', array(
	                'mod_prefix' => $sPrefix,
	                'mod_icon' => 'tasks',
	                'user_name' => $sOwner,
	                'user_link' => $sOwnerLink,
	                'item_page' => $aItem['poll_url'],
	                'item_title' => $aItem['poll_question'],
	                'item_description' => $this->_getWallContent($aItem),
	                'item_comments' => (int)$aItem['poll_comments_count'] > 0 ? _t('_wall_n_comments', $aItem['poll_comments_count']) : _t('_wall_no_comments'),
	                'item_comments_link' => $aItem['poll_url'] . '#cmta-' . $sPrefix . '-' . $aItem['id'],
	                'post_id' => $aEvent['id'],
	                'post_ago' => $aEvent['ago']
	            ))
            );
        }

    	function serviceGetWallAddComment($aEvent)
        {
            $iId = (int)$aEvent['object_id'];
            $iOwner = (int)$aEvent['owner_id'];
            $sOwner = $iOwner != 0 ? getNickName($iOwner) : _t('_Anonymous');

			$aContent = unserialize($aEvent['content']);
			if(empty($aContent) || empty($aContent['object_id']))
				return '';

			$iItem = (int)$aContent['object_id'];
            $aItem = $this->_oDb->getPollInfo($iItem);
            if(empty($aItem) || !is_array($aItem))
        		return array('perform_delete' => true);

            if(!$this->oPrivacy->check('view', $iItem, getLoggedId()))
                return '';

            bx_import('BxTemplCmtsView');
            $oCmts = new BxTemplCmtsView('bx_poll', $iItem);
            if(!$oCmts->isEnabled())
                return '';

            $aComment = $oCmts->getCommentRow($iId);
            if(empty($aComment) || !is_array($aComment))
        		return array('perform_delete' => true);

            $sCss = '';
            if($aEvent['js_mode'])
                $sCss = $this->_oTemplate->addCss('wall_post.css', true);
            else
                $this->_oTemplate->addCss('wall_post.css');

			$aItem = array_shift($aItem);
            $aItem['url'] = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . '&action=show_poll_info&id=' . $aItem['id_poll'];               

            $sTextWallObject = _t('_bx_poll_wall_object');
            return array(
                'title' => _t('_bx_poll_wall_added_new_title_comment', $sOwner, $sTextWallObject),
                'description' => $aComment['cmt_text'],
                'content' => $sCss . $this->_oTemplate->parseHtmlByName('wall_post_comment.html', array(
	                'cpt_user_name' => $sOwner,
	                'cpt_added_new' => _t('_bx_poll_wall_added_new_comment'),
	                'cpt_object' => $sTextWallObject,
	                'cpt_item_url' => $aItem['url'],
	                'cnt_comment_text' => $aComment['cmt_text'],
	                'cnt_item_page' => $aItem['url'],
	                'cnt_item_title' => $aItem['poll_question'],
	                'cnt_item_description' => $this->_getWallContent($aItem),
	                'post_id' => $aEvent['id'],
	            ))
            );
        }

        /**
	     * DEPRICATED, saved for backward compatibility
	     */
        function serviceGetWallPostComment($aEvent)
        {
            $iId = (int)$aEvent['object_id'];
            $iOwner = (int)$aEvent['owner_id'];
            $sOwner = getNickName($iOwner);

            $aItem = $this->_oDb->getPollInfo($iId);
            if(empty($aItem))
        		return array('perform_delete' => true);

            if(!$this->oPrivacy->check('view', $iId, getLoggedId()))
                return '';

            $aContent = unserialize($aEvent['content']);
            if(empty($aContent) || !isset($aContent['comment_id']))
                return '';

            bx_import('BxTemplCmtsView');
            $oCmts = new BxTemplCmtsView('bx_poll', $iId);
            if(!$oCmts->isEnabled())
                return '';

			$aItem = array_shift($aItem);
            $aItem['url'] = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . '&action=show_poll_info&id=' . $aItem['id_poll'];
            $aComment = $oCmts->getCommentRow((int)$aContent['comment_id']);
            if(empty($aComment) || !is_array($aComment))
        		return array('perform_delete' => true);

            $sCss = '';
            if($aEvent['js_mode'])
                $sCss = $this->_oTemplate->addCss('wall_post.css', true);
            else
                $this->_oTemplate->addCss('wall_post.css');

            $sTextWallObject = _t('_bx_poll_wall_object');
            return array(
                'title' => _t('_bx_poll_wall_added_new_title_comment', $sOwner, $sTextWallObject),
                'description' => $aComment['cmt_text'],
                'content' => $sCss . $this->_oTemplate->parseHtmlByName('wall_post_comment.html', array(
	                'cpt_user_name' => $sOwner,
	                'cpt_added_new' => _t('_bx_poll_wall_added_new_comment'),
	                'cpt_object' => $sTextWallObject,
	                'cpt_item_url' => $aItem['url'],
	                'cnt_comment_text' => $aComment['cmt_text'],
	                'cnt_item_page' => $aItem['url'],
	                'cnt_item_title' => $aItem['poll_question'],
	                'cnt_item_description' => $this->_getWallContent($aItem),
	                'post_id' => $aEvent['id'],
	            ))
            );
        }

        function serviceGetWallData ()
        {
        	$sUri = $this->_oConfig->getUri();
        	$aName = 'bx_' . $sUri;

            return array(
                'handlers' => array(
                    array('alert_unit' => $aName, 'alert_action' => 'add', 'module_uri' => $sUri, 'module_class' => 'Module', 'module_method' => 'get_wall_post', 'groupable' => 0, 'group_by' => '', 'timeline' => 1, 'outline' => 1),
                    array('alert_unit' => $aName, 'alert_action' => 'comment_add', 'module_uri' => $sUri, 'module_class' => 'Module', 'module_method' => 'get_wall_add_comment', 'groupable' => 0, 'group_by' => '', 'timeline' => 1, 'outline' => 0),

                    //DEPRICATED, saved for backward compatibility
                    array('alert_unit' => $aName, 'alert_action' => 'commentPost', 'module_uri' => $sUri, 'module_class' => 'Module', 'module_method' => 'get_wall_post_comment', 'groupable' => 0, 'group_by' => '', 'timeline' => 1, 'outline' => 0)
                ),
                'alerts' => array(
                    array('unit' => $aName, 'action' => 'add')
                )
            );
        }

        function _getWallContent($aItem, $bAsArray = false)
        {
            $aItemAns  = explode(BX_POLL_ANS_DIVIDER, trim($aItem['poll_answers'], BX_POLL_ANS_DIVIDER));
            $iItemAns = count($aItemAns);
            $aItemRes = explode(BX_POLL_RES_DIVIDER, trim($aItem['poll_results'], BX_POLL_RES_DIVIDER));
            $iItemTotal = (int)$aItem['poll_total_votes'];

            $aResult = array();
            for($i = 0; $i < $iItemAns; $i++)
                if(!empty($aItemAns[$i]))
                    $aResult[] = array(
                        'answer' => $aItemAns[$i],
                        'result' => round(($iItemTotal != 0 ? 100 * ($aItemRes[$i] / $iItemTotal) : 0), 1)
                    );

            return $bAsArray ? $aResult : $this->_oTemplate->parseHtmlByName('poll_answers.html', array('bx_repeat:items' => $aResult));
        }

        function serviceGetSpyData ()
        {
            return array(
                'handlers' => array(
                    array('alert_unit' => 'bx_poll', 'alert_action' => 'add', 'module_uri' => 'poll', 'module_class' => 'Module', 'module_method' => 'get_spy_post'),
                    array('alert_unit' => 'bx_poll', 'alert_action' => 'answered', 'module_uri' => 'poll', 'module_class' => 'Module', 'module_method' => 'get_spy_post'),
                    array('alert_unit' => 'bx_poll', 'alert_action' => 'rate', 'module_uri' => 'poll', 'module_class' => 'Module', 'module_method' => 'get_spy_post'),
                    array('alert_unit' => 'bx_poll', 'alert_action' => 'commentPost', 'module_uri' => 'poll', 'module_class' => 'Module', 'module_method' => 'get_spy_post'),
                ),
                'alerts' => array(
                    array('unit' => 'bx_poll', 'action' => 'add'),
                    array('unit' => 'bx_poll', 'action' => 'answered'),
                    array('unit' => 'bx_poll', 'action' => 'rate'),
                    array('unit' => 'bx_poll', 'action' => 'delete_poll'),
                    array('unit' => 'bx_poll', 'action' => 'commentPost'),
                    array('unit' => 'bx_poll', 'action' => 'commentRemoved')
                )
            );
        }

        /**
         * Function will get post data for spy module;
         *
         * @return : (array);
         */
        function serviceGetSpyPost($sAction, $iObjectId = 0, $iSenderId = 0, $aExtraParams = array())
        {
            $aRet = array();

            switch($sAction) {
                case 'add' :
                    // define some poll's data for rendering;
                    $sNickName      = getNickName($iSenderId);
                    $sProfileLink   = getProfileLink($iSenderId);

                    $aPollInfo      =  $this -> _oDb -> getPollInfo($iObjectId);
                    $sPollCaption   = $aPollInfo[0]['poll_question'];
                    $sPollLink      = $this -> sPathToModule . '&action=show_poll_info&id=' . $iObjectId;

                    $aRet = array(
                        'lang_key'  => '_bx_poll_added',
                        'params'    => array(
                            'profile_link' => $sProfileLink,
                            'profile_nick' => $sNickName,
                            'poll_url'     => $sPollLink,
                            'poll_caption' => $sPollCaption,
                        ),
                        'recipient_id'     => 0,
                    );
                    break;

                case 'answered' :
                    $aRet = $this -> _getSpyArray($aExtraParams['poll_id'], $iSenderId, '_bx_poll_answered', 'content_activity', $iObjectId);
                    break;

                case 'rate' :
                    $aRet = $this -> _getSpyArray($iObjectId, $iSenderId, '_bx_poll_rated');
                    break;

                case 'commentPost' :
                    $aRet = $this -> _getSpyArray($iObjectId, $iSenderId, '_bx_poll_commented');
                    break;
            }
            return $aRet;
        }

        /**
         * Function will get array for spy module
         *
         * @param  : $iObjectId (integer) - poll's Id;
         * @param  : $iSenderId (integer) - alert's sender id's;
         * @param  : $sLangKey  (string)  - language key;
         * @param  : $sActivityType (string)  - type of activity;
         * @return : array;
         */
        function _getSpyArray($iObjectId, $iSenderId, $sLangKey, $sActivityType = 'content_activity', $iRecipientId = 0)
        {
            $aRet = array();

            // try define the poll's owner;
            $aPollInfo = array_shift( $this -> _oDb -> getPollInfo($iObjectId) );
            if( isset($aPollInfo['id_profile']) ) {
                $sRecipientNickName     = getNickName($aPollInfo['id_profile']);
                $sRecipientProfileLink  = getProfileLink($aPollInfo['id_profile']);
                $sSenderNickName        = $iSenderId ? getNickName($iSenderId) : _t('_Guest');
                $sSenderProfileLink     = $iSenderId ? getProfileLink($iSenderId) : 'javascript:void(0)';
                $sPollLink              = $this -> sPathToModule . '&action=show_poll_info&id=' . $aPollInfo['id_poll'];

                $aRet = array(
                    'lang_key'  => $sLangKey,
                    'params'    => array(
                        'recipient_p_link' => $sRecipientProfileLink,
                        'recipient_p_nick' => $sRecipientNickName,
                        'profile_nick'     => $sSenderNickName,
                        'profile_link'     => $sSenderProfileLink,
                        'poll_link'        => $sPollLink,
                    ),
                    'recipient_id'     => ($iRecipientId) ? $iRecipientId : $aPollInfo['id_profile'],
                    'spy_type'         => $sActivityType,
                );
            }

            return $aRet;
        }

        /**
         * Function will generate polls columns;
         *
         * @param  : $aActivePolls (array)   - active polls list;
         * @param  : $iBlockStep   (integer) - number of elements for per line;
         * @return : (text) - html presentation data;
         */
        function genPollsList(&$aActivePolls)
        {
            $iNow = time();

            $sOutputCode = '';
            foreach($aActivePolls as $iKey => $aItems) {
                $aItems['poll_ago'] = $iNow - (int)$aItems['poll_date'];
                $sOutputCode .= $this->getPollBlock($aItems);
            }

            return '<div id="pol_container">' . $sOutputCode . '</div>';
        }

        /**
         * Function will check membership level for current type if users;
         *
         * @param : $iMemberId (integer) - member's Id;
         * @param : $isPerformAction (boolean) - if isset this parameter that function will amplify the old action's value;
         */
        function isPollCreateAlowed($iMemberId = false, $isPerformAction = false)
        {
            if (false === $iMemberId)
                $iMemberId = getLoggedId();
            $this -> _defineActions();
            $aCheck = checkAction($iMemberId, BX_CREATE_POLLS, $isPerformAction);
            return $aCheck[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED;
        }

	    function isAllowedShare(&$aDataEntry)
	    {
	    	if($aDataEntry['allow_view_to'] != BX_DOL_PG_ALL)
	    		return false;

	        return true;
	    }

        function _defineActions()
        {
            defineMembershipActions( array('create polls') );
        }
    }
