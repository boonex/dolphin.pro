<?php

    /**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    require_once( BX_DIRECTORY_PATH_MODULES . $aModule['path'] . 'classes/' . $aModule['class_prefix'] . 'View.php');
    require_once( BX_DIRECTORY_PATH_MODULES . $aModule['path'] . 'classes/' . $aModule['class_prefix'] . 'Module.php');

    // ** init some needed variables ;

    global $_page;
    global $_page_cont;

    $iProfileId = getLoggedId();

    $iIndex = 57;

    $iPollId = ( isset($_GET['id']) )  ? (int) $_GET['id'] : 0;

    // define all needed poll's settings ;
    $aPollSettings = array
    (
        // check admin mode ;
        'admin_mode' => isAdmin() ? true : false,

        // logged member's id ;
        'member_id'  =>  $iProfileId,

        // number of poll's columns for per page ;
        'page_columns' => 2,

        // number of poll's elements for per page ;
        'per_page' => ( isset($_GET['per_page']) )
            ? (int) $_GET['per_page']
            : 6,

        // current page ;
        'page' => ( isset($_GET['page']) )
            ? (int) $_GET['page']
            : 1,

       'featured_page' => ( isset($_GET['featured_page']) )
            ? (int) $_GET['featured_page']
            : 1,

       'featured_per_page' => ( isset($_GET['featured_per_page']) )
            ? (int) $_GET['featured_per_page']
            : 3,

        // contain some specific actions for polls ;
        'action' => ( isset($_GET['action']) )
            ? $_GET['action']
            : null,

        // contain number of needed pool id ;
        'edit_poll_id' => ( isset($_GET['edit_poll_id']) )
            ? (int) $_GET['edit_poll_id']
            : 0,

        'mode'  => ( isset($_GET['mode']) )
            ? addslashes($_GET['mode'])
            : null,

        'tag'   =>  ( isset($_GET['tag']) )
            ? addslashes($_GET['tag'])
            : null
    );

    $oPoll = new BxPollModule($aModule, $aPollSettings);

    $_page['name_index']	= $iIndex;

    $sPageCaption = _t('_bx_poll_all');

    $_page['header']        = $sPageCaption ;
    $_page['header_text']   = $sPageCaption ;
    $_page['css_name']      = 'main.css';

    $oPoll -> _oTemplate -> setPageDescription( _t('_bx_poll_PH') );
    $oPoll -> _oTemplate -> addPageKeywords( _t('_bx_poll_keyword') );

    // get custom actions button;
    $oPoll -> getCustomActionButton();

    if(!$aPollSettings['action']) {
        $_page_cont[$iIndex]['page_main_code'] = $oPoll -> searchAll();
    } else {
        switch($aPollSettings['action']) {
            case 'user' :
                $sUserName = ( isset($_GET['nickname']) )
                    ? $_GET['nickname']
                    : null;

                // define profile's Id;
                $iProfileId = getId($sUserName);

                if($iProfileId) {
                    $GLOBALS['oTopMenu']->setCurrentProfileID($iProfileId);
                    $_page_cont[$iIndex]['page_main_code'] = $oPoll -> searchAllProfilePolls($iProfileId);
                } else {
                    // if profile's Id not defined will draw all polls list;
                    $_page_cont[$iIndex]['page_main_code'] = $oPoll -> searchAll();
                }
            break;

            case 'tag' :
                $sPageCaption = _t('_bx_poll_tags');

                $_page['header']        = $sPageCaption ;
                $_page['header_text']   = $sPageCaption ;

                $sTag = ( isset($_GET['tag']) )
                    ? uri2title($_GET['tag'])
                    : null;

                $_page_cont[$iIndex]['page_main_code'] = $oPoll -> searchTags($sTag);
            break;

            case 'category' :
                $sPageCaption = _t('_bx_poll_view_category');

                $_page['header']        = $sPageCaption ;
                $_page['header_text']   = $sPageCaption ;

                $sCategory = ( isset($_GET['category']) )
                    ? uri2title($_GET['category'])
                    : null;

                $_page_cont[$iIndex]['page_main_code'] = $oPoll -> searchCategories($sCategory);
            break;

            case 'featured' :
                $sPageCaption = _t('_bx_poll_featured_polls');

                $_page['header']        = $sPageCaption ;
                $_page['header_text']   = $sPageCaption ;

                $_page_cont[$iIndex]['page_main_code'] = $oPoll -> searchFeatured();
            break;

            case 'popular' :
                $sPageCaption = _t('_bx_poll_popular_polls');

                $_page['header']        = $sPageCaption ;
                $_page['header_text']   = $sPageCaption ;

                $_page_cont[$iIndex]['page_main_code'] = $oPoll -> searchPopular();
            break;

            case 'my' :
                $sPageCaption = _t('_bx_poll_my');

                $_page['header']        = $sPageCaption ;
                $_page['header_text']   = $sPageCaption ;

                if($iProfileId) {
                    $GLOBALS['oTopMenu']->setCurrentProfileID($iProfileId);
                    $_page_cont[$iIndex]['page_main_code'] = $oPoll -> searchMy();
                } else {
                    member_auth(0);
                }
            break;

            case 'show_poll_info' :
            case 'poll_home' :

                // draw polls question on menu's panel;
                $aPollInfo = current($oPoll -> _oDb -> getPollInfo($iPollId));
                $sCode = '';
                $sInitPart = $oPoll -> getInitPollPage();
                if ($aPollSettings['action'] == 'show_poll_info') {
                    $isAllowView = FALSE;
                    if (!empty($aPollInfo))
                    {
                        if ((int)$aPollInfo['poll_approval'] == 1 || isAdmin($iProfileId) || isModerator($iProfileId))
                            $isAllowView = TRUE;
                    }
                    if ($isAllowView)
                    {
                        $oViewPoll = bx_instance($aModule['class_prefix'] . 'View', array($aPollSettings['action'], $aModule, $oPoll, $iPollId), $aModule);
                        $sPageTitle = $aPollInfo['poll_question'];

                        $sPageCaption = _t('_bx_poll_view', $aPollInfo['poll_question']);
                        $_page['header']        = $sPageCaption ;
                        $_page['header_text']   = $sPageCaption ;

                        $oPoll -> _oTemplate -> addJsTranslation(array('_Are_you_sure'));
                        $oPoll -> _oTemplate -> setPageDescription($aPollInfo['poll_question']);
                        $oPoll -> _oTemplate -> addPageKeywords($aPollInfo['poll_answers'], BX_POLL_ANS_DIVIDER);

                        if( mb_strlen($sPageTitle) > $oPoll -> sPollHomeTitleLenght) {
                            $sPageTitle = mb_substr($sPageTitle, 0, $oPoll -> sPollHomeTitleLenght) . '...';
                        }
                        $sCode = $sInitPart . $oViewPoll -> getCode();
                    }
                    else
                    {
                        $oPoll->_oTemplate->displayPageNotFound();
                    }
                    $GLOBALS['oTopMenu'] -> setCustomSubHeader($sPageTitle);                        
                    $_page_cont[$iIndex]['page_main_code'] = $sCode;
                } else {
                    $oViewPoll = bx_instance($aModule['class_prefix'] . 'View', array($aPollSettings['action'], $aModule, $oPoll, $iPollId), $aModule);
                    $sPageCaption = _t('_bx_poll_home');
                    $_page['header']        = $sPageCaption ;
                    $_page['header_text']   = $sPageCaption ;
                    $sCode = $sInitPart . $oViewPoll -> getCode();
                }
                $_page_cont[$iIndex]['page_main_code'] = $sCode;
            break;

            case 'delete_poll':
                if($iPollId)
                    $oPoll->deletePoll($iPollId);

                $_page_cont[$iIndex]['page_main_code'] = $oPoll -> searchAll();
            break;

            default :
                $_page_cont[$iIndex]['page_main_code'] = $oPoll -> searchAll();
        }
    }

    PageCode($oPoll -> _oTemplate);
