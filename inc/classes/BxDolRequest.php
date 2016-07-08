<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

define('BX_DOL_REQUEST_ERROR_MODULE_NOT_FOUND', 1);
define('BX_DOL_REQUEST_ERROR_PAGE_NOT_FOUND', 2);

$GLOBALS['bxDolClasses'] = array();

class BxDolRequest
{
    public static function processAsFile($aModule, &$aRequest)
    {
        if (empty($aRequest) || ($sFileName = array_shift($aRequest)) == "") {
            $sFileName = 'index';
        }

        $sFile = BX_DIRECTORY_PATH_MODULES . $aModule['path'] . $sFileName . '.php';
        if (!file_exists($sFile)) {
            (new self())->pageNotFound($sFileName, $aModule['uri']);
        } else {
            if (isset($GLOBALS['bx_profiler'])) {
                $GLOBALS['bx_profiler']->beginModule('file', ($sPrHash = uniqid(rand())), $aModule, $sFileName);
            }
            include($sFile);
            if (isset($GLOBALS['bx_profiler'])) {
                $GLOBALS['bx_profiler']->endModule('file', $sPrHash);
            }
        }
    }

    public static function processAsAction($aModule, &$aRequest, $sClass = "Module")
    {
        $sAction = empty($aRequest) || (isset($aRequest[0]) && empty($aRequest[0])) ? 'Home' : array_shift($aRequest);
        $sMethod = 'action' . str_replace(' ', '', ucwords(str_replace('_', ' ', $sAction)));

        if (isset($GLOBALS['bx_profiler'])) {
            $GLOBALS['bx_profiler']->beginModule('action', ($sPrHash = uniqid(rand())), $aModule, $sClass, $sMethod);
        }

        $mixedRet = (new self())->_perform($aModule, $sClass, $sMethod, $aRequest);

        if (isset($GLOBALS['bx_profiler'])) {
            $GLOBALS['bx_profiler']->endModule('action', $sPrHash);
        }

        return $mixedRet;
    }

    public static function processAsService($aModule, $sMethod, $aParams, $sClass = "Module")
    {
        $sMethod = 'service' . str_replace(' ', '', ucwords(str_replace('_', ' ', $sMethod)));

        if (isset($GLOBALS['bx_profiler'])) {
            $GLOBALS['bx_profiler']->beginModule('service', ($sPrHash = uniqid(rand())), $aModule, $sClass, $sMethod);
        }

        $mixedRet = (new self())->_perform($aModule, $sClass, $sMethod, $aParams, false);

        if (isset($GLOBALS['bx_profiler'])) {
            $GLOBALS['bx_profiler']->endModule('service', $sPrHash);
        }

        return $mixedRet;
    }

    public static function serviceExists($mixedModule, $sMethod, $sClass = "Module")
    {
        $oBxDolRequest = new self();
        return $oBxDolRequest->_methodExists($mixedModule, 'service', $sMethod, $sClass);
    }

    public static function actionExists($mixedModule, $sMethod, $sClass = "Module")
    {
        $oBxDolRequest = new self();
        return $oBxDolRequest->_methodExists($mixedModule, 'action', $sMethod, $sClass);
    }

    public static function moduleNotFound($sModule)
    {
        (new self())->_error('module', $sModule);
    }

    function pageNotFound($sPage, $sModule)
    {
        (new self())->_error('page', $sPage, $sModule);
    }

    function methodNotFound($sMethod, $sModule)
    {
        (new self())->_error('method', $sMethod, $sModule);
    }

    function _perform($aModule, $sClass, $sMethod, $aParams, $bTerminateOnError = true)
    {
        $sClass = $aModule['class_prefix'] . $sClass;

        $oModule = $this->_require($aModule, $sClass);
        if ($oModule === false && $bTerminateOnError) {
            $this->methodNotFound($sMethod, $aModule['uri']);
        } else {
            if ($oModule === false && !$bTerminateOnError) {
                return false;
            }
        }

        $bMethod = method_exists($oModule, $sMethod);
        if ($bMethod) {
            return call_user_func_array(array($oModule, $sMethod), $aParams);
        } else {
            if (!$bMethod && $bTerminateOnError) {
                $this->methodNotFound($sMethod, $aModule['uri']);
            } else {
                if (!$bMethod && !$bTerminateOnError) {
                    return false;
                }
            }
        }
    }

    public static function _require($aModule, $sClass)
    {
        if (!isset($GLOBALS['bxDolClasses'][$sClass])) {
            $sFile = BX_DIRECTORY_PATH_MODULES . $aModule['path'] . 'classes/' . $sClass . '.php';
            if (!file_exists($sFile)) {
                return false;
            }

            require_once($sFile);
            $oModule = new $sClass($aModule);

            $GLOBALS['bxDolClasses'][$sClass] = $oModule;
        } else {
            $oModule = $GLOBALS['bxDolClasses'][$sClass];
        }

        return $oModule;
    }

    function _methodExists($mixedModule, $sMethodType, $sMethodName, $sClass = "Module")
    {
        $aModule = $mixedModule;
        if (is_string($mixedModule)) {
            $oModuleDb = new BxDolModuleDb();
            $aModule = $oModuleDb->getModuleByUri($mixedModule);
        }

        if(empty($aModule)) {
            return false;
        }

        $sClass = $aModule['class_prefix'] . $sClass;
        if (($oModule = $this->_require($aModule, $sClass)) === false) {
            return false;
        }

        $sMethod = $sMethodType . str_replace(' ', '', ucwords(str_replace('_', ' ', $sMethodName)));

        return method_exists($oModule, $sMethod);
    }

    function _error($sType, $sParam1 = '', $sParam2 = '')
    {
        header('Status: 404 Not Found');
        header('HTTP/1.0 404 Not Found');

        global $_page;
        global $_page_cont;

        $iIndex = 13;
        $_page['name_index'] = $iIndex;
        $_page['header'] = _t("_sys_request_" . $sType . "_not_found_cpt");
        $_page_cont[$iIndex]['page_main_code'] = MsgBox(_t("_sys_request_" . $sType . "_not_found_cnt",
            htmlspecialchars_adv($sParam1), htmlspecialchars_adv($sParam2)));
        PageCode();
        exit;
    }
}
