<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

// error handling functions

class Mistake extends ThingPage
{

// private variables

    var $_error;							// current error string

// public functions

    /**
     * constructor
     */
    function __construct ()
    {
    }

    /**
     *	set error string for the object
     */
    function log ($s)
    {
        global $gConf;

        if (strlen ($gConf['dir']['error_log'])) {
            $fp = @fopen ($gConf['dir']['error_log'], "a");
            if ($fp) {
                @fwrite ($fp, date ('Y-m-d H:i:s', time ()) . "\t$s\n");
                @fclose ($fp);
            }
        }

        if($gConf['debug'])
            $this->displayError($s);

        $this->_error = $s;
    }

    function displayError ($s)
    {
        global $gConf;

        transCheck ($this->getErrorPageXML ($s), $gConf['dir']['xsl'] . 'default_error.xsl', 1);

        exit;
    }

    /**
     * returns page XML
     */
    function getErrorPageXML ($s)
    {
        return $this->addHeaderFooter ($s, $s);
    }

// private functions

}
