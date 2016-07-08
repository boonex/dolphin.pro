<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

// class for XML/XSL tranformation

define ('BXXSLTRANSFORM_FF', 4); // xml and xsl data in two files
define ('BXXSLTRANSFORM_FS', 2); // xml data in the file and xsl data in the string
define ('BXXSLTRANSFORM_SF', 1); // xml data in the string and xsl data in the file
define ('BXXSLTRANSFORM_SS', 0); // xml and xsl data in strings

define ('BXXSLTRANSFORM_XML_FILE', 2); // xml file bit
define ('BXXSLTRANSFORM_XSL_FILE', 1); // xsl file bit

class BxXslTransform
{
    var $_tmp_dir = "/tmp/";
    var $_mode;
    var $_xml;
    var $_xsl;
    var $_header = 'Content-Type: application/xml; charset=UTF-8';

    /**
     * contructor
     *	@param $xml		xml for transformation
     *	@param $xsl		xsl for transformation
     *	@param $mode	xml/xsl transformation mode (BXXSLTRANSFORM_FF|BXXSLTRANSFORM_FS|BXXSLTRANSFORM_SF|BXXSLTRANSFORM_SS)
     */
    function __construct ($xml, $xsl, $mode)
    {
        $this->_mode = $mode;
        $this->_xml = $xml;
        $this->_xsl = $xsl;
    }

    function process ()
    {
        global $gConf;

        if ('client' == $gConf['xsl_mode']) {
            echo 'depricated'; exit;
        }

        header ($this->_header);

        // xml: string, xsl: file
        if ( !($this->_mode & BXXSLTRANSFORM_XML_FILE) && ($this->_mode & BXXSLTRANSFORM_XSL_FILE) ) {
            $args = array(
                '/_xml' => $this->_xml,
            );

            validate_unicode ($this->_xml);

            if (((int)phpversion()) >= 5) {

                $xml = new DOMDocument();
                if (!@$xml->loadXML($this->_xml)) {
                    $mk = new Mistake ();
                    $mk->log ("BxXslTransform::process - can not load xml:\n " . $this->_xml);
                    $mk->displayError ("[L[Site is unavailable]]");
                }

                $xsl = new DomDocument();
                $xsl->load($this->_xsl);

                $proc = new XsltProcessor();
                $proc->importStyleSheet($xsl);
                $res = $proc->transformToXML($xml);

            } else {

                if (function_exists('domxml_xslt_stylesheet_file')) {
                    $xmldoc = new DomDocument ($this->_xml);
                    $xsldoc = domxml_xslt_stylesheet_file($this->_xsl);
                    $result =  $xsldoc->process($xmldoc);
                    $res =  $xsldoc->result_dump_mem($result);
                } elseif (function_exists ('xslt_create')) {
                    $xh = xslt_create();
                    xslt_setopt($xh, XSLT_SABOPT_IGNORE_DOC_NOT_FOUND);
                    $res = xslt_process ($xh, 'arg:/_xml', $this->_xsl, NULL, $args);
                    xslt_free($xh);
                } else {
                    die('Server XSLT support is not enabled, try to use client XSL transformation http://your-domain/orca_folder/?xsl_mode=client');
                }
            }

            return $res;
        }

        // xml: file, xsl: file
        if ( ($this->_mode & BXXSLTRANSFORM_XML_FILE) && ($this->_mode & BXXSLTRANSFORM_XSL_FILE) ) {

            if (((int)phpversion()) >= 5) {
                $xml = new DOMDocument();
                $xml->load($this->_xml);

                $xsl = new DomDocument();
                $xsl->load($this->_xsl);

                $proc = new XsltProcessor();
                $proc->importStyleSheet($xsl);
                $res = $proc->transformToXML($xml);
            } else {
                if (function_exists('domxml_xslt_stylesheet_file')) {
                    $xmldoc = new DomDocument ($this->_xml);
                    $xsldoc = domxml_xslt_stylesheet_file($this->_xsl);
                    $result =  $xsldoc->process($xmldoc);
                    $res =  $xsldoc->result_dump_mem($result);
                } elseif (function_exists ('xslt_create')) {
                    $xh = xslt_create();
                    $res = xslt_process ($xh, $this->_xml, $this->_xsl, NULL, $args);
                    xslt_setopt($xh, XSLT_SABOPT_IGNORE_DOC_NOT_FOUND);
                    xslt_free($xh);
                } else {
                    die('XSLT support is not enabled');
                }
            }
            return $res;
            //return  `/opt/jre1.5.0_06/bin/java -jar /opt/saxon/saxon.jar -ds {$this->_xml} {$this->_xsl}`;
        }

        return "<h1>not supported</h1>";
    }

    function setHeader ($s)
    {
        $this->_header = $s;
    }

// private methods

    function _genFilename ()
    {
        list ($usec, $sec) = explode (' ', microtime());
        srand ((float) $sec + ((float) $usec * 100000));
        return $this->_tmp_dir . '/' . rand() . '_' . rand() . '.xml';
    }

}
