<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( '../inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'admin_design.inc.php' );
bx_import('BxDolDatabaseBackup');
bx_import('BxTemplFormView');

$logged['admin'] = member_auth(1, true, true);
set_time_limit(36000);

$aEnabledDbAction = array(
	'perform' => 1,
);
$oZ = new BxDolAlerts('system', 'admin_db_backup_actions', 0, 0, array(
	'actions' => &$aEnabledDbAction
));
$oZ->alert();

$sStatusText = $sStatusTextRestore = '';
if(isset($aEnabledDbAction['perform']))
	list($sStatusText, $sStatusTextRestore) = getActionResultBlock();

$sRestoreC = _t('_adm_dbtools_Database_restore');

$sTablesBackupToolsBlock = getTablesBackupTools($sStatusText);
$sDatabaseBackupToolsBlock = DatabaseBackupTools($sStatusText);
$sDatabaseRestoreBlock = getDatabaseRestoreBlock($sStatusText);

$sBoxContent = <<<EOF
<style>
    div.hidden {
        display:none;
    }
</style>
<script type="text/javascript">
    <!--
    function switchAdmPage(oLink)
    {
        var sType = jQuery(oLink).attr('id').replace('main_menu', '');
        var sName = '#page' + sType;

        jQuery(oLink).parent('.notActive').hide().siblings('.notActive:hidden').show().siblings('.active').hide().siblings('#' + jQuery(oLink).attr('id') + '-act').show();
        jQuery(sName).siblings('div:visible').bx_anim('hide', 'fade', 'slow', function(){
            jQuery(sName).bx_anim('show', 'fade', 'slow');
        });

        return false;
    }
    -->
</script>

<div class="boxContent" id="adm_pages">
    <div id="page1" class="visible">{$sTablesBackupToolsBlock}</div>
    <div id="page2" class="hidden">{$sDatabaseBackupToolsBlock}</div>
</div>
EOF;

$aTopItems = array(
    'main_menu1' => array('href' => 'javascript:void(0)', 'onclick' => 'javascript:switchAdmPage(this)', 'title' => _t('_adm_dbtools_Tables_backup_tools'), 'active' => 1),
    'main_menu2' => array('href' => 'javascript:void(0)', 'onclick' => 'javascript:switchAdmPage(this)', 'title' => _t('_adm_dbtools_Database_backup_tools'), 'active' => 0)
);

$sResult = DesignBoxAdmin(_t('_adm_dbtools_title'), $sStatusText . $sBoxContent, $aTopItems);
$sResult .= DesignBoxAdmin(_t('_adm_dbtools_Database_restore'), $sStatusTextRestore . $sDatabaseRestoreBlock);
$sResult .= adm_hosting_promo();

$iNameIndex = 15;
$_page = array(
    'name_index' => $iNameIndex,
    'css_name' => array('forms_adv.css'),
    'header' => _t('_adm_dbtools_title'),
);

$_page_cont[$iNameIndex]['page_main_code'] = $sResult;

$oZ = new BxDolAlerts('system', 'admin_db_backup_page', 0, 0, array(
	'page_vars' => &$_page,
	'page_cont' => &$_page_cont,
));

$oZ->alert();

PageCodeAdmin();

// Functions
function getActionResultBlock()
{
    $sSuccDumpedIntoFileC = _t('_adm_dbtools_succ_dumped_into_file');
    $sDumpFileSuccDeletedC = _t('_adm_dbtools_Dump_file_succefully_deleted');
    $sPleaseSelectDumpFileC = _t('_adm_dbtools_Please_select_dump_file');
    $sDateRestoredFromDumpC = _t('_adm_dbtools_Data_succefully_restored_from_dump');
    $sPleaseSelectCorrectDumpFileC = _t('_adm_dbtools_Please_select_correct_dump_file');
    $sDateRestoredFromPcC = _t('_adm_dbtools_Data_succefully_restored_from_PC');

    $status_text = $status_text_restore = '';

    if (isset($_POST['TablesBackup'])) { ##Block of table backup create
        //echo "For: Tables Tools". $_POST['tbl_op'] . ' Table - ' . $_POST['tbl'] . ' Show param - ' . $_POST['savetype'] ;

        $OutPutType  = preg_replace("/[^0-9]/", '', $_POST['tbl_op']);
        $oNewBackup = new BxDolDatabaseBackup();
        $oNewBackup -> _getTableStruct($_POST['tbl'],  $OutPutType);

        if ($_POST['savetype'] == 'client') {
            $sqlfile = date("Y-m-d_H:i:s").'_'.$_POST['tbl'].'.sql';
            header("Content-Type: text/plain");
            header("Content-Disposition: attachment;filename=\"".$sqlfile."\"");
            echo $oNewBackup -> sInputs;
            exit();
        }
        if ($_POST['savetype'] == 'server') {
            $sqlfile = BX_DIRECTORY_PATH_ROOT . 'backup/'.date("Y-m-d_H-i-s").'_'.$_POST['tbl'].'.sql';
            $file = fopen($sqlfile, 'w');
            fputs($file, $oNewBackup -> sInputs);
            $status_text .= "<hr size=1 /><font color='green'><center>{$sSuccDumpedIntoFileC} <b>{$sqlfile}</b></center></font>\n";
            fclose($file);
        }
        if ($_POST['savetype'] == 'show') {
            $status_text = "<center><textarea cols='100' rows='30' name='content' style='font-family: Arial; font-size: 11px' readonly='readonly'>" . $oNewBackup -> sInputs ."</textarea></center>";
        }
    }

    if (isset($_POST['DatabasesBackup'])) {
        $OutPutType  = preg_replace("/[^0-9]/", '', $_POST['db_op']);
        $oNewBackup = new BxDolDatabaseBackup();
        $oNewBackup ->  _getAllTables($OutPutType);

        if ($_POST['savetype'] == 'show') {
            $status_text = "<center><textarea cols='100' rows='30' name='content' style='font-family: Arial; font-size: 11px' readonly='readonly'>" . $oNewBackup -> sInputs ."</textarea></center>";
        }
        if ($_POST['savetype'] == 'server') {
            $sqlfile = BX_DIRECTORY_PATH_ROOT . 'backup/'.date("Y-m-d_H-i-s").'_all.sql';
            $file = fopen($sqlfile, 'w');
            fputs($file, $oNewBackup -> sInputs);
            $status_text .= "<hr size=1 /><font color='green'><center>{$sSuccDumpedIntoFileC} <b>{$sqlfile}</b></center></font>\n";
            fclose($file);
        }
        if ($_POST['savetype'] == 'client') {
            $sqlfile = date("Y-m-d_H:i:s").'_all.sql';
            header("Content-Type: text/plain");
            header("Content-Disposition: attachment;filename=\"".$sqlfile."\"");
            echo $oNewBackup -> sInputs;
            exit();
        }
    }

    if (isset($_POST['DatabasesRestore'])) {
        if ($_POST['savetype'] == 'delete') {
            if(is_file(BX_DIRECTORY_PATH_ROOT.'backup/'.$_POST['dump_file'])) {
                @unlink(BX_DIRECTORY_PATH_ROOT.'backup/'.$_POST['dump_file']);
                $status_text_restore .= "<hr size=1 /><font color='green'><center>{$sDumpFileSuccDeletedC} <b>{$sqlfile}</b></center></font>\n";
            } else
                $status_text_restore .= "<hr size=1 /><font color='red'><center>{$sPleaseSelectDumpFileC} <b>{$sqlfile}</b></center></font>\n";
        }
        if ($_POST['savetype'] == 'restore') {
            if(is_file(BX_DIRECTORY_PATH_ROOT.'backup/'.$_POST['dump_file'])) {
                $oNewBackup = new BxDolDatabaseBackup();
                $oNewBackup ->	_restoreFromDumpFile(BX_DIRECTORY_PATH_ROOT.'backup/'.$_POST['dump_file']);
                $status_text_restore .= "<hr size=1 /><font color='green'><center>{$sDateRestoredFromDumpC}</center></font>\n";
            } else
                $status_text_restore .= "<hr size=1 /><font color='red'><center>{$sPleaseSelectDumpFileC} <b>{$sqlfile}</b></center></font>\n";
        }
    }

    if (isset($_FILES['sqlfile'])) {
        if (preg_match("/.sql/", $_FILES['sqlfile']['name'])) { #it is correct
            $oNewBackup = new BxDolDatabaseBackup();
            $oNewBackup ->	_restoreFromDumpFile($_FILES['sqlfile']['tmp_name'] );
            @unlink($_FILES['sqlfile']['tmp_name']);
            $status_text_restore .= "<hr size=1 /><font color='green'><center>{$sDateRestoredFromPcC} </center></font>\n";
        } else
            $status_text_restore .= "<hr size=1 /><font color='red'><center>{$sPleaseSelectCorrectDumpFileC}</center></font>\n";
    }

    return array($status_text, $status_text_restore);
}

function getTablesBackupTools($status_text)
{
    $sChooseOperationC = _t('_adm_dbtools_Choose_operation_and_table');
    $sBackup1C = _t('_adm_dbtools_Backup_structure_content');
    $sBackup2C = _t('_adm_dbtools_Backup_structure');
    $sBackup3C = _t('_adm_dbtools_Backup_content');
    $sSaveOption1C = _t('_adm_dbtools_Save_to_server');
    $sSaveOption2C = _t('_adm_dbtools_Save_to_your_PC');
    $sSaveOption3C = _t('_adm_dbtools_Show_on_the_screen');
    $sBackupTableC = _t('_adm_dbtools_Backup_table');
    $sActionC = _t('_Action');

    // All tables of Database
    $aTablesOptions = array();
    $tbls = db_list_tables();
    foreach ($tbls as $tbl) {
        $aTablesOptions[$tbl] = $tbl;
    }

    $sStatusText = ($status_text and isset($_POST['TablesBackup'])) ? $status_text : '';

    $aForm = array(
        'form_attrs' => array(
            'name' => 'TablesBackupTools_form',
            'action' => $GLOBALS['site']['url_admin'] . 'db.php',
            'method' => 'post',
        ),
        'inputs' => array(
            'TablesBackup' => array(
                'type' => 'hidden',
                'name' => 'TablesBackup',
                'value' => 'YES',
            ),
            'tbl_op' => array(
                'type' => 'select',
                'name' => 'tbl_op',
                'caption' => $sChooseOperationC,
                'values' => array (
                    2 => $sBackup1C,
                    0 => $sBackup2C,
                    1 => $sBackup3C,
                ),
            ),
            'tbl' => array(
                'type' => 'select',
                'name' => 'tbl',
                'caption' => '',
                'values' => $aTablesOptions,
            ),
            'savetype' => array(
                'type' => 'radio_set',
                'name' => 'savetype',
                'caption' => $sActionC,
                'values' => array (
                    'server' => $sSaveOption1C,
                    'client' => $sSaveOption2C,
                    'show' => $sSaveOption3C,
                ),
            ),
            'tables_backup_tool' => array(
                'type' => 'submit',
                'name' => 'tables_backup_tool',
                'caption' => '',
                'value' => $sBackupTableC,
            ),
        ),
    );

    $oForm = new BxTemplFormView($aForm);
    return $GLOBALS['oAdmTemplate']->parseHtmlByName('design_box_content.html', array('content' => $oForm->getCode() . $sStatusText));
    //return DesignBoxContent(_t('_adm_dbtools_Tables_backup_tools'), '<div style="margin:9px;">' . $oForm->getCode() . '</div>' . $sStatusText, 1);
}

function DatabaseBackupTools($status_text)
{
    $sChooseOperationC = _t('_adm_dbtools_Choose_operation');
    $sBackup1C = _t('_adm_dbtools_Backup_structure_content');
    $sBackup2C = _t('_adm_dbtools_Backup_structure');
    $sSaveOption1C = _t('_adm_dbtools_Save_to_server');
    $sSaveOption2C = _t('_adm_dbtools_Save_to_your_PC');
    $sSaveOption3C = _t('_adm_dbtools_Show_on_the_screen');
    $sBackupDatabaseC = _t('_adm_dbtools_Backup_database');
    $sActionC = _t('_Action');

    $sStatusText = ($status_text and isset($_POST['DatabasesBackup'])) ? $status_text : '';

    $aForm = array(
        'form_attrs' => array(
            'name' => 'DatabaseBackupTools_form',
            'action' => $GLOBALS['site']['url_admin'] . 'db.php',
            'method' => 'post',
        ),
        'inputs' => array(
            'DatabasesBackup' => array(
                'type' => 'hidden',
                'name' => 'DatabasesBackup',
                'value' => 'YES',
            ),
            'db_op' => array(
                'type' => 'select',
                'name' => 'db_op',
                'caption' => $sChooseOperationC,
                'values' => array (
                    2 => $sBackup1C,
                    0 => $sBackup2C,
                ),
            ),
            'savetype' => array(
                'type' => 'radio_set',
                'name' => 'savetype',
                'caption' => $sActionC,
                'values' => array (
                    'server' => $sSaveOption1C,
                    'client' => $sSaveOption2C,
                    'show' => $sSaveOption3C,
                ),
            ),
            'database_backup_tool' => array(
                'type' => 'submit',
                'name' => 'database_backup_tool',
                'caption' => '',
                'value' => $sBackupDatabaseC,
            ),
        ),
    );

    $oForm = new BxTemplFormView($aForm);
    return $GLOBALS['oAdmTemplate']->parseHtmlByName('design_box_content.html', array('content' => $oForm->getCode() . $sStatusText));
    //return DesignBoxContent(_t('_adm_dbtools_Database_backup_tools'), '<div style="margin:9px;">' . $oForm->getCode() . '</div>' . $sStatusText, 1);
}

function getDatabaseRestoreBlock($status_text)
{
    $sPleaseSelectDumpFileC = _t('_adm_dbtools_Please_select_dump_file');
    $sRestoreDataFromDumpC = _t('_adm_dbtools_Restore_data_from_dump');
    $sDeleteDumpFromServerC = _t('_adm_dbtools_Delete_dump_from_server');
    $sDatabaseRestoreFromPCC = _t('_adm_dbtools_Database_restore_from_your_PC');
    $sSendC = _t('_Send');
    $sSubmitC = _t('_Submit');
    $sActionC = _t('_Action');

    $aExistedFilesOptions = array();
    if ( $handle = @opendir(BX_DIRECTORY_PATH_ROOT.'backup/') ) {
        while ( $file = readdir($handle) ) {
            if ( preg_match("/.sql/", $file) )
                $aExistedFilesOptions[$file] = $file;
        }
    }

    $sStatusText = ($status_text and isset($_POST['DatabasesRestore']) or isset($_FILES['sqlfile']) ) ? $status_text : '';

    $aForm = array(
        'form_attrs' => array(
            'name' => 'DatabaseRestore1_form',
            'action' => $GLOBALS['site']['url_admin'] . 'db.php',
            'method' => 'post',
        ),
        'inputs' => array(
            'DatabasesRestore' => array(
                'type' => 'hidden',
                'name' => 'DatabasesRestore',
                'value' => 'YES',
            ),
            'dump_file' => array(
                'type' => 'select',
                'name' => 'dump_file',
                'caption' => $sPleaseSelectDumpFileC,
                'values' => $aExistedFilesOptions,
            ),
            'savetype' => array(
                'type' => 'radio_set',
                'name' => 'savetype',
                'caption' => $sActionC,
                'values' => array (
                    'restore' => $sRestoreDataFromDumpC,
                    'delete' => $sDeleteDumpFromServerC,
                ),
                'value' => 'restore',
            ),
            'DatabaseRestore1' => array(
                'type' => 'submit',
                'name' => 'DatabaseRestore1',
                'caption' => '',
                'value' => $sSubmitC,
            ),
        ),
    );
    $oForm = new BxTemplFormView($aForm);

    $aForm2 = array(
        'form_attrs' => array(
            'name' => 'DatabaseRestore1_form',
            'action' => $GLOBALS['site']['url_admin'] . 'db.php',
            'method' => 'post',
            'enctype' => 'multipart/form-data',
        ),
        'inputs' => array(
            'header1' => array(
                'type' => 'block_header',
                'caption' => $sDatabaseRestoreFromPCC,
            ),
            'sqlfile' => array(
                'type' => 'file',
                'name' => 'sqlfile',
                'caption' => $sPleaseSelectDumpFileC,
                'required' => true,
            ),
            'DatabaseRestore2' => array(
                'type' => 'submit',
                'name' => 'DatabaseRestore2',
                'caption' => '',
                'value' => $sSendC,
            ),
        ),
    );
    $oForm2 = new BxTemplFormView($aForm2);

    return $GLOBALS['oAdmTemplate']->parseHtmlByName('design_box_content.html', array('content' => $oForm->getCode() . '<br />' . $oForm2->getCode() . $sStatusText));
}
