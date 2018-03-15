<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolPageView');
bx_import('BxTemplVotingView');

require_once('BxPollCmts.php');

class BxPollView extends BxDolPageView
{
    // contain link on current module's object;
    var $oModule;

    var $iPollId;

    // contain some information about current poll;
    var $aPollInfo = array();

    // need for vote objects;
    var $oVotingView;

    var $oCmtsView;

    // logged member's id;
    var $iMemberId;

    // contain some info about current module;
    var $aModule = array();

    // count of polls in poll's home page;
    var $iHomePage_countLatest   = 8;
    var $iHomePage_countFeatured = 4;

    /**
     * Class constructor;
     *
     * @param : $sPageName   (string)  - builder's page name;
     * @param : $oPollModule (object)  - created poll's module object;
     * @param : $iPollId     (integer) - poll's Id;
     */
    function __construct($sPageName, &$aModule, &$oPollModule, $iPollId)
    {
        parent::__construct($sPageName);

        // define member's Id;
        $aProfileInfo = getProfileInfo();
        $this -> iMemberId   = ( isset($aProfileInfo['ID']) )
            ? $aProfileInfo['ID']
            : 0;

        $this -> oModule   = $oPollModule;
        $this -> iPollId   = $iPollId;

        if($this -> iPollId) {
            $this -> aPollInfo = $this -> oModule -> _oDb -> getPollInfo($this -> iPollId);
            if(!$this -> oModule -> oPrivacy -> check('view', $this -> aPollInfo[0]['id_poll'], $this -> iMemberId) ) {
                echo $this -> oModule -> _oTemplate -> defaultPage( _t('_bx_poll'), MsgBox(_t('_bx_poll_access_denied')), 2 );
                exit;
            }
        }

        if($this -> aPollInfo) {
            $this -> aPollInfo = array_shift($this -> aPollInfo);
        }

        $this -> oVotingView = new BxTemplVotingView('bx_poll', $this -> iPollId);
        $this -> oCmtsView   = new BxPollCmts('bx_poll', $this -> iPollId);

        $this -> aModule = $aModule;

        if($sPageName == 'show_poll_info')
        	$GLOBALS['oTopMenu']->setCustomSubHeaderUrl(BX_DOL_URL_ROOT . $this -> oModule -> _oConfig -> getBaseUri() . '&action=show_poll_info&id=' . $this -> iPollId);

        $GLOBALS['oTopMenu']->setCustomBreadcrumbs(array(
            _t('_bx_poll') => BX_DOL_URL_ROOT . $this -> oModule -> _oConfig -> getBaseUri() . '&action=poll_home',
            $this -> aPollInfo['poll_question'] => '',
        ));
    }

    function genColumnsHeader()
    {
    	parent::genColumnsHeader();

        $this -> sCode .= $this -> oModule -> getInitPollPage();
    }

    /**
     * Function will generate featured polls;
     */
    function getBlockCode_FeaturedHome()
    {
        // ** init some variables;
        $sPaginate = null;

        $iPage  = ( isset($_GET['page']) )
            ? (int) $_GET['page']
            : 1;

        $iPerPage = ( isset($_GET['per_page']) )
            ? (int) $_GET['per_page']
            : $this -> iHomePage_countFeatured;

        if ($iPerPage <= 0 ) {
            $iPerPage = $this -> iHomePage_countFeatured;
        }

        if ( !$iPage ) {
            $iPage = 1;
        }

        // get only the member's polls ;
        $iTotalNum = $this -> oModule -> _oDb -> getFeaturedCount(1, true);

        if ( !$iTotalNum ) {
            $sOutputCode  = MsgBox( _t( '_Empty' ) );
        } else {
            $sLimitFrom   = ($iPage - 1) * $iPerPage;
            $sqlLimit     = "LIMIT {$sLimitFrom}, {$iPerPage}";
            $aPolls       = $this -> oModule -> _oDb -> getAllFeaturedPolls($sqlLimit, 1, true);
            $sOutputCode  = $this -> oModule -> genPollsList($aPolls);

            // define path to module;
            $sModulePath = $this -> oModule -> getModulePath() . '?action=featured';

            // build paginate block;
            $oPaginate = new BxDolPaginate(array(
                'page_url' => $sModulePath,
                'count' => $iTotalNum,
                'per_page' => $iPerPage,
                'page' => $iPage,
                'on_change_page' => 'return !loadDynamicBlock({id}, \'' . $sModulePath . '&action=poll_home&page={page}&per_page={per_page}\');',
            ));

            $sPaginate = $oPaginate -> getSimplePaginate($sModulePath);
        }

        return array($sOutputCode, array(), $sPaginate);
    }

    /**
     * Function will generate latest polls;
     */
    function getBlockCode_LatestHome()
    {
        // ** init some variables;
        $sPaginate = null;

        $iPage  = ( isset($_GET['page']) )
            ? (int) $_GET['page']
            : 1;

        $iPerPage = ( isset($_GET['per_page']) )
            ? (int) $_GET['per_page']
            : $this -> iHomePage_countLatest;

        if ($iPerPage <= 0 ) {
            $iPerPage = $this -> iHomePage_countLatest;
        }

        if ( !$iPage ) {
            $iPage = 1;
        }

        // get only the member's polls ;
        $iTotalNum = $this -> oModule -> _oDb -> getFeaturedCount(0, true);

        if ( !$iTotalNum ) {
            $sOutputCode  = MsgBox( _t( '_Empty' ) );
        } else {
            $sLimitFrom   = ($iPage - 1) * $iPerPage;
            $sqlLimit     = "LIMIT {$sLimitFrom}, {$iPerPage}";
            $aPolls       = $this -> oModule -> _oDb -> getAllFeaturedPolls($sqlLimit, 0, true);
            $sOutputCode  = $this -> oModule -> genPollsList($aPolls);

            // define path to module;
            $sModulePath = $this -> oModule -> getModulePath();

            // build paginate block;
            $oPaginate = new BxDolPaginate(array(
                'page_url' => $sModulePath,
                'count' => $iTotalNum,
                'per_page' => $iPerPage,
                'page' => $iPage,
                'on_change_page' => 'return !loadDynamicBlock({id}, \'' . $sModulePath . '&action=poll_home&page={page}&per_page={per_page}\');',
            ));

            $sPaginate = $oPaginate -> getSimplePaginate($sModulePath);
        }

        return array($sOutputCode, array(), $sPaginate);
    }

    /**
     * Function will generate block with actions;
     */
    function getBlockCode_ActionsBlock()
    {
    	$sCode = '';

        if(!$this -> aPollInfo)
            return MsgBox(_t('_Empty'));

        // prepare all needed keys
        $aUnitInfo = array(
            'ViewerID' => (int)$this->iMemberId,
            'ID' => (int)$this->aPollInfo['id_poll'],
            'BaseUri' => $this->oModule->_oConfig->getBaseUri(),
        );
        
        $aUnitInfo['base_url']     =  BX_DOL_URL_ROOT . $aUnitInfo['BaseUri'];
        $aUnitInfo['approved_cpt'] = '';
		$aUnitInfo['featured_cpt'] = '';
        
        $aUnitInfo['del_poll_title'] = $aUnitInfo['del_poll_url'] = $aUnitInfo['del_poll_script'];
        if(isLogged() && ($this -> aPollInfo['id_profile'] == $this->iMemberId || isAdmin())) {
            $sDeleteLink = $this->oModule->getModulePath() . '&action=delete_poll&id=' . $aUnitInfo['ID'];

            $aUnitInfo['del_poll_title'] = _t('_bx_poll_delete');
            $aUnitInfo['del_poll_url'] = $sDeleteLink;
            $aUnitInfo['del_poll_script'] = "if(confirm('" . bx_js_string(_t('_Are_you_sure')) . "')) window.open ('" . $sDeleteLink . "','_self'); return false;";
        }
        
        $sMainPrefix = 'bx_poll';
        
        if (isAdmin($this->iMemberId) || (isModerator($this->iMemberId) && $this->aPollInfo['id_profile'] != $this->iMemberId))
        {
            $aUnitInfo['approved_cpt'] = _t('_' . $sMainPrefix .  ($this->aPollInfo['poll_approval'] ? '_dis' : '_') . 'approve');			
			$aUnitInfo['featured_cpt'] = _t('_' . $sMainPrefix .  ($this->aPollInfo['poll_featured'] ? '_un' : '_') . 'featured');            
        }

        $oSubscription = BxDolSubscription::getInstance();
        $aButton = $oSubscription -> getButton($this -> iMemberId, $sMainPrefix, '', $this -> aPollInfo['id_poll']);
        $sCode .= $oSubscription -> getData();
        $aUnitInfo['sbs_poll_title']  =  $aButton['title'];
        $aUnitInfo['sbs_poll_script'] =  $aButton['script'];

        $aUnitInfo['TitleShare'] = $this->oModule->isAllowedShare($this -> aPollInfo) ? _t('_Share') : '';

        $aUnitInfo['repostCpt'] = $aUnitInfo['repostScript'] = '';
    	if(BxDolRequest::serviceExists('wall', 'get_repost_js_click')) {
        	$sCode .= BxDolService::call('wall', 'get_repost_js_script');

			$aUnitInfo['repostCpt'] = _t('_Repost');
			$aUnitInfo['repostScript'] = BxDolService::call('wall', 'get_repost_js_click', array($this->iMemberId, $sMainPrefix, 'add', $this->aPollInfo['id_poll']));
        }

        $sActions = $GLOBALS['oFunctions'] -> genObjectsActions($aUnitInfo, $sMainPrefix);
        if(empty($sActions))
        	return '';

        return  $sCode . $sActions;
    }

    /**
     * The function will generate the block of the polls owner
     */
    function getBlockCode_OwnerBlock()
    {
        if(!$this -> aPollInfo) {
            return MsgBox( _t('_Empty') );
        }

       return  $this -> oModule -> getOwnerBlock($this -> aPollInfo['id_profile'], $this -> aPollInfo);
    }

    /**
     * Function will generate poll block;
     */
    function getBlockCode_PoolBlock()
    {
        if(!$this -> aPollInfo)
            return MsgBox( _t('_Empty') );

        $sContent = $this -> oModule -> getPollBlock($this -> aPollInfo, false, true);
        return array($sContent);
    }

    /**
     * Function will generate comments block;
     */
    function getBlockCode_CommentsBlock()
    {
        if(!$this -> aPollInfo)
            return MsgBox(_t('_Empty'));

        if(  $this -> oModule -> oPrivacy -> check('comment', $this -> aPollInfo['id_poll'], $oPoll -> aPollSettings['member_id']) ) {
            $sOutputCode = $this -> oCmtsView  -> getExtraCss();
            $sOutputCode .= $this -> oCmtsView  -> getExtraJs();

            $sOutputCode .= ( !$this -> oCmtsView -> isEnabled() )
                                ? null
                                : $this -> oCmtsView -> getCommentsFirst();
        } else
            $sOutputCode = MsgBox( _t( '_bx_poll_privacy_comment_error' ) );

        return $sOutputCode;
    }

    /**
     * Function will generate vote block;
     */
    function getBlockCode_VotingsBlock()
    {
        if(!$this -> aPollInfo)
            return MsgBox(_t('_Empty'));

        if(  $this -> oModule -> oPrivacy -> check('vote', $this -> aPollInfo['id_poll'], $oPoll -> aPollSettings['member_id']) ) {
            if ( $this -> oVotingView -> isEnabled()){
                $sOutputCode = $this -> oVotingView -> getBigVoting();
            }
        } else
            $sOutputCode = MsgBox( _t( '_bx_poll_privacy_vote_error' ) );

        return array($sOutputCode);
    }

    function getBlockCode_SocialSharing()
    {
    	if(!$this->oModule->isAllowedShare($this->aPollInfo))
    		return '';

        $sUrl = BX_DOL_URL_ROOT . $this -> oModule -> _oConfig -> getBaseUri() . '&action=show_poll_info&id=' . $this -> aPollInfo['id_poll'];
        $sTitle = $this -> aPollInfo['poll_question'];

        bx_import('BxTemplSocialSharing');
        $sCode = BxTemplSocialSharing::getInstance()->getCode($sUrl, $sTitle);
        return array($sCode, array(), array(), false);
    }
}
