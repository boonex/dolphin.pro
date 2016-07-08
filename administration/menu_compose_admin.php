<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

/*
 * Page for displaying and editing profile fields.
 */
require_once( '../inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'profiles.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'admin_design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'utils.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'languages.inc.php' );

// Check if administrator is logged in.  If not display login form.
$logged['admin'] = member_auth( 1, true, true );

if(bx_get('action') !== false) {
    switch(bx_get('action')) {
        case 'edit_form':
            $id = (int)bx_get('id');

            if( $id < 1000 ) {
                $aItem = db_assoc_arr( "SELECT * FROM `sys_menu_admin` WHERE `id` = '{$id}'", 0 );
                if( $aItem )
                    echo showEditFormCustom( $aItem );
                else
                    echo echoMenuEditMsg( _t('_Error'), 'red' );
            } else {
                $id = $id - 1000;
                $aItem = db_assoc_arr( "SELECT * FROM `sys_menu_admin` WHERE `id` = '{$id}' AND `parent_id`='0'", 0 );
                if( $aItem )
                    echo showEditFormTop( $aItem );
                else
                    echo echoMenuEditMsg( _t('_Error'), 'red' );
            }
            exit;
        case 'create_item':
            $newID = createNewElement($_POST['type'], (int)$_POST['source']);
            echo $newID;
            exit;
        case 'deactivate_item':
            $id = (int)bx_get('id');
            if( $id > 1000 ) {
                $id = $id - 1000;
                $res = db_res( "DELETE FROM `sys_menu_admin` WHERE `id`='{$id}' AND `parent_id`='0'" );
                echo db_affected_rows($res);
            } else
                echo 1;
            exit;
        case 'save_item':
            $id = (int)$_POST['id'];
            if( !$id ) {
                $aResult = array('code' => 1, 'message' => _t('_Error occured'));
            } else {
                if( $id < 1000 ) {
                    $aItemFields = array( 'title', 'url', 'description', 'check', 'icon' );
                    $aItem = array();
                    foreach( $aItemFields as $field )
                        $aItem[$field] = $_POST[$field];
                } else {
                    $id = $id - 1000;
                    $aItemFields = array( 'title', 'icon', 'icon_large' );
                    $aItem = array();
                    foreach( $aItemFields as $field )
                        $aItem[$field] = $_POST[$field];
                }
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

            if( $id > 1000 ) {
                $id = $id - 1000;

                $res = db_res( "DELETE FROM `sys_menu_admin` WHERE `id` = '{$id}' AND `parent_id`='0'" );
            } else {
                $res = db_res( "DELETE FROM `sys_menu_admin` WHERE `id` = '{$id}'" );
            }

            if( db_affected_rows($res) )
                echo 'OK';
            else
                echo _t('_adm_mbuilder_Could_not_delete_the_item');
            exit;
        case 'save_orders':
            saveOrders(bx_get('top'), bx_get('custom'));
            echo 'OK';
            exit;
    }
}

$sTopQuery = "SELECT `id`, `title` FROM `sys_menu_admin` WHERE `parent_id`='0' ORDER BY `order`";
$rTopItems = db_res( $sTopQuery );

$sAllTopQuery = "SELECT * FROM (SELECT `id` + 1000 AS `id`, `title` FROM `sys_menu_admin` WHERE `parent_id`='0' UNION SELECT `id`, `title` FROM `sys_menu_admin`) AS `t`";
$aAllTopItems = $GLOBALS['MySQL']->getPairs($sAllTopQuery, 'id', 'title');

$sComposerInit = "
    <script type=\"text/javascript\">
        topParentID = 'menu_app_wrapper';
        parserUrl = '" . $GLOBALS['site']['url_admin'] . "menu_compose_admin.php?';

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
    $sTopestTitle = bx_js_string(_t($aTopItem['title']), BX_ESCAPE_STR_APOS);
    $sComposerInit .= "

        aTopItems[$iIndex] = [" . ($aTopItem['id'] + 1000) . ", '{$sTopestTitle}', 3];
        aCustomItems[$iIndex] = {};";
    $sQuery = "SELECT `id`, `title` FROM `sys_menu_admin` WHERE `parent_id`='{$aTopItem['id']}' ORDER BY `order`";

    $iSubIndex = 0;
    $rCustomItems = db_res( $sQuery );
    while(($aCustomItem = $rCustomItems->fetch()) !== false) {
        $sCustomTitle = bx_js_string(_t($aCustomItem['title']), BX_ESCAPE_STR_APOS);
        $sComposerInit .= "
        aCustomItems[$iIndex][" . ($iSubIndex++) . "] = [{$aCustomItem['id']}, '{$sCustomTitle}', 3];";
    }

    $iIndex++;
}

$sComposerInit .= "\n";

foreach ($aAllTopItems as $iId => $sLangKey)
    $aAllTopItems[$iId] = _t($sLangKey);

asort($aAllTopItems);

foreach ($aAllTopItems as $iId => $sTitle) {
    $sTopTitle = bx_js_string($sTitle, BX_ESCAPE_STR_APOS);
    $sComposerInit .= "
        aAllItems['{$iId} '] = '{$sTopTitle}';";
}

$sComposerInit .= "
    </script>
";

$iNameIndex = 12;
$_page = array(
    'name_index' => $iNameIndex,
    'css_name' => array('menu_compose.css', 'forms_adv.css'),
    'js_name' => array('menu_compose.js', 'BxDolMenu.js'),
    'header' => _t('_adm_ambuilder_title')
);

$sContent = $GLOBALS['oAdmTemplate']->parseHtmlByName('menu_compose.html', array(
    'extra_js' => $sComposerInit
));

$_page_cont[$iNameIndex]['page_main_code'] = DesignBoxAdmin(_t('_adm_ambuilder_title'), $sContent);

PageCodeAdmin();

// Functions
function showEditFormCustom( $aItem )
{
    $aForm = array(
        'form_attrs' => array(
            'id' => 'formItemEdit',
            'name' => 'formItemEdit',
            'action' => $GLOBALS['site']['url_admin'] . 'menu_compose_admin.php',
            'method' => 'post',
            'enctype' => 'multipart/form-data',
        ),
        'inputs' => array (
            'Title' => array(
                'type' => 'text',
                'name' => 'title',
                'caption' => _t('_Title'),
                'value' => $aItem['title'],
                'attrs' => array()
            ),
            'Url' => array(
                'type' => 'text',
                'name' => 'url',
                'caption' => _t('_URL'),
                'value' => $aItem['url'],
                'attrs' => array()
            ),
            'Check' => array(
                'type' => 'text',
                'name' => 'check',
                'caption' => _t('_adm_ambuilder_Check'),
                'value' => htmlspecialchars_adv( $aItem['check'] ),
                'attrs' => array()
            ),
            'Description' => array(
                'type' => 'text',
                'name' => 'description',
                'caption' => _t('_Description'),
                'value' => htmlspecialchars_adv( $aItem['description'] ),
                'attrs' => array()
            ),
            'Icon' => array(
                'type' => 'text',
                'name' => 'icon',
                'caption' => _t('_adm_ambuilder_Icon'),
                'value' => htmlspecialchars_adv( $aItem['icon'] ),
                'attrs' => array()
            ),
            'submit' => array(
                'type' => 'input_set',
                array(
                    'type' => 'button',
                    'name' => 'save',
                    'value' => _t('_Save Changes'),
                    'attrs' => array(
                        'onclick' => 'javascript:saveItem(' . $aItem['id'] . ');'
                    )
                ),
                array(
                    'type' => 'button',
                    'name' => 'delete',
                    'value' => _t('_Delete'),
                    'attrs' => array(
                        'onclick' => 'javascript:deleteItem(' . $aItem['id'] . ');'
                    )
                )
            ),
        )
    );

    $oForm = new BxTemplFormView($aForm);
    return PopupBox('amc_edit_popup_custom', _t('_adm_mbuilder_edit_item'), $GLOBALS['oAdmTemplate']->parseHtmlByName('design_box_content.html', array('content' => $oForm->getCode() . LoadingBox('formItemEditLoading'))));
}

function showEditFormTop( $aItem )
{
    $aForm = array(
        'form_attrs' => array(
            'id' => 'formItemEdit',
            'name' => 'formItemEdit',
            'action' => $GLOBALS['site']['url_admin'] . 'menu_compose_admin.php',
            'method' => 'post',
            'enctype' => 'multipart/form-data',
        ),
        'inputs' => array (
            'Title' => array(
                'type' => 'text',
                'name' => 'title',
                'caption' => _t('_Title'),
                'value' => $aItem['title'],
                'attrs' => array()
            ),
            'BigIcon' => array(
                'type' => 'text',
                'name' => 'icon',
                'caption' => _t('_adm_ambuilder_Small_Icon'),
                'value' => htmlspecialchars_adv( $aItem['icon'] ),
                'attrs' => array()
            ),
            'SmallIcon' => array(
                'type' => 'text',
                'name' => 'icon_large',
                'caption' => _t('_adm_ambuilder_Big_Icon'),
                'value' => htmlspecialchars_adv( $aItem['icon_large'] ),
                'attrs' => array()
            ),
            'submit' => array(
                'type' => 'input_set',
                array(
                    'type' => 'button',
                    'name' => 'save',
                    'value' => _t('_Save Changes'),
                    'attrs' => array(
                        'onclick' => 'javascript:saveItem(' . ($aItem['id'] + 1000) . ');'
                    )
                ),
                array(
                    'type' => 'button',
                    'name' => 'delete',
                    'value' => _t('_Delete'),
                    'attrs' => array(
                        'onclick' => 'javascript:deleteItem(' . ($aItem['id'] + 1000) . ');'
                    )
                )
            ),
        )
    );

    $oForm = new BxTemplFormView($aForm);
    return PopupBox('amc_edit_popup_top', _t('_adm_mbuilder_edit_item'), $GLOBALS['oAdmTemplate']->parseHtmlByName('design_box_content.html', array('content' => $oForm->getCode() . LoadingBox('formItemEditLoading'))));
}

function createNewElement( $type, $source )
{
    if( $source ) {
        if( $type == 'top' and $source > 1000 ) {
            $source = $source - 1000;

            db_res( "
                INSERT INTO `sys_menu_admin`
                    (`title`, `icon`, `icon_large`)
                SELECT
                      `title`, `icon`, `icon_large`
                FROM `sys_menu_admin`
                WHERE `id` = '{$source}'
            " );
            $newID = db_last_id();
        } elseif( $type == 'custom' and $source < 1000 ) {
            $aItem = db_res( "SELECT * FROM `sys_menu_admin` WHERE `id` = '{$source}'" );

            if( $aItem['parent_id'] == 0 )
                $newID = $source;
            else {
                db_res( "
                    INSERT INTO `sys_menu_admin`
                        (`title`, `url`, `description`, `check`, `icon`)
                    SELECT
                          `title`, `url`, `description`, `check`, `icon`
                    FROM `sys_menu_admin`
                    WHERE `id` = '{$source}'
                " );
                $newID = db_last_id();
            }
        } elseif( $type == 'custom' and $source > 1000 ) {
            $source = $source - 1000;

            db_res( "
                INSERT INTO `sys_menu_admin`
                    (`title`)
                SELECT
                      `title`
                FROM `sys_menu_admin`
                WHERE `id` = '{$source}'
            " );
            $newID = db_last_id();
        } elseif( $type == 'top' and $source < 1000 ) {
            db_res( "
                INSERT INTO `sys_menu_admin`
                    (`title`)
                SELECT
                      `title`
                FROM `sys_menu_admin`
                WHERE `id` = '{$source}'
            " );
            $newID = db_last_id();
        }
    } else {
        db_res( "INSERT INTO `sys_menu_admin` SET `title` = 'NEW ITEM'" );
        $newID = db_last_id();
    }
    return $newID;
}

function echoMenuEditMsg( $text, $color = 'black' )
{
    return <<<EOF
<div onclick="hideEditForm();" style="color:{$color};text-align:center;">{$text}</div>
EOF;
}

function saveItem( $id, $aItem )
{
    $sSavedC = _t('_Saved');

    $aOldItem = db_arr( "SELECT * FROM `sys_menu_admin` WHERE `id`='{$id}'" );

    if( !$aOldItem )
        return array('code' => 2, 'message' => _t('_Error') . ' ' . _t('_adm_mbuilder_Item_not_found'));

    $sQuerySet = '';
    foreach( $aItem as $field => $value )
        $sQuerySet .= ", `{$field}`='" . process_db_input( $value ) ."'";

    $sQuerySet = substr( $sQuerySet, 1 );

    $sQuery = "UPDATE `sys_menu_admin` SET {$sQuerySet} WHERE `id` = '{$id}'";
    db_res( $sQuery );

    return array('code' => 0, 'message' => $sSavedC, 'timer' => 3);
}

function saveOrders( $sTop, $aCustom )
{
    db_res( "UPDATE `sys_menu_admin` SET `order` = 0, `parent_id` = 0" );

    $sTop = trim( $sTop, ' ,' );
    $aTopIDs = explode( ',', $sTop );
    foreach( $aTopIDs as $iOrd => $iID ) {
        $iID = trim( $iID, ' ,' );
        $iID = (int)$iID;

        if( !$iID )
            continue;

        $iID = $iID - 1000;

        db_res( "UPDATE `sys_menu_admin` SET `order` = {$iOrd} WHERE `id` = '{$iID}'" );
    }

    foreach( $aCustom as $iParent => $sCustom ) {
        $iParent = (int)$iParent;
        $iParent = $iParent - 1000;

        $sCustom = trim( $sCustom, ' ,' );
        $aCustomIDs = explode( ',', $sCustom );

        foreach( $aCustomIDs as $iOrd => $iID ) {
            $iID = trim( $iID, ' ,' );
            $iID = (int)$iID;

            if( !$iID )
                continue;

			if($iID > 1000)
				$iID -= 1000;

            db_res( "UPDATE `sys_menu_admin` SET `order` = '{$iOrd}', `parent_id`='{$iParent}' WHERE `id` = '{$iID}'" );
        }
    }
}
