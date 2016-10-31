<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolSiteMapsQuery');

define('BX_SITE_MAPS_URLS_PER_FILE', 20000);
define('BX_SITE_MAPS_FILES_PREFIX', 'sitemap_');
define('BX_SITE_MAPS_FILE_INDEX', 'sitemap.xml');

/**
 * Sitemaps for search engines: http://www.sitemaps.org/protocol.html
 *
 * To add sitemap to your module you need to add a record to 'sys_objects_site_maps' table and custom class:
 *
 * id - autoincremented id for internal usage
 * object - your unique module name, with vendor prefix, lowercase and spaces are underscored
 * priority - priority, allowed values from 0.0 to 1.0
 * changefreq - how frequently contents change, allowed values: always, hourly, daily, weekly, monthly, yearly, never
 * class_name - your custom class name
 * class_file - file where your class_name is stored
 * order - order in which this sitemap is generated
 * active - is object active, allowed values 0 or 1
 *
 * You can refer to BoonEx modules for sample record in this table.
 *
 */
class BxDolSiteMaps
{
    static protected $BASE_PATH = BX_DIRECTORY_PATH_CACHE_PUBLIC; // path to generated sitemaps
    static protected $BASE_URL = BX_DOL_URL_CACHE_PUBLIC; // url to generated sitemaps
    static protected $BASE_PATH_INDEX = BX_DIRECTORY_PATH_ROOT; // path to generated index sitemap
    static protected $BASE_URL_INDEX = BX_DOL_URL_ROOT; // url to generated index sitemap

    protected $_aSystem = array (); // current sitemap system array
    protected $_oQuery = null;
    protected $_aQueryParts = array (
        'fields' => "", // fields list, for example: `id`, `uri`, `last_edit_ts`
        'field_date' => "", // date field name, for example: `last_edit_ts`
        'field_date_type' => "timestamp", // date field type (datetime or timestamp)
        'table' => "", // table name, for example: `my_table`
        'join' => "", // join SQL part
        'where' => "", // SQL condition, without WHERE, for example: active = 1
        'order' => "", // SQL order, without ORDER BY, for example: `date` ASC
    );

    protected function __construct($aSystem)
    {
        $this->_aSystem = $aSystem;
        $this->_oQuery = new BxDolSiteMapsQuery($this->_aSystem);
    }

    /**
     * Get sitemap object instance by object name
     * @param $sObject object name
     * @return object instance or false on error
     */
    static public function getObjectInstance($sObject)
    {
        if (isset($GLOBALS['bxDolClasses']['BxDolSiteMaps!'.$sObject]))
            return $GLOBALS['bxDolClasses']['BxDolSiteMaps!'.$sObject];

        $aSystems =& self::getSystems ();
        if (!$aSystems || !isset($aSystems[$sObject]))
            return false;

        $aObject = $aSystems[$sObject];

        if (!($sClass = $aObject['class_name']))
            return false;

        if (!empty($aObject['class_file']))
            require_once(BX_DIRECTORY_PATH_ROOT . $aObject['class_file']);
        else
            bx_import($sClass);

        $o = new $sClass($aObject);

        return ($GLOBALS['bxDolClasses']['BxDolSiteMaps!'.$sObject] = $o);
    }

    /**
     * get all systems
     */
    static public function & getSystems () {
        if (!isset($GLOBALS['bx_dol_site_maps_systems']))
            $GLOBALS['bx_dol_site_maps_systems'] = BxDolSiteMapsQuery::getAllActiveSystemsFromCache ();
        return $GLOBALS['bx_dol_site_maps_systems'];
    }

    /**
     * it is called on cron every day or similar period to generate sitemaps from all modules
     */
    static public function generateAllSiteMaps ()
    {
        if (!self::deleteAllSiteMaps ())
            return false;

        if (!getParam('sys_sitemap_enable'))
            return false;

        $aSystems =& self::getSystems ();
        $aFiles = array ();
        foreach ($aSystems as $sSystem => $aSystem) {
            if (!($o = self::getObjectInstance($sSystem)))
                continue;
            $aFiles = array_merge($aFiles, $o->generate());
        }
        if (empty($aFiles))
            return true;

        $sFileContents = '<' . '?xml version="1.0" encoding="UTF-8"?' . ">\n";
        $sFileContents .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($aFiles as $sFile) {
            $sFileContents .= "\t<sitemap>\n";
            $sFileContents .= "\t\t<loc>" . self::_escape(self::$BASE_URL . $sFile) . "</loc>\n";
            $sFileContents .= "\t\t<lastmod>" . self::_formatDate (time()) . "</lastmod>\n";
            $sFileContents .= "\t</sitemap>\n";
        }

        $sFileContents .= '</sitemapindex>';

        if (!file_put_contents (self::$BASE_PATH_INDEX . BX_SITE_MAPS_FILE_INDEX, $sFileContents))
            return false;

        return true;
    }

    /**
     * delete all sitemaps
     */
    static public function deleteAllSiteMaps ()
    {
        if (file_exists(self::$BASE_PATH_INDEX . BX_SITE_MAPS_FILE_INDEX) && false === file_put_contents(self::$BASE_PATH_INDEX . BX_SITE_MAPS_FILE_INDEX, ''))
            return false;

        if (!($rHandler = opendir(self::$BASE_PATH)))
            return false;

        $l = strlen(BX_SITE_MAPS_FILES_PREFIX);
        while (($sFile = readdir($rHandler)) !== false)
            if (0 === strncmp($sFile, BX_SITE_MAPS_FILES_PREFIX, $l) && file_exists(self::$BASE_PATH . $sFile))
                @unlink (self::$BASE_PATH . $sFile);

        closedir($rHandler);

        return true;
    }

    /**
     * get sitemaps index file url
     */
    static public function getSiteMapIndexUrl ()
    {
        return self::$BASE_URL_INDEX . BX_SITE_MAPS_FILE_INDEX;
    }

    /**
     * get sitemaps index file path
     */
    static public function getSiteMapIndexPath ()
    {
        return self::$BASE_PATH_INDEX . BX_SITE_MAPS_FILE_INDEX;
    }

    /**
     * generate files for current
     */
    public function generate ()
    {
        if (!getParam('sys_sitemap_enable'))
            return array ();

        $aFiles = array ();
        $iStart = 0;
        $iCount = $this->_getCount();
        if (!$iCount)
            return array ();

        do {

            $aRows = $this->_getRecords ($iStart);
            if (empty($aRows))
                break;

            $iFileIndex = round($iStart / BX_SITE_MAPS_URLS_PER_FILE);
            $sFileName = BX_SITE_MAPS_FILES_PREFIX . $this->_aSystem['object'] . '_' . $iFileIndex . '.xml';
            if (!($f = fopen (self::$BASE_PATH . $sFileName, 'w')))
                break;

            fwrite($f, $this->_genXmlUrlsBegin ());
            foreach ($aRows as $aRow)
                fwrite($f, $this->_genXmlUrl ($aRow));
            fwrite($f, $this->_genXmlUrlsEnd ());

            fclose($f);

            @chmod (self::$BASE_PATH . $sFileName, 0666);

            $aFiles[] = $sFileName;

            $iStart += BX_SITE_MAPS_URLS_PER_FILE;

        } while ($iStart < $iCount);

        return $aFiles;
    }

    protected function _genXmlUrlsBegin ()
    {
        $s = '<' . '?xml version="1.0" encoding="UTF-8"?' . ">\n";
        $s .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        return $s;
    }

    protected function _genXmlUrlsEnd ()
    {
        return '</urlset>';
    }

    protected function _genXmlUrl ($a)
    {
        $sUrl = $this->_genUrl($a);

        if (BxDolRequest::serviceExists('pageac', 'is_url_accessable')) {
            if (!BxDolService::call('pageac', 'is_url_accessable', array($sUrl)))
                return '';
        }

        $s  = "\t<url>\n";

        $s .= "\t\t<loc>" . $this->_escape($sUrl) . "</loc>\n";

        if (!empty($a[$this->_aQueryParts['field_date']]))
            $s .= "\t\t<lastmod>" . $this->_genDate ($a) . "</lastmod>\n";

        if (!empty($this->_aSystem['changefreq']))
            $s .= "\t\t<changefreq>" . $this->_genChangeFreq($a) . "</changefreq>\n";

        if (!empty($this->_aSystem['priority']))
            $s .= "\t\t<priority>" . $this->_aSystem['priority'] . "</priority>\n";

        $s .= "\t</url>\n";

        return $s;
    }

    protected function _genUrl ($a)
    {
        // override this
        return '';
    }

    protected function _genDate ($a)
    {
        return $this->_formatDate ($this->_getDateTimeStamp($a));
    }

    protected function _genChangeFreq ($a)
    {
        if ('auto' != $this->_aSystem['changefreq'])
            return $this->_aSystem['changefreq'];

        if (empty($a[$this->_aQueryParts['field_date']]))
            return 'daily';

        // auto
        $iTimestamp = abs(time() - $this->_getDateTimeStamp($a));
        if ($iTimestamp < 2*86400) // less than 2 days
            return 'hourly';
        elseif ($iTimestamp < 14*86400) // less than 2 weeks
            return 'daily';
        elseif ($iTimestamp < 60*86400) // less than 2 months
            return 'weekly';
        else
            return 'monthly';
    }

    protected function _getCount ()
    {
        return $this->_oQuery->getCount($this->_aQueryParts);
    }

    protected function _getRecords ($iStart)
    {
        return $this->_oQuery->getRecords($this->_aQueryParts, $iStart, BX_SITE_MAPS_URLS_PER_FILE);
    }

    static protected function _formatDate ($iTimestamp)
    {
        return date('Y-m-d', $iTimestamp);
    }

    protected function _getDateTimeStamp ($a)
    {
        if ('datetime' == $this->_aQueryParts['field_date_type'])
            return 0 === strncmp('0000-00-00', $a[$this->_aQueryParts['field_date']], 10) ? time() : strtotime($a[$this->_aQueryParts['field_date']]);
        else
            return 0 == $this->_aQueryParts['field_date'] ? time() : $a[$this->_aQueryParts['field_date']];
    }

    static protected function _escape ($s)
    {
        return htmlspecialchars ($s, ENT_COMPAT|ENT_HTML401, 'UTF-8');
    }
}
