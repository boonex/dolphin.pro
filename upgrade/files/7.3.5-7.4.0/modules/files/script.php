<?php

    if ($this->oDb->getOne("SELECT COUNT(*) AS `count` FROM `bx_files_main` GROUP BY `Uri` ORDER BY `count` DESC LIMIT 1") <= 1) {

        if ($this->oDb->isIndexExists('bx_files_main', 'Uri'))
            $this->oDb->res("ALTER TABLE `bx_files_main` DROP INDEX `Uri`");

        $this->oDb->res("ALTER TABLE `bx_files_main` ADD UNIQUE (`Uri`)");
    }

    return true;
