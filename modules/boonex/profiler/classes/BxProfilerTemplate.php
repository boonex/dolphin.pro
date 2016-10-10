<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolTwigTemplate');

class BxProfilerTemplate extends BxDolTwigTemplate
{
    var $_isAjaxOutput = false;

    /**
     * Constructor
     */
    function __construct(&$oConfig)
    {
        $oDb = null;
        parent::__construct($oConfig, $oDb);
        $this->_isAjaxOutput = $this->_isAjaxRequest();
    }

    function plank($sTitle, $sContent = '')
    {
        /*
        if ($this->_isAjaxOutput) {
            if (headers_sent())
                return '';
            if ($sContent && is_array($sContent))
                fb($sContent, $sTitle, FIREPHP_TABLE);
            else
                fb($sTitle . $sContent);
            return '';
        }
        */
        if ($sContent)
            $sContent = '<div class="bx_profiler_switch" onclick="bx_profiler_switch(this)">+</div><div class="bx_profiler_content">'.$sContent.'</div>';
        return '<div class="bx_profiler_plank_wrapper" style="width:' . getParam('main_div_width') . '"><div class="bx_profiler_plank bx-def-margin-sec-leftright"><span class="bx_profiler_plank_title">' . $sTitle . '</span>' . $sContent . '</div>';
    }

    function nameValue ($sName, $sVal)
    {
        return $this->_isAjaxOutput ? "{$sName}{$sVal} | " : "<u>$sName</u><b>$sVal</b>";
    }

    function table ($a, $sHighlight = '')
    {
        if ($this->_isAjaxOutput) {
            $table = array();
            foreach ($a as $r) {

                if (!$table)
                    $table[] = array_keys($r);

                $rr = array_values($r);
                if (false !== strpos($rr[0], '&#160;'))
                    $rr[0] = str_replace('&#160;', '-', $rr[0]);
                $table[] = $rr;
            }
            return $table;
        }

        $sId = md5(time() . rand());
        $s = '<table id="'.$sId.'" class="bx_profiler_table">';
        $th = '';
        foreach ($a as $r) {
            if (!$th) {
                foreach ($r as $k => $v)
                    $th .= "<th>$k</th>";
                $s .= "<thead><tr>$th</tr></thead><tbody>";
            }
            $s .= '<tr>';
            foreach ($r as $k => $v) {
                $sClass = '';
                if ($sHighlight && $k == $sHighlight)
                    $sClass = ' class="highlight" ';

                $s .= "<td $sClass>".htmlspecialchars_adv($v)."</td>";
            }
            $s .= '</tr>';
        }
        $s .= '</tbody></table>';
        $s .= '<script type="text/javascript">$(\'#'.$sId.'\').tablesorter();</script>';
        return $s;
    }

    function _isAjaxRequest ()
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) and $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
            return true;
        if (isset($_GET['bx_profiler_ajax_request']))
            return true;
        if (preg_match('/popup\.php/', bx_html_attribute($_SERVER['PHP_SELF'])))
            return true;
        if (preg_match('/vote\.php/', bx_html_attribute($_SERVER['PHP_SELF'])))
            return true;
        if (!empty($_GET['r']) && (preg_match('/^poll\/set_answer/', $_GET['r']) || preg_match('/^poll\/get_poll_block/', $_GET['r']) || preg_match('/^poll\/get_questions/', $_GET['r'])))
            return true;
        if (preg_match('/pageBuilder\.php/', bx_html_attribute($_SERVER['PHP_SELF'])) && $_REQUEST['action'] == 'load')
            return true;
        return false;
    }

}
