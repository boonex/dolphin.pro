<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolModuleDb.php' );

define('BX_QUOTES_TABLE', 'bx_quotes_units');

/*
* Quotes module Data
*/
class BxQuotesDb extends BxDolModuleDb
{
    var $_oConfig;
    /*
    * Constructor.
    */
    function __construct(&$oConfig)
    {
        parent::__construct();

        $this->_oConfig = $oConfig;
    }

    function getRandomQuote()
    {
        return $this->getRow("SELECT `Text`, `Author` FROM `" . BX_QUOTES_TABLE . "` ORDER BY RAND() LIMIT 1");
    }
    function getQuote($iID)
    {
        return $this->getRow("SELECT * FROM `" . BX_QUOTES_TABLE . "` WHERE `ID`= ? LIMIT 1", [$iID]);
    }
    function getAllQuotes()
    {
        return $this->getAll("SELECT * FROM `" . BX_QUOTES_TABLE . "`");
    }
    function deleteUnit($iID)
    {
        return $this->query("DELETE FROM `" . BX_QUOTES_TABLE . "` WHERE `ID`= ? LIMIT 1", [$iID]);
    }
}
