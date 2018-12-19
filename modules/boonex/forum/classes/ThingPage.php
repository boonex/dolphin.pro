<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

// parent object for all pages classes

class ThingPage extends Thing
{

// public functions

    /**
     * constructor
     */
    function __construct ()
    {
    }

    function getLangs ()
    {
        global $gConf;

        $a = array ();
        if (isset($gConf['dir']['langs'])) {
            $d = dir($gConf['dir']['langs']);
            while (FALSE !== ($entry = $d->read())) {
                if ($entry[0] == '.')
                    continue;
                $a[] = substr($entry, 0, 2);
            }
        }
        return $a;
    }

    function getLangsXml ()
    {
        return '<langs>' . array2xml($this->getLangs(), 'lang') . '</langs>';
    }

    function getUrlsXml ()
    {
        global $gConf;

        $ret = '';

        $ret .= "<urls>";
        $ret .= "<base>{$gConf['url']['base']}</base>\n";
        $ret .= "<xsl_mode>{$gConf['xsl_mode']}</xsl_mode>";
        $ret .= "<icon>{$gConf['url']['icon']}</icon>";
        $ret .= "<img>{$gConf['url']['img']}</img>";
        $ret .= "<css>{$gConf['url']['css']}</css>";
        $ret .= "<xsl>{$gConf['url']['xsl']}</xsl>";
        $ret .= "<js>{$gConf['url']['js']}</js>";
        $ret .= "<editor>{$gConf['url']['editor']}</editor>";
        $ret .= "</urls>\n";

        return $ret;
    }

    function addHeaderFooter (&$li, $content)
    {
        global $gConf, $l;
        global $glBeforeContent;
        global $glAfterContent;
        global $glAddToHeadSection;

        $ret = '';

        $ret .= "<root>\n";

                                                                                                                                                                                                                        $l('JHJldCAuPSAnPGRpc2FibGVfYm9vbmV4X2Zvb3RlcnM+JyAuICFnZXRQYXJhbSgnZW5hYmxlX2RvbHBoaW5fZm9vdGVyJykgLiAnPC9kaXNhYmxlX2Jvb25leF9mb290ZXJzPic7');

        $ret .= '<index_begin><![CDATA[' . $GLOBALS['glIndexBegin'] . ']]></index_begin>';
        $ret .= '<index_end><![CDATA[' . $GLOBALS['glIndexEnd'] . ']]></index_end>';
        
        $ret .= '<before_content><![CDATA['.$glBeforeContent.']]></before_content>';
        $ret .= '<after_content><![CDATA['.$glAfterContent.']]></after_content>';
        $ret .= '<add_to_head_section><![CDATA['.$glAddToHeadSection.']]></add_to_head_section>';

        $ret .= "<min_point>{$gConf['min_point']}</min_point>\n";

        $ret .= "<base>{$gConf['url']['base']}</base>\n";

        $ret .= "<title>" . (isset($gConf['title']) && $gConf['title'] ? $gConf['title'] : $gConf['def_title']) . "</title>\n";

        $integration_xml = '';
        @include ($gConf['dir']['base'] . 'integrations/' . BX_ORCA_INTEGRATION . '/xml.php');
        $ret .= $integration_xml;

        $ret .= $this->getUrlsXml ();

        if (is_array($li)) {
            $ret .= "<logininfo>";
            foreach ($li as $k => $v) {
                if ('role' == $k)
                    encode_post_text ($v);
                elseif ('profile_title' == $k)
                    encode_post_text ($v);
                $ret .= "<$k>$v</$k>";
            }
            $ret .= "</logininfo>";

            if (1 == $li['admin']) {
                $ret .= $this->getLangsXml();
            }
        }

        $ret .= "<page>\n";

        $ret .= $content;

        $ret .= "</page>\n";

        $ret .= "</root>\n";

        return $ret;
    }

    /**
     * returns page XML
     */
    function getPageXML ($first_load = 1, &$p)
    {
        return $this->addHeaderFooter ($p, $this->content);
    }

    /**
     * write cache to a file
     *	@param $fn	filename to write to
     *	@param $s	string to write
     */
    function cacheWrite ($fn, $s)
    {
        global $gConf;

        if (!$gConf['cache']['on']) return;

        $f = fopen ($gConf['dir']['xmlcache'] . $fn, "w");

        if (!$f) {
            $mk = new Mistake ();
            $mk->log ("ThingPage::readCache - can not open file({$gConf['dir']['xmlcache']}$fn) for writing");
            $mk->displayError ("[L[Site is unavailable]]");
        }

        fwrite ($f, $s);

        fclose ($f);
    }

    /**
     * read cache from a file
     *	@param $fn		filename to read from
     *	@param return	string from a file
     */
    function cacheRead ($fn)
    {
        global $gConf;

        $f = fopen ($gConf['dir']['xmlcache'] . $fn, "r");

        if (!$f) {
            $mk = new Mistake ();
            $mk->log ("ThingPage::readCache - can not open file({$gConf['dir']['xmlcache']}$fn) for reading");
            $mk->displayError ("[L[Site is unavailable]]");
        }

        $s = '';
        while ($st = fread ($f, 1024)) $s .= $st;

        fclose ($f);

        return $s;
    }

    /**
     * check if cache is available
     *	@param $fn		filename to check
     *	@param return	true if cache is available
     */
    function cacheExists ($fn)
    {
        global $gConf;
        return file_exists ($gConf['dir']['xmlcache'] . $fn);
    }

    /**
     * check if cache is enabled
     */
    function cacheEnabled ()
    {
        global $gConf;
        return $gConf['cache']['on'];
    }

// private functions

}
