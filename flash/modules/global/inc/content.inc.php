<?php
/***************************************************************************
*
* IMPORTANT: This is a commercial product made by BoonEx Ltd. and cannot be modified for other than personal usage.
* This product cannot be redistributed for free or a fee without written permission from BoonEx Ltd.
* This notice may not be removed from the source code.
*
***************************************************************************/

require_once($sIncPath . "xml.inc.php");
require_once($sIncPath . "constants.inc.php");
require_once($sIncPath . "functions.inc.php");
require_once($sIncPath . "apiFunctions.inc.php");

function rayGetSettingValue($sWidget, $sSettingKey)
{
    return getSettingValue($sWidget, $sSettingKey);
}

function isBoonexWidgetsRegistered()
{
    global $sGlobalDir;
    return getSettingValue($sGlobalDir, "registered") == TRUE_VAL;
}

function setRayBoonexLicense($sLicense)
{
    if(getSettingValue(GLOBAL_MODULE, "license", "main") == $sLicense) return;

    global $sModulesPath;
    $rDirHandler = opendir($sModulesPath);
    while(($sInner = readdir($rDirHandler)) !== false)
        if(is_dir($sModulesPath . $sInner) && substr($sInner, 0, 1) != '.') {
            if(isset($aInfo)) unset($aInfo);
            $sConstantsFile = $sModulesPath . $sInner . "/inc/constants.inc.php";
            if(!file_exists($sConstantsFile)) continue;
            require($sConstantsFile);
            if(strtolower($aInfo['author']) == 'boonex')
                setSettingValue($sInner, array("status", "license"), array(WIDGET_STATUS_ENABLED, $sLicense), "main");
        }
}

function getRayIntegrationJS($isDolphinPage = 0)
{
    global $sHomeUrl;
    global $sGlobalPath;
    global $sGlobalUrl;
    global $sDataPath;
    global $sRayHomeDir;
    global $sModulesDir;
    global $sGlobalDir;

    $sIntegrationData = $sDataPath . "integration.dat";
    $sReturn = '<script type="text/javascript" language="javascript">var sRayUrl = "' . $sHomeUrl . '";var aRayApps = new Array();';
    if(file_exists($sIntegrationData) && filesize($sIntegrationData) > 0)
        $sReturn .= @file_get_contents($sIntegrationData);
    $sReturn .= '</script><script src="' . $sGlobalUrl . 'js/integration.js" type="text/javascript" language="javascript"></script>';

    if ($isDolphinPage)
        $GLOBALS['oSysTemplate']->addJs($sRayHomeDir . $sModulesDir . $sGlobalDir . 'js/|swfobject.js');
    else
        $sReturn .= '<script src="' . $sGlobalUrl . 'js/swfobject.js" type="text/javascript" language="javascript"></script>';

    return $sReturn;
}

/**
 * Checks if given widget exists
 * @param sWidget - widget name
 * @return bExists - true/false
 */
function widgetExists($sWidget)
{
    global $sModulesPath;

    $sFilePath = $sModulesPath . $sWidget . "/xml/main.xml";
    $bExists = file_exists($sFilePath) && filesize($sFilePath) > 0;
    return $bExists;
}

function getFlashConfig($sModule, $sApp, $aParamValues)
{
    global $sModulesPath;
    global $sModulesUrl;
    global $sRayXmlUrl;

    if(isset($aModules)) unset($aModules);
    if($sModule != GLOBAL_MODULE) require($sModulesPath . $sModule . "/inc/header.inc.php");
    require($sModulesPath . $sModule . "/inc/constants.inc.php");

    $sHolder = $aInfo['mode'] == "as3" ? "holder_as3.swf" : "holder.swf";
    if(isset($aModules[$sApp]['holder'])) $sHolder = $aModules[$sApp]['holder'] . ".swf";
    $sHolder = $sModulesUrl . GLOBAL_MODULE . "/app/" . $sHolder;

    $iWidth = getSettingValue($sModule, $sApp . "_width");
    if(empty($iWidth)) $iWidth = $aModules[$sApp]['layout']['width'];
    $iHeight = getSettingValue($sModule, $sApp . "_height");
    if(empty($iHeight)) $iHeight = $aModules[$sApp]['layout']['height'];

    $aFlashVars = array(
        'url' => $sRayXmlUrl,
        'module' => $sModule,
        'app' => $sApp
    );
    foreach($aModules[$sApp]['parameters'] as $sParameter) {
        $aFlashVars[$sParameter] = isset($aParamValues[$sParameter]) ? $aParamValues[$sParameter] : process_db_input($_REQUEST[$sParameter]);
    }

    $aParams = array(
        'allowScriptAccess' => "always",
        'allowFullScreen' => "true",
        'base' => $sModulesUrl . $sModule . "/",
        'bgcolor' => "#" . getSettingValue(GLOBAL_MODULE, "backColor"),
        'wmode' => getWMode()
    );

    return array(
        'holder' => $sHolder,
        'width' => $iWidth,
        'height' => $iHeight,
        'flashVars' => $aFlashVars,
        'params' => $aParams,
        'modules' => $aModules
    );
}

/**
 * Gets the embed code of necessary widget's application.
 * @param sModule - module(widget) name.
 * @param sApp - application name in the widget.
 * @param aParamValues - an associative array of parameters to be passed into the Flash object.
 */
function getEmbedCode($sModule, $sApp, $aParamValues)
{
    global $sGlobalUrl;

    $sTemplate = '<object style="display:block;" width="#width#" height="#height#"><param name="movie" value="#holder#"></param>#objectParams#<embed src="#holder#" type="application/x-shockwave-flash" width="#width#" height="#height#" #embedParams#></embed></object>';
    $aConfig = getFlashConfig($sModule, $sApp, $aParamValues);

    $aFlashVars = array();
    foreach($aConfig['flashVars'] as $sKey => $sValue)
        $aFlashVars[] = $sKey . '=' . $sValue;
    $aConfig['params']['flashVars'] = implode('&amp;', $aFlashVars);

    $aObjectParams = array();
    $aEmbedParams = array();
    foreach($aConfig['params'] as $sKey => $sValue) {
        $aObjectParams[] = '<param name="' . $sKey . '" value="' . $sValue . '"></param>';
        $aEmbedParams[] = $sKey . '="' . $sValue . '"';
    }

    $sReturn = str_replace("#holder#", $aConfig['holder'], $sTemplate);
    $sReturn = str_replace("#width#", $aConfig['width'], $sReturn);
    $sReturn = str_replace("#height#", $aConfig['height'], $sReturn);
    $sReturn = str_replace("#objectParams#", implode("", $aObjectParams), $sReturn);
    $sReturn = str_replace("#embedParams#", implode(" ", $aEmbedParams), $sReturn);
    return $sReturn;
}

/**
 * Gets the content of necessary widget's application.
 * @param sModule - module(widget) name.
 * @param sApp - application name in the widget.
 * @param aParamValues - an associative array of parameters to be passed into the Flash object.
 * @param bInline - whether you want to have it with the full page code(for opening in a new window)
 * or only DIV with flash object (for embedding into the existing page).
 */
function getApplicationContent($sModule, $sApp, $aParamValues = array(), $bInline = false, $bEmbedCode = false, $sHtmlId = "")
{
    global $sGlobalUrl;
    global $sHomeUrl;
    global $sRayHomeDir;
    global $sModulesUrl;
    global $sModulesPath;
    global $sFlashPlayerVersion;

    $sModule = isset($sModule) ? $sModule : process_db_input($_REQUEST['module']);
    $sApp = isset($sApp) ? $sApp : process_db_input($_REQUEST['app']);

    $sModuleStatus = getSettingValue($sModule, "status", "main");
    if($sModuleStatus == WIDGET_STATUS_NOT_INSTALLED || $sModuleStatus == WIDGET_STATUS_DISABLED) return "";

    $aConfig = getFlashConfig($sModule, $sApp, $aParamValues);
    $aModules = $aConfig['modules'];
    if(!isset($bInline))$bInline = $aModules[$sApp]['inline'];

    //--- Parameters for container's div ---//
    $sDivId = !empty($aModules[$sApp]['div']['id']) ? $aModules[$sApp]['div']['id'] : '';
    if(!empty($sHtmlId)) $sDivId = $sHtmlId;
    if(empty($sDivId)) $sDivId = $sModule . '_' . $sApp;
    $sInnerDivId = $sDivId . "_" . time();
    if(empty($sHtmlId)) $sHtmlId = "ray_" . $sModule . "_" . $sApp . "_object";

    $sDivName = !empty($aModules[$sApp]['div']['name']) ? ' name="' . $aModules[$sApp]['div']['name'] . '"' : '';
    if(!empty($aModules[$sApp]['div']['style'])) {
        $sDivStyle = ' style="';
        foreach($aModules[$sApp]['div']['style'] as $sKey => $sValue)
            $sDivStyle .= $sKey . ':' . $sValue . ';';
        $sDivStyle .= '"';
    } else $sDivStyle='';

    //--- Parameters for SWF object and reloading ---//
    $aParametersReload = array();
    if(!isset($_GET["module"])) $aParametersReload[] = "module=" . $sModule;
    if(!isset($_GET["app"])) $aParametersReload[] = "app=" . $sApp;

    ob_start();
    if(!$bInline) {
?>
<!DOCTYPE html>
<html>
        <head>
            <title><?=$aModules[$sApp]['caption']; ?></title>
            <meta http-equiv=Content-Type content="text/html;charset=UTF-8" />
        </head>
        <body style="margin:0; padding:0;"  <?=$aModules[$sApp]['hResizable'] || $aModules[$sApp]['vResizable'] ? 'onLoad="resizeWindow()" onResize="if ( window.resizeWindow ) resizeWindow()"' : ''; ?> >
<?php
        echo getRayIntegrationJS();
    }
    if(!$bEmbedCode)
        foreach($aModules[$sApp]['js'] as $sJSUrl)
            echo "\t\t<script src=\"" . $sJSUrl . "\" type=\"text/javascript\" language=\"javascript\"></script>\n";

    if(!$bEmbedCode && ($aModules[$sApp]['hResizable'] || $aModules[$sApp]['vResizable'])) {
        $iMinWidth = (int)$aModules[$sApp]['minSize']['width'];
        $iMinHeight = (int)$aModules[$sApp]['minSize']['height'];
?>
    <script type="text/javascript" language="javascript">
        function resizeWindow()
        {
            var frameWidth = 0;
            var frameHeight = 0;

            if (document.documentElement) {
                if(document.documentElement.clientHeight) {
                    frameWidth = document.documentElement.clientWidth;
                    frameHeight = document.documentElement.clientHeight;
                }
            } else if(window.innerWidth) {
                frameWidth = window.innerWidth;
                frameHeight = window.innerHeight;
            } else if (document.body) {
                frameWidth = document.body.offsetWidth;
                frameHeight = document.body.offsetHeight;
            }

            var sAppName = 'ray_flash_<?=$sModule?>_<?=$sApp?>_';
            var o = document.getElementById(sAppName + 'object');
            var e = document.getElementById(sAppName + 'embed');

            frameWidth = (frameWidth < <?=$iMinWidth?>) ? <?=$iMinWidth?> : frameWidth;
            frameHeight = (frameHeight < <?=$iMinHeight?>) ? <?=$iMinHeight?> : frameHeight;

<?php
    $sRet = $aModules[$sApp]['hResizable'] ? "o.width = frameWidth;\n" : "";
    $sRet .= $aModules[$sApp]['vResizable'] ? "o.height = frameHeight;\n" : "";
    $sRet .= "if(e != null){";
    $sRet .= $aModules[$sApp]['hResizable'] ? "e.width = frameWidth;\n" : "";
    $sRet .= $aModules[$sApp]['vResizable'] ? "e.height = frameHeight;\n" : "";
    $sRet .= "}";
    echo $sRet;
?>
        }
    </script>
<?php
    }
    if(!$bEmbedCode && $aModules[$sApp]['reloadable']) {
        if(!$bInline) echo getRedirectForm($sModule, $sApp, array_merge($_GET, $_POST));
?>
    <script type="text/javascript" language="javascript">
        function reload()
        {
<?php
            $sGet = $_SERVER['QUERY_STRING'];
            $sExtraGet = implode("&", $aParametersReload);
            if(!empty($sGet) && !empty($sExtraGet)) $sGet .= "&";
?>
<?= !$bInline ? "redirect();" : "location.href='" . $_SERVER['PHP_SELF'] . "?" . $sGet . $sExtraGet . "';" ?>
        }
    </script>
<?php
    }
?>
<div id="<?=$sDivId?>" <?=$sDivName . $sDivStyle?>><div id="<?=$sInnerDivId?>"></div></div>
<script type="text/javascript" language="javascript">
<?php
    foreach($aConfig['flashVars'] as $sKey => $sValue) {
        if(!isset($_GET[$sKey]) && $sKey != 'url') $aParametersReload[] = $sKey . "=" . (isset($aConfig['flashVars'][$sKey]) ? $aConfig['flashVars'][$sKey] : process_db_input($_REQUEST[$sKey]));
    }
?>
<?=phpArrayToJS($aConfig['flashVars'], "flashvars")?>
<?=phpArrayToJS($aConfig['params'], "params")?>

var attributes = {
    id: "ray_flash_<?=$sModule?>_<?=$sApp?>_object",
    name: "ray_flash_<?=$sModule?>_<?=$sApp?>_embed",
    style: "display:block;"
};
swfobject.embedSWF("<?=$aConfig['holder']?>", "<?=$sInnerDivId?>", "<?=$aConfig['width']?>", "<?=$aConfig['height']?>", "<?=$sFlashPlayerVersion?>", "<?=$sGlobalUrl?>app/expressInstall.swf", flashvars, params, attributes);
</script>
<?php
    if(!$bInline) {
?>
        </body>
    </html>
<?php
    }
    $sWidgetContent = ob_get_contents();
    ob_end_clean();

    return $sWidgetContent;
}

/**
 * Make redirect and send necessary parameters using POST method.
 */
function getRedirectForm($sModule, $sApp, $aRequest)
{
    ob_start();
?>
    <form style="margin:0; padding:0;" name="<?= $sModule . "-" . $sApp; ?>" method="POST" action="<?= $_SERVER['PHP_SELF']; ?>">
        <input type="hidden" name="module" value="<?= $sModule; ?>" />
        <input type="hidden" name="app" value="<?= $sApp; ?>" />
<?php
        foreach($aRequest as $sKey => $sValue) {
?>
            <input type="hidden" name="<?=process_db_input($sKey, BX_TAGS_SPECIAL_CHARS)?>" value="<?=process_db_input($sValue, BX_TAGS_SPECIAL_CHARS)?>" />
<?php
        }
?>
    </form>
    <script>
    <!--
        function redirect()
        {
            document.forms['<?= $sModule . "-" . $sApp; ?>'].submit();
        }
    -->
    </script>
<?php
    $sReturn = ob_get_contents();
    ob_end_clean();
    return $sReturn;
}

function phpArrayToJS($aArray, $sName)
{
    $aNewArray = array();
    foreach($aArray as $sKey => $sValue)
        $aNewArray[] = $sKey . ':"' . $sValue . '"';
    return "var " . $sName . "={" . implode(",", $aNewArray) . "};";
}
