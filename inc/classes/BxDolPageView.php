<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolPrivacy');
bx_import('BxDolPageViewAdmin'); // for caching abilities

/**
 * This class used for generation content of columned pages with blocks.
 *
 * Example of using.
 * To create your own columned php page using this class you should follow these instructions:
 *
 * 1. Create in the root of your site file with chosen name. For example we will use example.php with following content:
 *
 * <?php
 *
 * require_once( 'inc/header.inc.php' );
 * require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
 * bx_import('BxDolPageView');
 *
 * class BxExamplePageView extends BxDolPageView {
 *     function BxExamplePageView() {
 *         parent::BxDolPageView('example'); // Attention! Here should be name which you will insert into sys_page_compose_pages in next step.
 *     }
 *
 *     // Here is functions that generate blocks on page. They are contain only contents of blocks.
 *     // You do not have to worry about it's title, design, etc. PageView class will make it itself.
 *
 *     // This function creates first block
 *     function getBlockCode_BlockOne() {
 *         return 'Hello world!';
 *     }
 *
 *     // This function creates another block with dynamic menu tabs
 *     function getBlockCode_BlockTwo() {
 *         return array(
 *              'I am Block Two. I have top menu!',
 *              array(
 *                  _t('_View') => array(
 *                      'href' => $_SERVER['PHP_SELF'] . '?view=true',
 *                      'dynamic' => true,
 *                      'active' => !$this->isEditable,
 *                  ),
 *                  _t('_Edit') => array(
 *                      'href' => $_SERVER['PHP_SELF'] . '?edit=true',
 *                      'dynamicPopup' => true,
 *                      'active' => $this->isEditable,
 *                  )
 *              )
 *        );
 *     }
 * }
 *
 * $_page['name_index']	= 0; // choose your own index of template or leave if in doubt
 * $_page['header'] = 'Example page';
 * $_ni = $_page['name_index'];
 *
 * $oEPV = new BxExamplePageView();
 * $_page_cont[$_ni]['page_main_code'] = $oEPV->getCode();
 *
 * PageCode();
 *
 * ?>
 *
 * 2. Insert into your sys_page_compose_pages table one line.
 *    Name - Unique identification name of the page. Used for association page with its blocks.
 *           We recommend use only latin symbols and digits. (In our example use "example")
 *    Title - Title of your page, it is shown in the Page Builder. ("Example page")
 *    Order - Just the order in list of pages in the Page Builder.
 *
 * 3. Insert into sys_page_compose lines for each block.
 *    ID - ID of the block. System field. Leave it by default (0). Will be passed to the function as first argument.
 *    Page - ID name of page which you inserted to sys_page_compose_pages ("example").
 *    PageWidth - System field. Leave it by default ("960px"). Customized later.
 *    Desc - Few words about this block. Description.
 *    Caption - Title of this block. ("Block One", "Block Two")
 *    Column - System field. Leave it by default (0). Customized later.
 *    Order - System field. Leave it by default (0). Customized later.
 *    Func - Name of function in your class (without prefix) which will be called to generate the block. ("BlockOne", "BlockTwo")
 *    Content - Optional argument. Rarely used. Passed as the second argument to the function.
 *    DesignBox - Number of Design Box (container). Leave it by default if in doubt (1).
 *    ColWidth - System field. Leave it by default (0). Customized later.
 *    Visible - System field. Leave it by default (0). Customized later.
 *    MinWidth - Minimum recommended width of the block.
 *
 * 4. Now go to the Page Builder, select your page in the list of pages and customize it (width, columns and blocks order).
      Then customize every block (caption, visibility, etc.).
 *
 * 5. Open http://yoursite.com/example.php in your browser and you will see your new customized columned page.
 *
 */


class BxDolPageView
{
    var $sPageName;
    var $aPage; //--- cache of this page
    var $sCode = '';
    var $sWhoViews = 'non';
    var $iMemberID = 0;
    var $bAjaxMode = false;

    var $aColumnsWidth = array ();

    var $sTableName = 'sys_page_compose';
    var $sCacheFile ;

    var $oCacher = null;

    function __construct( $sPageName )
    {
        $this -> sCacheFile = 'sys_page_compose.inc';
        $this -> sPageName = $sPageName;

        if( !$this -> load() )
            return false;

        $this -> getViewerInfo();

        $this -> checkAjaxMode();

        $oZ = new BxDolAlerts('system', 'page_init', 0, 0, array(
            'page_name' => $this -> sPageName,
            'page_data' => &$this -> aPage,
        ));
        $oZ->alert();
    }

    function checkAjaxMode()
    {
        if( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) and $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' )
            $this -> bAjaxMode = true;
    }

    function createCache()
    {
        if ($this->oCacher == null)
            $this->oCacher = new BxDolPageViewCacher($this->sTableName, $this->sCacheFile);
       return $this->oCacher->createCache();
    }

    function load()
    {
        $oCache = $GLOBALS['MySQL']->getDbCacheObject();
        $aCache = $oCache->getData($GLOBALS['MySQL']->genDbCacheKey($this->sCacheFile));
        if (null === $aCache) {
            if (!$this->createCache() ) {
                echo '<br /><b>Warning</b> PageView cache not found';
                return false;
            }
            $aCache = $oCache->getData($GLOBALS['MySQL']->genDbCacheKey($this->sCacheFile));
        }

        if( !$aCache ) {
            echo '<br /><b>Warning!</b> PageView cache cannot be evaluated. Please recompile.';
            return false;
        }

        if( !array_key_exists( $this -> sPageName, $aCache ) ) {
            //echo '<br /><b>Warning!</b> The page not found in PageView cache.';
            return false;
        }

        $this -> aPage = $aCache[ $this->sPageName ];

        if (BxDolRequest::serviceExists('pageac', 'page_blocks_filter')) {
            BxDolService::call('pageac', 'page_blocks_filter', array(&$this));
        }

        return true;
    }

    function isLoaded()
    {
        return (isset($this->aPage) && !empty($this->aPage));
    }

    function getPageTitle()
    {
        return $this->aPage['Title'];
    }

    function getPageWidth()
    {
        return $this->aPage['Width'];
    }

    function getPageClass()
    {
    	return 'sys_page_' . $this->sPageName;
    }

    function getViewerInfo()
    {
        if (isMember()) {
            $this -> sWhoViews = 'memb';
            $this -> iMemberID = (int)$_COOKIE['memberID'];
        }
    }

    function forcePageWidth($iWidth)
    {
        $this->aPage['Width'] = $iWidth;
    }

    function gen()
    {
        global $_page_cont, $_ni, $_page, $oSysTemplate;

        if(!$this->aPage)
            return false;

        $this -> genColumnsHeader();

        $oSysTemplate->setPageWidth($this -> aPage['Width']);
        $oSysTemplate->addJs('view_edit.js');

        if(isset($this->aPage['Columns']) && !empty($this->aPage['Columns'])){
            foreach(array_keys($this->aPage['Columns']) as $iColumn)
                $this->genColumn($iColumn);
        }
        else
			$this->genPageEmpty();

        $this->genColumnsFooter();
    }

    function genPageEmpty()
    {
        $this->genColumnsHeader();
        $this->genColumnHeader(1, 100);
        $this->sCode .= MsgBox(_t('_Empty'));
        $this->genColumnFooter(1);
        $this->genColumnsFooter();
    }

    function genOnlyBlock( $iBlockID, $sDynamicType = 'tab' )
    {
        if( !$iBlockID )
            return false;

        // search block
        foreach( array_keys( $this -> aPage['Columns'] ) as $iColumn ) {
            $aColumn = $this -> aPage['Columns'][ $iColumn ];
            if( !$aColumn )
                return false;

            if (isset($aColumn['Blocks'][$iBlockID])) {
                $this -> genBlock( $iBlockID, $aColumn['Blocks'][$iBlockID], false, $sDynamicType );
                return true;
            }
        }

        return false;
    }

    function getCode()
    {
        if( !($this -> bAjaxMode and (int)$_REQUEST['pageBlock']) )
            $this -> gen();
        else {
            $this -> genOnlyBlock( (int)$_REQUEST['pageBlock'], $_REQUEST['dynamic'] );

            $oZ = new BxDolAlerts('system', 'page_output_block', 0, 0, array(
                'page_name' => $this -> sPageName,
                'page_data' => &$this -> aPage,
                'block_id' => (int)$_REQUEST['pageBlock'],
                'block_code' => &$this -> sCode,
            ));
            $oZ->alert();

            header( 'Content-type:text/html;charset=utf-8' );
            echo $this -> sCode;
            exit;
        }

        $oZ = new BxDolAlerts('system', 'page_output', 0, 0, array(
            'page_name' => $this -> sPageName,
            'page_data' => &$this -> aPage,
            'page_code' => &$this -> sCode,
        ));
        $oZ->alert();

        return $this -> sCode;
    }

    //for customizability
    function genColumnsHeader()
    {
    	$this -> sCode .= '<div class="sys_page ' . $this->getPageClass() . ' clearfix">';
    }

    //for customizability
    function genColumnsFooter()
    {
    	$this -> sCode .= '</div>';
    }

    function genColumn( $iColumn )
    {
        $aColumn = $this -> aPage['Columns'][ $iColumn ];
        if( !$aColumn )
            return false;

        $bShowDebugBlockInfo = !empty($_REQUEST['debug_mode']);

        $this -> genColumnHeader( $iColumn, $aColumn['Width'] );
        foreach( $aColumn['Blocks'] as $iBlockID => $aBlock ) {
            if ($bShowDebugBlockInfo)
                $iCurTime = getmicrotime();

            $this -> genBlock( $iBlockID, $aBlock );

            if ($bShowDebugBlockInfo) {
                $iCurTime2 = getmicrotime();
                $iGenTime = $iCurTime2 - $iCurTime;
                $this -> sCode .= "<!-- {$aBlock['Func']}:{$iGenTime} -->";
            }
        }

        $this -> genColumnFooter( $iColumn );
    }

    function getBlockCode_Topest($iColumn)
    {
        return '';
    }

    function genColumnHeader( $iColumn, $fColumnWidth )
    {
        $iPageWidth = (int)$this->aPage['Width'];
        $iColumnsCount = count($this -> aPage['Columns']);

        $bColumnFull = ($fColumnWidth == 100);

        $bLastColumnWasFull = (isset($this->aPage['Columns'][$iColumn - 1]) && ($this->aPage['Columns'][$iColumn - 1]['Width'] == 100));
        $bNextColumnIsFull = (isset($this->aPage['Columns'][$iColumn + 1]) && ($this->aPage['Columns'][$iColumn + 1]['Width'] == 100));

        $sAddClass = ' page_column_';
        if($bColumnFull)
            $sAddClass .= 'full';
        else if(($iColumn == 1) || $bLastColumnWasFull)
            $sAddClass .= 'first';
        else if($iColumn == $iColumnsCount || ($bNextColumnIsFull))
            $sAddClass .= 'last';
        else
            $sAddClass .= 'middle';

        if(preg_replace('/\d+/', '', $this->aPage['Width']) == 'px' && $GLOBALS['oTemplConfig']->PageComposeColumnCalculation == 'px') {
            $iPageContentWidth = $iPageWidth - (int)$GLOBALS['oTemplConfig']->iPageGap;

            $fColumnWidth = (int)round($iPageContentWidth * $fColumnWidth / 100);
            $sColumnWidth = $fColumnWidth . 'px';
        } else {
            $sColumnWidth = $fColumnWidth . '%';
        }

        $sAddClassWidth = $sAddClass . '_width_' . str_replace('.', '_', $fColumnWidth);
        $GLOBALS['oSysTemplate']->addCssStyle('.' . trim($this->getPageClass()) . ' .' . trim ($sAddClassWidth), array(
            'width' => $sColumnWidth
        ));

        $this -> sCode .= '<div class="page_column' . $sAddClass . $sAddClassWidth . '" id="page_column_' . $iColumn . '">';
        $this -> sCode .= $this->getBlockCode_Topest($iColumn);
    }

    function genColumnFooter( $iColumn )
    {
        $this -> sCode .= '</div>';
    }

    /*
        Note: if bStatic == false, it is popup
    */
    function genBlock( $iBlockID, $aBlock, $bStatic = true, $sDynamicType = 'tab' )
    {
        if( !$this -> isBlockVisible( $aBlock['Visible'] ) )
            return false;

        if (isset($GLOBALS['bx_profiler'])) $GLOBALS['bx_profiler']->beginPageBlock(_t($aBlock['Caption']), $iBlockID);

        if ($aBlock['Cache'] > 0 && getParam('sys_pb_cache_enable') == 'on') {
            $oCache = $this->getBlocksCacheObject ();
            $sBlock = $oCache->getData($this->genBlocksCacheKey ($iBlockID.$bStatic.$sDynamicType.isMember()), $aBlock['Cache']);
            if ($sBlock !== null) {
                if (isset($GLOBALS['bx_profiler'])) $GLOBALS['bx_profiler']->endPageBlock($iBlockID, $sBlock ? false : true, true);
                $this->sCode .= $sBlock;
                return;
            }
        }

        $sBlockFunction = 'getBlockCode_' . $aBlock['Func'];

        $mBlockCode = '';
        if( method_exists( $this, $sBlockFunction ) ) {
            $mBlockCode = $this -> $sBlockFunction( $iBlockID, $aBlock['Content'] );
        }

        // $sBlockFunction can return simple string or array with few values:
        // 0 - content, 1 - array of caption links, 2 - bottom links, 3 - caption addon (array or string)

        $sTopCode  = '';
        $sBottomCode = '';
        if( is_array( $mBlockCode ) ) {
            $sCaptionCode = $this->_getBlockCaptionCode($iBlockID, $aBlock, $mBlockCode, $bStatic, $sDynamicType);
            $sTopCode = $this->_getBlockTopCode($iBlockID, $aBlock, $mBlockCode, $bStatic, $sDynamicType);
            $sBlockCode = !isset($mBlockCode[3]) || (isset($mBlockCode[3]) && $mBlockCode[3] === false) ? '<div class="dbContent bx-def-bc-margin">' . $mBlockCode[0] . '</div>' : $mBlockCode[0];
            $sBottomCode = $this->_getBlockBottomCode($iBlockID, $aBlock, $mBlockCode, $bStatic, $sDynamicType);
        } else if(is_string($mBlockCode)) {
            $sCaptionCode = $this->_getBlockCaptionCode($iBlockID, $aBlock, $mBlockCode, $bStatic, $sDynamicType);
            $sBlockCode = $mBlockCode;
        } else
            $sBlockCode = false;

        $sBlock = '';

        if ($sBlockCode) {

            $sCodeDB = DesignBoxContent( $sCaptionCode, $sBlockCode, $aBlock['DesignBox'], $sTopCode, $sBottomCode);

            if ($bStatic) {
                $sBlock .= '<div class="page_block_container bx-def-margin-sec-leftright" id="page_block_' . $iBlockID . '">' . $sCodeDB . '</div>';
            } else {
                if ($sDynamicType == 'tab')
                    $sBlock .= $sCodeDB;
                elseif ($sDynamicType == 'popup')
                    $sBlock .= $GLOBALS['oFunctions']->transBox($sCodeDB, true);
            }
        }

        $this->sCode .= $sBlock;

        if ($aBlock['Cache'] > 0 && getParam('sys_pb_cache_enable') == 'on') {
            $oCache = $this->getBlocksCacheObject ();
            $oCache->setData($this->genBlocksCacheKey ($iBlockID.$bStatic.$sDynamicType.isMember()), $sBlock, $aBlock['Cache']);
        }

        if (isset($GLOBALS['bx_profiler'])) $GLOBALS['bx_profiler']->endPageBlock($iBlockID, $sBlockCode ? false : true, false );

        if (!$sBlockCode)
            return false;
    }

    function _getBlockCaptionCode($iBlockID, $aBlock, $aBlockCode, $bStatic = true, $sDynamicType = 'tab')
    {
        $sCode = "";

         if(is_array($aBlockCode) && isset($aBlockCode[3]) && (is_array($aBlockCode[3]) || (is_string($aBlockCode[3]) && $aBlockCode[3]))) {
               // if array is passed, pass it to the original caption translation
               if(is_array($aBlockCode[3]))
                $sCode = _t($aBlock['Caption'], $aBlockCode[3][0], $aBlockCode[3][1], $aBlockCode[3][2]);
            // if string is passed, replace the caption
            else if(is_string($aBlockCode[3]) && $aBlockCode[3])
                $sCode = _t($aBlockCode[3]);
          }
          // else pass the original caption
        else
            $sCode = _t($aBlock['Caption']);

        return $sCode;
     }
    function _getBlockTopCode($iBlockID, $aBlock, $aBlockCode, $bStatic = true, $sDynamicType = 'tab')
    {
        $sCaptionMenuFunc = !isset($aBlockCode[4]) ? 'getBlockCaptionItemCode' : $aBlockCode[4];

        $sCode = "";
        if(!$bStatic && $sDynamicType == 'popup')
            $sCode = '<div class="dbTopMenu"><i class="login_ajx_close sys-icon times"></i></div>';
        else if(isset($aBlockCode[1]) && is_array($aBlockCode[1]))
            $sCode = $this -> $sCaptionMenuFunc($iBlockID, $aBlockCode[1]);

        return $sCode;
    }

    function _getBlockBottomCode($iBlockID, $aBlock, $aBlockCode, $bStatic = true, $sDynamicType = 'tab')
    {
        $sCode = "";
        if(!empty($aBlockCode[2]) && is_array($aBlockCode[2]))
            $sCode = $this->getBlockBottomCode($iBlockID, $aBlockCode[2]);
        else
            $sCode = empty($aBlockCode[2]) ? '' : $aBlockCode[2];

        return str_replace('{id}', $iBlockID, $sCode);
    }

    function isBlockVisible( $sVisible )
    {
        if( strpos( $sVisible, $this -> sWhoViews ) === false )
            return false;
        else
            return true;
    }

    public static function getBlockCaptionItemCode( $iBlockID, $aLinks )
    {
    	if(empty($aLinks))
    		return '';

        $sActive = "";
        $aItems = array();
        foreach( $aLinks as $sTitle => $aLink ) {
        	$bActive = (bool)$aLink['active'];
            $sTitle = htmlspecialchars_adv(_t( $sTitle ));
            if($bActive)
            	$sActive = $sTitle;

            $isTextIcon = (isset($aLink['icon']) && false === strpos($aLink['icon'], '.'));

            $sClass = !empty($aLink['class']) ? $aLink['class'] : 'top_members_menu';
            $sClass .= isset($aLink['icon']) && !$isTextIcon ? ' with_icon' : '';

            $sOnclick = '';
            if(!empty($aLink['onclick']))
                $sOnclick = 'onclick="' . $aLink['onclick'] . '"';
            else if($aLink['dynamic'])
                $sOnclick = 'onclick="return !loadDynamicBlock(' . $iBlockID . ', this.href);"';
            else if($aLink['dynamicPopup'])
                $sOnclick = 'onclick="loadDynamicPopupBlock(' . $iBlockID . ', this.href); return false;"';

            $aItems[] = array(
                'bx_if:item_act' => array(
                    'condition' => $bActive,
                    'content' => array(
                        'bx_if:icon_act' => array(
                            'condition' => isset($aLink['icon']) && !$isTextIcon,
                            'content' => array(
                                'class' => $sClass,
                                'src' => empty($aLink['icon']) ? '' : $aLink['icon'],
                            )
                        ),
                        'bx_if:texticon_act' => array(
                            'condition' => isset($aLink['icon']) && $isTextIcon,
                            'content' => array(
                                'class' => $sClass,
                                'icon' => isset($aLink['icon']) ? $aLink['icon'] : '',
                            )
                        ),
                        'title' => $sTitle
                    )
                ),
                'bx_if:item_pas' => array(
                    'condition' => !$bActive,
                    'content' => array(
                        'bx_if:icon_pas' => array(
                            'condition' => isset($aLink['icon']) && !$isTextIcon,
                            'content' => array(
                                'class' => $sClass,
                                'src' => empty($aLink['icon']) ? '' : $aLink['icon']
                            )
                        ),
                        'bx_if:texticon_pas' => array(
                            'condition' => isset($aLink['icon']) && $isTextIcon,
                            'content' => array(
                                'class' => $sClass,
                                'icon' => empty($aLink['icon']) ? '' : $aLink['icon']
                            )
                        ),
                        'id' => (!empty($aLink['id'])  ? 'id="' . $aLink['id'] . '"' : ''),
                        'href' => htmlspecialchars_adv($aLink['href']),
                        'class' => $sClass,
                        'target' => (!empty($aLink['target'])  ? ' target="' . $aLink['target'] . '"' : ''),
                        'on_click' => $sOnclick,
                        'title' => $sTitle
                    )
                )
            );
        }

        return $GLOBALS['oSysTemplate']->parseHtmlByName('designbox_menu.html', array(
            'id' => $iBlockID,
        	'title' => $sActive,
        	'popup' => $GLOBALS['oFunctions']->transBox($GLOBALS['oSysTemplate']->parseHtmlByName('designbox_menu_items_1.html', array(
        		'bx_repeat:items' => $aItems
        	)))
        ));
    }

    public static function getBlockCaptionMenu( $iBlockID, $aLinks )
    {
		if(empty($aLinks))
    		return '';

    	$sActive = '';
        $aItems = array();
        foreach ( $aLinks as $sId => $aLink ) {
        	$bActive = isset($aLink['active']) && $aLink['active'] == 1;
        	$sTitle = htmlspecialchars_adv(_t($aLink['title']));
            if($bActive)
            	$sActive = $sTitle;

            $isTextIcon = (isset($aLink['icon']) && (false === strpos($aLink['icon'], '.')));

            if (isset($aLink['icon']) && !$isTextIcon)
                $aLink['class'] = isset($aLink['class']) ? $aLink['class'] . ' with_icon' : 'with_icon';

            $sHidden = ' style="display:none"';
            $sClass = isset($aLink['class']) ? ' class="' . $aLink['class'] . '"' : '';

            $aItems[] = array(
                'id' => $sId,
                'show_active' => !$bActive ? $sHidden : '',
                'show_passive' => $bActive ? $sHidden : '',
                'bx_if:icon_act' => array(
                    'condition' => isset($aLink['icon']) && !$isTextIcon,
                    'content' => array(
                        'class' => $sClass,
                        'src' => isset($aLink['icon']) ? $aLink['icon'] : '',
                    )
                ),
                'bx_if:texticon_act' => array(
                    'condition' => isset($aLink['icon']) && $isTextIcon,
                    'content' => array(
                        'class' => $sClass,
                        'icon' => isset($aLink['icon']) ? $aLink['icon'] : '',
                    )
                ),
                'bx_if:icon_pas' => array(
                    'condition' => isset($aLink['icon']) && !$isTextIcon,
                    'content' => array(
                        'class' => $sClass,
                        'src' => isset($aLink['icon']) ? $aLink['icon'] : '',
                    )
                ),
                'bx_if:texticon_pas' => array(
                    'condition' => isset($aLink['icon']) && $isTextIcon,
                    'content' => array(
                        'class' => $sClass,
                        'icon' => isset($aLink['icon']) ? $aLink['icon'] : '',
                    )
                ),
                'class' => $sClass,
                'title' => $sTitle,
                'href' => (isset($aLink['href']) ? 'href="' . htmlspecialchars_adv($aLink['href']) . '"' : ''),
                'target' => (isset($aLink['target'])  ? 'target="' . $aLink['target'] . '"' : ''),
                'on_click' => (isset($aLink['onclick']) ? 'onclick="' . $aLink['onclick'] . '"' : '')
            );
        }

        return $GLOBALS['oSysTemplate']->parseHtmlByName('designbox_menu.html', array(
            'id' => '' . $iBlockID,
            'title' => $sActive,
        	'popup' => $GLOBALS['oFunctions']->transBox($GLOBALS['oSysTemplate']->parseHtmlByName('designbox_menu_items_2.html', array(
        		'bx_repeat:items' => $aItems
        	)))
        ));
    }

    function getBlockBottomCode( $iBlockID, $aLinks )
    {
        if (empty($aLinks))
            return;

        $sCode = '
            <div class="dbBottomMenu">';

        foreach( $aLinks as $sTitle => $aLink ) {
            $sTitle = htmlspecialchars_adv( $sTitle );
            $sClass = $aLink['class'] ? $aLink['class'] : 'moreMembers';

            $sPossibleIconClass = (isset($aLink['icon_class']) && $aLink['icon_class']=='left') ? 'left' : 'right';
            $sPossibleIcon = (isset($aLink['icon'])==false) ? '' : <<<EOF
<img class="bot_icon_{$sPossibleIconClass}" alt="" src="{$aLink['icon']}" />
EOF;

            if( $aLink['active'] ) {
                $sCode .= <<<BLAH
                {$sPossibleIcon}<span class="{$sClass}">{$sTitle}</span>
BLAH;
            } else {
                $sTarget  = $aLink['target']  ? ( 'target="' . $aLink['target'] . '"' ) : '';
                $sOnclick = $aLink['dynamic'] ? ( 'onclick="return !loadDynamicBlock(' . $iBlockID . ', '. (isset($aLink['static']) && false == $aLink['static'] ? "'{$aLink['href']}'" : 'this.href') . ');"' ) : '';
                $sHref = isset($aLink['static']) && false == $aLink['static'] ? 'javascript:void(0);' : $aLink['href'];
                $sCode .= <<<BLAH
                {$sPossibleIcon}<a href="{$sHref}" class="{$sClass}" {$sTarget} {$sOnclick}>{$sTitle}</a>
BLAH;
            }
        }

        $sCode .= '
            </div>';

        return $sCode;
    }

    /* * * * Page Blocks * * * */

    /**
     * members statistic block
     */
    function getBlockCode_MemberStat()
    {
        return getSiteStatUser();
    }

    function getBlockCode_Echo( $iBlockID, $sContent )
    {
        return '<div class="dbContentHtml">' . $sContent . '</div>';
    }

    function getBlockCode_Text($iBlockID, $sContent)
    {
        return $this->getBlockCode_Echo($iBlockID, $sContent);
    }

    function getBlockCode_XML( $iBlockID, $sContent )
    {
        $sApplication = BxDolService::call('open_social', 'gen_application', array($sContent));
        return <<<EOF
<div class="dbContent bx-def-bc-margin">
    <div style="margin:5px;">{$sApplication}</div>
</div>
EOF;
    }

    function getBlockCode_PHP( $iBlockID, $sContent )
    {
        ob_start();
        $aResult = eval($sContent);
        $sContent = ob_get_clean();
        return !empty($aResult) ? $aResult : $sContent;
    }

    function getBlockCode_Code( $iBlockID, $sContent )
    {
        return $this->getBlockCode_PHP( $iBlockID, $sContent );
    }

    function getBlockCode_RSS( $iBlockID, $sContent )
    {
        $iNum = 0;
        if($sContent) {
            list( $sUrl, $iNum ) = explode( '#', $sContent );
            $iNum = (int)$iNum;
        }

        $iAddID = 0;
        if (isset( $this -> oProfileGen -> _iProfileID))
            $iAddID = $this -> oProfileGen -> _iProfileID;
        elseif (isMember())
            $iAddID = (int)$_COOKIE['memberID'];

        return '<div class="RSSAggrCont" rssid="' . $iBlockID . '" rssnum="' . $iNum . '" member="' . $iAddID . '">' . $GLOBALS['oFunctions']->loadingBoxInline() . '</div>';
    }


    function getBlockCode_LoginSection($iBlockID, $sParams = '')
    {
        $sDolUrl = BX_DOL_URL_ROOT;
        $sAdminUrl = BX_DOL_URL_ADMIN;

        $sAdminPanelC = _t('_Admin Panel');
        $sLogoutC = _t('_Log Out');
        $sControlPanelC = _t('_Control Panel');
        $sHelloMemberC = _t( '_Hello member', getNickName( $this -> iMemberID ) );

        $ret = '';
        if (isAdmin()) {
            $ret .= <<<EOF
<div class="logged_section_block">
    <span><a href="{$sAdminUrl}index.php" class="logout">{$sAdminPanelC}</a></span>
    <span> |&nbsp;| </span>
    <span><a href="{$sDolUrl}logout.php?action=admin_logout" class="logout">{$sLogoutC}</a></span>
</div>
EOF;
        } elseif (isMember()) {
            $sMemberIcon = get_member_icon( $memberID, 'left' );

            $ret .= <<<EOF
<div class="logged_member_block">
    {$sMemberIcon}
    <div class="hello_member bx-def-margin-sec-left">
        {$sHelloMemberC}<br />
        <a href="{$sDolUrl}member.php" class="logout">{$sControlPanelC}</a>&nbsp;
        <a href="{$sDolUrl}logout.php?action=member_logout" class="logout">{$sLogoutC}</a>
    </div>
</div>
EOF;
        } else {
            return getMemberLoginFormCode('login_box_form', $sParams);
        }

        return '<div class="dbContent bx-def-bc-margin">'.$ret.'</div>';
    }

    function GenFormWrap($sMainContent, $sPage, $sFunctionName, $iMaxThumbWidth, $iThumbsCnt)
    {
        $sBlockWidthSQL = "SELECT `PageWidth`, `ColWidth` FROM `sys_page_compose` WHERE `Page`='{$sPage}' AND (`Func`='{$sFunctionName}' OR `Caption`='{$sFunctionName}')";
        $aBlockWidthInfo = db_arr($sBlockWidthSQL);
        $aBlockWidth = (int)((int)($aBlockWidthInfo['PageWidth'] - 20) * (int)$aBlockWidthInfo['ColWidth'] / 100) - 14;
        $iDestWidth = $iThumbsCnt * $iMaxThumbWidth;
        $iDestWidth = getBlockWidth($aBlockWidth, $iMaxThumbWidth, $iThumbsCnt);
        $sWidthCent = ($iDestWidth>0) ? "width:{$iDestWidth}px;" : '';

        return <<<EOF
<div class="block_rel_100 bx-def-bc-margin">
    <div class="centered_div" style="{$sWidthCent}">
        {$sMainContent}
    </div>
    <div class="clear_both"></div>
</div>
EOF;
    }

    function getBlocksCacheObject ()
    {
        if ($this->oCacher == null)
            $this->oCacher = new BxDolPageViewCacher($this->sTableName, $this->sCacheFile);
        return $this->oCacher->getBlocksCacheObject ();
    }

    function genBlocksCacheKey ($sId)
    {
        if ($this->oCacher == null)
            $this->oCacher = new BxDolPageViewCacher($this->sTableName, $this->sCacheFile);
        return $this->oCacher->genBlocksCacheKey ($sId);
    }
}
