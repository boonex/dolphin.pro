<?php

    /**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    bx_import('BxDolMenuSimple');

    /**
     * @see BxDolMenuBottom;
     */
    class BxBaseMenuSimple extends BxDolMenuSimple
    {
        /**
         * Class constructor;
         */
        function __construct()
        {
            parent::__construct();
        }

        /*
         * Generate navigation menu source
         */
        function getCode()
        {
            if(empty($this->aItems))
                $this->load();

            if(isset($GLOBALS['bx_profiler']))
                $GLOBALS['bx_profiler']->beginMenu(ucfirst($this->sName) . ' Menu');

            $sResult = $this->getItems();

            if(isset($GLOBALS['bx_profiler']))
                $GLOBALS['bx_profiler']->endMenu(ucfirst($this->sName) . ' Menu');

            return $sResult;
        }

        function getItems()
        {
            $aTmplVars = array();
            foreach($this->aItems as $aItem) {
                if(!$this->checkToShow($aItem))
                    continue;

                list( $aItem['Link'] ) = explode( '|', $aItem['Link'] );

                $aItem['Caption'] = _t($this->replaceMetas($aItem['Caption']));
                $aItem['Link'] = $this->replaceMetas($aItem['Link']);
                $aItem['Script'] = $this->replaceMetas($aItem['Script']);

                $aTmplVars[] = array(
                    'caption' => $aItem['Caption'],
                	'caption_attr' => bx_html_attribute($aItem['Caption']),
                	'icon' => $aItem['Icon'],
                    'link' => $aItem['Script'] ? 'javascript:void(0)' : $this->oPermalinks->permalink($aItem['Link']),
                    'script' => $aItem['Script'] ? 'onclick="' . $aItem['Script'] . '"' : null,
                    'target' => $aItem['Target'] ? 'target="_blank"' : null
                );
            }

            return $GLOBALS['oSysTemplate']->parseHtmlByName('extra_' . $this->sName . '_menu.html', array('bx_repeat:items' => $aTmplVars));
        }

        function getItemsArray($iLimit=1)
        {
            if(empty($this->aItems))
                $this->load();

            if(isset($GLOBALS['bx_profiler']))
                $GLOBALS['bx_profiler']->beginMenu(ucfirst($this->sName) . ' Menu');

            $iCount = 0;
            $aTmplVars = array();
            foreach ($this->aItems as $aItem) {
                if (!$this->checkToShow($aItem))
                    continue;

                $iCount++;
                if ($iCount > $iLimit)
                    break;

                list($aItem['Link']) = explode('|', $aItem['Link']);

                $aItem['Caption'] = _t($this->replaceMetas($aItem['Caption']));
                $aItem['Link'] = $this->replaceMetas($aItem['Link']);
                $aItem['Script'] = $this->replaceMetas($aItem['Script']);

                $aTmplVars[] = array(
                    'name' => $aItem['Name'],
                    'caption' => $aItem['Caption'],
                    'caption_attr' => bx_html_attribute($aItem['Caption']),
                    'icon' => $aItem['Icon'],
                    'link' => $aItem['Script'] ? 'javascript:void(0)' : $this->oPermalinks->permalink($aItem['Link']),
                    'script' => $aItem['Script'] ? 'onclick="' . $aItem['Script'] . '"' : null,
                    'target' => $aItem['Target'] ? 'target="_blank"' : null
                );
            }

            if(isset($GLOBALS['bx_profiler']))
                $GLOBALS['bx_profiler']->endMenu(ucfirst($this->sName) . ' Menu');

            return $aTmplVars;
        }
    }
