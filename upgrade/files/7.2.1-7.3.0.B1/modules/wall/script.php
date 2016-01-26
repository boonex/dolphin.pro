<?php

    $aFields = $this->oDb->getFields('bx_wall_events');
    if (!in_array('reposts', $aFields['original']))
        $this->oDb->res("ALTER TABLE `bx_wall_events` ADD `reposts` int(11) unsigned NOT NULL default '0' AFTER `description`");

    $aFields = $this->oDb->getFields('bx_wall_events');
    if (!in_array('active', $aFields['original']))
        $this->oDb->res("ALTER TABLE `bx_wall_events` ADD `active` tinyint(4) NOT NULL default '1'");

    $aFields = $this->oDb->getFields('bx_wall_events');
    if (!in_array('hidden', $aFields['original']))
        $this->oDb->res("ALTER TABLE `bx_wall_events` ADD `hidden` tinyint(4) NOT NULL default '0'");

    return true;
