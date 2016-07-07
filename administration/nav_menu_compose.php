<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

/*
 * Page for displaying and editing profile fields.
 */

define ('BX_SECURITY_EXCEPTIONS', true);
$aBxSecurityExceptions = array ();
$aBxSecurityExceptions[] = 'POST.Link';
$aBxSecurityExceptions[] = 'REQUEST.Link';

require_once( '../inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'profiles.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'admin_design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'utils.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'languages.inc.php' );
bx_import('BxDolMenu');

// Check if administrator is logged in.  If not display login form.
$logged['admin'] = member_auth(1, true, true);

$GLOBALS['oAdmTemplate']->addJsTranslation(array(
    '_adm_mbuilder_Sorry_could_not_insert_object',
    '_adm_mbuilder_This_items_are_non_editable'
));

$oMenu = new BxDolMenu();

if(bx_get('action') !== false) {
    switch(bx_get('action')) {
        case 'edit_form':
            $id = (int)bx_get('id');

            header('Content-Type: text/html; charset=utf-8');

            $aItem = db_assoc_arr( "SELECT * FROM `sys_menu_top` WHERE `ID` = '{$id}'", 0 );
            if( $aItem )
                echo showEditForm( $aItem );
            else
                echoMenuEditMsg( _t('_Error occured'), 'red' );
        exit;
        case 'create_item':
            $newID = createNewElement($_POST['type'], (int)$_POST['source']);
            echo $newID;
        exit;
        case 'deactivate_item':
            $res = db_res( "UPDATE `sys_menu_top` SET `Active`=0 WHERE `ID`=" . (int)bx_get('id'));
            echo db_affected_rows($res);
            $oMenu -> compile();
        exit;
        case 'save_item':
            $id = (int)$_POST['id'];
            if(!$id)
                $aResult = array('code' => 1, 'message' => _t('_Error occured'));
            else {
                $aItemFields = array('Name', 'Caption', 'Link', 'Picture', 'Icon');
                $aItem = array();
                foreach( $aItemFields as $field )
                    $aItem[$field] = $_POST[$field];

                $aVis = array();
                if( (int)$_POST['Visible_non'] )
                    $aVis[] = 'non';
                if( (int)$_POST['Visible_memb'] )
                    $aVis[] = 'memb';

                $aItem['Visible'] = implode( ',', $aVis );
                $aItem['BQuickLink'] = (int)$_POST['BInQuickLink'] ? '1' : '0';
                $aItem['Target'] = $_POST['Target'] == '_blank' ? '_blank' : '';

                $aResult = saveItem( $id, $aItem );
            }

            $aResult['message'] = MsgBox($aResult['message']);

            echo json_encode($aResult);
        exit;
        case 'delete_item':
            $id = (int)$_POST['id'];
            if( !$id ) {
                echo _t('_adm_mbuilder_Item_ID_not_specified');
                exit;
            }

            $aItem = db_arr( "SELECT `Deletable` FROM `sys_menu_top` WHERE `ID` = '{$id}'" );
            if( !$aItem ) {
                echo _t('_adm_mbuilder_Item_not_found');
                exit;
            }

            if( !(int)$aItem['Deletable'] ) {
                echo _t('_adm_mbuilder_Item_is_non_deletable');
                exit;
            }

            $res = db_res( "DELETE FROM `sys_menu_top` WHERE `ID` = $id" );
            if( db_affected_rows($res) )
                echo 'OK';
            else
                echo _t('_adm_mbuilder_Could_not_delete_the_item');
            $oMenu -> compile();
        exit;
        case 'save_orders':
            saveOrders(bx_get('top'), bx_get('custom'));
            echo 'OK';
        exit;
    }
}

$sTopQuery = "SELECT `ID`, `Name`, `Movable` FROM `sys_menu_top` WHERE `Active`=1 AND `Type`='top' ORDER BY `Order`";
$rTopItems = db_res( $sTopQuery );

$sSysQuery = "SELECT `ID`, `Name`, `Movable` FROM `sys_menu_top` WHERE `Active`=1 AND `Type`='system' ORDER BY `Order`";
$rSysItems = db_res( $sSysQuery );

$sAllQuery = "SELECT `ID`, `Name` FROM `sys_menu_top` WHERE `Type`!='system' AND (`Clonable`='1' OR (`Clonable`='0' AND `Active`='0')) ORDER BY `Name`";
$rAllItems = db_res( $sAllQuery );

$sAdminUrl = BX_DOL_URL_ADMIN;

$sComposerInit = "
    <script type=\"text/javascript\">
    <!--
        topParentID = 'menu_app_wrapper';
        parserUrl = '" . $GLOBALS['site']['url_admin'] . "nav_menu_compose.php?';

        allowNewItem = true;
        allowAddToTop = true;
        allowAddToCustom = true;
        iInactivePerRow = 7;
        sendSystemOrder = false;

        aCoords = {};
        aCoords['startX'] = 6;
        aCoords['startY'] = 24;
        aCoords['width']  = 117;
        aCoords['height'] = 28;
        aCoords['diffX']  = 122;
        aCoords['diffY']  = 32;

        aTopItems = {};
        aCustomItems = {};
        aSystemItems = {};
        aAllItems = {};
";

$iIndex = 0;
while(($aTopItem = $rTopItems->fetch()) !== false) {
    $sComposerInit .= "

        aTopItems[$iIndex] = [{$aTopItem['ID']}, '" . bx_js_string( $aTopItem['Name'], BX_ESCAPE_STR_APOS ) . "', " . $aTopItem['Movable'] . "];
        aCustomItems[$iIndex] = {};";
    $sQuery = "SELECT `ID`, `Name`, `Movable` FROM `sys_menu_top` WHERE `Active`=1 AND `Type`='custom' AND `Parent`={$aTopItem['ID']} ORDER BY `Order`";

    $iSubIndex = 0;
    $rCustomItems = db_res( $sQuery );
    while(($aCustomItem = $rCustomItems->fetch()) !== false) {
        $sComposerInit .= "
        aCustomItems[$iIndex][" . ($iSubIndex++) . "] = [{$aCustomItem['ID']}, '" . bx_js_string( $aCustomItem['Name'], BX_ESCAPE_STR_APOS ) . "', " . $aCustomItem['Movable'] . "];";
    }

    $iIndex++;
}

while(($aSystemItem = $rSysItems->fetch()) !== false) {
    $sComposerInit .= "

        aSystemItems[$iIndex] = [{$aSystemItem['ID']}, '" . bx_js_string( $aSystemItem['Name'], BX_ESCAPE_STR_APOS ) . "', " . $aSystemItem['Movable'] . "];
        aCustomItems[$iIndex] = {};";
    $sQuery = "SELECT `ID`, `Name`, `Movable` FROM `sys_menu_top` WHERE `Active`=1 AND `Type`='custom' AND `Parent`={$aSystemItem['ID']} ORDER BY `Order`";

    $iSubIndex = 0;
    $rCustomItems = db_res( $sQuery );
    while(($aCustomItem = $rCustomItems->fetch()) !== false) {
        $sComposerInit .= "
        aCustomItems[$iIndex][" . ($iSubIndex++) . "] = [{$aCustomItem['ID']}, '" . bx_js_string( $aCustomItem['Name'], BX_ESCAPE_STR_APOS ) . "', " . $aCustomItem['Movable'] . "];";
    }

    $iIndex++;
}

$sComposerInit .= "\n";
while(($aAllItem = $rAllItems->fetch()) !== false) {
    $sComposerInit .= "
        aAllItems['{$aAllItem['ID']} '] = '" . bx_js_string( $aAllItem['Name'], BX_ESCAPE_STR_APOS ) . "';";
}
$sComposerInit .= "
    -->
    </script>";

$iNameIndex = 12;
$_page = array(
    'name_index' => $iNameIndex,
    'css_name' => array('menu_compose.css', 'forms_adv.css'),
    'js_name' => array('menu_compose.js', 'BxDolMenu.js'),
    'header' => _t('_adm_mbuilder_title')
);

$sContent = $GLOBALS['oAdmTemplate']->parseHtmlByName('menu_compose.html', array(
    'extra_js' => $sComposerInit
));

$_page_cont[$iNameIndex]['controls'] = null;
$_page_cont[$iNameIndex]['page_main_code'] = DesignBoxAdmin(_t('_adm_mbuilder_title_box'), $sContent);

PageCodeAdmin();

// Functions
function showEditForm($aItem)
{
    $aForm = array(
        'form_attrs' => array(
            'id' => 'formItemEdit',
            'name' => 'formItemEdit',
            'action' => $GLOBALS['site']['url_admin'] . 'nav_menu_compose.php',
            'method' => 'post',
            'enctype' => 'multipart/form-data',
        ),
        'inputs' => array (
            'Name' => array(
                'type' => 'text',
                'name' => 'Name',
                'caption' => _t('_adm_mbuilder_System_Name'),
                'value' => $aItem['Name'],
                'attrs' => array()
            ),
            'Caption' => array(
                'type' => 'text',
                'name' => 'Caption',
                'caption' => _t('_adm_mbuilder_Language_Key'),
                'value' => $aItem['Caption'],
                'attrs' => array()
            ),
            'LangCaption' => array(
                'type' => 'text',
                'name' => 'LangCaption',
                'caption' => _t('_adm_mbuilder_Default_Name'),
                'value' => _t( $aItem['Caption'] ),
                'attrs' => array()
            ),
            'Link' => array(
                'type' => 'text',
                'name' => 'Link',
                'caption' => _t('_URL'),
                'value' => $aItem['Link'],
                'attrs' => array()
            ),
            'Picture' => array(
                'type' => 'text',
                'name' => 'Picture',
                'caption' => _t('_Picture'),
                'value' => $aItem['Picture'],
                'attrs' => array()
            ),
            'Icon' => array(
                'type' => 'text',
                'name' => 'Icon',
                'caption' => _t('_adm_mbuilder_icon'),
                'value' => $aItem['Icon'],
                'attrs' => array()
            ),
            'BInQuickLink' => array(
                'type' => 'checkbox',
                'name' => 'BInQuickLink',
                'caption' => _t('_adm_mbuilder_Quick_Link'),
                'value' => 'on',
                'checked' => $aItem['BQuickLink'] != 0,
                'attrs' => array()
            ),
            'Target' => array(
                'type' => 'radio_set',
                'name' => 'Target',
                'caption' => _t('_adm_mbuilder_Target_Window'),
                'value' => $aItem['Target'] == '_blank' ? '_blank' : '_self',
                'values' => array(
                    '_self' => _t('_adm_mbuilder_Same'),
                    '_blank' => _t('_adm_mbuilder_New')
                ),
                'attrs' => array()
            ),
            'Visible' => array(
                'type' => 'checkbox_set',
                'name' => 'Visible',
                'caption' => _t('_adm_mbuilder_Visible_for'),
                'value' => array(),
                'values' => array(
                    'non' => _t('_Guest'),
                    'memb' => _t('_Member')
                ),
                'attrs' => array()
            ),
            'submit' => array(
                'type' => 'input_set',
                array(
                    'type' => 'button',
                    'name' => 'save',
                    'value' => _t('_Save Changes'),
                    'attrs' => array(
                        'onclick' => 'javascript:saveItem(' . $aItem['ID'] . ');'
                    )
                ),
                array(
                    'type' => 'button',
                    'name' => 'delete',
                    'value' => _t('_Delete'),
                    'attrs' => array(
                        'onclick' => 'javascript:deleteItem(' . $aItem['ID'] . ');'
                    )
                )
            ),
        )
    );

    foreach($aForm['inputs'] as $sKey => $aInput)
        if(in_array($aInput['type'], array('text', 'checkbox')) && !$aItem['Editable'])
            $aForm['inputs'][$sKey]['attrs']['disabled'] = "disabled";

    if(strpos($aItem['Visible'], 'non') !== false)
        $aForm['inputs']['Visible']['value'][] = 'non';
    if(strpos($aItem['Visible'], 'memb') !== false)
        $aForm['inputs']['Visible']['value'][] = 'memb';

    $oForm = new BxTemplFormView($aForm);
    return PopupBox('tmc_edit_popup', _t('_adm_mbuilder_edit_item'), $GLOBALS['oAdmTemplate']->parseHtmlByName('design_box_content.html', array('content' => $oForm->getCode() . LoadingBox('formItemEditLoading'))));
}

function createNewElement( $type, $source )
{
    global $oMenu;

    if( $source ) {
        $sourceActive = db_value( "SELECT `Active` FROM `sys_menu_top` WHERE `ID`='{$source}'" );
        if( !$sourceActive ) {
            //convert to active
            db_res( "UPDATE `sys_menu_top` SET `Active`=1, `Type`='{$type}' WHERE `ID`='{$source}'" );
            $newID = $source;
        } else {
            //create from source
            db_res( "INSERT INTO `sys_menu_top`
                        (`Name`, `Caption`, `Link`, `Visible`, `Target`, `Onclick`, `Check`, `Movable`, `Clonable`, `Editable`, `Deletable`, `Type`, `Picture`, `Icon`, `BQuickLink`, `Statistics`)
                    SELECT
                          `Name`, `Caption`, `Link`, `Visible`, `Target`, `Onclick`, `Check`, `Movable`, '0', `Editable`, '1', '{$type}', `Picture`, `Icon`, `BQuickLink`, `Statistics`
                    FROM `sys_menu_top`
                    WHERE `ID`='{$source}'" );
            $newID = db_last_id();
        }
    } else {
        //create new
        db_res( "INSERT INTO `sys_menu_top` ( `Name`, `Type` ) VALUES ( 'NEW ITEM', '{$type}' )" );
        $newID = db_last_id();
    }

    $oMenu -> compile();
    return $newID;
}

function echoMenuEditMsg( $text, $color = 'black' )
{
    echo <<<EOF
<div onclick="hideEditForm();" style="color:{$color};text-align:center;">{$text}</div>
<script type="text/javascript">setTimeout( 'hideEditForm();', 1000 )</script>
EOF;
}

function saveItem( $id, $aItem )
{
    global $oMenu;

    $sSavedC = _t('_Saved');
    $sItemNotFoundC = _t('_adm_mbuilder_Item_not_found');
    $sItemNonEditableC = _t('_adm_mbuilder_Item_is_non_editable');

    $aOldItem = db_arr( "SELECT * FROM `sys_menu_top` WHERE `ID`='{$id}'" );

    if(!$aOldItem)
        return array('code' => 2, 'message' => $sItemNotFoundC);

    if((int)$aOldItem['Editable'] != 1)
        return array('code' => 3, 'message' => $sItemNonEditableC);

    $sQuerySet = '';
    foreach( $aItem as $field => $value )
        $sQuerySet .= ", `{$field}`='" . process_db_input( $value ) ."'";

    $sQuerySet = substr( $sQuerySet, 1 );

    $sQuery = "UPDATE `sys_menu_top` SET {$sQuerySet} WHERE `ID` = '{$id}'";

    db_res( $sQuery );
    $oMenu -> compile();

    return array('code' => 0, 'message' => $sSavedC, 'timer' => 3);
}

function updateLangFile( $key, $string )
{
    if (!$key)
        return;

    $langName = getParam( 'lang_default' );
    $langID = db_value( "SELECT `ID` FROM `sys_localization_languages` WHERE `Name` = '" . process_db_input( $langName ) . "'" );

    $keyID = db_value( "SELECT `ID` FROM `sys_localization_keys` WHERE `Key` = '" . process_db_input( $key ) . "'" );
    if( $keyID ) {
        db_res( "UPDATE `sys_localization_strings` SET `String` = '" .process_db_input( $string ) . "' WHERE `IDKey`='{$keyID}' AND `IDLanguage`='{$langID}'" );
    } else {
        db_res( "INSERT INTO `sys_localization_keys` SET `IDCategory` = 2, `Key` = '" . process_db_input( $key ) . "'" );
        db_res( "INSERT INTO `sys_localization_strings` SET `IDKey` = " . db_last_id() . ", `IDLanguage` = '{$langID}', `String` = '" .process_db_input( $string ) . "'" );
    }

    compileLanguage($langID);
}

function saveOrders( $sTop, $aCustom )
{
    global $oMenu;

    db_res( "UPDATE `sys_menu_top` SET `Order` = 0, `Parent` = 0" );

    $sTop = trim( $sTop, ' ,' );
    $aTopIDs = explode( ',', $sTop );
    foreach( $aTopIDs as $iOrd => $iID ) {
        $iID = trim( $iID, ' ,' );
        $iID = (int)$iID;

        if( !$iID )
            continue;

        db_res( "UPDATE `sys_menu_top` SET `Order` = '{$iOrd}', `Type` = 'top' WHERE `ID` = '{$iID}'" );
    }

    foreach( $aCustom as $iParent => $sCustom ) {
        $iParent = (int)$iParent;
        $sCustom = trim( $sCustom, ' ,' );
        $aCustomIDs = explode( ',', $sCustom );
        foreach( $aCustomIDs as $iOrd => $iID ) {
            $iID = trim( $iID, ' ,' );
            $iID = (int)$iID;

            if( !$iID )
                continue;

            db_res( "UPDATE `sys_menu_top` SET `Order` = '{$iOrd}', `Type` = 'custom', `Parent`='{$iParent}' WHERE `ID` = '{$iID}'" );
        }
    }
    $oMenu -> compile();
}
