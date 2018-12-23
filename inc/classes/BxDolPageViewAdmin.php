<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

class BxDolPageViewAdmin
{
    var $aPages = array();
    var $oPage;
    var $sPage_db; //name of current page, used form database manipulations
    var $sDBTable; //used database table
    var $bAjaxMode = false;
    var $aTitles; // array containing aliases of pages

    function __construct( $sDBTable, $sCacheFile )
    {
        $GLOBALS['oAdmTemplate']->addJsTranslation(array(
            '_adm_pbuilder_Reset_page_warning',
            '_adm_pbuilder_Column_non_enough_width_warn',
            '_adm_pbuilder_Column_delete_confirmation',
            '_adm_pbuilder_Add_column',
            '_adm_pbuilder_Want_to_delete',
            '_delete'
        ));

        $this -> sDBTable = $sDBTable;
        $this -> sCacheFile = $sCacheFile;

        // special actions (without creating page)
        if (isset($_REQUEST['action_sys'])) {
            switch ($_REQUEST['action_sys']) {
                case 'loadNewPageForm':
                	header('Content-Type: text/html; charset=utf-8');
                    echo $this -> showNewPageForm();
                break;

                case 'createNewPage':
                	header('Content-Type:text/javascript');
                    echo json_encode($this->createUserPage());
                break;

                case 'addCodeBlock':
                	header('Content-Type:text/javascript');
                    echo json_encode(array('result' => $GLOBALS['MySQL']->query("INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`, `Cache`) VALUES ('', '1140px', 'Simple PHP Block', '_Code Block', 0, 0, 'Sample', 'Code', 11, 0, 'non,memb', 0, 0)") ? 'ok' : 'fail'));
                break;

                case 'removeCodeBlock':
                	header('Content-Type:text/javascript');
                    echo json_encode(array('result' => $GLOBALS['MySQL']->query("DELETE FROM `sys_page_compose` WHERE `Func` = 'Sample' AND `Content` = 'Code'") ? 'ok' : 'fail'));
                break;
            }
            exit;
        }

        $sPage = process_pass_data( isset( $_REQUEST['Page'] ) ? trim( urldecode ($_REQUEST['Page']) ) : '' );

        $this -> getPages();

        if (strlen($sPage) && in_array($sPage, $this->aPages)) {
            $this->oPage = new BxDolPVAPage( $sPage, $this );
        }

        $this -> checkAjaxMode();
        if(!empty($_REQUEST['action']) && $this -> oPage) {
            $this -> sPage_db = addslashes( $this -> oPage -> sName );

            switch( $_REQUEST['action'] ) {
                case 'load':
                    header( 'Content-type:text/javascript' );
                    send_headers_page_changed();
                    echo $this -> oPage -> getJSON();
                    break;

                case 'saveColsWidths':
                    if( is_array( $_POST['widths'] ) ) {
                        $this -> saveColsWidths( $_POST['widths'] );
                        $this -> createCache();
                    }
                    break;

                case 'saveBlocks':
                    if( is_array( $_POST['columns'] ) ) {
                        $this -> saveBlocks( $_POST['columns'] );
                        $this -> createCache();
                    }
                    break;

                case 'loadEditForm':
                    $iBlockID = (int)$_POST['id'];
                    if($iBlockID) {
                        header( 'Content-type:text/html;charset=utf-8' );
                        echo $this -> showPropForm( $iBlockID );
                    }
                    break;

                case 'saveItem':
                    if( (int)$_POST['id'] ) {
                        $this -> saveItem( $_POST );
                        $this -> createCache((int)$_POST['id']);
                    }
                    break;

                case 'deleteCustomPage' :
                    header( 'Content-type:text/html;charset=utf-8' );
                    $sPage = isset($_POST['Page']) ? $_POST['Page'] : '';

                    if(!$sPage) {
                        echo _t('_Error Occured');
                    } else {
                        //remove page from page builder
                        $this -> deleteCustomPage($sPage);
                    }
                    break;

                case 'deleteBlock':
                    if( $iBlockID = (int)$_REQUEST['id'] ) {
                        $this -> deleteBlock( $iBlockID );
                        $this -> createCache();
                    }
                    break;

                case 'checkNewBlock':
                    if( $iBlockID = (int)$_REQUEST['id'] )
                        $this -> checkNewBlock( $iBlockID );
                    break;

                case 'savePageWidth':
                    if( $sPageWidth = process_pass_data( $_POST['width'] ) ) {
                        $this -> savePageWidth( $sPageWidth );
                        $this -> createCache();
                    }
                    break;

                case 'saveOtherPagesWidth':
                    if( $sWidth = $_REQUEST['width'] ) {
                        setParam( 'main_div_width', $sWidth );
                        echo 'OK';
                    }
                    break;

                case 'resetPage':
                    $this -> resetPage();
                    $this -> createCache();
                    break;
            }
        }
        if($this -> bAjaxMode)
            exit;

        $sMainPageContent = $this -> showBuildZone();

        global $_page, $_page_cont;
        $iNameIndex = 0;
        $_page = array(
            'name_index' => $iNameIndex,
            'css_name' => array('pageBuilder.css', 'forms_adv.css'),
            'js_name' => array('jquery.ui.core.min.js', 'jquery.ui.widget.min.js', 'jquery.ui.mouse.min.js', 'jquery.ui.sortable.min.js', 'jquery.ui.slider.min.js', 'jquery.cookie.min.js', 'BxDolPageBuilder.js'),
            'header' => _t('_adm_pbuilder_title'),
            'header_text' => _t('_adm_pbuilder_box_title'),
        );
        $_page_cont[$iNameIndex]['page_main_code'] = $sMainPageContent;

        PageCodeAdmin();
    }

    function createUserPage()
    {
        $sUri = uriGenerate(process_db_input($_REQUEST['uri']), $this -> sDBTable . '_pages', 'Name');
        $sTitle = process_db_input($_REQUEST['title']);

        $res = db_res("INSERT INTO `{$this -> sDBTable}_pages` (`Name`, `Title`, `Order`, `System`) SELECT '{$sUri}', '$sTitle', MAX(`Order`) + 1, '0' FROM `{$this -> sDBTable}_pages` LIMIT 1");
        if(!db_affected_rows($res))
        	return array('code' => '1', 'message' => 'Failed database insert');

        $iPageId = db_last_id();
        $oZ = new BxDolAlerts('page_builder', 'page_add', $iPageId, 0, array('uri' => $sUri));
        $oZ->alert();        

        return array('code' => '0', 'message' => 'OK', 'uri' => $sUri);
    }

    function savePageWidth( $sPageWidth )
    {
        $sPageWidth = process_db_input( $sPageWidth, BX_TAGS_STRIP );
        $sQuery = "UPDATE `{$this -> sDBTable}` SET `PageWidth` = '{$sPageWidth}' WHERE `Page` = '{$this -> sPage_db}'";
        db_res( $sQuery );

        echo 'OK';
    }

    function createCache($iBlockId = 0)
    {
        $oCacher = new BxDolPageViewCacher( $this -> sDBTable, $this -> sCacheFile );
        $oCacher -> createCache();

        if ($iBlockId > 0) {
            $oCacheBlocks = $oCacher->getBlocksCacheObject ();
            $a = array (
                $iBlockId.true.'tab'.false,
                $iBlockId.false.'tab'.false,
                $iBlockId.true.'popup'.false,
                $iBlockId.false.'popup'.false,
                $iBlockId.true.'tab'.true,
                $iBlockId.false.'tab'.true,
                $iBlockId.true.'popup'.true,
                $iBlockId.false.'popup'.true
            );
            foreach ($a as $sKey)
                $oCacheBlocks->delData($oCacher->genBlocksCacheKey ($sKey));
        }

    }

    function checkNewBlock( $iBlockID )
    {
        $iBlockID = (int) $iBlockID;

        $sQuery = "SELECT `Desc`, `Caption`, `Func`, `Content`, `Visible`, `DesignBox` FROM `{$this -> sDBTable}` WHERE `ID` = '{$iBlockID}'";
        $aBlock = db_assoc_arr( $sQuery );

        if( $aBlock['Func'] == 'Sample' ) {
            $sQuery = "
                INSERT INTO `{$this -> sDBTable}` SET
                    `Desc`    = '" . addslashes( $aBlock['Desc']    ) . "',
                    `Caption` = '" . addslashes( $aBlock['Caption'] ) . "',
                    `Func`    = '{$aBlock['Content']}',
                    `Visible` = '{$aBlock['Visible']}',
                    `DesignBox` = '{$aBlock['DesignBox']}',
                    `Page`    = '{$this -> sPage_db}'
            ";
            db_res( $sQuery );

            echo db_last_id();

            $this -> createCache();
        }
    }

    function deleteCustomPage($sPageName)
    {
        $sPageName = process_db_input($sPageName, BX_TAGS_STRIP, BX_SLASHES_AUTO);
        $sQuery = "DELETE `{$this -> sDBTable}_pages`, `{$this -> sDBTable}` FROM  `{$this -> sDBTable}_pages`
            LEFT JOIN `{$this -> sDBTable}` ON `{$this -> sDBTable}`.`Page` = `{$this -> sDBTable}_pages`.`Name`
            WHERE `{$this -> sDBTable}_pages`.`Name` = '{$sPageName}'";

        if(!db_res($sQuery)) 
            return false;

        $oZ = new BxDolAlerts('page_builder', 'page_delete', 0, 0, array('uri' => $sPageName));
        $oZ->alert();

        return true;
    }

    function deleteBlock( $iBlockID )
    {
        $iBlockID = (int) $iBlockID;
        $aBlock = db_assoc_arr("SELECT * FROM `{$this -> sDBTable}` WHERE `ID`='{$iBlockID}'");
        if(empty($aBlock) || !is_array($aBlock))
            return true;

        $sQuery = "DELETE FROM `{$this -> sDBTable}` WHERE `Page` = '{$this -> sPage_db}' AND `ID`='{$iBlockID}'";
        if(!db_res( $sQuery ))
            return false;

        $oZ = new BxDolAlerts('page_builder', 'block_delete', $iBlockID, 0, array('page_uri' => $aBlock['Page']));
        $oZ->alert();

        return true;
    }

    function resetPage()
    {
        if( $this -> oPage -> bResetable ) {
            $sQuery = "DELETE FROM `{$this -> sDBTable}` WHERE `Page` = '{$this -> sPage_db}'";
            db_res($sQuery);
            execSqlFile( $this -> oPage -> sDefaultSqlFile );

            $oZ = new BxDolAlerts('page_builder', 'page_reset', 0, 0, array('uri' => $this -> sPage_db));
            $oZ->alert();
        }

        echo (int)$this -> oPage -> bResetable;
    }

    function saveItem( $aData )
    {
        $iID = (int)$aData['id'];

        $sQuery = "SELECT `Func` FROM `{$this -> sDBTable}` WHERE `ID` = $iID";
        $sFunc  = db_value( $sQuery );
        if( !$sFunc )
            return;

        $sCaption = process_db_input($aData['Caption'], BX_TAGS_STRIP);
        $iDesignBox = isset($aData['DesignBox']) > 0 ? (int)$aData['DesignBox'] : 1;
        $sVisible = is_array( $aData['Visible'] ) ? implode( ',', $aData['Visible'] ) : '';
        $iCache = (int)$aData['Cache'] > 0 ? (int)$aData['Cache'] : 0;

        if( $sFunc == 'RSS' )
            $sContentUpd = "`Content` = '" . process_db_input($aData['Url'], BX_TAGS_STRIP) . '#' . (int)$aData['Num'] . "',";
        elseif( $sFunc == 'Echo' || $sFunc == 'Text' || $sFunc == 'Code' )
            $sContentUpd = "`Content` = '" . process_db_input($aData['Content'], BX_TAGS_NO_ACTION) . "',";
        elseif( $sFunc == 'XML' ) {
            $iApplicationID = (int)$aData['application_id'];
            $sContentUpd = "`Content` = '" . $iApplicationID . "',";
        } else
            $sContentUpd = '';

        $sQuery = "
            UPDATE `{$this -> sDBTable}` SET
                `Caption` = '{$sCaption}',
                {$sContentUpd}
                `DesignBox` = '{$iDesignBox}',
                `Visible` = '{$sVisible}',
                `Cache` = '{$iCache}'
            WHERE `ID` = '{$iID}'
        ";
        db_res( $sQuery );
        $sCaption = process_pass_data($aData['Caption']);
                if (mb_strlen($sCaption) == 0)
                    $sCaption = '_Empty';

        $oZ = new BxDolAlerts('page_builder', 'block_form_save', $iID, 0, array('data' => $aData, 'caption_key' => &$sCaption));
        $oZ->alert();

        echo _t($sCaption);
    }

    function saveColsWidths( $aWidths )
    {
        $iCounter = 0;
        foreach( $aWidths as $iWidth ) {
            $iCounter ++;
            $iWidth = (float)$iWidth;

            $sQuery = "UPDATE `{$this -> sDBTable}` SET `ColWidth` = $iWidth WHERE `Page` = '{$this -> sPage_db}' AND `Column` = $iCounter";
            db_res( $sQuery );
        }

        echo 'OK';
    }

    function saveBlocks( $aColumns )
    {
        //reset blocks on this page
        $sQuery = "UPDATE `{$this -> sDBTable}` SET `Column` = 0, `Order` = 0 WHERE `Page` = '{$this -> sPage_db}'";
        db_res( $sQuery );

        $iColCounter = 0;
        foreach( $aColumns as $sBlocks ) {
            $iColCounter ++;

            $aBlocks = explode( ',', $sBlocks );
            foreach( $aBlocks as $iOrder => $iBlockID ) {
                $iBlockID = (int)$iBlockID;
                $sQuery = "UPDATE `{$this -> sDBTable}` SET `Column` = $iColCounter, `Order` = $iOrder WHERE `ID` = $iBlockID AND `Page` = '{$this -> sPage_db}'";
                db_res( $sQuery );
            }
        }

        echo 'OK';
    }

    function getCssCode()
    {
        return $GLOBALS['oAdmTemplate']->addCss(array('general.css', 'forms_adv.css', 'plugins/jquery/themes/|jquery-ui.css'), true);
    }

    function showBuildZone()
    {
    	$sEditorId = $sEditorCode = ''; 

    	bx_import('BxDolEditor');
        $oEditor = BxDolEditor::getObjectInstance();
        if($oEditor) {
        	$sEditorId = 'buildZoneEditor';
            $sEditorCode = $oEditor->attachEditor('#' . $sEditorId, BX_EDITOR_FULL);
        }

        return $GLOBALS['oAdmTemplate']->parseHtmlByName('pbuilder_content.html', array(
            'top_controls' => $this->getPageSelector(),
            'bx_if:page' => array(
                'condition' => (bool)$this -> oPage,
                'content' => array(
                    'bx_if:delete_link' => array(
                        'condition' => (isset($this->oPage->isSystem) && !$this->oPage->isSystem),
                        'content' => array(
                        )
                    ),
                    'bx_if:view_link' => array(
                        'condition' => (isset($this->oPage->isSystem) && !$this->oPage->isSystem),
                        'content' => array(
                            'site_url' => $GLOBALS['site']['url'],
                            'page_name' => (!isset($this->oPage->sName)) ?: htmlspecialchars($this->oPage->sName)
                        )
                    ),
                    'parser_url' => bx_html_attribute($_SERVER['PHP_SELF']),
                    'page_name' => (!isset($this->oPage->sName)) ?: addslashes($this->oPage->sName),
                    'page_width_min' => getParam('sys_template_page_width_min'),
                    'page_width_max' => getParam('sys_template_page_width_max'),
                    'page_width' => (!isset($this->oPage->iPageWidth)) ?: $this->oPage->iPageWidth,
                    'main_width' => getParam('main_div_width')
                )
            ),
            'bx_if:empty' => array(
                'condition' => !(bool)$this -> oPage,
                'content' => array(
                    'content' => MsgBox(_t('_Empty'))
                )
            ),
            'bx_if:editor' => array(
            	'condition' => !empty($sEditorCode),
            	'content' => array(
            		'editor_id' => $sEditorId,
            		'editor_code' => $sEditorCode
            	)
            )
        ));
    }

    function getPageSelector()
    {
        $bPage = !empty($this->oPage->sName);
        $aPages = array(
            array(
                'value' => 'none',
                'title' => _t('_adm_txt_pb_select_page'),
                'selected' => !$bPage ? 'selected="selected"' : ''
            )
        );

        asort($this->aTitles);
        foreach($this->aTitles as $sName => $sTitle)
            $aPages[] = array(
                'value' => htmlspecialchars_adv(urlencode($sName)),
                'title' => htmlspecialchars(!empty($sTitle) ? $sTitle : $sName),
                'selected' => (isset($this->oPage->sName) && $this->oPage->sName == $sName) ? 'selected="selected"' : ''
            );

        return $GLOBALS['oAdmTemplate']->parseHtmlByName('pbuilder_cpanel.html', array(
            'bx_repeat:pages' => $aPages,
            'bx_if:show_add_column' => array(
                'condition' => $bPage,
                'content' => array()
            ),
            'url' => bx_html_attribute($_SERVER['PHP_SELF'])
        ));
    }

    function getPages()
    {
        $sPagesQuery = "SELECT `Name`, `Title` FROM `{$this -> sDBTable}_pages` ORDER BY `Order`";

        $rPages = db_res( $sPagesQuery );
        while( $aPage = $rPages->fetch() ) {
            $this -> aPages[] = $aPage['Name'];
            $this -> aTitles[$aPage['Name']] = $aPage['Title'];
        }
    }

    function checkAjaxMode()
    {
        if( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) and $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' )
            $this -> bAjaxMode = true;
    }

    function showPropForm($iBlockID)
    {
        $sNoPropertiesC = _t('_adm_pbuilder_This_block_has_no_properties');
        $sProfileFieldsC = _t('_adm_pbuilder_Profile_Fields');
        $sHtmlBlockC = _t('_adm_pbuilder_HTML_Block');
        $sXmlBlockC = _t('_adm_pbuilder_XML_Block');
        $sRssBlockC = _t('_adm_pbuilder_RSS_Feed');
        $sPhpBlockC = _t('_adm_pbuilder_Code_Block');
        $sTextBlockC = _t('_adm_pbuilder_Text_Block');
        $sSpecialBlockC = _t('_adm_pbuilder_Special_Block');
        $sXmlPathC = _t('_adm_pbuilder_XML_path');
        $sUrlRssFeedC = _t('_adm_pbuilder_Url_of_RSS_feed');
        $sNumbRssItemsC = _t('_adm_pbuilder_Number_RSS_items');
        $sTypeC = _t('_Type');
        $sDescriptionC = _t('_Description');
        $sCaptionLangKeyC = _t('_adm_pbuilder_Caption_Lang_Key');
        $sDesignBoxLangKeyC = _t('_adm_pbuilder_DesignBox_Lang_Key');
        $sVisibleForC = _t('_adm_mbuilder_Visible_for');
        $sGuestC = _t('_Guest');
        $sMemberC = _t('_Member');

        $sQuery = "SELECT * FROM `{$this -> sDBTable}` WHERE `Page` = '{$this -> sPage_db}' AND `ID` = $iBlockID";
        $aItem = db_assoc_arr($sQuery);
        if(!$aItem)
            return MsgBox($sNoPropertiesC);

        $sPageName = htmlspecialchars($this->oPage->sName);

        $sBlockType = '';
        switch( $aItem['Func'] ) {
            case 'PFBlock': $sBlockType = $sProfileFieldsC; break;
            case 'Echo':    $sBlockType = $sHtmlBlockC; break;
            case 'Text':    $sBlockType = $sTextBlockC; break;
            case 'Code':    $sBlockType = $sPhpBlockC; break;
            case 'XML':     $sBlockType = $sXmlBlockC; break;
            case 'RSS':     $sBlockType = $sRssBlockC; break;
            default:        $sBlockType = $sSpecialBlockC; break;
        }

        $aVisibleValues = array();
        if(strpos($aItem['Visible'], 'non') !== false)
            $aVisibleValues[] = 'non';
        if(strpos( $aItem['Visible'], 'memb' ) !== false)
            $aVisibleValues[] = 'memb';

        $aForm = array(
            'form_attrs' => array(
                'name' => 'formItemEdit',
                'action' => bx_html_attribute($_SERVER['PHP_SELF']),
                'method' => 'post',
            ),
            'inputs' => array(
                'Page' => array(
                    'type' => 'hidden',
                    'name' => 'Page',
                    'value' => $sPageName,
                ),
                'id' => array(
                    'type' => 'hidden',
                    'name' => 'id',
                    'value' => $iBlockID,
                ),
                'action' => array(
                    'type' => 'hidden',
                    'name' => 'action',
                    'value' => 'saveItem',
                ),
                'Cache' => array(
                    'type' => 'hidden',
                    'name' => 'Cache',
                    'value' => (int)$aItem['Cache'],
                ),
                'header1' => array(
                    'type' => 'value',
                	'name' => 'header1',
                    'caption' => $sTypeC,
                	'value' => $sBlockType,
                ),
                'header2' => array(
                    'type' => 'value',
                	'name' => 'header2',
                    'caption' => $sDescriptionC,
                	'value' => $aItem['Desc'], 
                ),
                'Caption' => array(
                    'type' => 'text',
                    'name' => 'Caption',
                    'caption' => $sCaptionLangKeyC,
                    'value' => $aItem['Caption'],
                    'required' => true,
                ),
                'DesignBox' => array(
                	'type' => 'select',
                    'name' => 'DesignBox',
                	'caption' => $sDesignBoxLangKeyC,
                    'value' => $aItem['DesignBox'],
                	'values' => array(
                		array('key' => 2, 'value' => _t('_adm_pbuilder_DesignBox_2')),
                		array('key' => 0, 'value' => _t('_adm_pbuilder_DesignBox_0')),
                		array('key' => 11, 'value' => _t('_adm_pbuilder_DesignBox_11')),
                		array('key' => 1, 'value' => _t('_adm_pbuilder_DesignBox_1')),
                		array('key' => 13, 'value' => _t('_adm_pbuilder_DesignBox_13')),
                		array('key' => 3, 'value' => _t('_adm_pbuilder_DesignBox_3')),
                	),
                    'required' => true,
                ),
                'Visible' => array(
                    'type' => 'checkbox_set',
                    'caption' => $sVisibleForC,
                    'name' => 'Visible',
                    'value' => $aVisibleValues,
                    'values' => array(
                        'non' => $sGuestC,
                        'memb' => $sMemberC
                    )
                )
            ),
        );

        $sBlockContent = $aItem['Content'];
        //$sBlockContent = htmlspecialchars_adv( $aItem['Content'] );

        if( $aItem['Func'] == 'Echo' ) {
        	$sMceEditorKey = 'bx_mce_editor_disabled';
			$bMceEditor = !isset($_COOKIE[$sMceEditorKey]) || (int)$_COOKIE[$sMceEditorKey] != 1;

            $aForm['inputs']['Content'] = array(
                'type' => 'textarea',
                'html' => 2, // $bMceEditor ? 2 : 0,
                // 'html_toggle' => true,
                'dynamic' => true,
                'attrs' => array ('id' => 'form_input_html'.$iBlockID, 'style' => 'height:250px;'),
                'name' => 'Content',
                'value' => $sBlockContent,
                'colspan' => true,
            );
        } elseif( $aItem['Func'] == 'Text' || $aItem['Func'] == 'Code') {

            $aForm['inputs']['Content'] = array(
                'type' => 'textarea',
                'name' => 'Content',
                'value' => $sBlockContent,
                'colspan' => true,
            );

        } elseif( $aItem['Func'] == 'XML' ) {
            $aExistedApplications = BxDolService::call('open_social', 'get_admin_applications', array());

            $aForm['inputs']['Applications'] = array(
                'type' => 'select',
                'name' => 'application_id',
                'caption' => _t('_osi_Existed_applications'),
                'values' => $aExistedApplications
            );
        } elseif( $aItem['Func'] == 'RSS' ) {
            list( $sUrl, $iNum ) = explode( '#', $aItem['Content'] );
            $iNum = (int)$iNum;

            $aForm['inputs']['Url'] = array(
                'type' => 'text',
                'name' => 'Url',
                'caption' => $sUrlRssFeedC,
                'value' => $sUrl,
                'required' => true,
            );
            $aForm['inputs']['Num'] = array(
                'type' => 'text',
                'name' => 'Num',
                'caption' => $sNumbRssItemsC,
                'value' => $iNum,
                'required' => true,
            );
        }

        $aForm['inputs']['controls'] = array(
            'type' => 'input_set',
            array(
                'type' => 'submit',
                'name' => 'Save',
                'value' => _t('_Save')
            )
        );

        if ($aItem['Func'] == 'RSS' || $aItem['Func'] == 'Echo' || $aItem['Func'] == 'Text' || $aItem['Func'] == 'Code' || $aItem['Func'] == 'XML') {
            $aForm['inputs']['controls'][] = array(
                'type' => 'reset',
                'name' => 'Delete',
                'value' => _t('_Delete')
            );
        }

        $oZ = new BxDolAlerts('page_builder', 'block_form_display', $iBlockID, 0, array('form' => &$aForm));
        $oZ->alert();

        $sResult = '';
        $oForm = new BxTemplFormView($aForm);

        $sContent = '';
        $sContent .= $this->getCssCode();
        $sContent .= $oForm->getCode();

        $sContent = $GLOBALS['oAdmTemplate']->parseHtmlByName('design_box_content.html', array('content' => $sContent));
        return $GLOBALS['oFunctions']->popupBox('adm-pbuilder-properties', _t('_adm_pbuilder_Block'), $sContent);
    }

    function showNewPageForm()
    {
        $oForm = new BxTemplFormView(array(
            'form_attrs' => array(
                'name' => 'formItemEdit',
                'action' => bx_html_attribute($_SERVER['PHP_SELF']),
                'method' => 'post',
            ),
            'inputs' => array(
                array(
                    'type' => 'hidden',
                    'name' => 'action_sys',
                    'value' => 'createNewPage',
                ),
                array(
                    'type' => 'text',
                    'name' => 'uri',
                    'value' => 'newpage',
                    'caption' => _t('_Page URI'),
                    'info' => _t('_adm_pbuilder_uri_info', BX_DOL_URL_ROOT . 'page/newpage'),
                ),
                array(
                    'type' => 'text',
                    'name' => 'title',
                    'caption' => _t('_Page title'),
                    'value' => 'New Page',
                    'info' => _t('_adm_pbuilder_title_info'),
                ),
                array(
                    'type' => 'submit',
                    'name' => 'do_submit',
                    'value' => _t('_adm_btn_Create_page'),
                ),
            ),
        ));

        $sContent = '';
        $sContent .= $this->getCssCode();
        $sContent .= $oForm->getCode();

        $sContent = $GLOBALS['oAdmTemplate']->parseHtmlByName('design_box_content.html', array('content' => $sContent));
        return $GLOBALS['oFunctions']->popupBox('adm-pbuilder-add-page', _t('_adm_pbuilder_Create_new_page'), $sContent);
    }
}

class BxDolPVAPage
{
    var $sName;
    var $sName_db;
    var $oParent;
    var $aColsWidths     = array();
    var $aBlocks         = array();
    var $aBlocksOrder    = array();
    var $aBlocksInactive = array();
    var $aBlocksSamples  = array();
    var $aMinWidths      = array();
    var $iPageWidth;
    var $bResetable; //defines if the page can be reset
    var $sDefaultSqlFile; //file containing default setting for reset
    var $isSystem; // defines if the page is system or created by admin

    function __construct( $sPage, &$oParent )
    {
        global $admin_dir;

        $this -> sName   = $sPage;
        $this -> sName_db = addslashes( $this -> sName );

        /* @var $this->oParent BxDolPageViewAdmin */
        $this -> oParent = &$oParent;

        $this -> sDefaultSqlFile = BX_DIRECTORY_PATH_ROOT . "{$admin_dir}/default_builders/{$this -> oParent -> sDBTable}_{$this -> sName}.sql";
        $this -> bResetable = file_exists( $this -> sDefaultSqlFile );

        $this -> loadContent();
    }

    function loadContent()
    {
    	global $MySQL;

        $sQuery = "SELECT `System` FROM `{$this -> oParent -> sDBTable}_pages` WHERE `Name` = '{$this -> sName_db}'";
        $this->isSystem = (int)$MySQL->getOne($sQuery) == 1;

        //get page width
        $sQuery = "SELECT `PageWidth` FROM `{$this -> oParent -> sDBTable}` WHERE `Page` = '{$this -> sName_db}' LIMIT 1";
        $this->iPageWidth = $MySQL->getOne($sQuery);

        if(!$this->iPageWidth)
			$this->iPageWidth = '960px';

        //get columns widths
        $sQuery = "SELECT
                `Column`,
                `ColWidth`
            FROM `{$this -> oParent -> sDBTable}`
            WHERE
                `Page` = ? AND
                `Column` != 0
            GROUP BY `Column`
            ORDER BY `Column`";

        $aColumns = $MySQL->getAllWithKey($sQuery, 'Column', [$this -> sName_db]);
        
		ksort($aColumns);

        foreach($aColumns as $aColumn) {
            $iColumn = (int)$aColumn['Column'];
            $this -> aColsWidths[$iColumn] = (float)$aColumn['ColWidth'];
            $this -> aBlocks[$iColumn]     = array();
            $this -> aBlocksOrder[$iColumn]= array();

            //get active blocks
            $sQueryActive = "SELECT
                    `ID`,
                    `Caption`
                FROM `{$this -> oParent -> sDBTable}`
                WHERE
                    `Page` = ? AND
                    `Column` = ?
                ORDER BY `Order`";

            $aBlocks = $MySQL->getAll($sQueryActive, [$this -> sName_db, $iColumn]);
            foreach($aBlocks as $aBlock) {
                $this->aBlocks[$iColumn][$aBlock['ID']] = _t($aBlock['Caption']);
                $this->aBlocksOrder[$iColumn][] = $aBlock['ID'];
            }
        }

        // load minimal widths
        $sQuery = "SELECT `ID`, `MinWidth` FROM `{$this -> oParent -> sDBTable}` WHERE `MinWidth` > 0 AND `Page`= ?";
        $aBlocks = $MySQL->getAll($sQuery, [$this -> sName_db]);
        foreach($aBlocks as $aBlock)
            $this->aMinWidths[(int)$aBlock['ID']] = (int)$aBlock['MinWidth'];

        $this->loadInactiveBlocks();
    }

    function loadInactiveBlocks()
    {
        //get inactive blocks and samples
        $sQueryInactive = "
            SELECT
                `ID`,
                `Caption`
            FROM `{$this -> oParent -> sDBTable}`
            WHERE
                `Page` = '{$this -> sName_db}' AND
                `Column` = 0
        ";

        $sQuerySamples = "
            SELECT
                `ID`,
                `Caption`
            FROM `{$this -> oParent -> sDBTable}`
            WHERE
                `Func` = 'Sample'
        ";

        $rInactive = db_res( $sQueryInactive );
        $rSamples  = db_res( $sQuerySamples );

        while( $aBlock =  $rInactive ->fetch() )
            $this -> aBlocksInactive[ (string)$aBlock['ID'] . ' '] = _t( $aBlock['Caption'] );

        while( $aBlock =  $rSamples ->fetch() )
            $this -> aBlocksSamples[ (int)$aBlock['ID'] ] = _t( $aBlock['Caption'] );

        asort($this -> aBlocksInactive, SORT_STRING | SORT_FLAG_CASE);
    }

    function getJSON()
    {
        $oPVAPageJSON = new BxDolPVAPageJSON( $this );
        return json_encode($oPVAPageJSON);
    }

}

/* temporary JSON object */
class BxDolPVAPageJSON
{
    var $active;
    var $active_order;
    var $inactive;
    var $samples;
    var $widths;
    var $min_widths;

    function __construct( $oParent )
    {
        $this -> widths     = $oParent -> aColsWidths;
        $this -> min_widths = $oParent -> aMinWidths;
        $this -> active     = $oParent -> aBlocks;
        $this -> active_order = $oParent -> aBlocksOrder;
        $this -> inactive   = $oParent -> aBlocksInactive;
        $this -> samples    = $oParent -> aBlocksSamples;
    }
}

class BxDolPageViewCacher
{
    var $sCacheFile;
    var $oBlocksCacheObject;

    function __construct( $sDBTable, $sCacheFile )
    {
        $this -> sDBTable = $sDBTable;
        $this -> sCacheFile = $sCacheFile;
    }

    function createCache()
    {
    	global $MySQL;

        $oCacheBlocks = $this->getBlocksCacheObject ();
        $oCacheBlocks->removeAllByPrefix ('pb_');

        $sCacheString = "// cache of Page View composer\n\nreturn array(\n  //pages\n";

        //get pages

        $sQuery = "SELECT `Page` AS `Name` FROM `{$this -> sDBTable}` WHERE `Page` != '' GROUP BY `Page`";

        $rPages = db_res( $sQuery );

        while ($aPageN = $rPages->fetch()) {
            $sPageName  = addslashes($aPageN['Name']);
            $aPageN['Title'] = db_value("SELECT `Title` FROM `{$this -> sDBTable}_pages` WHERE `Name` = '$sPageName'");
            $sPageTitle = addslashes($aPageN['Title']);
            $sPageWidth = db_value("SELECT `PageWidth` FROM `{$this -> sDBTable}` WHERE `Page` = '$sPageName' LIMIT 1");
            $sPageWidth = empty($sPageWidth) ? '998px' : $sPageWidth;

            $sCacheString .= "  '$sPageName' => array(\n";
            $sCacheString .= "    'Title' => '$sPageTitle',\n";
            $sCacheString .= "    'Width' => '$sPageWidth',\n";
            $sCacheString .= "    'Columns' => array(\n";

            //get columns
            $sQuery = "SELECT
                    `Column`,
                    `ColWidth`
                FROM `{$this -> sDBTable}`
                WHERE
                    `Page` = ? AND
                    `Column` > 0
                GROUP BY `Column`
                ORDER BY `Column`";
            $aColumns = $MySQL->getAllWithKey($sQuery, 'Column', [$sPageName]);

			ksort($aColumns);

            foreach($aColumns as $aColumn) {
                $iColumn = $aColumn['Column'];
                $iColWidth  = $aColumn['ColWidth'];

                $sCacheString .= "      $iColumn => array(\n";
                $sCacheString .= "        'Width'  => $iColWidth,\n";
                $sCacheString .= "        'Blocks' => array(\n";

                //get blocks of column
                $sQuery = "SELECT
                        `ID`,
                        `Caption`,
                        `Func`,
                        `Content`,
                        `DesignBox`,
                        `Visible`,
                        `Cache`
                    FROM `{$this -> sDBTable}`
                    WHERE
                        `Page` = ? AND
                        `Column` = ?
                    ORDER BY `Order` ASC";
                $aBlocks = $MySQL->getAll($sQuery, [$sPageName, $iColumn]);

                foreach($aBlocks as $aBlock) {
                    $sCacheString .= "          {$aBlock['ID']} => array(\n";

                    $sCacheString .= "            'Func'      => '{$aBlock['Func']}',\n";
                    $sCacheString .= "            'Content'   => '" . $this -> addSlashes( $aBlock['Content'] ) . "',\n";
                    $sCacheString .= "            'Caption'   => '" . $this -> addSlashes( $aBlock['Caption'] ) . "',\n";
                    $sCacheString .= "            'Visible'   => '{$aBlock['Visible']}',\n";
                    $sCacheString .= "            'DesignBox' => {$aBlock['DesignBox']},\n";
                    $sCacheString .= "            'Cache'     => {$aBlock['Cache']}\n";

                    $sCacheString .= "          ),\n"; //close block
                }
                $sCacheString .= "        )\n"; //close blocks
                $sCacheString .= "      ),\n"; //close column
            }

            $sCacheString .= "    )\n"; //close columns
            $sCacheString .= "  ),\n"; //close page
        }

        $sCacheString .= ");\n"; //close main array

        $aResult = eval($sCacheString);

        $oCache = $MySQL->getDbCacheObject();
        $oCache->setData ($MySQL->genDbCacheKey($this -> sCacheFile), $aResult);

        return true;
    }

    function addSlashes( $sText )
    {
        $sText = str_replace( '\\', '\\\\', $sText );
        $sText = str_replace( '\'', '\\\'', $sText );

        return $sText;
    }

    function getBlocksCacheObject ()
    {
        if ($this->oBlocksCacheObject != null) {
            return $this->oBlocksCacheObject;
        } else {
            $sEngine = getParam('sys_pb_cache_engine');
            $this->oBlocksCacheObject = bx_instance ('BxDolCache'.$sEngine);
            if (!$this->oBlocksCacheObject->isAvailable())
                $this->oBlocksCacheObject = bx_instance ('BxDolCacheFile');
            return $this->oBlocksCacheObject;
        }
    }

    function genBlocksCacheKey ($sId)
    {
        global $site;
        return 'pb_' . $sId . '_' . md5($site['ver'] . $site['build'] . $site['url'] . getCurrentLangName(false) . $GLOBALS['oSysTemplate']->getCode()) . '.php';
    }
}
