<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolSession');

define('BX_DOL_FORM_METHOD_GET', 'get');
define('BX_DOL_FORM_METHOD_POST', 'post');

/**
 * Forms base class, consists of
 *  - automatic check routines
 *  - get clear variables from input routines
 *  - database insert/update routines
 *
 *  Init form object examples:
 *
 *      $aForm = array(
 *            'form_attrs' => array(
 *                'name'     => 'form_my',
 *                'method'   => 'post',
 *            ),
 *
 *            'params' => array (
 *                'db' => array(
 *                    'table' => 'table_name', // table name
 *                    'key' => 'ID', // key field name
 *                    'uri' => 'EntryUri', // uri field name
 *                    'uri_title' => 'Title', // title field to generate uri from
 *                    'submit_name' => 'submit_form', // some filed name with non empty value to determine if the for was submitted,
 *                                                       in most cases it is submit button name
 *                ),
 *                'csrf' => array(
 *					  'disable' => true, //if it wasn't set or has some other value then CSRF checking is enabled for current form, take a look at sys_security_form_token_enable to disable CSRF checking completely.
 *                )
 *              ),
 *
 *            'inputs' => array(
 *
 *                'Title' => array(
 *                    'type' => 'text',
 *                    'name' => 'Title', // the same as key and database field name
 *                    'caption' => 'Some caption',
 *                    'required' => true,
 *
 *                    // checker params
 *                    'checker' => array (
 *                        'func' => 'length', // see BxDolFormCheckerHelper class for all check* functions
 *                        'params' => array(3,100),
 *                        'error' => 'length must be from 3 to 100 characters',
 *                    ),
 *                    // database params
 *                    'db' => array (
 *                        'pass' => 'Xss',  // do XSS clear before getting this value, see BxDolFormCheckerHelper class for all pass* functions
 *                    ),
 *                ),
 *
 *                'Description' => array(
 *                    'type' => 'textarea',
 *                    'name' => 'Description', // the same as key and database field name
 *                    'caption' => 'Some caption',
 *                    'required' => true,
 *
 *                    // checker params
 *                    'checker' => array (
 *                        'func' => 'length',
 *                        'error' => 'enter at least 3 characters',
 *                        'params' => array(3,64000),
 *                    ),
 *                    'db' => array (
 *                        'pass' => 'XssHtml',  // do XSS clear, but keep HTML before getting this value
 *                    ),
 *                ),
 *            );
 *
 *
 *   Example of using:
 *
 *
 *        $oForm = new BxTemplFormView ($aForm);
 *        $oForm->initChecker();
 *
 *        if ($oForm->isSubmittedAndValid ()) {
 *
 *            // add additional vars to database, in this case creation date field is added
 *            $aValsAdd = array (
 *                'Date' => time(),
 *            );
 *
 *            echo 'insert last id: ' . $oForm->insert ($aValsAdd); // insert validated data to database
 *
 *        } else {
 *
 *            echo $oForm->getCode (); // show form
 *
 *        }
 *
 */
class BxDolForm
{
    var $_isValid = true;
    var $_sCheckerHelper;

    var $aFormAttrs;
    var $aTableAttrs;
    var $aInputs;
    var $aParams;

    /**
     * Form element id
     * @var string
     */
    var $id;

    function __construct ($aInfo)
    {
        $this->aFormAttrs    = isset($aInfo['form_attrs'])   ? $aInfo['form_attrs']  : array();
        $this->aTableAttrs   = isset($aInfo['table_attrs'])  ? $aInfo['table_attrs'] : array();
        $this->aInputs       = isset($aInfo['inputs'])       ? $aInfo['inputs']      : array();
        $this->aParams       = isset($aInfo['params'])       ? $aInfo['params']      : array();

        // get form element id
        $this->id = $this->aFormAttrs['id'] = (!empty($this->aFormAttrs['id']) ? $this->aFormAttrs['id'] : (!empty($this->aFormAttrs['name']) ? $this->aFormAttrs['name'] : 'form_advanced'));

        // set default method
        if (!isset($this->aFormAttrs['method']))
            $this->aFormAttrs['method'] = BX_DOL_FORM_METHOD_GET;

        // set default action
        if (!isset($this->aFormAttrs['action']))
            $this->aFormAttrs['action'] = '';

        $this->_sCheckerHelper = isset($this->aParams['checker_helper']) ? $this->aParams['checker_helper'] : '';

        BxDolForm::genCsrfToken();

        $oZ = new BxDolAlerts('form', 'init', 0, 0, array(
            'form_object' => $this,
            'form_attrs' => &$this->aFormAttrs,
            'table_attrs' => &$this->aTableAttrs,
            'params' => &$this->aParams,
            'inputs' => &$this->aInputs,
        ));
        $oZ->alert();
    }

    function initChecker ($aValues = array ())
    {
        $oChecker = new BxDolFormChecker($this->_sCheckerHelper);
        $oChecker->setFormMethod($this->aFormAttrs['method']);        

        if ($this->isSubmitted ()) {
            $oChecker->enableFormCsrfChecking(isset($this->aParams['csrf']['disable']) && $this->aParams['csrf']['disable'] === true ? false : true);
            $this->_isValid = $oChecker->check($this->aInputs);
        } 
        elseif ($aValues) {
            $oChecker->fillWithValues($this->aInputs, $aValues);
        }

        $oZ = new BxDolAlerts('form', 'init_checker', 0, 0, array(
            'values' => $aValues,
            'checker_object' => $oChecker,
            'form_object' => $this,
            'form_attrs' => &$this->aFormAttrs,
            'table_attrs' => &$this->aTableAttrs,
            'params' => &$this->aParams,
            'inputs' => &$this->aInputs,
        ));

        $oZ->alert();
    }

    function insert ($aValsToAdd = array())
    {
        $oChecker = new BxDolFormChecker($this->_sCheckerHelper);
        $oChecker->setFormMethod($this->aFormAttrs['method']);
        $sSql = $oChecker->dbInsert($this->aParams['db'], $this->aInputs, $aValsToAdd);
        if (!$sSql) return false;
        if (!db_res ($sSql))
            return false;
        $iLastId = db_last_id();

        $oZ = new BxDolAlerts('form', 'insert_data', 0, 0, array(
            'vals_to_add' => $aValsToAdd,
            'checker_object' => $oChecker,
            'form_object' => $this,
            'form_attrs' => &$this->aFormAttrs,
            'table_attrs' => &$this->aTableAttrs,
            'params' => &$this->aParams,
            'inputs' => &$this->aInputs,
        ));
        $oZ->alert();

        return $iLastId;
    }

    function update ($val, $aValsToAdd = array())
    {
        $oChecker = new BxDolFormChecker($this->_sCheckerHelper);
        $oChecker->setFormMethod($this->aFormAttrs['method']);
        $sSql = $oChecker->dbUpdate($val, $this->aParams['db'], $this->aInputs, $aValsToAdd);
        if (!$sSql)
            return false;
        if (!($res = db_res ($sSql)))
            return false;

        $oZ = new BxDolAlerts('form', 'update_data', 0, 0, array(
            'val' => $val,
            'vals_to_add' => $aValsToAdd,
            'checker_object' => $oChecker,
            'form_object' => $this,
            'form_attrs' => &$this->aFormAttrs,
            'table_attrs' => &$this->aTableAttrs,
            'params' => &$this->aParams,
            'inputs' => &$this->aInputs,
        ));
        $oZ->alert();

        return $res;
    }

    function generateUri ()
    {
        $f = &$this->aParams['db'];
        $sUri = $this->getCleanValue ($f['uri_title']);
        return uriGenerate($sUri, $f['table'], $f['uri']);
    }

    function getCleanValue ($sName)
    {
        $oChecker = new BxDolFormChecker($this->_sCheckerHelper);
        $oChecker->setFormMethod($this->aFormAttrs['method']);
        $a = $this->aInputs[$sName];
        if ($a)
            return $oChecker->get ($a['name'], $a['db']['pass'], $a['db']['params'] ? $a['db']['params'] : array());
        else
           return $oChecker->get ($sName);
    }

    function isSubmitted ()
    {
        return BxDolForm::getSubmittedValue($this->aParams['db']['submit_name'], $this->aFormAttrs['method']) ? true : false;
    }

    function isValid ()
    {
        return $this->_isValid;
    }

    function isSubmittedAndValid ()
    {
        return ($this->isSubmitted() && $this->isValid());
    }

    public static function getSubmittedValue($sKey, $sMethod)
    {
        $aData = array();
        if($sMethod == BX_DOL_FORM_METHOD_GET)
            $aData = &$_GET;
        else if($sMethod == BX_DOL_FORM_METHOD_POST)
            $aData = &$_POST;

        return isset($aData[$sKey]) ? $aData[$sKey] : false;
    }

    // Static Methods related to CSRF Tocken
    function genCsrfToken($bReturn = false)
    {
        if($GLOBALS['MySQL']->getParam('sys_security_form_token_enable') != 'on' || defined('BX_DOL_CRON_EXECUTE'))
            return;

        $oSession = BxDolSession::getInstance();

        $iCsrfTokenLifetime = (int)$GLOBALS['MySQL']->getParam('sys_security_form_token_lifetime');
        if($oSession->getValue('csrf_token') === false || ($iCsrfTokenLifetime != 0 && time() - (int)$oSession->getValue('csrf_token_time') > $iCsrfTokenLifetime)) {
            $sToken = genRndPwd(20, true);
            $oSession->setValue('csrf_token', $sToken);
            $oSession->setValue('csrf_token_time', time());
        } else
            $sToken = $oSession->getValue('csrf_token');

        if($bReturn)
            return $sToken;
    }

    public static function getCsrfToken()
    {
        $oSession = BxDolSession::getInstance();
        return $oSession->getValue('csrf_token');
    }

    function getCsrfTokenTime()
    {
        $oSession = BxDolSession::getInstance();
        return $oSession->getValue('csrf_token_time');
    }
}

class BxDolFormChecker
{
    var $_oChecker;
    var $_sFormMethod;
    var $_bFormCsrfChecking;

    function __construct ($sHelper = '')
    {
        $this->_sFormMethod = BX_DOL_FORM_METHOD_GET;
        $this->_bFormCsrfChecking = true;

        $sCheckerName = !empty($sHelper) ? $sHelper : 'BxDolFormCheckerHelper';
        $this->_oChecker = new $sCheckerName();
    }

    function setFormMethod($sMethod)
    {
        $this->_sFormMethod = $sMethod;
    }

    function enableFormCsrfChecking($bFormCsrfChecking)
    {
        $this->_bFormCsrfChecking = $bFormCsrfChecking;
    }

    // check function
    function check (&$aInputs)
    {
        $oChecker = $this->_oChecker;
        $iErrors = 0;

        // check CSRF token if it's needed.
        if($GLOBALS['MySQL']->getParam('sys_security_form_token_enable') == 'on' && !defined('BX_DOL_CRON_EXECUTE') && $this->_bFormCsrfChecking === true && ($mixedCsrfTokenSys = BxDolForm::getCsrfToken()) !== false) {
            $mixedCsrfTokenUsr = BxDolForm::getSubmittedValue('csrf_token', $this->_sFormMethod);
            unset($aInputs['csrf_token']);

            if($mixedCsrfTokenUsr === false || $mixedCsrfTokenSys != $mixedCsrfTokenUsr)
                return false;
        }

        foreach ($aInputs as $k => $a) {
            $a['name'] = str_replace('[]', '', $a['name']);
            $val = BxDolForm::getSubmittedValue($a['name'], $this->_sFormMethod);
            if($val === false)
                $val = isset($_FILES[$a['name']]) ? $_FILES[$a['name']] : '';

            if (!isset ($a['checker']))  {
                if ($a['type'] != 'checkbox' && $a['type'] != 'submit')
                    $aInputs[$k]['value'] = $_FILES[$a['name']] ? '' : $val;
                continue;
            }

            $sCheckFunction = array($oChecker, 'check'.ucfirst($a['checker']['func']));

            if (is_callable($sCheckFunction))
                $bool = call_user_func_array ($sCheckFunction, $a['checker']['params'] ? array_merge(array($val), $a['checker']['params']) : array ($val));
            else
                $bool = true;

            if (is_string($bool)) {
                ++$iErrors;
                $aInputs[$k]['error'] = $bool;
            } elseif (!$bool) {
                ++$iErrors;
                $aInputs[$k]['error'] = $a['checker']['error'];
            }
            $aInputs[$k]['value'] = $_FILES[$a['name']] ? '' : $val;
        }

        // check for spam
        if (!$iErrors && ('on' == getParam('sys_uridnsbl_enable') || 'on' == getParam('sys_akismet_enable'))) {

            foreach ($aInputs as $k => $a) {

                if ($a['type'] != 'textarea')
                    continue;

                $a['name'] = str_replace('[]', '', $a['name']);
                $val = BxDolForm::getSubmittedValue($a['name'], $this->_sFormMethod);
                if (!$val)
                    continue;

                if ($oChecker->checkNoSpam($val))
                    continue;

                ++$iErrors;
                $aInputs[$k]['error'] = sprintf(_t("_sys_spam_detected"), BX_DOL_URL_ROOT . 'contact.php');

            }
        }

        return $iErrors ? false : true;
    }

    // get clean value from GET/POST
    function get ($sName, $sPass = 'Xss', $aParams = array(), $sType = '')
    {
        if (!$sPass)
            $sPass = 'Xss';
        $this->_oChecker;
        $val = BxDolForm::getSubmittedValue($sName, $this->_sFormMethod);
        $mixedVal = call_user_func_array (array($this->_oChecker, 'pass'.ucfirst($sPass)), $aParams ? array_merge(array($val), $aParams) : array ($val));
        if (is_array($mixedVal) && 'select_multiple' == $sType)
            $mixedVal = serialize($mixedVal);
        return $mixedVal;
    }

    // db functions
    function serializeDbValues (&$aInputs, &$aValsToAdd)
    {
        $oChecker = $this->_oChecker;
        $s = '';
        foreach ($aInputs as $k => $a) {
            if (!isset ($a['db'])) continue;
            $valClean = $this->get ($a['name'], $a['db']['pass'], $a['db']['params'] ? $a['db']['params'] : array(), $a['type']);
            $s .= "`{$a['name']}` = '$valClean',";
            $aInputs[$k]['db']['value'] = $valClean;
        }
        foreach ($aValsToAdd as $k => $val) {
            $s .= "`{$k}` = '$val',";
        }
        return $s ? substr ($s, 0, -1) : '';
    }

    function dbInsert (&$aDb, &$aInputs, $aValsToAdd = array())
    {
        if (!$aDb['table']) return '';
        $sFields = $this->serializeDbValues ($aInputs, $aValsToAdd);
        if (!$sFields) return '';
        return "INSERT INTO `{$aDb['table']}` SET $sFields";
    }

    function dbUpdate ($val, &$aDb, &$aInputs, $aValsToAdd = array())
    {
        if (!$aDb['table'] || !$aDb['key']) return '';
        $sFields = $this->serializeDbValues ($aInputs, $aValsToAdd);
        if (!$sFields) return '';
        return "UPDATE `{$aDb['table']}` SET $sFields WHERE `{$aDb['key']}` = '$val'";
    }

    function fillWithValues (&$aInputs, &$aValues)
    {
        foreach ($aInputs as $k => $a) {
            if (!isset($aValues[$k])) continue;
            $sMethod = 'display'.ucfirst($a['db']['pass']);
            if (method_exists($this->_oChecker, $sMethod))
                $aInputs[$k]['value'] = call_user_func_array (array($this->_oChecker, $sMethod), $a['db']['params'] ? array_merge(array($aValues[$k]), $a['db']['params']) : array ($aValues[$k]));
            else
                $aInputs[$k]['value'] = $aValues[$k];

            if ($a['type'] == 'select_box')
                $aInputs[$k]['value'] = explode (';', $aInputs[$k]['value']);
            elseif ($a['type'] == 'select_multiple')
                $aInputs[$k]['value'] = @unserialize($aInputs[$k]['value']);
        }
    }
}

class BxDolFormCheckerHelper
{
    // check functions - check values for limits or patterns

    public static function checkLength ($s, $iLenMin, $iLenMax)
    {
        if (is_array($s)) {
            foreach ($s as $k => $v) {
                $iLen = get_mb_len ($v);
                if ($iLen < $iLenMin || $iLen > $iLenMax)
                    return false;
            }
            return true;
        }
        $iLen = get_mb_len ($s);
        return $iLen >= $iLenMin && $iLen <= $iLenMax ? true : false;
    }

    public static function checkDate ($s)
    {
        return self::checkPreg ($s, '#^\d+\-\d+\-\d+$#');
    }

    public static function checkDateTime ($s)
    {
        // remove unnecessary opera's input value;
        $s = str_replace('T', ' ', $s);
        $s = str_replace('Z', ':00', $s);

        return self::checkPreg ($s, '#^\d+\-\d+\-\d+[\sT]{1}\d+:\d+$#');
    }

    public static function checkPreg ($s, $r)
    {
        if (is_array($s)) {
            foreach ($s as $k => $v)
                if (!preg_match($r, $v))
                    return false;
            return true;
        }
        return preg_match($r, $s) ? true : false;
    }

    public static function checkAvail ($s)
    {
        if (is_array($s)) {
            return !self::_isEmptyArray($s);
        }
        return $s ? true : false;
    }

    public static function checkEmail($s)
    {
        return filter_var($s, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function checkCaptcha($s)
    {
        // init captcha object
        bx_import('BxDolCaptcha');
        $oCaptcha = BxDolCaptcha::getObjectInstance();
        if (!$oCaptcha)
            return false;

        // try to get "cached" value
        bx_import('BxDolSession');
        $oSession = BxDolSession::getInstance();
        $sSessKey = 'captcha-' . $oCaptcha->getUserResponse();
        if ($iSessVal = $oSession->getValue($sSessKey)) {
            $oSession->setValue($sSessKey, --$iSessVal);
            return true;
        }

        // perform captcha check
        if (!$oCaptcha->check ())
            return false;

        // "cache" success result (need for repeated AJAX submittions, since origonal captcha can't perform duplicate checking)
        bx_import('BxDolSession');
        $oSession = BxDolSession::getInstance();
        $oSession->setValue($sSessKey, 3);

        return true;
    }

    public static function checkNoSpam($val)
    {
        return !bx_is_spam($val);
    }

    // pass functions, prepare values to insert to database
    public static function passInt ($s)
    {
        if (is_array($s)) {
            $a = array ();
            foreach ($s as $k => $v) {
                $a[$k] = (int)trim($v);
            }
            return $a;
        }
        return (int)$s;
    }

    public static function passFloat ($s)
    {
        if (is_array($s)) {
            $a = array ();
            foreach ($s as $k => $v) {
                $a[$k] = (float)$v;
            }
            return $a;
        }
        return (float)$s;
    }

    public static function passDate ($s)
    {
        if (is_array($s)) {
            $a = array ();
            foreach ($s as $k => $v) {
                $a[$k] = self::_passDate ($v);
            }
            return $a;
        }
        return self::_passDate ($s);
    }

    public static function passDateUTC ($s)
    {
        if (is_array($s)) {
            $a = array ();
            foreach ($s as $k => $v) {
                $a[$k] = self::_passDate ($v, 'gmmktime');
            }
            return $a;
        }
        return self::_passDate ($s, 'gmmktime');
    }

    public static function _passDate ($s, $sFunc = 'mktime')
    {
        list($iYear, $iMonth, $iDay) = explode( '-', $s);
        $iDay   = (int)$iDay;
        $iMonth = (int)$iMonth;
        $iYear  = (int)$iYear;
        $iRet = $sFunc (0, 0, 0, $iMonth, $iDay, $iYear);
        return $iRet > 0 ? $iRet : 0;
    }

    public static function passDateTime ($s)
    {
        if (is_array($s)) {
            $a = array ();
            foreach ($s as $k => $v) {
                $a[$k] = self::_passDateTime ($v);
            }
            return $a;
        }
        return self::_passDateTime ($s);
    }

    public static function passDateTimeUTC ($s)
    {
        if (is_array($s)) {
            $a = array ();
            foreach ($s as $k => $v) {
                $a[$k] =self::_passDateTime ($v, 'gmmktime');
            }
            return $a;
        }
        return self::_passDateTime ($s, 'gmmktime');
    }

    public static function _passDateTime ($s, $sFunc = 'mktime')
    {
        if (preg_match('#(\d+)\-(\d+)\-(\d+)[\sT]{1}(\d+):(\d+)#', $s, $m)) {
            $iDay   = $m[3];
            $iMonth = $m[2];
            $iYear  = $m[1];
            $iH = $m[4];
            $iM = $m[5];
            $iRet = $sFunc ($iH, $iM, 0, $iMonth, $iDay, $iYear);
            return $iRet > 0 ? $iRet : 0;
        }
        return self::passDate ($s);
    }

    public static function passXss ($s)
    {
        if (is_array($s)) {
            $a = array ();
            foreach ($s as $k => $v) {
                $a[$k] = process_db_input ($v, BX_TAGS_STRIP);
            }
            return $a;
        }
        return process_db_input ($s, BX_TAGS_STRIP);
    }

    public static function passXssHtml ($s)
    {
        if (is_array($s)) {
            $a = array ();
            foreach ($s as $k => $v) {
                $a[$k] = process_db_input ($v, BX_TAGS_VALIDATE);
            }
            return $a;
        }
        return process_db_input ($s, BX_TAGS_VALIDATE);
    }

    public static function passAll ($s)
    {
        if (is_array($s)) {
            $a = array ();
            foreach ($s as $k => $v) {
                $a[$k] = process_db_input ($v, BX_TAGS_NO_ACTION);
            }
            return $a;
        }
        return process_db_input ($s, BX_TAGS_NO_ACTION);
    }

    public static function passPreg ($s, $r)
    {
        if (is_array($s)) {
            $a = array ();
            foreach ($s as $k => $v) {
                $a[$k] = self::_passPreg ($v, $r);
            }
            return $a;
        }
        return self::_passPreg($s, $r);
    }

    public static function _passPreg ($s, $r)
    {
        if (preg_match ($r, $s, $m)) {
            return $m[1];
        }
        return '';
    }

    public static function passTags ($s)
    {
        if (is_array($s)) {
            $a = array ();
            foreach ($s as $k => $v) {
                $a[$k] = self::_passTags ($v);
            }
            return $a;
        }
        return self::_passTags($s);
    }

    public static function _passTags ($s)
    {
        $sTags = self::passXss ($s);
        $aTags = explodeTags($sTags);
        return implode(",", $aTags);
    }

    public static function passCategories ($aa)
    {
        if (is_array($aa)) {
            $a = array ();
            foreach ($aa as $k => $v)
                if ($v)
                    $a[$k] = self::passXss ($v);
        } else {
            $a = self::passXss ($aa);
        }
        return is_array($a) ? implode(CATEGORIES_DIVIDER, $a) : $a;

    }

    public static function passBoolean ($s)
    {
        if (is_array($s)) {
            $a = array ();
            foreach ($s as $k => $v) {
                $a[$k] = $v == 'on' ? true : false;
            }
            return $a;
        }
        return $s == 'on' ? true : false;
    }

    // display functions, prepare values to output to the screen
    public static function displayDate ($i)
    {
        return date("Y-m-d", $i);
    }

    public static function displayDateTime ($i)
    {
        return date("Y-m-d H:i", $i);
    }

    public static function displayDateUTC ($i)
    {
        return gmdate("Y-m-d", $i);
    }

    public static function displayDateTimeUTC ($i)
    {
        return gmdate("Y-m-d H:i", $i);
    }

    // for internal usage only
    public static function _isEmptyArray ($a)
    {
        if (!is_array($a))
            return true;
        if (empty($a))
            return true;
        foreach ($a as $k => $v)
            if ($v)
                return false;
        return true;
    }
}
