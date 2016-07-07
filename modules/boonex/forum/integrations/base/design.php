<?php
/**
*                            Orca Interactive Forum Script
*                              ---------------
*     Started             : Mon Mar 23 2006
*     Copyright           : (C) 2007 BoonEx Group
*     Website             : http://www.boonex.com
* This file is part of Orca - Interactive Forum Script
* GPL
**/

// generate custom $glHeader and $glFooter variables here

// ******************* include dolphin header/footer [begin]

check_logged();

require_once(BX_DIRECTORY_PATH_INC . 'db.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'params.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'design.inc.php');
require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolPageView.php');


class BxDolOrcaForumsTemplate extends BxDolTemplate
{
    function __construct($sRootPath = BX_DIRECTORY_PATH_ROOT, $sRootUrl = BX_DOL_URL_ROOT)
    {
        parent::__construct($sRootPath, $sRootUrl);
        $this->addLocation('BxDolOrcaForums', $GLOBALS['gConf']['dir']['base'], $GLOBALS['gConf']['url']['base']);
    }
}

class BxDolOrcaForumsIndex extends BxDolPageView
{
    var $sMarker = '-=++=-';

    function __construct()
    {
        parent::__construct('forums_index');
    }

    function getBlockCode_FullIndex()
    {
        return $this->sMarker;
    }
}

class BxDolOrcaForumsHome extends BxDolPageView
{
    var $sMarker = '-=++=-';

    function __construct()
    {
        parent::__construct('forums_home');
    }

    function getBlockCode_Search()
    {
        $oTemplate = new BxDolOrcaForumsTemplate();
        $aVars = array(
            'base_url_forum' => $GLOBALS['gConf']['url']['base'],
        );
        return array($oTemplate->parseHtmlByName('search_block.html', $aVars));
    }

    function getBlockCode_ShortIndex()
    {
        global $gConf;

        $s = '<div class="forums_index_short">';
        $ac = $GLOBALS['f']->fdb->getCategs();
        foreach ($ac as $c) {
            $s .= '<div class="forums_index_short_cat bx-def-font-large"><a href="' . $gConf['url']['base'] . sprintf($gConf['rewrite']['cat'], $c['cat_uri'], 0) . '" onclick="return f.selectForumIndex(\'' . $c['cat_uri'] . '\')">'. $c['cat_name'] .'</a></div>';
            $af = $GLOBALS['f']->fdb->getForumsByCatUri (filter_to_db($c['cat_uri']));
            foreach ($af as $ff)
                $s .= '<div class="forums_index_short_forum bx-def-padding-sec-left"><a href="' . $gConf['url']['base'] . sprintf($gConf['rewrite']['forum'], $ff['forum_uri'], 0) . '" onclick="return f.selectForum(\'' . $ff['forum_uri'] . '\', 0)">' . $ff['forum_title'] . '</a></div>';
        }
        $s .= '</div>';
        return array($s);
    }

    function getBlockCode_RecentTopics()
    {
        return $this->sMarker;
    }
}


global $_page, $glHeader, $glFooter, $logged, $_ni;

$GLOBALS['name_index'] = $_page['name_index'] = 55;

$_page['header'] = $gConf['def_title'];
$_page['header_text'] = $gConf['def_title'];

$_ni = $_page['name_index'];
$_page_cont[$_ni]['page_main_code'] = '-=++=-';

global $gConf;

$sCssPathUrl = bx_ltrim_str($gConf['url']['css'], BX_DOL_URL_ROOT);
$sCssPathDir = bx_ltrim_str("{$gConf['dir']['layouts']}{$gConf['skin']}/css/", BX_DIRECTORY_PATH_ROOT);
$GLOBALS['oSysTemplate']->addCss ("{$sCssPathDir}|{$sCssPathUrl}|main.css");

$sJsPathUrl = bx_ltrim_str($gConf['url']['js'], BX_DOL_URL_ROOT);
$sJsPathDir = bx_ltrim_str($gConf['dir']['js'], BX_DIRECTORY_PATH_ROOT);
$GLOBALS['oSysTemplate']->addJs (array(
    'history.js',
    "{$sJsPathDir}|{$sJsPathUrl}|util.js",
    "{$sJsPathDir}|{$sJsPathUrl}|BxError.js",
    "{$sJsPathDir}|{$sJsPathUrl}|BxXmlRequest.js",
    "{$sJsPathDir}|{$sJsPathUrl}|BxXslTransform.js",
    "{$sJsPathDir}|{$sJsPathUrl}|BxForum.js",
    "{$sJsPathDir}|{$sJsPathUrl}|BxHistory.js",
    "{$sJsPathDir}|{$sJsPathUrl}|BxLogin.js",
    "{$sJsPathDir}|{$sJsPathUrl}|BxAdmin.js",
));

$GLOBALS['BxDolTemplateInjections']['page_'.$_ni]['injection_body'][] = array('type' => 'text', 'data' => 'id="body" onload="if(!document.body) { document.body = document.getElementById(\'body\'); }; h = new BxHistory(\'' . $gConf['url']['base'] .  '\'); document.h = h; return h.init(\'h\'); "');

if (BX_ORCA_INTEGRATION == 'dolphin') {
    $aVars = array ('ForumBaseUrl' => $gConf['url']['base']);
    $GLOBALS['oTopMenu']->setCustomSubActions($aVars, 'bx_forum_title', false);
}

if (isLogged()) {
    bx_import('BxDolEditor');
    $oEditor = BxDolEditor::getObjectInstance();
    $sEditorId = isset($_REQUEST['new_topic']) ? '#tinyEditor' : '#fakeEditor';
    if ($oEditor) {
        if ('sys_tinymce' == $oEditor->getObjectName())
            $oEditor->setCustomConf('setup :
function(ed) {
    ed.on("init", function(e) {
        if ("undefined" === typeof(glOrcaSettings))
            glOrcaSettings = tinyMCE.activeEditor["settings"];
        orcaInitInstance(ed);
    });
},');
        $sEditor .= $oEditor->attachEditor ($sEditorId, BX_EDITOR_FULL) . '<div id="fakeEditor" style="display:none;"></div>';
    }
}


// add css from pages
$sAction = bx_get('action');
if ('goto' == $sAction && isset($_GET['index'])) {
    $o = new BxDolOrcaForumsIndex();
    $o->getCode();
} 
elseif (!$sAction) {
    $o = new BxDolOrcaForumsHome();
    $o->getCode();
}


ob_start();
PageCode();
$sDolphinDesign = ob_get_clean();

$iPos = strpos($sDolphinDesign, '-=++=-');
$glHeader = substr ($sDolphinDesign, 0, $iPos) . $sEditor;
$glFooter = substr ($sDolphinDesign, $iPos + 6 - strlen($sDolphinDesign));
$glIndexBegin = '';
$glIndexEnd = '';

// ******************* include dolphin header/footer [ end ]

