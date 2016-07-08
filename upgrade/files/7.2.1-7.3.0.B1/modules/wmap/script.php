<?php

    $aFields = $this->oDb->getFields('bx_wmap_parts');

    if (!in_array('join_field_longitude', $aFields['original']))
        $this->oDb->res("ALTER TABLE `bx_wmap_parts` ADD `join_field_longitude` varchar(64) NOT NULL AFTER `join_field_address`");

    if (!in_array('join_field_latitude', $aFields['original']))
        $this->oDb->res("ALTER TABLE `bx_wmap_parts` ADD `join_field_latitude` varchar(64) NOT NULL AFTER `join_field_address`");

    return true;
