<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolSiteMaps');
bx_import('BxDolPrivacy');

/**
 * Sitemaps generator for Events
 */
class BxEventsSiteMaps extends BxDolSiteMaps
{
    protected $_oModule;

    protected function __construct($aSystem)
    {
        parent::__construct($aSystem);

        $this->_aQueryParts = array (
            'fields' => "`ID`, `EntryUri`, `Date`, `EventStart`, `EventEnd`", // fields list
            'field_date' => "Date", // date field name
            'field_date_type' => "timestamp", // date field type
            'table' => "`bx_events_main`", // table name
            'join' => "", // join SQL part
            'where' => "AND `Status` = 'approved' AND `allow_view_event_to` = '" . BX_DOL_PG_ALL . "'", // SQL condition, without WHERE
            'order' => " `Date` ASC ", // SQL order, without ORDER BY
        );

        $this->_oModule = BxDolModule::getInstance('BxEventsModule');
    }

    protected function _genUrl ($a)
    {
        return BX_DOL_URL_ROOT . $this->_oModule->_oConfig->getBaseUri() . 'view/' . $a['EntryUri'];
    }

    protected function _genChangeFreq ($a)
    {
        // calculate the date which is closest to now
        $iDiffMin = PHP_INT_MAX;
        $aDateFields = array ('Date', 'EventStart', 'EventEnd');
        foreach ($aDateFields as $sField) {
            $iDiff = abs(time() - $a[$sField]);
            if ($iDiff < $iDiffMin)
                $iDiffMin = $iDiff;
        }

        if ($iDiffMin != PHP_INT_MAX)
            $a[$this->_aQueryParts['field_date']] = time() - $iDiffMin;

        return parent::_genChangeFreq ($a);
    }
}
