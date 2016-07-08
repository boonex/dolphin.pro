<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
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

bx_import('BxDolMemberMenu');

 // Check if administrator is logged in.  If not display login form.
$logged['admin'] = member_auth(1, true, true);

$GLOBALS['oAdmTemplate']->addJsTranslation(array(
    '_adm_mbuilder_Sorry_could_not_insert_object',
    '_adm_mbuilder_This_items_are_non_editable'
));

$oMemeberMenu = new BxDolMemberMenu();

$aMenuSection = array (
    'top' => array ('title' => '_top', 'href' => 'member_menu_compose.php?menu_position=top'),
    'top_extra' => array ('title' => '_top_extra', 'href' => 'member_menu_compose.php?menu_position=top_extra'),
);

$sResponce = null;

// top is default position ;
$sMenuSection = 'top';

if (isset($_GET['menu_position'])) {
    foreach ($aMenuSection AS $sValue => $a) {
        if ($sValue == $_GET['menu_position']) {
            $sMenuSection = $sValue;
            break;
        }
    }
}
$aMenuSection[$sMenuSection]['active'] = 1;

// ** FOR 'AJAX' REQUESTS ;
if(bx_get('action') !== false) {
    switch(bx_get('action')) {
        case 'edit_form':
            $id = (int)bx_get('id');

            header('Content-Type: text/html; charset=utf-8');

            $aItem = db_assoc_arr( "SELECT * FROM `sys_menu_member` WHERE `ID` = $id", 0 );
            $sResponce = ($aItem) ? showEditForm($aItem, $sMenuSection) : echoMenuEditMsg('Error', 'red');
            break;
        case 'create_item':
            $newID = createNewElement($_POST['type'], (int)$_POST['source'], $sMenuSection);
            $sResponce =  $newID;
            break;
        case 'deactivate_item':
            $res = db_res( "UPDATE `sys_menu_member` SET `Active`='0' WHERE `ID`=" . (int)bx_get('id') );
            $sResponce =  db_affected_rows($res);
            break;
        case 'save_item':
            $id = (int)$_POST['id'];
            if( !$id ) {
                $sResponce = echoMenuEditMsg( 'Error', 'red' );
            } else {
                $aItemFields = array( 'Name', 'Caption', 'Link', 'Target', 'Icon', 'Script' );
                $aItem = array();

                foreach( $aItemFields as $field ) {
                    $aItem[$field] = ( isset($_POST[$field]) ) ? $_POST[$field] : null ;
                }

                if ( !$aItem['Icon'] ) {
                    $aItem['Icon'] = 'member_menu_default.png';
                }

                $res = saveItem( $id, $aItem, $sMenuSection );
                updateLangFile( $_POST['Caption'], $_POST['LangCaption'] );

                $res['message'] = MsgBox($res['message']);
                echo json_encode($res);
                exit;
            }
            break;

        case 'delete_item':
            $id = (int)$_POST['id'];
            if( !$id ) {
                $sResponce = 'Item ID is not specified';
            } else {
                $aItem = db_arr( "SELECT `Deletable` FROM `sys_menu_member` WHERE `ID` = $id" );
                if( !$aItem ) {
                    $sResponce = 'Item not found';
                } else if( !(int)$aItem['Deletable'] ) {
                    $sResponce = 'Item is non-deletable';
                } else {
                    $res = db_res( "DELETE FROM `sys_menu_member` WHERE `ID` = $id" );
                    $sResponce = ( db_affected_rows($res) ) ? 'OK' : 'Couldn\'t delete the item';
                }
            }
            break;

        case 'save_orders':
            saveOrders( bx_get('top'), bx_get('custom'), $sMenuSection );
            $sResponce = 'OK';
            break;
    }

    // return script's response and recompile the menu cache ;
    $oMemeberMenu -> createMemberMenuCache();
    echo $sResponce;
    exit;
}

// generate all active menu items ;

$sTopQuery = "SELECT `ID`, `Name`, `Movable` FROM `sys_menu_member`	WHERE `Active`='1' AND `Position`='{$sMenuSection}' AND `Type`<>'linked_item' ORDER BY `Order`";
$rTopItems = db_res($sTopQuery);

$sAllQuery = "SELECT `ID`, `Name` FROM `sys_menu_member` WHERE `Type`<>'linked_item' AND (`Clonable`='1' OR (`Clonable`='0' AND `Active`='0')) ORDER BY `Name`";

$rAllItems = db_res( $sAllQuery );

$sComposerInit = "
    <script type=\"text/javascript\">
        topParentID = 'menu_app_wrapper';
        parserUrl = '" . $GLOBALS['site']['url_admin'] . "member_menu_compose.php?menu_position={$sMenuSection}';

        allowNewItem = true;
        allowAddToTop = true;
        allowAddToCustom = false;
        iInactivePerRow = 5;
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

        aTopItems[$iIndex] = [{$aTopItem['ID']}, '" . bx_js_string( $aTopItem['Name'], BX_ESCAPE_STR_APOS ) . "', {$aTopItem['Movable']}];
        aCustomItems[$iIndex] = {};";

    $iIndex++;
}

$sComposerInit .= "\n";
while(($aAllItem = $rAllItems->fetch()) !== false) {
    $sComposerInit .= "
        aAllItems['{$aAllItem['ID']} '] = '" . bx_js_string( $aAllItem['Name'], BX_ESCAPE_STR_APOS ) . "';";
}
    $sComposerInit .= "
    </script>
";

$iNameIndex = 12;
$_page = array(
    'name_index' => $iNameIndex,
    'css_name' => array('menu_compose.css', 'forms_adv.css'),
    'js_name' => array('menu_compose.js', 'BxDolMenu.js'),
    'header' => _t('_mmbuilder_page_title')
);

$sContent = $GLOBALS['oAdmTemplate']->parseHtmlByName('menu_compose.html', array(
    'extra_js' => $sComposerInit
));

$_page_cont[$iNameIndex]['page_main_code'] = DesignBoxAdmin(_t('_mmbuilder_box_title'), $sContent, $aMenuSection);


PageCodeAdmin();


function showEditForm( $aItem, $sMenuSection )
{
    $aForm = array(
        'form_attrs' => array(
            'id' => 'formItemEdit',
            'name' => 'formItemEdit',
            'action' => $GLOBALS['site']['url_admin'] . 'member_menu_compose.php',
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
                'value' => htmlspecialchars_adv( $aItem['Link'] ),
                'attrs' => array()
            ),
            'Script' => array(
                'type' => 'text',
                'name' => 'Script',
                'caption' => _t('_adm_mbuilder_script'),
                'value' => htmlspecialchars_adv( $aItem['Script'] ),
                'attrs' => array()
            ),
            'Icon' => array(
                'type' => 'text',
                'name' => 'Icon',
                'caption' => _t('_adm_mbuilder_icon'),
                'value' => htmlspecialchars_adv( $aItem['Icon'] ),
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
            'submit' => array(
                'type' => 'input_set',
                array(
                    'type' => 'button',
                    'name' => 'save',
                    'value' => _t('_Save Changes'), //if( $aItem['Editable'] )
                    'attrs' => array(
                        'onclick' => 'javascript:saveItem(' . $aItem['ID'] . ');'
                    )
                ),
                array(
                    'type' => 'button',
                    'name' => 'delete',
                    'value' => _t('_Delete'), //if( $aItem['Deletable'] )
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
    return PopupBox('mmc_edit_popup', _t('_adm_mbuilder_edit_item')
            , $GLOBALS['oAdmTemplate']->parseHtmlByName('design_box_content.html'
            , array('content' => $oForm->getCode() . LoadingBox('formItemEditLoading'))));
}

/**
 * @description : function will create new menu items ;
 * @param 		: $type (string) - type of ellement ;
 * @param 		: $source (integer) - menu's ID;
 * @return 		: ID of created menu item ;
*/
function createNewElement( $type, $source, $sMenuSection = 'top' )
{
    global $oMenu;

    if( $source ) {
        $sourceActive = db_value( "SELECT `Active` FROM `sys_menu_member` WHERE `ID`=$source" );
        if( !$sourceActive ) {
            //convert to active
            db_res( "UPDATE `sys_menu_member` SET `Active`='1', `Position`='$type' WHERE `ID`=$source" );
            $newID = $source;
        } else {
            //create from source
            db_res( "INSERT INTO `sys_menu_member`
                        (`Caption`, `Name`, `Icon`, `Link`, `Script`, `Eval`, `PopupMenu`, `Movable`, `Clonable`, `Editable`, `Deletable`, `Target`, `Position`, `Type`, `Bubble`, `Description`)
                    SELECT `Caption`, `Name`, `Icon`, `Link`, `Script`, `Eval`, `PopupMenu`, `Movable`, '0', `Editable`, '1', `Target`, `Position`, `Type`, `Bubble`, `Description`
                    FROM `sys_menu_member`
                    WHERE `ID`=$source" );
            $newID = db_last_id();
        }
    } else {
        //create new
        db_res( "INSERT INTO `sys_menu_member` ( `Name`, `Position` ) VALUES ( 'NEW ITEM', '$type' )" );
        $newID = db_last_id();
    }

    return $newID;
}

function echoMenuEditMsg( $text, $color = 'black' )
{
    return <<<HTML
        <div style="color:{$color};text-align:center;">{$text}</div>
HTML;
}

/**
 * @description : function will save all changes into menu items ;
 * @param		: $id (integer) - ID of menu items ;
 * @param		: $aItem (array) - all needed POST variables ;
 * @param		: $sMenuSection (string) - position of menu ;
 * @return		: Html presentation data (Answer code);
*/
function saveItem( $id, $aItem, $sMenuSection )
{
    global $oMenu, $sMenuSection, $oMemeberMenu;

    $aOldItem = db_arr( "SELECT * FROM `sys_menu_member` WHERE `ID` = $id" );

    if(!$aOldItem) {
        return array( 'code' => 2, 'message' => _t('_adm_mbuilder_Item_not_found') );
    }

    if( (int) $aOldItem['Editable'] != 1 ) {
        return array('code' => 3, 'message' => _t('_adm_mbuilder_Item_is_non_editable') );
    }

    $sQuerySet = '';
    foreach( $aItem as $field => $value )
        $sQuerySet .= ", `$field`='" . process_db_input( $value ) ."'";

    $sQuerySet = substr( $sQuerySet, 1 );

    $sQuery = "UPDATE `sys_menu_member` SET $sQuerySet WHERE `ID` = $id";
    db_res( $sQuery );

    // return script's response and recompile the menu cache ;
    $oMemeberMenu -> createMemberMenuCache();

    return array('code' => 0, 'message' => _t('_Saved'), 'timer' => 3);
}

function updateLangFile( $key, $string )
{
    // clear from special chars ;
    $key = preg_replace( '|\{([^\}]+)\}|', '', $key);

    if (!$key)
        return;

    $langName = getParam( 'lang_default' );
    $langID = db_value( "SELECT `ID` FROM `sys_localization_languages` WHERE `Name` = '" . process_db_input( $langName ) . "'" );

    $keyID = db_value( "SELECT `ID` FROM `sys_localization_keys` WHERE `Key` = '" . process_db_input( $key ) . "'" );
    if( $keyID ) {
        db_res( "UPDATE `sys_localization_strings` SET `String` = '" .process_db_input( $string ) . "' WHERE `IDKey`=$keyID AND `IDLanguage`=$langID" );
    } else {
        db_res( "INSERT INTO `sys_localization_keys` SET `IDCategory` = 2, `Key` = '" . process_db_input( $key ) . "'" );
        db_res( "INSERT INTO `sys_localization_strings` SET `IDKey` = " . db_last_id() . ", `IDLanguage` = $langID, `String` = '" .process_db_input( $string ) . "'" );
    }

    compileLanguage($langID);
}

/**
 * @description : function will save menu orders ;
 * @param		: $sTop ( string ) - current menu ellement ;
 * @param		: $aCustom ( array ) - all mrnu items ;
 * @param		: $sMenuSection (string) - position of menu ;
*/
function saveOrders( $sTop, $aCustom, $sMenuSection )
{
    db_res( "UPDATE `sys_menu_member` SET `Order` = 0 WHERE `Position` = '{$sMenuSection}' " );

    $sTop = trim( $sTop, ' ,' );
    $aTopIDs = explode( ',', $sTop );

    foreach( $aTopIDs as $iOrd => $iID ) {
        $iID = trim( $iID, ' ,' );
        $iID = (int)$iID;

        if( !$iID )
            continue;

        db_res( "UPDATE `sys_menu_member` SET `Order` = $iOrd, `Position` = '{$sMenuSection}'  WHERE `ID` = $iID" );
    }
}
