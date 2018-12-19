<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( BX_DIRECTORY_PATH_ROOT . 'templates/tmpl_' . $GLOBALS['tmpl'] . '/scripts/BxTemplSearchResultText.php');
require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolPaginate.php' );
require_once( 'BxPollModule.php' );

class BxPollSearch extends BxTemplSearchResultText
{
    var $oPollObject;
    var $aModule;

    /**
     * Class constructor ;
     */
    function __construct($oPollObject = null)
    {
        // call the parent constructor ;
        parent::__construct();

        if(!$oPollObject) {
            $this -> oPollObject = BxDolModule::getInstance('BxPollModule');
        } else {
            $this -> oPollObject = $oPollObject;
        }

        // init some needed db table's fields ;

        /* main settings for shared modules
           ownFields - fields which will be got from main table ($this->aCurrent['table'])
           searchFields - fields which using for full text key search
           join - array of join tables
                join array (
                    'type' - type of join
                    'table' - join table
                    'mainField' - field from main table for 'on' condition
                    'onField' - field from joining table for 'on' condition
                    'joinFields' - array of fields from joining table
                )
        */

        $this -> aCurrent = array (

            // module name ;
            'name'  => 'poll',
            'title' => '_bx_polls',
            'table' => $this -> oPollObject -> _oDb -> sTablePrefix . 'data',

            'ownFields'     => array('id_poll', 'poll_question', 'poll_answers', 'poll_date'),
            'searchFields'  => array('poll_question', 'poll_answers', 'poll_tags', 'poll_categories'),

            'join' => array(
                'profile' => array(
                    'type' => 'left',
                    'table' => 'Profiles',
                    'mainField' => 'id_profile',
                    'onField' => 'ID',
                    'joinFields' => array('NickName'),
                )
            ),

            'restriction' => array (
                'activeStatus' => array('value'=>'active', 'field'=>'poll_status', 'operator'=>'='),
                'approvalStatus' => array('value'=>'1', 'field'=>'poll_approval', 'operator'=>'='),
                'tag' => array('value'=>'', 'field'=>'poll_tags', 'operator'=>'against', 'paramName'=>'tag'),
                'category' => array('value'=>'', 'field'=>'poll_categories', 'operator'=>'against', 'paramName'=>'categoryUri'),
                'owner' => array('value'=>'', 'field'=>'id_profile', 'operator'=>'=', 'paramName'=>'userID'),
                'featured' => array('value'=>'', 'field'=>'poll_featured', 'operator'=>'='),
                'unfeatured' => array('value'=>'', 'field'=>'poll_featured', 'operator'=>'='),
                'my'       => array('value'=>'', 'field'=>'id_profile', 'operator'=>'='),
                'calendar-min' => array('value' => "", 'field' => 'poll_date', 'operator' => '>=', 'no_quote_value' => true),
                'calendar-max' => array('value' => "", 'field' => 'poll_date', 'operator' => '<=', 'no_quote_value' => true),
                'allow_view' => array('value' => "", 'field' => 'allow_view_to', 'operator' => 'in'),
            ),

            'paginate' => array( 'perPage' => 6, 'page' => 1, 'totalNum' => 10, 'totalPages' => 1),
            'sorting' => 'last',
            'view' => 'full',
            'ident' => 'id_poll'
        );
    }

    function displayResultBlock ()
    {
        $sCode = parent::displayResultBlock();
        return !empty($sCode) ? $this->oPollObject->_oTemplate->parseHtmlByName('default_margin.html', array('content' => $sCode)) : $sCode;
    }

    /**
     * Function will generate poll block ;
     *
     * @return : (text) - Html presentation data ;
     */
    function displaySearchUnit($aData)
    {
        $aData['poll_ago'] = time() - $aData['poll_date'];
        $sOutputHtml =  $this -> oPollObject -> getPollBlock($aData);
        return $sOutputHtml;
    }

    /**
     * Function will generate searched result;
     *
     * @return : (text) - Html presentation data ;
     */
    function displaySearchBox ($sCode, $sPaginate = '', $bAdminBox = false)
    {
        // generate the init poll's part ;
        $sInitSection =  $this -> oPollObject -> getInitPollPage(false);

        if (isset($this->aCurrent['rss']) && $this->aCurrent['rss']['link'])
            $aCaptionMenu = '<div class="dbTopMenu"><div class="notActive notActiveIcon" style="background-image:url('.getTemplateIcon('rss.png').')"><a target="_blank" class="top_members_menu" href="' . $this->aCurrent['rss']['link'] . (false === strpos($this->aCurrent['rss']['link'], '?') ? '?' : '&') . 'rss=1">' . _t('RSS') . '</a></div></div>';
        $sCode = DesignBoxContent(_t($this->aCurrent['title']), $sCode. $sPaginate, 1, $aCaptionMenu);
        if (!isset($_POST['searchMode'], $_GET['searchMode']))
            $sCode = '<div id="page_block_'.$this->id.'">'.$sCode.'<div class="clear_both"></div></div>';

        // include css file ;
        $sCssStyles = $this -> oPollObject -> _oTemplate -> addCss('main.css', true);

        return $sCssStyles . $sInitSection . $sCode;
    }

    function _getPseud ()
    {
    }

    function getAlterOrder ()
    {
        $aSql = array();
        switch($this->aCurrent['sorting']) {
            case 'popular' :
                $aSql['order'] = " ORDER BY `poll_rate` DESC";
            break;

            case 'last' :
                $aSql['order'] = " ORDER BY `poll_date` DESC";
            break;

            default;
                $aSql['order'] = " ORDER BY `poll_rate_count` DESC";
            break;
        }

        return $aSql;
    }

    function showPagination($aParams = array())
    {
        $sModulePath = isset($aParams['module_path']) && !empty($aParams['module_path']) ? $aParams['module_path'] : false;

        $aParameters['settings'] = array(
            'count'             => $this -> aCurrent['paginate']['totalNum'],
            'per_page'          => $this -> aCurrent['paginate']['perPage'],
            'page'              => $this -> aCurrent['paginate']['page'],
        );

        //define some pagination parameters;
        if(!$sModulePath) {
            $aLinkAddon = $this -> getLinkAddByPrams();
            $aParameters['settings']['page_url']            = $this -> getCurrentUrl('browseAll', 0, '');
            $aParameters['settings']['on_change_page']      = 'return !loadDynamicBlock(' . $this -> id . ', \'searchKeywordContent.php?searchMode=ajax&section[]=' . $this -> aCurrent['name'] . '&keyword=' . rawurlencode($_REQUEST['keyword']) . $aLinkAddon['params'] . '&page={page}&per_page={per_page}\');';
            $aParameters['settings']['on_change_per_page']  = 'return !loadDynamicBlock(' . $this -> id . ', \'searchKeywordContent.php?searchMode=ajax&section[]=' . $this -> aCurrent['name'] . '&keyword=' . rawurlencode($_REQUEST['keyword']) . $aLinkAddon['params'] . '&page=1&per_page=\' + this.value);';
        } else {
            $aParameters['settings']['page_url']            = $sModulePath . '&page={page}&per_page={per_page}';
            $aParameters['settings']['on_change_page']      = null;
            $aParameters['settings']['on_change_per_page']  = null;
        }

        $oPaginate = new BxDolPaginate( array_shift($aParameters) );
        $sPaginate = '<div class="clear_both"></div>' . $oPaginate -> getPaginate();

        return $sPaginate;
    }
}
