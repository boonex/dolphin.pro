<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

class BxJsGzipLoader
{
    /**
     * @param $sType        type (d - directory (to read js files from), ja - js array, )
     * @param $p            depents on t
     * @param $sJsUrl       js base url
     * @param $sCacheDir    cache dir
     */
    function __construct ($sType, $p, $sJsDir = '', $sCacheDir = '')
    {
        $this->_sType = $sType;
        $this->_p = $p;
        $this->_a = array (); // array of js files
        $this->_sJsDir = $sJsDir;

        $this->_sCacheDir = $sCacheDir;
        $this->_bCache = $sCacheDir ? true : false;
        $this->_sCacheFilename = '';

        $this->_bGzip = false; // gzip supported
        $this->_sEnc = ''; // encoding

        $this->_c = ''; // content;
        $this->_zc = ''; // gzip content;

        $this->sendheaders ();
        $this->buildJsList ();
        $this->checkEncoding ();
        if ($this->cacheRead()) exit;
        $this->readContent ();
        $this->outputContent ();
    }

    function buildJsList ()
    {
        if ( 'ja' == $this->_sType) {
            foreach ($this->_p as $sJsFile) {
                $this->_a[] = $this->_sJsDir . $sJsFile;
                $this->_sCacheFilename .= $sJsFile;
            }
            $this->_sCacheFilename = md5 ($this->_sCacheFilename);
            return;
        }

        if ('d' == $this->_sType && is_dir ($this->_p)) {

            if (!($dh = opendir($this->_p))) return;

            while (($sJsFile = readdir($dh)) !== false) {
                if (strtolower(substr($sJsFile, -3)) != '.js') continue;
                $this->_a[] = $this->_p . $sJsFile;
                $this->_sCacheFilename .= $sJsFile;
            }

            $this->_sCacheFilename = md5 ($this->_sCacheFilename);

            closedir($dh);

            //print_r ($this->_a); exit;
        } else {
            die ("alert ('Wrong js directory')");
        }
    }

    /**
     *  check if client browser supports gzip
     */
    function checkEncoding ()
    {
        $encodings = array ();
        if (isset($_SERVER['HTTP_ACCEPT_ENCODING']))
            $encodings = explode(',', strtolower(preg_replace("/\s+/", "", $_SERVER['HTTP_ACCEPT_ENCODING'])));

        if ((in_array('gzip', $encodings) || in_array('x-gzip', $encodings) || isset($_SERVER['---------------'])) && function_exists('ob_gzhandler') && !ini_get('zlib.output_compression') && ini_get('output_handler') != 'ob_gzhandler') {
            $this->_sEnc = in_array('x-gzip', $encodings) ? "x-gzip" : "gzip";
            $this->_bGzip = true;
        }
    }

    function readContent ()
    {
        foreach ($this->_a as $sFile)
            $this->_c .= $this->getFile($sFile);
    }

    function outputContent ()
    {
        if (!$this->_bGzip) {
            echo $this->_c;
            return;
        }

        header("Content-Encoding: " . $this->_sEnc);
        $this->_cz = gzencode ($this->_c, 9, FORCE_GZIP);

        $this->cacheWrite();

        // Stream to client
        echo $this->_cz;
    }

    function getFile($s)
    {
        $path = realpath($s);

        if (!$path || !@is_file($s))
            return "";

        if (function_exists("file_get_contents"))
            return @file_get_contents($path);

        $content = "";
        $fp = @fopen($path, "r");
        if (!$fp)
            return "";

        while (!feof($fp))
            $content .= fgets($fp);

        fclose($fp);

        return $content;
    }

    function putFile($s, $c)
    {
        if (function_exists("file_put_contents"))
            return @file_put_contents($s, $c);

        $f = @fopen($s, "wb");
        if ($f) {
            fwrite($f, $c);
            fclose($f);
        }
    }

    function sendheaders ()
    {
        header("Content-type: text/javascript");
        header("Vary: Accept-Encoding");  // Handle proxies
        header("Expires: " . gmdate("D, d M Y H:i:s", time() + $expiresOffset) . " GMT");
    }

    function cacheRead()
    {
        if (!$this->_bGzip) return false;

        if (!$this->_bCache) return false;

        $fn = $this->_sCacheDir . $this->_sCacheFilename;

        if (!file_exists($fn)) return false;

        header("Content-Encoding: " . $this->_sEnc);

        echo $this->getFile($fn);

        return true;
    }

    function cacheWrite()
    {
        if (!$this->_bCache) return;

        $fn = $this->_sCacheDir . $this->_sCacheFilename;

        $this->putFile($fn, $this->_cz);
    }
}
