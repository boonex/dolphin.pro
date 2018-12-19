<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

ini_set('pcre.backtrack_limit', 1000000);

define('BX_DOL_TEMPLATE_DEFAULT_CODE', 'uni');
define('BX_DOL_TEMPLATE_FOLDER_ROOT', 'templates');
define('BX_DOL_TEMPLATE_FOLDER_BASE', 'base');

define('BX_DOL_TEMPLATE_INJECTIONS_CACHE', 'sys_injections.inc');

define('BX_DOL_TEMPLATE_CHECK_IN_BOTH', 'both');
define('BX_DOL_TEMPLATE_CHECK_IN_BASE', 'base');
define('BX_DOL_TEMPLATE_CHECK_IN_TMPL', 'tmpl');

/**
 * Template engine.
 *
 * An object of the class allows to:
 *  1. Manage HTML templates.
 *  2. Get URL/path for any template image/icon.
 *  3. Attach CSS/JavaScript files to the output.
 *  4. Add some content to any template key using Injection engine.
 *
 *
 * Avalable constructions.
 *  1. <bx_include_auto:template_name.html /> - the content of the file would be inserted. File would be taken from current template if it existes there, and from base directory otherwise.
 *  2. <bx_include_base:template_name.html /> - the content of the file would be inserted. File would be taken from base directory.
 *  3. <bx_include_tmpl:template_name.html /> - the content of the file would be inserted. File would be taken from tmpl_xxx directory.
 *  4. <bx_url_root /> - the value of $GLOBALS['site']['url'] variable will be inserted.
 *  5. <bx_url_admin /> - the value of $GLOBALS['site']['url_admin'] variable will be inserted.
 *  6. <bx_text:_language_key /> - _language_key will be translated using language file(function _t()) and inserted.
 *  7. <bx_text_js:_language_key /> - _language_key will be translated using language file(function _t()) and inserted, use it to insert text into js string.
 *  8. <bx_text_attribute:_language_key /> - _language_key will be translated using language file(function _t()) and inserted, use it to insert text into html attribute.
 *  8. <bx_image_url:image_file_name /> - image with 'image_file_name' file name will be searched in the images folder of current template.
 *     If it's not found, then it will be searched in the images folder of base template. On success full URL will be inserted, otherwise an empty string.
 *  9. <bx_icon_url:icon_file_name /> - the same with <bx_image_url:image_file_name />, but icons will be searched in the images/icons/ folders.
 *  10. <bx_injection:injection_name /> - will be replaced with injections registered with the page and injection_name in the `sys_injections`/`sys_injections_admin`/ tables.
 *  11. <bx_if:tag_name>some_HTML</bx_if:tag_name> - will be replaced with provided content if the condition is true, and with empty string otherwise.
 *  12. <bx_repeat:cycle_name>some_HTML</bx_repeat:cycle_name> - an inner HTML content will be repeated in accordance with received data.
 *
 *
 * Related classes:
 *  BxDolTemplateAdmin - for processing admin templates.
 *  Template classes in modules - for processing modiles' templates.
 *
 *
 * Global variables:
 *  oSysTemplate - is used for template processing in user part.
 *  oAdmTemplate - is used for template processing in admin part.
 *
 *
 * Add injection:
 *  1. Register it in the `sys_injections` table or `sys_injections_admin` table for admin panel.
 *  2. Clear injections cache(sys_injections.inc and sys_injections_admin.inc in cache folder).
 *
 *
 * Predefined template keys to add injections:
 *  1. injection_head - add injections in the <head> tag.
 *  2. injection_body - add ingection(attribute) in the <body> tag.
 *  3. injection_header - add injection inside the <body> tag at the very beginning.
 *  4. injection_logo_before - add injection at the left of the main logo(inside logo's DIV).
 *  5. injection_logo_after - add injection at the right of the main logo(inside logo's DIV).
 *  6. injection_between_logo_top_menu - add injection between logo and top menu.
 *  7. injection_top_menu_before - add injection at the left of the top menu(inside top menu's DIV).
 *  8. injection_top_menu_after - add injection at the right of the top menu(inside top menu's DIV).
 *  9. injection_between_top_menu_breadcrumb - add injection between top menu and breadcrumb.
 *  10. injection_breadcrumb_before - add injection at the left of the breadcrumb(inside breadcrumb's DIV).
 *  11. injection_breadcrumb_after - add injection at the right of the breadcrumb(inside breadcrumb's DIV).
 *  12. injection_between_breadcrumb_content - add injection between breadcrumb and main content.
 *  13. injection_content_before - add injection just before main content(inside content's DIV).
 *  14. injection_content_after - add injection just after main content(inside content's DIV).
 *  15. injection_between_content_footer - add injection between content and footer.
 *  16. injection_footer_before - add injection at the left of the footer(inside footer's DIV).
 *  17. injection_footer_after - add injection at the right of the footer(inside footer's DIV).
 *  18. injection_footer - add injection inside the <body> tag at the very end.
 *
 *
 * Example of usage:
 *  global $oSysTemplate;
 *
 *  $oSysTemplate->addCss(array('test1.css', 'test2.css'));
 *  $oSysTemplate->addJs(array('test1.js', 'test2.js'));
 *  $oSysTemplate->parseHtmlByName('messageBox.html', array(
 *    'id' => $iId,
 *     'msgText' => $sText,
 *     'bx_if:timer' => array(
 *        'condition' => $iTimer > 0,
 *        'content' => array(
 *           'id' => $iId,
 *           'time' => 1000 * $iTimer,
 *           'on_close' => $sOnClose,
 *        )
 *     ),
 *     'bx_if:timer' => array(
 *        array(
 *           'name' => $sName,
 *           'title' => $sTitle
 *        ),
 *        array(
 *           'name' => $sName,
 *           'title' => $sTitle
 *        )
 *     )
 *  ));
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
class BxDolTemplate
{
    /**
     * Main fields
     */
    var $_sPrefix;
    var $_sRootPath;
    var $_sRootUrl;
    var $_sInjectionsTable;
    var $_sInjectionsCache;
    var $_sCode;
    var $_sCodeKey;
    var $_sKeyWrapperHtml;
    var $_sFolderHtml;
    var $_sFolderCss;
    var $_sFolderImages;
    var $_sFolderIcons;
    var $_aTemplates;

    var $_aLocations;
    var $_aLocationsJs;

    /**
     * Cache related fields
     */
    var $_bCacheEnable;
    var $_sCacheFolderUrl;
    var $_sCachePublicFolderUrl;
    var $_sCachePublicFolderPath;
    var $_sCacheFilePrefix;
    var $_bImagesInline;
    var $_iImagesMaxSize;
    var $_bCssCache;
    var $_bCssArchive;
    var $_sCssCachePrefix;
    var $_bJsCache;
    var $_bJsArchive;
    var $_sJsCachePrefix;

    /**
     * Constructor
     */
    function __construct($sRootPath = BX_DIRECTORY_PATH_ROOT, $sRootUrl = BX_DOL_URL_ROOT)
    {
        $this->_sPrefix = 'BxDolTemplate';

        $this->_sRootPath = $sRootPath;
        $this->_sRootUrl = $sRootUrl;
        $this->_sInjectionsTable = 'sys_injections';
        $this->_sInjectionsCache = BX_DOL_TEMPLATE_INJECTIONS_CACHE;

        $this->_sCodeKey = 'skin';
        $this->_sCode = $GLOBALS['MySQL']->getParam('template');
        if(empty($this->_sCode))
            $this->_sCode = BX_DOL_TEMPLATE_DEFAULT_CODE;

        //--- Check selected template in COOKIE(the lowest priority) ---//
        $sCode = empty($_COOKIE[$this->_sCodeKey]) ? '' : $_COOKIE[$this->_sCodeKey];
        if (!empty($sCode) && preg_match('/^[A-Za-z0-9_-]+$/', $sCode) && file_exists($this->_sRootPath . 'templates/tmpl_' . $sCode) && !is_file($this->_sRootPath . 'templates/tmpl_' . $sCode))
            $this->_sCode = $sCode;

        //--- Check selected template in GET(the highest priority) ---//
        $sCode = empty($_GET[$this->_sCodeKey]) ? '' : $_GET[$this->_sCodeKey];
        if(!empty($sCode) && preg_match('/^[A-Za-z0-9_-]+$/', $sCode) && file_exists($this->_sRootPath . 'templates/tmpl_' . $sCode) && !is_file($this->_sRootPath . 'templates/tmpl_' . $sCode)) {
            $this->_sCode = $sCode;

            $aUrl = parse_url($GLOBALS['site']['url']);
            $sPath = isset($aUrl['path']) && !empty($aUrl['path']) ? $aUrl['path'] : '/';

            if (!bx_get('preview'))
                setcookie( $this->_sCodeKey, $this->_sCode, time() + 60*60*24*365, $sPath);

            if (isset($_GET[$this->_sCodeKey])) {
                bx_import('BxDolPermalinks');
                $oPermalinks = new BxDolPermalinks();
                if ($oPermalinks->redirectIfNecessary(array($this->_sCodeKey)))
                    exit;
            }

        }

        $this->_sKeyWrapperHtml = '__';
        $this->_sFolderHtml = '';
        $this->_sFolderCss = 'css/';
        $this->_sFolderImages = 'images/';
        $this->_sFolderIcons = 'images/icons/';
        $this->_aTemplates = array();

        $this->addLocation('system', $this->_sRootPath, $this->_sRootUrl);

        $this->addLocationJs('system_inc_js', BX_DIRECTORY_PATH_INC . 'js/' , BX_DOL_URL_ROOT . 'inc/js/');
        $this->addLocationJs('system_inc_js_classes', BX_DIRECTORY_PATH_INC . 'js/classes/' , BX_DOL_URL_ROOT . 'inc/js/classes/');
        $this->addLocationJs('system_plugins', BX_DIRECTORY_PATH_PLUGINS, BX_DOL_URL_PLUGINS);
        $this->addLocationJs('system_plugins_jquery', BX_DIRECTORY_PATH_PLUGINS . 'jquery/' , BX_DOL_URL_PLUGINS . 'jquery/');
        $this->addLocationJs('system_plugins_tinymce', BX_DIRECTORY_PATH_PLUGINS . 'tiny_mce/' , BX_DOL_URL_PLUGINS . 'tiny_mce/');

        $this->_bCacheEnable = !defined('BX_DOL_CRON_EXECUTE') && getParam('sys_template_cache_enable') == 'on';
        $this->_sCacheFolderUrl = '';
        $this->_sCachePublicFolderUrl = BX_DOL_URL_CACHE_PUBLIC;
        $this->_sCachePublicFolderPath = BX_DIRECTORY_PATH_CACHE_PUBLIC;
        $this->_sCacheFilePrefix = "bx_templ_";

        $this->_bImagesInline = getParam('sys_template_cache_image_enable') == 'on';
        $this->_iImagesMaxSize = (int)getParam('sys_template_cache_image_max_size') * 1024;

        $bArchive = getParam('sys_template_cache_compress_enable') == 'on';
        $this->_bCssCache = !defined('BX_DOL_CRON_EXECUTE') && getParam('sys_template_cache_css_enable') == 'on';
        $this->_bCssArchive = $this->_bCssCache && $bArchive;
        $this->_sCssCachePrefix = $this->_sCacheFilePrefix . 'css_';

        $this->_bJsCache = !defined('BX_DOL_CRON_EXECUTE') && getParam('sys_template_cache_js_enable') == 'on';
        $this->_bJsArchive = $this->_bJsCache && $bArchive;
        $this->_sJsCachePrefix = $this->_sCacheFilePrefix . 'js_';
    }
    /**
     * Load templates.
     *
     */
    function loadTemplates()
    {
        $aResult = array();
        foreach($this->_aTemplates as $sName)
            $aResult[$sName] = $this->getHtml($sName . '.html');
        $this->_aTemplates = $aResult;
    }
    /**
     * Initialize template engine.
     * Note. The method is executed with the system, you shouldn't execute it in your subclasses.
     */
    function init()
    {
        //--- Load injection's cache ---//
        $oCache = $GLOBALS['MySQL']->getDbCacheObject();
        $aInjections = $oCache->getData($GLOBALS['MySQL']->genDbCacheKey($this->_sInjectionsCache));
        if (null === $aInjections) {
            $rInjections = db_res("SELECT `page_index`, `name`, `key`, `type`, `data`, `replace` FROM `" . $this->_sInjectionsTable . "` WHERE `active`='1'");
            while($aInjection = $rInjections->fetch())
                $aInjections['page_' . $aInjection['page_index']][$aInjection['key']][] = $aInjection;

            $oCache->setData ($GLOBALS['MySQL']->genDbCacheKey($this->_sInjectionsCache), $aInjections);
        }

        $GLOBALS[$this->_sPrefix . 'Injections'] = isset($GLOBALS[$this->_sPrefix . 'Injections']) ? array_merge_recursive ($GLOBALS[$this->_sPrefix . 'Injections'], $aInjections) : $aInjections;

        //--- Load page elements related static variables ---//
		$GLOBALS[$this->_sPrefix . 'PageKeywords'] = array();
        $GLOBALS[$this->_sPrefix . 'OG']  = array();

        $GLOBALS[$this->_sPrefix . 'Js'] = array();
        $GLOBALS[$this->_sPrefix . 'JsSystem'] = array();

        $GLOBALS[$this->_sPrefix . 'Css'] = array();
        $GLOBALS[$this->_sPrefix . 'CssSystem'] = array();
        $GLOBALS[$this->_sPrefix . 'CssStyles'] = array();
        $GLOBALS[$this->_sPrefix . 'CssAsync'] = array();

        $this->setPageWidth(getParam('main_div_width'));
        $this->setPageTitle('');
        $this->setPageMainBoxTitle('');
        $this->setPageDescription('');
    }

    /**
     * Add location in array of locations.
     * Note. Location is the path/url to folder where 'templates' folder is stored.
     *
     * @param string $sKey          - location's	unique key.
     * @param string $sLocationPath - location's path. For modules: '[path_to_dolphin]/modules/[vendor_name]/[module_name]/'
     * @param string $sLocationUrl  - location's url. For modules: '[url_to_dolphin]/modules/[vendor_name]/[module_name]/'
     */
    function addLocation($sKey, $sLocationPath, $sLocationUrl)
    {
        $this->_aLocations[$sKey] = array(
            'path' => $sLocationPath . BX_DOL_TEMPLATE_FOLDER_ROOT . DIRECTORY_SEPARATOR,
            'url' => $sLocationUrl . BX_DOL_TEMPLATE_FOLDER_ROOT . '/'
        );
    }
    /**
     * Add dynamic location.
     *
     * @param  string   $sLocationPath - location's path. For modules: '[path_to_dolphin]/modules/[vendor_name]/[module_name]/'
     * @param  string   $sLocationUrl  - location's url. For modules: '[url_to_dolphin]/modules/[vendor_name]/[module_name]/'
     * @return location key. Is needed to remove the location.
     */
    function addDynamicLocation($sLocationPath, $sLocationUrl)
    {
        $sLocationKey = time();
        $this->addLocation($sLocationKey, $sLocationPath, $sLocationUrl);

        return $sLocationKey;
    }
    /**
     * Remove location from array of locations.
     * Note. Location is the path/url to folder where templates are stored.
     *
     * @param string $sKey - location's	unique key.
     */
    function removeLocation($sKey)
    {
        if(isset($this->_aLocations[$sKey]))
           unset($this->_aLocations[$sKey]);
    }
    /**
     * Add JS location in array of JS locations.
     * Note. Location is the path/url to folder where JS files are stored.
     *
     * @param string $sKey          - location's	unique key.
     * @param string $sLocationPath - location's path. For modules: '[path_to_dolphin]/modules/[vendor_name]/[module_name]/js/'
     * @param string $sLocationUrl  - location's url. For modules: '[url_to_dolphin]/modules/[vendor_name]/[module_name]/js/'
     */
    function addLocationJs($sKey, $sLocationPath, $sLocationUrl)
    {
        $this->_aLocationsJs[$sKey] = array(
            'path' => $sLocationPath,
            'url' => $sLocationUrl
        );
    }
    /**
     * Add dynamic JS location.
     *
     * @param  string   $sLocationPath - location's path. For modules: '[path_to_dolphin]/modules/[vendor_name]/[module_name]/'
     * @param  string   $sLocationUrl  - location's url. For modules: '[url_to_dolphin]/modules/[vendor_name]/[module_name]/'
     * @return location key. Is needed to remove the location.
     */
    function addDynamicLocationJs($sLocationPath, $sLocationUrl)
    {
        $sLocationKey = time();
        $this->addLocationJs($sLocationKey, $sLocationPath, $sLocationUrl);

        return $sLocationKey;
    }
    /**
     * Remove JS location from array of locations.
     * Note. Location is the path/url to folder where templates are stored.
     *
     * @param string $sKey - JS location's	unique key.
     */
    function removeLocationJs($sKey)
    {
        if(isset($this->_aLocationsJs[$sKey]))
           unset($this->_aLocationsJs[$sKey]);
    }
    /**
     * Get request line key.
     *
     * @return string key.
     */
    function getCodeKey()
    {
        return $this->_sCodeKey;
    }
    /**
     * Get currently active template code.
     *
     * @return string template's code.
     */
    function getCode()
    {
        return isset($GLOBALS['iAdminPage']) && (int)$GLOBALS['iAdminPage'] == 1 ? BX_DOL_TEMPLATE_DEFAULT_CODE : $this->_sCode;
    }
    /**
     * Get page width.
     *
     * @return string with page width.
     */
    function getPageWidth()
    {
        return $GLOBALS[$this->_sPrefix . 'PageWidth'];
    }
    /**
     * Set page width.
     *
     * @param string $sWidth necessary page width.
     */
    function setPageWidth($sWidth)
    {
        $GLOBALS[$this->_sPrefix . 'PageWidth'] = $sWidth;

        $this->addCssStyle('.sys_main_page_width', array(
        	'max-width' => $GLOBALS[$this->_sPrefix . 'PageWidth']
        ));
    }
    /**
     * Set page title.
     *
     * @param string $sTitle necessary page title.
     */
    function setPageTitle($sTitle)
    {
        $GLOBALS[$this->_sPrefix . 'PageTitle'] = $sTitle;
    }
    /**
     * Set page's main box title.
     *
     * @param string $sTitle necessary page's main box title.
     */
    function setPageMainBoxTitle($sTitle)
    {
        $GLOBALS[$this->_sPrefix . 'PageMainBoxTitle'] = $sTitle;
    }
    /**
     * Set page description.
     *
     * @param string $sDescription necessary page description.
     */
    function setPageDescription($sDescription)
    {
        $GLOBALS[$this->_sPrefix . 'PageDescription'] = $sDescription;
    }
    /**
     * Add Option in JS output.
     *
     * @param mixed $mixedName option's name or an array of options' names.
     */
    function addJsOption($mixedName)
    {
        if(is_string($mixedName))
            $mixedName = array($mixedName);

        foreach($mixedName as $sName)
            $GLOBALS['BxDolTemplateJsOptions'][$sName] = $GLOBALS['MySQL']->getParam($sName);
    }
    /**
     * Add language translation for key in JS output.
     *
     * @param mixed $mixedKey language key or an array of keys.
     */
    function addJsTranslation($mixedKey)
    {
        if(is_string($mixedKey))
            $mixedKey = array($mixedKey);

        foreach($mixedKey as $sKey)
            $GLOBALS['BxDolTemplateJsTranslations'][$sKey] = _t($sKey, '{0}', '{1}');
    }
    /**
     * Add image in JS output.
     *
     * @param array $aImages an array of image descriptors.
     * The descriptor is a key/value pear in the array of descriptors.
     */
    function addJsImage($aImages)
    {
        if(!is_array($aImages))
            return;

        foreach($aImages as $sKey => $sFile) {
            $sUrl = $this->getImageUrl($sFile);
            if(empty($sUrl))
                continue;

            $GLOBALS['BxDolTemplateJsImages'][$sKey] = $sUrl;
        }
    }
    /**
     * Add icon in JS output.
     *
     * @param array $aIcons an array of icons descriptors.
     * The descriptor is a key/value pear in the array of descriptors.
     */
    function addJsIcon($aIcons)
    {
        if(!is_array($aIcons))
            return;

        foreach($aIcons as $sKey => $sFile) {
            $sUrl = $this->getIconUrl($sFile);
            if(empty($sUrl))
                continue;

            $GLOBALS[$this->_sPrefix . 'JsImages'][$sKey] = $sUrl;
        }
    }
	/**
	 * Add CSS style.
	 *
	 * @param string $sName CSS class name.
	 * @param string $sContent CSS class styles.
	 */
	function addCssStyle($sName, $sContent)
	{
		$GLOBALS[$this->_sPrefix . 'CssStyles'][$sName] = $sContent;
	}
    /**
     * Set page keywords.
     *
     * @param mixed  $mixedKeywords necessary page keywords(string - single keyword, array - an array of keywords).
     * @param string $sDevider      - string devider.
     */
    function addPageKeywords($mixedKeywords, $sDevider = ',')
    {
        if (!$mixedKeywords)
            return;

        if(is_string($mixedKeywords))
            $mixedKeywords = strpos($mixedKeywords, $sDevider) !== false ? explode($sDevider, $mixedKeywords) : array($mixedKeywords);

        foreach($mixedKeywords as $iKey => $sValue)
            $mixedKeywords[$iKey] = trim($sValue);

        $GLOBALS[$this->_sPrefix . 'PageKeywords'] = array_merge($GLOBALS[$this->_sPrefix . 'PageKeywords'], $mixedKeywords);
    }
    /**
     * Set page meta Open Graph info.
     * @param array $a open graph info, such as type, image, title, site_name
     * @param string $sNamespace namespace, by default 'og'
     */
    function setOpenGraphInfo($a, $sNamespace = 'og')
    {
        $GLOBALS[$this->_sPrefix . 'OG'][$sNamespace] = array_merge(isset($GLOBALS[$this->_sPrefix . 'OG'][$sNamespace]) ? $GLOBALS[$this->_sPrefix . 'OG'][$sNamespace] : array(), $a);
    }
    /**
     * Returns page meta info, like meta keyword, meta description, location, etc
     */
    function getMetaInfo()
    {
        $sRet = '';

        if (!empty($GLOBALS[$this->_sPrefix . 'PageKeywords']) && is_array($GLOBALS[$this->_sPrefix . 'PageKeywords']) && $GLOBALS[$this->_sPrefix . 'PageKeywords'])
            $sRet .= '<meta name="keywords" content="' . bx_html_attribute(implode(',', $GLOBALS[$this->_sPrefix . 'PageKeywords'])) . "\" />\n";
    
        if (!empty($GLOBALS[$this->_sPrefix . 'PageDescription']) && is_string($GLOBALS[$this->_sPrefix . 'PageDescription']))
            $sRet .= '<meta name="description" content="' . bx_html_attribute($GLOBALS[$this->_sPrefix . 'PageDescription']) . "\" />\n";

        if (!empty($GLOBALS[$this->_sPrefix . 'OG']))
            foreach ($GLOBALS[$this->_sPrefix . 'OG'] as $sNamespace => $a)
                foreach ($a as $k => $s)
                    $sRet .= '<meta property="' . ($sNamespace  ? $sNamespace . ':' : '') . $k . '" content="' . bx_html_attribute($s) . "\" />\n";

        return $sRet;
    }
    /**
     * Get template, which was loaded earlier.
     * @see method this->loadTemplates and field this->_aTemplates
     *
     * @param  string $sName - template name.
     * @return string template's content.
     */
    function getTemplate($sName)
    {
        return $this->_aTemplates[$sName];
    }
    /**
     * Get full URL for the icon.
     *
     * @param  string $sName    icon's file name.
     * @param  string $sCheckIn where the content would be searched(base, template, both)
     * @return string full URL.
     */
    function getIconUrl($sName, $sCheckIn = BX_DOL_TEMPLATE_CHECK_IN_BOTH)
    {
        $sContent = "";
        if(($sContent = $this->_getInlineData('icon', $sName, $sCheckIn)) !== false)
            return $sContent;

        return $this->_getAbsoluteLocation('url', $this->_sFolderIcons, $sName, $sCheckIn);
    }
    /**
     * Get absolute Path for the icon.
     *
     * @param  string $sName    - icon's file name.
     * @param  string $sCheckIn where the content would be searched(base, template, both)
     * @return string absolute path.
     */
    function getIconPath($sName, $sCheckIn = BX_DOL_TEMPLATE_CHECK_IN_BOTH)
    {
        return $this->_getAbsoluteLocation('path', $this->_sFolderIcons, $sName, $sCheckIn);
    }
    /**
     * Get full URL for the image.
     *
     * @param  string $sName    - images's file name.
     * @param  string $sCheckIn where the content would be searched(base, template, both)
     * @return string full URL.
     */
    function getImageUrl($sName, $sCheckIn = BX_DOL_TEMPLATE_CHECK_IN_BOTH)
    {
        $sContent = "";
        if(($sContent = $this->_getInlineData('image', $sName, $sCheckIn)) !== false)
            return $sContent;

        return $this->_getAbsoluteLocation('url', $this->_sFolderImages, $sName, $sCheckIn);
    }
    /**
     * Get absolute Path for the image.
     *
     * @param  string $sName    - image's file name.
     * @param  string $sCheckIn where the content would be searched(base, template, both)
     * @return string absolute path.
     */
    function getImagePath($sName, $sCheckIn = BX_DOL_TEMPLATE_CHECK_IN_BOTH)
    {
        return $this->_getAbsoluteLocation('path', $this->_sFolderImages, $sName, $sCheckIn);
    }
    /**
     * Get full URL of CSS file.
     *
     * @param  string $sName    - CSS file name.
     * @param  string $sCheckIn where the content would be searched(base, template, both)
     * @return string full URL.
     */
    function getCssUrl($sName, $sCheckIn = BX_DOL_TEMPLATE_CHECK_IN_BOTH)
    {
        return $this->_getAbsoluteLocation('url', $this->_sFolderCss, $sName, $sCheckIn);
    }
    /**
     * Get full Path of CSS file.
     *
     * @param  string $sName    - CSS file name.
     * @param  string $sCheckIn where the content would be searched(base, template, both)
     * @return string full URL.
     */
    function getCssPath($sName, $sCheckIn = BX_DOL_TEMPLATE_CHECK_IN_BOTH)
    {
        return $this->_getAbsoluteLocation('path', $this->_sFolderCss, $sName, $sCheckIn);
    }
    /**
     * Get content of HTML file.
     *
     * @param  string $sName    - HTML file name.
     * @param  string $sCheckIn where the content would be searched(base, template, both)
     * @return string full content of the file and false on failure.
     */
    function getHtml($sName, $sCheckIn = BX_DOL_TEMPLATE_CHECK_IN_BOTH)
    {
        $sAbsolutePath = $this->_getAbsoluteLocation('path', $this->_sFolderHtml, $sName, $sCheckIn);
        return !empty($sAbsolutePath) ? file_get_contents($sAbsolutePath) : false;
    }

    /**
     * Parse HTML template. Search for the template with accordance to it's file name.
     *
     * @see allows to use cache.
     *
     * @param  string $sName               - HTML file name.
     * @param  array  $aVariables          - key/value pairs. key should be the same as template's key, but without prefix and postfix.
     * @param  mixed  $mixedKeyWrapperHtml - key wrapper(string value if left and right parts are the same, array(left, right) otherwise).
     * @param  string $sCheckIn            where the content would be searched(base, template, both)
     * @return string the result of operation.
     */
    function parseHtmlByName($sName, $aVariables, $mixedKeyWrapperHtml = null, $sCheckIn = BX_DOL_TEMPLATE_CHECK_IN_BOTH)
    {
        if (isset($GLOBALS['bx_profiler'])) $GLOBALS['bx_profiler']->beginTemplate($sName, $sRand = time().rand());

        if (($sContent = $this->getCached($sName, $aVariables, $mixedKeyWrapperHtml, $sCheckIn)) !== false) {
            if (isset($GLOBALS['bx_profiler'])) $GLOBALS['bx_profiler']->endTemplate($sName, $sRand, $sRet, true);
            return $sContent;
        }

        $sRet = '';
        if (($sContent = $this->getHtml($sName, $sCheckIn)) !== false)
            $sRet = $this->_parseContent($sContent, $aVariables, $mixedKeyWrapperHtml);

        if (isset($GLOBALS['bx_profiler'])) $GLOBALS['bx_profiler']->endTemplate($sName, $sRand, $sRet, false);

        return $sRet;
    }
    /**
     * Parse HTML template.
     *
     * @see Doesn't allow to use cache.
     *
     * @param  string $sContent            - HTML file content.
     * @param  array  $aVariables          - key/value pairs. key should be the same as template's key, but without prefix and postfix.
     * @param  mixed  $mixedKeyWrapperHtml - key wrapper(string value if left and right parts are the same, array(left, right) otherwise).
     * @return string the result of operation.
     */
    function parseHtmlByContent($sContent, $aVariables, $mixedKeyWrapperHtml = null)
    {
        if(empty($sContent))
            return "";

        return $this->_parseContent($sContent, $aVariables, $mixedKeyWrapperHtml);
    }
    /**
     * Parse earlier loaded HTML template.
     *
     * @see Doesn't allow to use cache.
     *
     * @param  string $sName      - template name.
     * @param  array  $aVariables - key/value pairs. Key should be the same as template's key, excluding prefix and postfix.
     * @return string the result of operation.
     * @see $this->_aTemplates
     */
    function parseHtmlByTemplateName($sName, $aVariables, $mixedKeyWrapperHtml = null)
    {
        if(!isset($this->_aTemplates[$sName]) || empty($this->_aTemplates[$sName]))
            return "";

        return $this->_parseContent($this->_aTemplates[$sName], $aVariables, $mixedKeyWrapperHtml);
    }
    /**
     * Parse page HTML template. Search for the page's template with accordance to it's file name.
     *
     * @see allows to use cache.
     *
     * @param  string $sName      - HTML file name.
     * @param  array  $aVariables - key/value pairs. key should be the same as template's key, but without prefix and postfix.
     * @return string the result of operation.
     */
    function parsePageByName($sName, $aVariables)
    {
        if (isset($GLOBALS['bx_profiler'])) $GLOBALS['bx_profiler']->beginPage($sName);

        // add facebook meta tag
        if ($sFbId = getParam('bx_facebook_connect_api_key'))
            $this->setOpenGraphInfo(array('app_id' => $sFbId), 'fb');

        $sContent = $this->parseHtmlByName($sName, $aVariables, $this->_sKeyWrapperHtml, BX_DOL_TEMPLATE_CHECK_IN_BOTH);
        if(empty($sContent))
            $sContent = $this->parseHtmlByName('default.html', $aVariables, $this->_sKeyWrapperHtml, BX_DOL_TEMPLATE_CHECK_IN_BOTH);

        //--- Add CSS and JS at the very last ---//
        if(strpos($sContent, '<bx_include_css_styles />') !== false)
			$sContent = str_replace('<bx_include_css_styles />', $this->includeCssStyles(), $sContent);

        if(strpos($sContent , '<bx_include_css />') !== false) {
            if (!empty($GLOBALS['_page']['css_name'])) {
                $this->addCss($GLOBALS['_page']['css_name']);
            }
            $sContent = str_replace('<bx_include_css />', $this->includeFiles('css', true) . $this->includeFiles('css'), $sContent);
        }

        if(strpos($sContent , '<bx_include_js />') !== false) {
            if (!empty($GLOBALS['_page']['js_name'])) {
                $this->addJs($GLOBALS['_page']['js_name']);
            }
            $sContent = str_replace('<bx_include_js />', $this->includeFiles('js', true) . $this->includeFiles('js') . $this->includeCssAsync(), $sContent);
        }        

        if (isset($GLOBALS['bx_profiler'])) $GLOBALS['bx_profiler']->endPage($sContent);

        return $sContent;
    }
    /**
     * Parse system keys.
     *
     * @param  string $sKey key
     * @return string value associated with the key.
     */
    function parseSystemKey($sKey, $mixedKeyWrapperHtml = null)
    {
        global $site;
        global $_page;
        global $oFunctions;
        global $oTemplConfig;
        global $logged;

        $aKeyWrappers = $this->_getKeyWrappers($mixedKeyWrapperHtml);

        $sRet = '';
        switch( $sKey ) {
            case 'dir':
                $a = bx_lang_info();
                return $a['Direction'];
            case 'page_charset':
                $sRet = 'UTF-8';
                break;
            case 'meta_info':
                $sRet = $this->getMetaInfo();
                break;
            case 'page_header':
                if(!empty($GLOBALS[$this->_sPrefix . 'PageTitle']))
                    $sRet = $GLOBALS[$this->_sPrefix . 'PageTitle'];
                else if(isset($_page['header']))
                    $sRet = $_page['header'];

                //$sRet = process_line_output($sRet);
                break;
            case 'page_header_text':
                if(!empty($GLOBALS[$this->_sPrefix . 'PageMainBoxTitle']))
                    $sRet = $GLOBALS[$this->_sPrefix . 'PageMainBoxTitle'];
                else if(isset($_page['header_text']))
                    $sRet = $_page['header_text'];

                //$sRet = process_line_output($sRet);
                break;
            case 'main_div_width':
                if(!empty($GLOBALS[$this->_sPrefix . 'PageWidth']))
                    $sRet = process_line_output($GLOBALS[$this->_sPrefix . 'PageWidth']);
                break;
            case 'main_logo':
                $sRet = $GLOBALS['oFunctions']->genSiteLogo();
                break;
            case 'main_splash':
                $sRet = $GLOBALS['oFunctions']->genSiteSplash();
                break;
            case 'main_search':
            	$sRet = $GLOBALS['oFunctions']->genSiteSearch();
            	break;
            case 'service_menu':
            	$sRet = $GLOBALS['oFunctions']->genSiteServiceMenu();
            	break;
            case 'top_menu':
                $sRet = $GLOBALS['oTopMenu'] -> getCode();
                break;
            case 'top_menu_breadcrumb':
                $sRet = !empty($GLOBALS['oTopMenu']->sBreadCrumb) ? $GLOBALS['oTopMenu'] -> sBreadCrumb : $GLOBALS['oTopMenu']->genBreadcrumb();
                break;
            case 'extra_top_menu':
                $iProfileId = getLoggedId();

                if ($iProfileId && getParam('ext_nav_menu_enabled')) {
                    bx_import('BxTemplMemberMenu');
                    $oMemberMenu = new BxTemplMemberMenu();
                    $sRet = $oMemberMenu -> genMemberMenu($iProfileId);
                }
                break;
            case 'bottom_links':
                $sRet = $oFunctions -> genSiteBottomMenu();
                break;
            case 'switch_skin_block':
                $sRet = getParam("enable_template") ? templates_select_txt() : '';
                break;
            case 'dol_images':
                $sRet = $this->_processJsImages();
                break;
            case 'dol_lang':
                $sRet = $this->_processJsTranslations();
                break;
            case 'dol_options':
                $sRet = $this->_processJsOptions();
                break;
            case 'bottom_text':
                $sRet = _t( '_bottom_text', date('Y') );
                break;
            case 'copyright':
                $sRet = _t( '_copyright',   date('Y') ) . getVersionComment();
                break;
            case 'flush_header':
                //TODO: add some variable to disable it if needed
                //flush();
                break;
            case 'extra_js':
                $sRet = empty($_page['extra_js']) ? '' : $_page['extra_js'];
                break;
            case 'is_profile_page':
                $sRet = (defined('BX_PROFILE_PAGE')) ? 'true' : 'false';
                break;
            default:
                $sRet = ($sTemplAdd = $oFunctions->TemplPageAddComponent($sKey)) !== false ? $sTemplAdd : $aKeyWrappers['left'] . $sKey . $aKeyWrappers['right'];
            }

        $sRet = BxDolTemplate::processInjection($_page['name_index'], $sKey, $sRet);
        return $sRet;
    }
    /**
     * Get cache object for templates
     * @return cache class instance
     */
    function getTemplatesCacheObject ()
    {
        $sCacheEngine = getParam('sys_template_cache_engine');
        $oCacheEngine = bx_instance('BxDolCache' . $sCacheEngine);
        if(!$oCacheEngine->isAvailable())
            $oCacheEngine = bx_instance('BxDolCacheFileHtml');
        return $oCacheEngine;
    }
    /**
     * Get template from cache if it's enabled.
     *
     * @param  string  $sName               template name
     * @param  string  $aVariables          key/value pairs. key should be the same as template's key, but without prefix and postfix.
     * @param  mixed   $mixedKeyWrapperHtml - key wrapper(string value if left and right parts are the same, array(0 => left, 1 => right) otherwise).
     * @param  string  $sCheckIn            where the content would be searched(base, template, both)
     * @param  boolean $bEvaluate           need to evaluate the template or not.
     * @return string  result of operation or false on failure.
     */
    function getCached($sName, &$aVariables, $mixedKeyWrapperHtml = null, $sCheckIn = BX_DOL_TEMPLATE_CHECK_IN_BOTH, $bEvaluate = true)
    {
        // initialization

        if (!$this->_bCacheEnable)
            return false;

        $sAbsolutePath = $this->_getAbsoluteLocation('path', $this->_sFolderHtml, $sName, $sCheckIn);
        if (empty($sAbsolutePath))
            return false;

        $oCacheEngine = $this->getTemplatesCacheObject ();
        $isFileBasedEngine = $bEvaluate && method_exists($oCacheEngine, 'getDataFilePath');

        // try to get cached content

        $sCacheVariableName = "a";
        $sCacheKey = $this->_getCacheFileName('html', $sAbsolutePath) . '.php';
        if ($isFileBasedEngine)
            $sCacheContent = $oCacheEngine->getDataFilePath($sCacheKey);
        else
            $sCacheContent = $oCacheEngine->getData($sCacheKey);


        // recreate cache if it is empty

        if ($sCacheContent === null && ($sContent = file_get_contents($sAbsolutePath)) !== false && ($sContent = $this->_compileContent($sContent, "\$" . $sCacheVariableName, 1, $aVariables, $mixedKeyWrapperHtml)) !== false) {
            if (false === $oCacheEngine->setData($sCacheKey, $sContent))
                return false;

            if ($isFileBasedEngine)
                $sCacheContent = $oCacheEngine->getDataFilePath($sCacheKey);
            else
                $sCacheContent = $sContent;
        }

        if ($sCacheContent === null)
            return false;

        // return simple cache content

        if (!$bEvaluate)
            return $sCacheContent;

        // return evaluated cache content

        ob_start();

            $$sCacheVariableName = &$aVariables;

            if ($isFileBasedEngine)
                include($sCacheContent);
            else
                eval('?'.'>' . $sCacheContent);

        $sContent = ob_get_clean();

        return $sContent;
    }

    /**
     * Add JS file(s) to global output.
     *
     * @param  mixed          $mixedFiles string value represents a single JS file name. An array - array of JS file names.
     * @param  boolean        $bDynamic   in the dynamic mode JS file(s) are not included to global output, but are returned from the function directly.
     * @return boolean/string result of operation.
     */
    function addJs($mixedFiles, $bDynamic = false)
    {
        return $this->_processFiles('js', 'add', $mixedFiles, $bDynamic);
    }

	/**
     * Add System JS file(s) to global output. 
     * System JS files are the files which are attached to all pages. They will be cached separately from the others.
     *
     * @param mixed $mixedFiles string value represents a single JS file name. An array - array of JS file names.
     * @param boolean $bDynamic in the dynamic mode JS file(s) are not included to global output, but are returned from the function directly.
     * @return boolean/string result of operation.
     */
    function addJsSystem($mixedFiles) {
        return $this->_processFiles('js', 'add', $mixedFiles, false, true);
    }

    /**
     * Delete JS file(s) from global output.
     *
     * @param  mixed   $mixedFiles string value represents a single JS file name. An array - array of JS file names.
     * @return boolean result of operation.
     */
    function deleteJs($mixedFiles)
    {
        return $this->_processFiles('js', 'delete', $mixedFiles);
    }

	/**
     * Delete System JS file(s) from global output.
     *
     * @param mixed $mixedFiles string value represents a single JS file name. An array - array of JS file names.
     * @return boolean result of operation.
     */
    function deleteJsSystem($mixedFiles) {
        return $this->_processFiles('js', 'delete', $mixedFiles, false, true);
    }

    /**
     * Compile JS files in one file.
     *
     * @param  string $sAbsolutePath CSS file absolute path(full URL for external CSS/JS files).
     * @param  array  $aIncluded     an array of already included JS files.
     * @return string result of operation.
     */
    function _compileJs($sAbsolutePath, &$aIncluded)
    {
        if(isset($aIncluded[$sAbsolutePath]))
           return '';

        $bExternal = strpos($sAbsolutePath, "http://") !== false || strpos($sAbsolutePath, "https://") !== false;
        if($bExternal) {
            $sPath = $sAbsolutePath;
            $sName = '';

            $sContent = bx_file_get_contents($sAbsolutePath);
        } else {
            $aFileInfo = pathinfo($sAbsolutePath);
            $sPath = $aFileInfo['dirname'] . DIRECTORY_SEPARATOR;
            $sName = $aFileInfo['basename'];

            $sContent = file_get_contents($sPath . $sName);
        }

        if(empty($sContent))
            return '';

        $sUrl = bx_ltrim_str($sPath, realpath(BX_DIRECTORY_PATH_ROOT), BX_DOL_URL_ROOT);
        $sUrl = str_replace(DIRECTORY_SEPARATOR, '/', $sPath);

        $sContent = "\r\n/*--- BEGIN: " . $sUrl . $sName . "---*/\r\n" . $sContent . ";\r\n/*--- END: " . $sUrl . $sName . "---*/\r\n";
        $sContent = str_replace(array("\n\r", "\r\n", "\r"), "\n", $sContent);

        $aIncluded[$sAbsolutePath] = 1;

        return preg_replace(
            array(
                "'<bx_url_root />'",
                "'\r\n'"
            ),
            array(
                BX_DOL_URL_ROOT,
                "\n"
            ),
            $sContent
        );
    }
    /**
     * Wrap an URL to JS file into JS tag.
     *
     * @param  string $sFile - URL to JS file.
     * @return string the result of operation.
     */
    function _wrapInTagJs($sFile)
    {
        return "<script language=\"javascript\" type=\"text/javascript\" src=\"" . $sFile . "\"></script>";
    }
    /**
     * Wrap JS code into JS tag.
     *
     * @param  string $sCode - JS code.
     * @return string the result of operation.
     */
    function _wrapInTagJsCode($sCode)
    {
        return "<script language=\"javascript\" type=\"text/javascript\">\n<!--\n" . $sCode . "\n-->\n</script>";
    }

    /**
     * Add CSS file(s) to global output.
     *
     * @param  mixed          $mixedFiles string value represents a single CSS file name. An array - array of CSS file names.
     * @param  boolean        $bDynamic   in the dynamic mode CSS file(s) are not included to global output, but are returned from the function directly.
     * @return boolean/string result of operation
     */
    function addCss($mixedFiles, $bDynamic = false)
    {
        return $this->_processFiles('css', 'add', $mixedFiles, $bDynamic);
    }

    /**
     * Add additional heavy css file (not very necessary) to load asynchronously for desktop browsers only
     * @param  mixed          $mixedFiles string value represents a single CSS file name. An array - array of CSS file names.
     */
    function addCssAsync($mixedFiles)
    {
        if (!is_array($mixedFiles))
            $mixedFiles = array($mixedFiles);

        foreach ($mixedFiles as $sFile)
            $GLOBALS[$this->_sPrefix . 'CssAsync'][] = $this->_getAbsoluteLocationCss('url', $sFile);

        $this->addJs('loadCSS.js');
    }

    /**
     * Return script tag with special code to load async css.
     * This tag is added after js files list
     */
    function includeCssAsync ()
    {
        if (empty($GLOBALS[$this->_sPrefix . 'CssAsync']))
            return '';

        $GLOBALS[$this->_sPrefix . 'CssAsync'] = array_unique($GLOBALS[$this->_sPrefix . 'CssAsync']);        

        $sList = '';
        foreach ($GLOBALS[$this->_sPrefix . 'CssAsync'] as $sUrl)
            $sList .= 'loadCSS("' . $sUrl . '", document.getElementById("bx_css_async"));';

        // don't load css for mobile devices
        return '
            <script id="bx_css_async">
                if(!/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
                    ' . $sList . '
                }
            </script>
        ';
    }

	/**
     * Add System CSS file(s) to global output.
     * System CSS files are the files which are attached to all pages. They will be cached separately from the others.
     *
     * @param mixed $mixedFiles string value represents a single CSS file name. An array - array of CSS file names.
     * @return boolean/string result of operation
     */
    function addCssSystem($mixedFiles) {
        return $this->_processFiles('css', 'add', $mixedFiles, false, true);
    }

    /**
     * Delete CSS file(s) from global output.
     *
     * @param  mixed   $mixedFiles string value represents a single CSS file name. An array - array of CSS file names.
     * @return boolean result of operation.
     */
    function deleteCss($mixedFiles)
    {
        return $this->_processFiles('css', 'delete', $mixedFiles);
    }

	/**
     * Delete System CSS file(s) from global output.
     *
     * @param mixed $mixedFiles string value represents a single CSS file name. An array - array of CSS file names.
     * @return boolean result of operation.
     */
    function deleteCssSystem($mixedFiles){
        return $this->_processFiles('css', 'delete', $mixedFiles, false, true);
    }

    /**
     * Compile CSS files' structure(@see @import css_file_path) in one file.
     *
     * @param  string $sAbsolutePath CSS file absolute path(full URL for external CSS/JS files).
     * @param  array  $aIncluded     an array of already included CSS files.
     * @return string result of operation.
     */
    function _compileCss($sAbsolutePath, &$aIncluded)
    {
        if(isset($aIncluded[$sAbsolutePath]))
           return '';

        $bExternal = strpos($sAbsolutePath, "http://") !== false || strpos($sAbsolutePath, "https://") !== false;
        if($bExternal) {
            $sPath = $sAbsolutePath;
            $sName = '';

            $sContent = bx_file_get_contents($sAbsolutePath);
        } else {
            $aFileInfo = pathinfo($sAbsolutePath);
            $sPath = $aFileInfo['dirname'] . DIRECTORY_SEPARATOR;
            $sName = $aFileInfo['basename'];

            $sContent = file_get_contents($sPath . $sName);
        }

        if(empty($sContent))
            return '';

        $sUrl = bx_ltrim_str($sPath, realpath(BX_DIRECTORY_PATH_ROOT), BX_DOL_URL_ROOT);
        $sUrl = str_replace(DIRECTORY_SEPARATOR, '/', $sPath);

        $sContent = "\r\n/*--- BEGIN: " . $sUrl . $sName . "---*/\r\n" . $sContent . "\r\n/*--- END: " . $sUrl . $sName . "---*/\r\n";
        $aIncluded[$sAbsolutePath] = 1;

        $sContent = str_replace(array("\n\r", "\r\n", "\r"), "\n", $sContent);
        if($bExternal) {
            $sContent = preg_replace_callback(
                "'@import\s+url\s*\(\s*[\'|\"]*\s*([a-zA-Z0-9\.\/_-]+)\s*[\'|\"]*\s*\)\s*;'",
                function($matches) { return ''; },
                $sContent
            );

            $sContent = preg_replace_callback(
                "'url\s*\(\s*[\'|\"]*\s*([a-zA-Z0-9\.\/\?\#_=-]+)\s*[\'|\"]*\s*\)'",
                function($matches) use ($sPath) { return "url({$sPath}{$matches[1]});"; },
                $sContent
            );
        } else {
            $sContent = preg_replace_callback(
                "'@import\s+url\s*\(\s*[\'|\"]*\s*([a-zA-Z0-9\.\/_-]+)\s*[\'|\"]*\s*\)\s*;'",
                function($matches) use ($sPath) { return $this->_compileCss(realpath($sPath . dirname($matches[1])) . DIRECTORY_SEPARATOR . basename($matches[1]), $aIncluded); },
                $sContent
            );

            $sContent = preg_replace_callback(
                "'url\s*\(\s*[\'|\"]*\s*([a-zA-Z0-9\.\/\?\#_=-]+)\s*[\'|\"]*\s*\)'",
                function ($aMatches) use ($sPath) { 
                    return BxDolTemplate::_callbackParseUrl(addslashes($sPath), $aMatches);
                },
                $sContent
            );
        }
        return $sContent;
    }

    /**
     * Minify CSS
     *
     * @param  string $s CSS string to minify
     * @return string minified CSS string.
     */
    function _minifyCss($s)
    {
        require_once(BX_DIRECTORY_PATH_PLUGINS . 'minify/lib/Minify/CSS/Compressor.php');
        return Minify_CSS_Compressor::process($s);
    }

    /**
     * Private callback function for CSS compiler.
     *
     * @param  string $sPath    CSS file absolute path.
     * @param  array  $aMatches matched parts of image's URL.
     * @return string converted image's URL.
     */
    public static function _callbackParseUrl($sPath, $aMatches)
    {
        $sFile = basename($aMatches[1]);
        $sDirectory = dirname($aMatches[1]);

        $sRootPath = realpath(BX_DIRECTORY_PATH_ROOT);
        $sAbsolutePath = realpath($sPath . $sDirectory) . DIRECTORY_SEPARATOR . $sFile;

        $sRootPath = str_replace(DIRECTORY_SEPARATOR, '/', $sRootPath);
        $sAbsolutePath = str_replace(DIRECTORY_SEPARATOR, '/', $sAbsolutePath);

        return 'url(' . bx_ltrim_str($sAbsolutePath, $sRootPath, BX_DOL_URL_ROOT) . ')';
    }

    /**
     * Wrap an URL to CSS file into CSS tag.
     *
     * @param  string $sFile - URL to CSS file.
     * @return string the result of operation.
     */
    function _wrapInTagCss($sFile)
    {
        if (!$sFile)
            return '';
        return "<link href=\"" . $sFile . "\" rel=\"stylesheet\" type=\"text/css\" />";
    }
    /**
     * Wrap CSS code into CSS tag.
     *
     * @param  string $sCode - CSS code.
     * @return string the result of operation.
     */
    function _wrapInTagCssCode($sCode)
    {
        return "<style>" . $sCode . "</style>";
    }
	/*
     *  Include CSS style(s) in the page's head section.
     */
	function includeCssStyles()
	{
		$sResult = "";
		if(empty($GLOBALS[$this->_sPrefix . 'CssStyles']) || !is_array($GLOBALS[$this->_sPrefix . 'CssStyles']))
			return $sResult;

		foreach($GLOBALS[$this->_sPrefix . 'CssStyles'] as $sName => $aContent) {
			$sContent = "";
			if(!empty($aContent) && is_array($aContent))
				foreach($aContent as $sStyleName => $sStyleValue)
					$sContent .= "\t" . $sStyleName . ": " . $sStyleValue . ";\r\n";

			$sResult .= $sName . " {\r\n" . $sContent . "}\r\n";
		}

		return !empty($sResult) ? $this->_wrapInTagCssCode($sResult) : '';
	}
    /**
     * Include CSS/JS file(s) attached to the page in its head section.
     * @see the method is system and would be called automatically.
     *
     * @param  string $sType the type of file('js' or 'css')
     * @return string the result CSS code.
     */
    function includeFiles($sType, $bSystem = false)
    {
        $sUpcaseType = ucfirst($sType);

        $sArrayKey = $this->_sPrefix . $sUpcaseType . ($bSystem ? 'System' : '');
        $aFiles = isset($GLOBALS[$sArrayKey]) ? $GLOBALS[$sArrayKey] : array();
        if(empty($aFiles) || !is_array($aFiles))
            return "";

        if(!$this->{'_b' . $sUpcaseType . 'Cache'})
            return $this->_includeFiles($sType, $aFiles);

        //--- If cache already exists, return it ---//
        $sMethodWrap = '_wrapInTag' . $sUpcaseType;
        $sMethodCompile = '_compile' . $sUpcaseType;
        $sMethodMinify = '_minify' . $sUpcaseType;

        ksort($aFiles);

        $sName = "";
        foreach($aFiles as $aFile)
            $sName .= $aFile['url'];
        $sName = $this->_getCacheFileName($sType, $sName);

        $sCacheAbsoluteUrl = $this->_sCachePublicFolderUrl . $sName . '.' . $sType;
        $sCacheAbsolutePath = $this->_sCachePublicFolderPath . $sName . '.' . $sType;
        if(file_exists($sCacheAbsolutePath)) {
            if($this->{'_b' . $sUpcaseType . 'Archive'})
                $sCacheAbsoluteUrl = $this->_getLoaderUrl($sType, $sName);

           return $this->$sMethodWrap($sCacheAbsoluteUrl);
        }

        //--- Collect all attached CSS/JS in one file ---//
        $sResult = "";
        $aIncluded = array();
        foreach($aFiles as $aFile)
            if(($sContent = $this->$sMethodCompile($aFile['path'], $aIncluded)) !== false)
                $sResult .= $sContent;

        if (method_exists($this, $sMethodMinify))
            $sResult = $this->$sMethodMinify($sResult);

        $mixedWriteResult = false;
        if(!empty($sResult) && ($rHandler = fopen($sCacheAbsolutePath, 'w')) !== false) {
            $mixedWriteResult = fwrite($rHandler, $sResult);
            fclose($rHandler);
            @chmod ($sCacheAbsolutePath, 0666);
        }

        if($mixedWriteResult === false)
            return $this->_includeFile($sType, $aFiles);

        if($this->{'_b' . $sUpcaseType . 'Archive'})
            $sCacheAbsoluteUrl = $this->_getLoaderUrl($sType, $sName);

        return $this->$sMethodWrap($sCacheAbsoluteUrl);
    }
    /**
     * Include CSS/JS files without caching.
     *
     * @param  string $sType  the file type (css or js)
     * @param  array  $aFiles CSS/JS files to be added to the page.
     * @return string result of operation.
     */
    function _includeFiles($sType, &$aFiles)
    {
        $sMethod = '_wrapInTag' . ucfirst($sType);

        $sResult = "";
        foreach($aFiles as $aFile)
           $sResult .= $this->$sMethod($aFile['url']);

        return $sResult;
    }
    /**
     * Insert/Delete CSS file from output stack.
     *
     * @param  string  $sType      the file type (css or js)
     * @param  string  $sAction    add/delete
     * @param  mixed   $mixedFiles string value represents a single CSS file name. An array - array of CSS file names.
     * @return boolean result of operation.
     */
    function _processFiles($sType, $sAction, $mixedFiles, $bDynamic = false, $bSystem = false)
    {
        if(empty($mixedFiles))
            return $bDynamic ? "" : false;

        if(is_string($mixedFiles))
            $mixedFiles = array($mixedFiles);

        $sUpcaseType = ucfirst($sType);
        $sMethodLocate = '_getAbsoluteLocation' . $sUpcaseType;
        $sMethodWrap = '_wrapInTag' . $sUpcaseType;
        $sResult = '';
        foreach($mixedFiles as $sFile) {
            //--- Process 3d Party CSS/JS file ---//
            if(strpos($sFile, "http://") !== false || strpos($sFile, "https://") !== false) {
                $sUrl = $sFile;
                $sPath = $sFile;
            }
            //--- Process Custom CSS/JS file ---//
            else if(strpos($sFile, "|") !== false && $aParts = explode("|", $sFile)) {
                $sFile = array_pop($aParts);
                if (!isset($aParts[0]))
                    $aParts[0] = '';
                $sUrl = BX_DOL_URL_ROOT . (isset($aParts[1]) ? $aParts[1] : $aParts[0]) . $sFile;
                $sPath = realpath(BX_DIRECTORY_PATH_ROOT . $aParts[0] . $sFile);
            }
            //--- Process Common CSS/JS file(check in default locations) ---//
            else {
                $sUrl = $this->$sMethodLocate('url', $sFile);
                $sPath = $this->$sMethodLocate('path', $sFile);
            }

            if(empty($sPath) || empty($sUrl))
                continue;

			$sArrayKey = $this->_sPrefix . $sUpcaseType . ($bSystem ? 'System' : '');
            switch($sAction) {
                case 'add':
                    if($bDynamic)
                        $sResult .= $this->$sMethodWrap($sUrl);
                    else {
                        $bFound = false;
                        foreach($GLOBALS[$sArrayKey]  as $iKey => $aValue)
                            if($aValue['url'] == $sUrl && $aValue['path'] == $sPath) {
                                $bFound = true;
                                break;
                            }

                        if(!$bFound)
                            $GLOBALS[$sArrayKey][] = array('url' => $sUrl, 'path' => $sPath);
                    }
                    break;
                case 'delete':
                    if(!$bDynamic)
                        foreach($GLOBALS[$sArrayKey]  as $iKey => $aValue)
                            if($aValue['url'] == $sUrl) {
                                unset($GLOBALS[$sArrayKey][$iKey]);
                                break;
                            }
                    break;
            }
        }

        return $bDynamic ? $sResult : true;
    }

    /**
     * Parse content.
     *
     * @param  string $sContent            - HTML file's content.
     * @param  array  $aVariables          - key/value pairs. key should be the same as template's key, but without prefix and postfix.
     * @param  mixed  $mixedKeyWrapperHtml - key wrapper(string value if left and right parts are the same, array(0 => left, 1 => right) otherwise).
     * @return string the result of operation.
     */
    function _parseContent($sContent, $aVariables, $mixedKeyWrapperHtml = null)
    {
        $aKeys = array_keys($aVariables);
        $aValues = array_values($aVariables);

        $aKeyWrappers = $this->_getKeyWrappers($mixedKeyWrapperHtml);

        $iCountKeys = count($aKeys);
        for ($i = 0; $i < $iCountKeys; $i++) {
            if (strncmp($aKeys[$i], 'bx_repeat:', 10) === 0) {
                $sKey = "'<" . $aKeys[$i] . ">(.*)<\/" . $aKeys[$i] . ">'s";

                $aMatches = array();
                preg_match($sKey, $sContent, $aMatches);

                $sValue = '';
                if(isset($aMatches[1]) && !empty($aMatches[1])) {
                    if(is_array($aValues[$i]))
                        foreach($aValues[$i] as $aValue)
                            $sValue .= $this->parseHtmlByContent($aMatches[1], $aValue, $mixedKeyWrapperHtml);
                    else if(is_string($aValues[$i]))
                        $sValue = $aValues[$i];
                }
            } else if (strncmp($aKeys[$i], 'bx_if:', 6) === 0) {
                $sKey = "'<" . $aKeys[$i] . ">(.*)<\/" . $aKeys[$i] . ">'s";

                $aMatches = array();
                preg_match($sKey, $sContent, $aMatches);

                $sValue = '';
                if(isset($aMatches[1]) && !empty($aMatches[1]))
                    if(is_array($aValues[$i]) && isset($aValues[$i]['content']) && $aValues[$i]['condition'])
                        $sValue .= $this->parseHtmlByContent($aMatches[1], $aValues[$i]['content'], $mixedKeyWrapperHtml);
            } else {
                $sKey = "'" . $aKeyWrappers['left'] . $aKeys[$i] . $aKeyWrappers['right'] . "'s";
                $sValue = $aValues[$i];
                //$sValue = str_replace('$', '\\$', $aValues[$i]);
            }

            $aKeys[$i] = $sKey;
            $aValues[$i] = $sValue;
        }

        $aKeys = array_merge($aKeys, array(
            "'<bx_include_auto:([^\s]+) \/>'s",
            "'<bx_include_tmpl:([^\s]+) \/>'s",
            "'<bx_include_base:([^\s]+) \/>'s",
            "'<bx_injection:([^\s]+) />'s",
            "'<bx_image_url:([^\s]+) \/>'s",
            "'<bx_icon_url:([^\s]+) \/>'s",
            "'<bx_text:([_\{\}\w\d\s]+[^\s]{1}) \/>'s",
        	"'<bx_text_js:([^\s]+) \/>'s",
			"'<bx_text_attribute:([^\s]+) \/>'s",
            "'<bx_url_root />'",
            "'<bx_url_admin />'"
        ));

        $aValues = array_merge($aValues, array(
            function($matches) use ($aVariables, $mixedKeyWrapperHtml) { return $this->parseHtmlByName($matches[1], $aVariables, $mixedKeyWrapperHtml, BX_DOL_TEMPLATE_CHECK_IN_BOTH); },
            function($matches) use ($aVariables, $mixedKeyWrapperHtml) { return $this->parseHtmlByName($matches[1], $aVariables, $mixedKeyWrapperHtml, BX_DOL_TEMPLATE_CHECK_IN_TMPL); },
            function($matches) use ($aVariables, $mixedKeyWrapperHtml) { return $this->parseHtmlByName($matches[1], $aVariables, $mixedKeyWrapperHtml, BX_DOL_TEMPLATE_CHECK_IN_BASE); },
            function($matches) { return $this->processInjection($GLOBALS['_page']['name_index'], $matches[1]); },
            function($matches) { return $this->getImageUrl($matches[1]); },
            function($matches) { return $this->getIconUrl($matches[1]); },
            function($matches) { return _t($matches[1]); },
        	function($matches) { return bx_js_string(_t($matches[1])); },
        	function($matches) { return bx_html_attribute(_t($matches[1])); },
            BX_DOL_URL_ROOT,
            BX_DOL_URL_ADMIN
        ));

        //--- Parse Predefined Keys ---//
        //$sContent = preg_replace($aKeys, $aValues, $sContent);

        $aCombined = array_combine($aKeys, $aValues);
        foreach($aCombined as $sPattern => $sValue) {

            if(is_object($sValue) && ($sValue instanceof Closure)) {
                $sContent = preg_replace_callback($sPattern, $sValue, $sContent);
                continue;
            }

            $sContent = preg_replace_callback($sPattern, function($matches) use ($sValue) {
                return $sValue;
            }, $sContent);
        }

        //--- Parse System Keys ---//
        //$sContent = preg_replace( "'" . $aKeyWrappers['left'] . "([a-zA-Z0-9_-]+)" . $aKeyWrappers['right'] . "'e", "\$this->parseSystemKey('\\1', \$mixedKeyWrapperHtml)", $sContent);
        $sContent = preg_replace_callback("'" . $aKeyWrappers['left'] . "([a-zA-Z0-9_-]+)" . $aKeyWrappers['right'] . "'",
            function($matches) use ($mixedKeyWrapperHtml) {

                return $this->parseSystemKey($matches[1], $mixedKeyWrapperHtml);
        }, $sContent);

        return $sContent;
    }

    /**
     * Compile content
     *
     * @param  string  $sContent            template.
     * @param  string  $aVarName            variable name to be saved in the output file.
     * @param  integer $iVarDepth           depth is used to process nesting, for example, in cycles.
     * @param  array   $aVarValues          values to be compiled in.
     * @param  mixed   $mixedKeyWrapperHtml key wrapper(string value if left and right parts are the same, array(0 => left, 1 => right) otherwise).
     * @return string  the result of operation.
     */
    function _compileContent($sContent, $aVarName, $iVarDepth, $aVarValues, $mixedKeyWrapperHtml = null)
    {
        $aKeys = array_keys($aVarValues);
        $aValues = array_values($aVarValues);

        $aKeyWrappers = $this->_getKeyWrappers($mixedKeyWrapperHtml);

        for($i = 0; $i < count($aKeys); $i++) {
            if(strpos($aKeys[$i], 'bx_repeat:') === 0) {
                $sKey = "'<" . $aKeys[$i] . ">(.*)<\/" . $aKeys[$i] . ">'s";

                $aMatches = array();
                preg_match($sKey, $sContent, $aMatches);

                $sValue = '';
                if(isset($aMatches[1]) && !empty($aMatches[1])) {
                    if(empty($aValues[$i]) || !is_array($aValues[$i]))
                        return false;

                    $sIndex = "\$" . str_repeat("i", $iVarDepth);
                    $sValue .= '<'."?php if(is_array(" . $aVarName . "['" . $aKeys[$i] . "'])) for(" . $sIndex . "=0; " . $sIndex . "<count(" . $aVarName . "['" . $aKeys[$i] . "']); " . $sIndex . "++){ ?".'>';
                    if(($sInnerValue = $this->_compileContent($aMatches[1], $aVarName . "['" . $aKeys[$i] . "'][" . $sIndex . "]", $iVarDepth + 1, current($aValues[$i]), $mixedKeyWrapperHtml)) === false)
                        return false;
                    $sValue .= $sInnerValue;
                    $sValue .= '<'."?php } else if(is_string(" . $aVarName . "['" . $aKeys[$i] . "'])) echo " . $aVarName . "['" . $aKeys[$i] . "']; ?".'>';
                }
            } else if(strpos($aKeys[$i], 'bx_if:') === 0) {
                $sKey = "'<" . $aKeys[$i] . ">(.*)<\/" . $aKeys[$i] . ">'s";

                $aMatches = array();
                preg_match($sKey, $sContent, $aMatches);

                $sValue = '';
                if(isset($aMatches[1]) && !empty($aMatches[1])) {
                    if(!is_array($aValues[$i]) || !isset($aValues[$i]['content']) || empty($aValues[$i]['content']) || !is_array($aValues[$i]['content']))
                        return false;

                    $sValue .= '<'."?php if(" . $aVarName . "['" . $aKeys[$i] . "']['condition']){ ?".'>';
                    if(($sInnerValue = $this->_compileContent($aMatches[1], $aVarName . "['" . $aKeys[$i] . "']['content']", $iVarDepth, $aValues[$i]['content'], $mixedKeyWrapperHtml)) === false)
                        return false;
                    $sValue .= $sInnerValue;
                    $sValue .= '<'.'?php } ?'.'>';
                }
            } else {
                $sKey = "'" . $aKeyWrappers['left'] . $aKeys[$i] . $aKeyWrappers['right'] . "'s";
                $sValue = '<'.'?=' . $aVarName . "['" . $aKeys[$i] . "'];?".'>';
            }

            $aKeys[$i] = $sKey;
            $aValues[$i] = $sValue;
        }

        $aKeys = array_merge($aKeys, array(
            "'<bx_include_auto:([^\s]+) \/>'s",
            "'<bx_include_base:([^\s]+) \/>'s",
            "'<bx_include_tmpl:([^\s]+) \/>'s",
            "'<bx_injection:([^\s]+) />'s",
            "'<bx_image_url:([^\s]+) \/>'s",
            "'<bx_icon_url:([^\s]+) \/>'s",
            "'<bx_text:([_\{\}\w\d\s]+[^\s]{1}) \/>'s",
        	"'<bx_text_js:([^\s]+) \/>'s",
			"'<bx_text_attribute:([^\s]+) \/>'s",
            "'<bx_url_root />'",
            "'<bx_url_admin />'"
        ));

        $aValues = array_merge($aValues, array(
            function($matches) use ($aVarValues, $mixedKeyWrapperHtml) { return $this->getCached($matches[1], $aVarValues, $mixedKeyWrapperHtml, BX_DOL_TEMPLATE_CHECK_IN_BOTH, false); },
            function($matches) use ($aVarValues, $mixedKeyWrapperHtml) { return $this->getCached($matches[1], $aVarValues, $mixedKeyWrapperHtml, BX_DOL_TEMPLATE_CHECK_IN_BASE, false); },
            function($matches) use ($aVarValues, $mixedKeyWrapperHtml) { return $this->getCached($matches[1], $aVarValues, $mixedKeyWrapperHtml, BX_DOL_TEMPLATE_CHECK_IN_TMPL, false); },
            function($matches) { return '<?=$this->processInjection($GLOBALS[\'_page\'][\'name_index\'], "'.$matches[1].'")?>'; },
            function($matches) { return $this->getImageUrl($matches[1]); },
            function($matches) { return $this->getIconUrl($matches[1]); },
            function($matches) { return _t($matches[1]); },
            function($matches) { return bx_js_string(_t($matches[1])); },
            function($matches) { return bx_html_attribute(_t($matches[1])); },
            BX_DOL_URL_ROOT,
            BX_DOL_URL_ADMIN
        ));

        //--- Parse Predefined Keys ---//
        $aCombined = array_combine($aKeys, $aValues);
        foreach($aCombined as $sPattern => $sValue) {
            if(is_object($sValue) && ($sValue instanceof Closure)) {
                $sContent = preg_replace_callback($sPattern, $sValue, $sContent);
                continue;
            }

            $sContent = preg_replace_callback($sPattern, function($matches) use ($sValue) {
                return $sValue;
            }, $sContent);
        }

        //--- Parse System Keys ---//
        $sContent = preg_replace( "'" . $aKeyWrappers['left'] . "([a-zA-Z0-9_-]+)" . $aKeyWrappers['right'] . "'", "<?=\$this->parseSystemKey('\\1', \$mixedKeyWrapperHtml);?".">", $sContent);

        return $sContent;
    }
    /**
     * Get absolute location of some template's part.
     *
     * @param  string $sType    - result type. Available values 'url' and 'path'.
     * @param  string $sFolder  - folders to be searched in. @see $_sFolderHtml, $_sFolderCss, $_sFolderImages and $_sFolderIcons
     * @param  string $sName    - requested part name.
     * @param  string $sCheckIn where the content would be searched(base, template, both)
     * @return string absolute location (path/url) of the part.
     */
    function _getAbsoluteLocation($sType, $sFolder, $sName, $sCheckIn = BX_DOL_TEMPLATE_CHECK_IN_BOTH)
    {
        if($sType == 'path') {
            $sDivider = DIRECTORY_SEPARATOR;
            $sRoot = BX_DIRECTORY_PATH_ROOT;
        } else if($sType == 'url') {
            $sDivider = '/';
            $sRoot = BX_DOL_URL_ROOT;
        }

        if(strpos($sName,'|') !== false) {
            $aParts = explode('|', $sName);

            $sName = $aParts[1];
            $sLocationKey = $this->addDynamicLocation(BX_DIRECTORY_PATH_ROOT . $aParts[0], BX_DOL_URL_ROOT . $aParts[0]);
        }

        $sResult = '';
        $aLocations = array_reverse($this->_aLocations, true);
        foreach($aLocations as $sKey => $aLocation) {
            $sCode = $this->getCode();

            if(($sCheckIn == BX_DOL_TEMPLATE_CHECK_IN_BOTH || $sCheckIn == BX_DOL_TEMPLATE_CHECK_IN_TMPL) && extFileExists($aLocation['path'] . 'tmpl_' . $sCode . DIRECTORY_SEPARATOR . $sFolder . $sName))
                $sResult = $aLocation[$sType] . 'tmpl_' . $sCode . $sDivider . $sFolder . $sName;
            else if(($sCheckIn == BX_DOL_TEMPLATE_CHECK_IN_BOTH || $sCheckIn == BX_DOL_TEMPLATE_CHECK_IN_BASE) && extFileExists($aLocation['path'] . BX_DOL_TEMPLATE_FOLDER_BASE . DIRECTORY_SEPARATOR . $sFolder . $sName))
                $sResult = $aLocation[$sType] . BX_DOL_TEMPLATE_FOLDER_BASE . $sDivider . $sFolder . $sName;
            else
                continue;
            break;
        }

        /**
         * try to find from received path
         */
        if(!$sResult && @is_file(BX_DIRECTORY_PATH_ROOT . $aParts[0] . DIRECTORY_SEPARATOR . $aParts[1])) {
            $sResult = $sRoot . $aParts[0] . $sDivider . $aParts[1];
        }

        if(isset($sLocationKey))
           $this->removeLocation($sLocationKey);

        return $sType == 'path' && !empty($sResult) ? realpath($sResult) : $sResult;
    }
    /**
     * Get absolute location of some template's part.
     *
     * @param  string $sType result type. Available values 'url' and 'path'.
     * @param  string $sName requested part name.
     * @return string absolute location (path/url) of the part.
     */
    function _getAbsoluteLocationJs($sType, $sName)
    {
        $sResult = '';
        $aLocations = array_reverse($this->_aLocationsJs, true);
        foreach($aLocations as $sKey => $aLocation) {
            if(extFileExists($aLocation['path'] . $sName))
                $sResult = $aLocation[$sType] . $sName;
            else
                continue;
            break;
        }
        return $sType == 'path' && !empty($sResult) ? realpath($sResult) : $sResult;
    }
    function _getAbsoluteLocationCss($sType, $sName)
    {
        return $this->_getAbsoluteLocation($sType, $this->_sFolderCss, $sName);
    }
    /**
     * Get inline data for Images and Icons.
     *
     * @param  string  $sType    image/icon
     * @param  string  $sName    file name
     * @param  string  $sCheckIn where the content would be searched(base, template, both)
     * @return unknown
     */
    function _getInlineData($sType, $sName, $sCheckIn)
    {
        switch($sType) {
            case 'image':
                $sFolder = $this->_sFolderImages;
                break;
            case 'icon':
                $sFolder = $this->_sFolderIcons;
                break;
        }
        $sPath = $this->_getAbsoluteLocation('path', $sFolder, $sName, $sCheckIn);

        $iFileSize = 0;
        if($this->_bImagesInline && ($iFileSize = filesize($sPath)) !== false && $iFileSize < $this->_iImagesMaxSize) {
            $aFileInfo = pathinfo($sPath);
            return "data:image/" . strtolower($aFileInfo['extension']) . ";base64," . base64_encode(file_get_contents($sPath));
        }

        return false;
    }
    /**
     * Get file name where the template would be cached.
     *
     * @param  string $sAbsolutePath template's real path.
     * @return string the result of operation.
     */
    function _getCacheFileName($sType, $sAbsolutePath)
    {
        $sResult = md5($sAbsolutePath . $GLOBALS['site']['ver'] . $GLOBALS['site']['build'] . $GLOBALS['site']['url']);
        switch($sType) {
            case 'html':
                $sResult = $this->_sCacheFilePrefix . bx_lang_name() . '_' . $this->_sCode .  '_' . $sResult;
                break;
            case 'css':
                $sResult = $this->_sCssCachePrefix . $sResult;
                break;
            case 'js':
                $sResult = $this->_sJsCachePrefix . $sResult;
                break;
        }

        return $sResult;
    }
    /**
     * Get template key wrappers(left, right)
     *
     * @param  mixed $mixedKeyWrapperHtml key wrapper(string value if left and right parts are the same, array(0 => left, 1 => right) otherwise).
     * @return array result of operation.
     */
    function _getKeyWrappers($mixedKeyWrapperHtml)
    {
        $aResult = array();
        if(!empty($mixedKeyWrapperHtml) && is_string($mixedKeyWrapperHtml))
            $aResult = array('left' => $mixedKeyWrapperHtml, 'right' => $mixedKeyWrapperHtml);
        else if(!empty($mixedKeyWrapperHtml) && is_array($mixedKeyWrapperHtml))
            $aResult = array('left' => $mixedKeyWrapperHtml[0], 'right' => $mixedKeyWrapperHtml[1]);
        else
            $aResult = array('left' => $this->_sKeyWrapperHtml, 'right' => $this->_sKeyWrapperHtml);
        return $aResult;
    }

    /**
     * Process all added language translations and return them as a string.
     *
     * @return string with JS code.
     */
    function _processJsTranslations()
    {
        $aSearch = array("\r", "\n", '\'');
        $aReplacement = array('', '\n', '\\\'');

        $sReturn = '';
        foreach($GLOBALS['BxDolTemplateJsTranslations'] as $sKey => $sString) {
            $sKey = str_replace($aSearch, $aReplacement, $sKey);
            $sString = str_replace($aSearch, $aReplacement, $sString);

               $sReturn .= "'" .  $sKey . "': '" . $sString . "',";
        }

        return '<script type="text/javascript" language="javascript">var aDolLang = {' . substr($sReturn, 0, -1) . '};</script>';
    }
    /**
     * Process all added options and return them as a string.
     *
     * @return string with JS code.
     */
    function _processJsOptions()
    {
        $sReturn = '';
        foreach($GLOBALS['BxDolTemplateJsOptions'] as $sName => $mixedValue)
            $sReturn .= "'" .  $sName . "': '" . addslashes($mixedValue) . "',";

        return '<script type="text/javascript" language="javascript">var aDolOptions = {' . substr($sReturn, 0, -1) . '};</script>';
    }
    /**
     * Process all added images and return them as a string.
     *
     * @return string with JS code.
     */
    function _processJsImages()
    {
        $sReturn = '';
        foreach($GLOBALS['BxDolTemplateJsImages'] as $sKey => $sUrl)
            $sReturn .= "'" .  $sKey . "': '" . $sUrl . "',";

        return '<script type="text/javascript" language="javascript">var aDolImages = {' . substr($sReturn, 0, -1) . '};</script>';
    }

    /**
     * Get Gzip loader URL.
     *
     * @param $sType content type CSS/JS
     * @param $sName file name.
     * @return string with URL
     */
    function _getLoaderUrl($sType, $sName)
    {
        return BX_DOL_URL_ROOT . 'gzip_loader.php?file=' . $sName . '.' . $sType;
    }

    /**
     *
     * Static functions to display pages with errors, messages and so on.
     *
     */
    function displayAccessDenied ()
    {
        $sTitle = _t('_Access denied');

        $GLOBALS['_page'] = array(
            'name_index' => 0,
            'header' => $sTitle,
            'header_text' => $sTitle
        );
        $GLOBALS['_page_cont'][0]['page_main_code'] = MsgBox($sTitle);

        PageCode();
        exit;
    }
    function displayNoData ()
    {
        $sTitle = _t('_Empty');

        $GLOBALS['_page'] = array(
            'name_index' => 0,
            'header' => $sTitle,
            'header_text' => $sTitle
        );
        $GLOBALS['_page_cont'][0]['page_main_code'] = MsgBox($sTitle);

        PageCode();
        exit;
    }
    function displayErrorOccured ()
    {
        $sTitle = _t('_Error Occured');

        $GLOBALS['_page'] = array(
            'name_index' => 0,
            'header' => $sTitle,
            'header_text' => $sTitle
        );
        $GLOBALS['_page_cont'][0]['page_main_code'] = MsgBox($sTitle);

        PageCode();
        exit;
    }
    function displayPageNotFound ()
    {
        $sTitle = _t('_sys_request_page_not_found_cpt');

        $GLOBALS['_page'] = array(
            'name_index' => 0,
            'header' => $sTitle,
            'header_text' => $sTitle
        );
        $GLOBALS['_page_cont'][0]['page_main_code'] = MsgBox($sTitle);

        header("HTTP/1.0 404 Not Found");
        PageCode();
        exit;
    }
    function displayMsg ($s, $bTranslate = false)
    {
        $sTitle = $bTranslate ? _t($s) : $s;

        $GLOBALS['_page'] = array(
            'name_index' => 0,
            'header' => $sTitle,
            'header_text' => $sTitle
        );
        $GLOBALS['_page_cont'][0]['page_main_code'] = MsgBox($sTitle);

        PageCode();
        exit;
    }

    /**
     * * * * Static methods for work with template injections * * *
     *
     * Static method is used to add/replace the content of some key in the template.
     * It's usefull when you don't want to modify existing template but need to add some data to existing template key.
     *
     * @param  integer $iPageIndex - page index where injections would processed. Use 0 if you want it to be done on all the pages.
     * @param  string  $sKey       - template key.
     * @param  string  $sValue     - the data to be added.
     * @return string  the result of operation.
     */
    function processInjection($iPageIndex, $sKey, $sValue = "")
    {
        if($iPageIndex != 0 && isset($GLOBALS[$this->_sPrefix . 'Injections']['page_0'][$sKey]) && isset($GLOBALS[$this->_sPrefix . 'Injections']['page_' . $iPageIndex][$sKey]))
           $aSelection = @array_merge($GLOBALS[$this->_sPrefix . 'Injections']['page_0'][$sKey], $GLOBALS[$this->_sPrefix . 'Injections']['page_' . $iPageIndex][$sKey]);
        else if(isset($GLOBALS[$this->_sPrefix . 'Injections']['page_0'][$sKey]))
           $aSelection = $GLOBALS[$this->_sPrefix . 'Injections']['page_0'][$sKey];
        else if(isset($GLOBALS[$this->_sPrefix . 'Injections']['page_' . $iPageIndex][$sKey]))
            $aSelection = $GLOBALS[$this->_sPrefix . 'Injections']['page_' . $iPageIndex][$sKey];
        else
            $aSelection = array();

        if(is_array($aSelection))
            foreach($aSelection as $aInjection) {

                if (isset($GLOBALS['bx_profiler'])) $GLOBALS['bx_profiler']->beginInjection($sRand = time().rand());

                switch($aInjection['type']) {
                    case 'text':
                        $sInjData = $aInjection['data'];
                        break;
                    case 'php':
                        ob_start();
                        $sInjData = eval($aInjection['data']);
                        if(!empty($sInjData))
                            ob_end_clean();
                        else
                            $sInjData = ob_get_clean();
                        break;
                }
                if((int)$aInjection['replace'] == 1)
                    $sValue = $sInjData;
                else
                    $sValue .= $sInjData;

                if (isset($GLOBALS['bx_profiler'])) $GLOBALS['bx_profiler']->endInjection($sRand, $aInjection['name'], $aInjection['key'], (int)$aInjection['replace'] == 1);

            }

        return $sValue != '__' . $sKey . '__' ? str_replace('__' . $sKey . '__', '', $sValue) : $sValue;
    }
    /**
     * Static method to add ingection available on the current page only.
     *
     * @param string  $sKey     - template's key.
     * @param string  $sType    - injection type(text, php).
     * @param string  $sData    - the data to be added.
     * @param integer $iReplace - replace already existed data or not.
     */
    function addInjection($sKey, $sType, $sData, $iReplace = 0)
    {
        $GLOBALS[$this->_sPrefix . 'Injections']['page_0'][$sKey][] = array(
            'page_index' => 0,
            'key' => $sKey,
            'type' => $sType,
            'data' => $sData,
            'replace' => $iReplace
        );
    }
}
