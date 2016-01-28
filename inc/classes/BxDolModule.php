<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

/**
 * Base class for Module classes in modules engine.
 *
 * The object of the class contains major objects of the whole module. They are:
 * a. An object of config class
 * @see BxDolConfig
 *
 * b. An object of database class.
 * @see BxDolModuleDb
 *
 * c. An object of template class.
 * @see BxDolTemplate
 *
 *
 * Example of usage:
 * @see any module included in the default Dolphin's package.
 *
 *
 * Static Methods:
 *
 * Get an instance of a module's class.
 * @see BxDolModule::getInstance($sClassName)
 *
 *
 * Memberships/ACL:
 * Doesn't depend on user's membership.
 *
 *
 * Alerts:
 * no alerts available
 *
 */
class BxDolModule
{
    var $_aModule;

    var $_oDb;

    var $_oTemplate;

    var $_oConfig;

    /**
     * constructor
     */
    function __construct($aModule)
    {
        $this->_aModule = $aModule;

        $sClassPrefix = $aModule['class_prefix'];
        $sClassPath = BX_DIRECTORY_PATH_MODULES . $aModule['path'] . 'classes/';

        $sClassName = $sClassPrefix . 'Config';
        require_once($sClassPath . $sClassName . '.php');
        $this->_oConfig = new $sClassName($aModule);

        $sClassName = $sClassPrefix . 'Db';
        require_once($sClassPath . $sClassName . '.php');
        $this->_oDb = new $sClassName($this->_oConfig);

        $sClassName = $sClassPrefix . 'Template';
        require_once($sClassPath . $sClassName . '.php');
        $this->_oTemplate = new $sClassName($this->_oConfig, $this->_oDb);
        $this->_oTemplate->loadTemplates();
    }

    /**
     * Static method to get an instance of a module's class.
     *
     * NOTE. The prefered usage is to get an instance of [ClassPrefix]Module class.
     * But if it's needed an instance of class which has constructor without parameters
     * or with one parameter(an array with module's info) it can be retrieved.
     *
     * @param $sClassName module's class name.
     */
    public static function getInstance($sClassName)
    {
        if(empty($sClassName))
            return null;

        if(isset($GLOBALS['bxDolClasses'][$sClassName]))
           return $GLOBALS['bxDolClasses'][$sClassName];
        else {
            $aModule = db_arr("SELECT * FROM `sys_modules` WHERE INSTR('" . $sClassName . "', `class_prefix`)=1 LIMIT 1");
            if(empty($aModule) || !is_array($aModule)) return null;

            $sClassPath = BX_DIRECTORY_PATH_MODULES . $aModule['path'] . '/classes/' . $sClassName . '.php';
            if(!file_exists($sClassPath)) return null;

            require_once($sClassPath);
            $GLOBALS['bxDolClasses'][$sClassName] = new $sClassName($aModule);
            return $GLOBALS['bxDolClasses'][$sClassName];
        }
    }
    /**
     * Check whether user logged in or not.
     *
     * @return boolean result of operation.
     */
    function isLogged()
    {
        return isLogged();
    }
    /**
     * Get currently logged in user ID.
     *
     * @return integer user ID.
     */
    function getUserId()
    {
        return getLoggedId();
    }
    /**
     * Get currently logged in user password.
     *
     * @return string user password.
     */
    function getUserPassword ()
    {
        return getLoggedPassword();
    }

    public static function getTitle($sUri)
    {
        return _t(BxDolModule::getTitleKey($sUri));
    }

    function getTitleKey($sUri)
    {
        return '_sys_module_' . strtolower(str_replace(' ', '_', $sUri));
    }

	function serviceGetBaseUrl()
    {
        return BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri();
    }
}
