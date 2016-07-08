<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

define ('BX_SECURITY_EXCEPTIONS', true);
$aBxSecurityExceptions = array ();
$aBxSecurityExceptions[] = 'POST.Check';
$aBxSecurityExceptions[] = 'REQUEST.Check';
$aBxSecurityExceptions[] = 'POST.Values';
$aBxSecurityExceptions[] = 'REQUEST.Values';

require_once( '../inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'db.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolPFM.php' );

send_headers_page_changed();

$logged['admin'] = member_auth( 1, true, true );

$sAction = bx_get('action');
switch(true) {
    case 'getArea' == $sAction:
        genAreaJSON((int)bx_get('id'));
        break;
    case 'createNewBlock' == $sAction:
        createNewBlock();
        break;
    case 'createNewItem' == $sAction:
        createNewItem();
        break;
    case 'savePositions' == $sAction:
        savePositions((int)bx_get('id'));
        break;
    case 'loadEditForm' == $sAction:
    	header('Content-Type: text/html; charset=utf-8');
        showEditForm((int)bx_get('id'), (int)bx_get('area'));
        break;
    case 'dummy' == $sAction:
        echo 'Dummy!';
        break;
    case true == bx_get('action-save'):
    case 'Save' == $sAction:
        saveItem((int)bx_get('area'), $_POST);
        break;
    case true == bx_get('action-delete'):
    case 'Delete' == $sAction:
        deleteItem((int)bx_get('id'), (int)bx_get('area'));
        break;
}

function createNewBlock()
{
    $oFields = new BxDolPFM( 1 );
    $iNewID = $oFields -> createNewBlock();
    header('Content-Type:text/javascript');
    echo '{"id":' . $iNewID . '}';
}

function createNewItem()
{
    $oFields = new BxDolPFM( 1 );
    $iNewID = $oFields -> createNewField();

    header('Content-Type:text/javascript');
    echo '{"id":' . $iNewID . '}';
}

function genAreaJSON( $iAreaID )
{
    $oFields = new BxDolPFM( $iAreaID );

    header('Content-Type:text/javascript; charset=utf-8');
    echo $oFields -> genJSON();
}

function savePositions( $iAreaID )
{
    $oFields = new BxDolPFM( $iAreaID );

    header( 'Content-Type:text/javascript' );
    $oFields -> savePositions( $_POST );

    $oCacher = new BxDolPFMCacher();
    $oCacher -> createCache();
}

function saveItem( $iAreaID, $aData )
{
    $oFields = new BxDolPFM( $iAreaID );
    $oFields -> saveItem( $_POST );

    $oCacher = new BxDolPFMCacher();
    $oCacher -> createCache();
}

function deleteItem( $iItemID, $iAreaID )
{
    $oFields = new BxDolPFM( $iAreaID );
    $oFields -> deleteItem( $iItemID );

    $oCacher = new BxDolPFMCacher();
    $oCacher -> createCache();
}

function showEditForm( $iItemID, $iAreaID )
{
    $oFields = new BxDolPFM( $iAreaID );

    ob_start();
    ?>
    <form name="fieldEditForm" method="post" action="<?=$GLOBALS['site']['url_admin'] . 'fields.parse.php'; ?>" target="fieldFormSubmit" onsubmit="clearFormErrors( this )">
        <div class="edit_item_table_cont">
            <?=$oFields -> genFieldEditForm( $iItemID ); ?>
        </div>
    </form>

    <iframe name="fieldFormSubmit" style="display:none;"></iframe>
    <?php
    $sResult = $GLOBALS['oAdmTemplate']->parseHtmlByName('design_box_content.html', array('content' => ob_get_clean()));

    echo PopupBox('pf_edit_popup', _t('_adm_fields_box_cpt_field'), $sResult);
}
