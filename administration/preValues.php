<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( '../inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'admin_design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'prof.inc.php' );

$logged['admin'] = member_auth( 1, true, true );

$_page['extraCodeInHead'] = <<<EOJ
<script type="text/javascript" src="{$site['plugins']}jquery/jquery.js"></script>
EOJ;

$aFields = array(
    'Value'  => _t('_adm_pvalues_help_value'),
    'LKey'   => _t('_adm_pvalues_help_lkey'),
    'LKey2'  => _t('_adm_pvalues_help_lkey2'),
    'Extra'  => _t('_adm_pvalues_help_extra'),
);

if(bx_get('popup') !== false && (int)bx_get('popup') == 1) {
    $iAmInPopup = true;

    $iNameIndex = 17;
    $_page = array(
        'name_index' => $iNameIndex,
        'css_name' => array('predefined_values.css'),
        'js_name' => array(),
        'header' => _t('_adm_page_cpt_pvalues_manage'),
        'header_text' => _t('_adm_box_cpt_pvalues_manage'),
    );
    $_page_cont[$iNameIndex]['page_main_code'] = PageCompPageMainCode();
} else {
    $iAmInPopup = false;

    $iNameIndex = 0;
    $_page = array(
        'name_index' => $iNameIndex,
        'css_name' => array('forms_adv.css', 'predefined_values.css'),
        'js_name' => array(),
        'header' => _t('_adm_page_cpt_pvalues_manage'),
        'header_text' => _t('_adm_box_cpt_pvalues_manage'),
    );
    $_page_cont[$iNameIndex]['page_main_code'] = PageCompPageMainCode();
}

PageCodeAdmin();

function PageCompPageMainCode()
{
    global $iAmInPopup;
    global $aFields;

    $sPopupAdd = $iAmInPopup ? '&popup=1' : '';
    $sResultMsg = '';

    if( isset( $_POST['action'] ) and $_POST['action'] == 'Save' and isset( $_POST['PreList'] ) and is_array( $_POST['PreList'] ) ) {
        if (true === saveList( $_POST['list'], $_POST['PreList'] ))
            $sResultMsg = _t('_Success');
        else
            $sResultMsg = _t('_Failed to apply changes');
    }

    //get lists
    $aLists = array();
    $aKeys = getPreKeys();
    foreach ($aKeys as $aList)
        $aLists[ $aList['Key'] ] = $aList['Key'];

    $sListIn = bx_get('list');
    if ($sListIn !== false) {
        $sList_db = process_db_input($sListIn);
        $sList    = process_pass_data($sListIn);

        $iCount = getPreValuesCount($sListIn);
        if (!$iCount) //if no rows returned...
            $aLists[ $sList ] = $sList; //create new list
    } else {
        $sList = '';
    }

    ob_start();
    if ($sResultMsg)
        echo MsgBox($sResultMsg);
    ?>
    <script type="text/javascript">
        function createNewList()
        {
            var sNewList = prompt( '<?php echo bx_js_string(_t('_adm_pvalues_msg_enter_list_name')); ?>' );

            if( sNewList == null )
                return false;

            sNewList = $.trim( sNewList );

            if( !sNewList.length ) {
                alert( '<?php echo bx_js_string(_t('_adm_pvalues_msg_enter_correct_name')); ?>' );
                return false;
            }

            window.location = '<?=$GLOBALS['site']['url_admin'] . 'preValues.php'; ?>?list=' + encodeURIComponent( sNewList ) + '<?= $sPopupAdd ?>';
        }

        function addRow( eImg )
        {
            $( eImg ).parent().parent().before(
                '<tr>' +
                <?php
                foreach( $aFields as $sField => $sHelp ) {
                    ?>
                    '<td><input type="text" class="value_input" name="PreList[' + iNextInd + '][<?= $sField ?>]" value="" /></td>' +
                    <?php
                }
                ?>
                    '<th class="row_controls">' +
                    	'<a class="row_control bx-def-margin-thd-left-auto" href="javascript:void(0)" onclick="javascript:delRow(this);" title="<?php echo bx_html_attribute(_t('_Delete')); ?>"><i class="sys-icon times"></i></a>' +
                		'<a class="row_control bx-def-margin-thd-left-auto" href="javascript:void(0)" onclick="javascript:moveUpRow(this);" title="<?php echo bx_html_attribute(_t('_adm_pvalues_txt_move_up')); ?>"><i class="sys-icon arrow-up"></i></a>' +
                		'<a class="row_control bx-def-margin-thd-left-auto" href="javascript:void(0)" onclick="javascript:moveDownRow(this);" title="<?php echo bx_html_attribute(_t('_adm_pvalues_txt_move_down')); ?>"><i class="sys-icon arrow-down"></i></a>' +
                    '</th>' +
                '</tr>'
            );

            iNextInd ++;

            sortZebra();
        }

        function delRow( eImg )
        {
            $( eImg ).parent().parent().remove();
            sortZebra();
        }

        function moveUpRow( eImg )
        {
            var oCur = $( eImg ).parent().parent();
            var oPrev = oCur.prev( ':not(.headers)' );
            if( !oPrev.length )
                return;

            // swap elements values
            var oCurElems  = $('input', oCur.get(0));
            var oPrevElems = $('input', oPrev.get(0));

            oCurElems.each( function(iInd) {
                var oCurElem  = $( this );
                var oPrevElem = oPrevElems.filter( ':eq(' + iInd + ')' );

                // swap them
                var sCurValue = oCurElem.val();
                oCurElem.val( oPrevElem.val() );
                oPrevElem.val( sCurValue );
            } );
        }

        function moveDownRow( eImg )
        {
            var oCur = $( eImg ).parent().parent();
            var oPrev = oCur.next( ':not(.headers)' );
            if( !oPrev.length )
                return;

            // swap elements values
            var oCurElems  = $('input', oCur.get(0));
            var oPrevElems = $('input', oPrev.get(0));

            oCurElems.each( function(iInd) {
                var oCurElem  = $( this );
                var oPrevElem = oPrevElems.filter( ':eq(' + iInd + ')' );

                // swap them
                var sCurValue = oCurElem.val();
                oCurElem.val( oPrevElem.val() );
                oPrevElem.val( sCurValue );
            } );
        }

        function sortZebra()
        {
            $( '#listEdit tr:even' ).removeClass( 'even odd' ).addClass( 'even' );
            $( '#listEdit tr:odd'  ).removeClass( 'even odd' ).addClass( 'odd'  );
        }

        //just a design
        $( document ).ready( sortZebra );
    </script>

    <form action="<?=$GLOBALS['site']['url_admin'] . 'preValues.php'; ?>" method="post" enctype="multipart/form-data">
    	<div class="adm-pv-cp-selector bx-def-margin-bottom">
			<div class="adm-pv-cp-item">
				<span><?php echo _t('_adm_pvalues_txt_select_list'); ?>:</span>
				<div class="input_wrapper input_wrapper_select bx-def-margin-sec-leftright clearfix">
					<select class="form_input_select bx-def-font-inputs" name="list" onchange="if( this.value != '' ) window.location = '<?=$GLOBALS['site']['url_admin'] . 'preValues.php'; ?>' + '?list=' + encodeURIComponent( this.value ) + '<?= $sPopupAdd ?>';"><?= genListOptions( $aLists, $sList ) ?></select>
				</div>
			</div>
			<div class="input_wrapper input_wrapper_submit clearfix">
				<input  class="form_input_submit bx-btn" type="button" value="<?php echo bx_html_attribute(_t('_adm_pvalues_txt_create_new')); ?>" onclick="createNewList();" />
			</div>
		</div>
        <table class="bx-def-table" id="listEdit" cellpadding="0" cellspacing="1"><?php $iNextInd = $sList !== '' ? genListRows( $sList_db ) : 0; ?></table>
		<div class="adm-pv-submit bx-def-margin-top">
			<input type="hidden" name="popup" value="<?= $iAmInPopup ?>" />
			<input type="hidden" name="action" value="Save" />
			<div class="input_wrapper input_wrapper_submit clearfix">
				<input class="form_input_submit bx-btn" type="submit" name="submit" value="<?= bx_html_attribute(_t('_Save')) ?>" />
			</div>
		</div>
        <script type="text/javascript">
            iNextInd = <?= $iNextInd ?>;
        </script>
    </form>
    <?php
    return $GLOBALS['oAdmTemplate']->parseHtmlByName('design_box_content.html', array('content' => ob_get_clean()));
}

function genListOptions( $aLists, $sActive )
{
	asort($aLists);

    $sRet = '<option value="">' . _t('_Select') . '</option>';
    foreach( $aLists as $sKey => $sValue )
        $sRet .= '<option value="' . htmlspecialchars( $sKey ) . '"' . ( ( $sKey == $sActive ) ? ' selected="selected"' : '' ) . '>' . htmlspecialchars( $sValue ) . '</option>';

    return $sRet;
}

function genListRows( $sList )
{
    global $aFields;

    $aRows = getPreValues($sList);
    ?>
        <tr class="headers">
    <?php
    foreach( $aFields as $sField => $sHelp ) {
        ?>
            <th>
                <span class="tableLabel" onmouseover="showFloatDesc( '<?= bx_js_string($sHelp) ?>' );" onmousemove="moveFloatDesc( event );" onmouseout="hideFloatDesc();"><?= $sField ?></span>
            </th>
        <?php
    }
    ?>
            <th>&nbsp;</th>
        </tr>
	<?php
    $iCounter = 0;
    foreach ($aRows as $aRow) {
	?>
        <tr>
        <?php
        foreach( $aFields as $sField => $sHelp )
			echo '<td><input type="text" class="value_input" name="PreList[' . $iCounter . '][' . $sField . ']" value="' . htmlspecialchars($aRow[$sField]) . '" /></td>';
        ?>
            <th class="row_controls"><a class="row_control bx-def-margin-thd-left-auto" href="javascript:void(0)" onclick="javascript:delRow(this);" title="<?php echo bx_html_attribute(_t('_Delete')); ?>"><i class="sys-icon times"></i></a><a class="row_control bx-def-margin-thd-left-auto" href="javascript:void(0)" onclick="javascript:moveUpRow(this);" title="<?php echo bx_html_attribute(_t('_adm_pvalues_txt_move_up')); ?>"><i class="sys-icon arrow-up"></i></a><a class="row_control bx-def-margin-thd-left-auto" href="javascript:void(0)" onclick="javascript:moveDownRow(this);" title="<?php echo bx_html_attribute(_t('_adm_pvalues_txt_move_down')); ?>"><i class="sys-icon arrow-down"></i></a></th>
        </tr>
	<?php
		$iCounter ++;
    }
    ?>
        <tr class="headers">
            <td colspan="<?= count( $aFields ) ?>">&nbsp;</td>
            <th class="row_controls">
				<a class="row_control" href="javascript:void(0)" onclick="javascript:addRow(this);" title="<?php echo bx_html_attribute(_t('_adm_pvalues_txt_add_record')); ?>"><i class="sys-icon plus"></i></a>
            </th>
        </tr>
    <?php

    return $iCounter;
}

function saveList( $sList, $aData )
{
    global $aFields;
    global $iAmInPopup;

    $sList_db = trim( process_db_input( $sList ) );

    if( $sList_db == '' )
        return false;

    $sQuery = "DELETE FROM `" . BX_SYS_PRE_VALUES_TABLE . "` WHERE `Key` = '$sList_db'";

    db_res( $sQuery );

    $sValuesAlter = '';

    foreach( $aData as $iInd => $aRow ) {
        $aRow['Value'] = str_replace( ',', '', trim( $aRow['Value'] ) );

        if( $aRow['Value'] == '' )
            continue;

        $sValuesAlter .= "'" . process_db_input( $aRow['Value'] ) . "', ";

        $sInsFields = '';
        $sInsValues = '';
        foreach( $aFields as $sField => $sTemp ) {
            $sValue = trim( process_db_input( $aRow[$sField] ) );

            $sInsFields .= "`$sField`, ";
            $sInsValues .= "'$sValue', ";
        }

        $sInsFields = substr( $sInsFields, 0, -2 ); //remove ', '
        $sInsValues = substr( $sInsValues, 0, -2 );

        $sQuery = "INSERT INTO `" . BX_SYS_PRE_VALUES_TABLE . "` ( `Key`, $sInsFields, `Order` ) VALUES ( '$sList_db', $sInsValues, $iInd )";

        db_res( $sQuery );
    }

    //alter Profiles table
    $sValuesAlter = substr( $sValuesAlter, 0, -2 ); //remove ', '
    $sQuery = "SELECT `Name` FROM `sys_profile_fields` WHERE `Type` = 'select_set' AND `Values` = '#!{$sList_db}'";
    $rFields = db_res( $sQuery );
    while( $aField =  $rFields ->fetch() ) {
        $sField = $aField['Name'];

        $sQuery = "ALTER TABLE `Profiles` CHANGE `$sField` `$sField` set($sValuesAlter) NOT NULL default ''";
        db_res( $sQuery );
    }

    compilePreValues();

    if( $iAmInPopup )
        echo '<script type="text/javascript">window.close()</script>';

    return true;
}
