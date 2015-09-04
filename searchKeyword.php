<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once('inc/header.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'design.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'languages.inc.php');

bx_import('BxDolSearch');
bx_import('BxTemplFormView');

check_logged();
$member['ID'] = getLoggedId();

$_page['name_index'] = 81;
$_page['css_name'] = array('searchKeyword.css');

$_page['header'] = _t( "_Search" );
$_page['header_text'] = _t("_Search");

ob_start();
?>
<script language="javascript">
    $(document).ready( function() {
         $('#searchForm').bind( 'submit', function() {
                bx_loading('searchForm', true);
                 var sQuery = $('input', '#searchForm').serialize();
                 $.post('searchKeywordContent.php', sQuery, function(data) {
                         $('#searchArea').html(data);
                         bx_loading('searchForm', false);
                    }
                );
            return false;
          }
         );
      }
    );
</script>
<?php
$sCode = '';
$_page['extra_js'] = ob_get_clean();

$_ni = $_page['name_index'];

$oZ = new BxDolSearch();
if (bx_get('keyword')) {
    $sCode = $oZ->response();
    if (mb_strlen($sCode) == 0)
        $sCode = $oZ->getEmptyResult();
}

$sForm = getSearchForm();
$sSearchArea = '<div id="searchArea">'.$sCode.'</div>';

$_page_cont[$_ni]['page_main_code'] = $sForm . $sSearchArea;

$aVars = array ();
$GLOBALS['oTopMenu']->setCustomSubActions($aVars, '');

PageCode();

function getSearchForm ()
{
    $aList = $GLOBALS['MySQL']->fromCache('sys_objects_search', 'getAllWithKey',
           'SELECT `ID` as `id`,
                   `Title` as `title`,
                   `ClassName` as `class`,
                   `ClassPath` as `file`,
                   `ObjectName`
            FROM `sys_objects_search`', 'ObjectName'
    );
    $aValues = array();
    foreach ($aList as $sKey => $aValue) {
        $aValues[$sKey] = _t($aValue['title']);
        if (!class_exists($aValue['class'])) {
            $sPath = BX_DIRECTORY_PATH_ROOT . str_replace('{tmpl}', $GLOBALS['tmpl'], $aValue['file']);
            require_once($sPath);
        }
        $oClass = new $aValue['class']();
        $oClass->addCustomParts();
    }

    if (isset($_GET['type'])) {
        $aValue = strip_tags($_GET['type']);
    } else
        $aValue = array_keys($aValues);

    $aForm = array(
        'form_attrs' => array(
           'id' => 'searchForm',
           'action' => '',
           'method' => 'post',
           'onsubmit' => '',
        ),
        'inputs' => array(
            'section' => array(
                'type' => 'checkbox_set',
                'name' => 'section',
                'caption' => _t('_Section'),
                'values' => $aValues,
                'value' => $aValue,
            ),
            'keyword' => array(
                'type' => 'text',
                'name' => 'keyword',
                'caption' => _t('_Keyword')
            ),
            'search' => array(
                'type' => 'submit',
                'name' => 'search',
                'value' => _t('_Search')
            )
        )
    );

    $oForm = new BxTemplFormView($aForm);
    $sFormVal = $oForm->getCode();

    return DesignBoxContent(_t( "_Search" ), $sFormVal, 11);
}
