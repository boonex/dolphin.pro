<?php

    $aFields = $this->oDb->getFields('sys_objects_auths');
    if (!in_array('OnClick', $aFields['original']))
        $this->oDb->res("ALTER TABLE `sys_objects_auths` ADD `OnClick` varchar(255) NOT NULL AFTER `Link`");

    $aFields = $this->oDb->getFields('Profiles');
    if (!in_array('FirstName', $aFields['original']))
        $this->oDb->res("ALTER TABLE `Profiles` ADD `FirstName` varchar(255) NOT NULL");
    if (!in_array('LastName', $aFields['original']))
        $this->oDb->res("ALTER TABLE `Profiles` ADD `LastName` varchar(255) NOT NULL");

    return true;
