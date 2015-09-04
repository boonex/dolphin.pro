<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolSiteMaps');

/**
 * Sitemaps generator for system pages
 */
class BxDolSiteMapsSystem extends BxDolSiteMaps
{
    protected $_aPages = array (
        array('page' => 'about_us.php'),
        array('page' => 'advice.php'),
        array('page' => 'contact.php'),
        array('page' => 'faq.php'),
        array('page' => 'forgot.php'),
        array('page' => 'help.php'),
        array('page' => 'join.php'),
        array('page' => 'privacy.php'),
        array('page' => 'search_home.php'),
        array('page' => 'services.php'),
        array('page' => 'terms_of_use.php'),
    );

    protected function __construct($aSystem)
    {
        parent::__construct($aSystem);
    }

    protected function _genUrl ($a)
    {
        return BX_DOL_URL_ROOT . $a['page'];
    }

    protected function _getCount ()
    {
        return count($this->_aPages);
    }

    protected function _getRecords ($iStart)
    {
        return array_slice($this->_aPages, $iStart, BX_SITE_MAPS_URLS_PER_FILE);
    }
}
