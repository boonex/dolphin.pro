<?php

    $aFields = $this->oDb->getFields('sys_localization_languages');

    if (!in_array('Direction', $aFields['original']))
        $this->oDb->res("ALTER TABLE `sys_localization_languages` ADD `Direction` enum('LTR','RTL') NOT NULL DEFAULT 'LTR'");

    if (!in_array('LanguageCountry', $aFields['original']))
        $this->oDb->res("ALTER TABLE `sys_localization_languages` ADD `LanguageCountry` varchar(8) NOT NULL");

    return true;
