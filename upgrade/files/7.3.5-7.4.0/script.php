<?php

    if ($this->oDb->isFieldExists('RayMp3Files', 'Uri'))
        $this->oDb->res("ALTER TABLE `RayMp3Files` DROP INDEX `Uri`");

    $this->oDb->res("ALTER TABLE `RayMp3Files` ADD UNIQUE (`Uri`)");


    if ($this->oDb->isFieldExists('RayVideoFiles', 'Uri'))
        $this->oDb->res("ALTER TABLE `RayVideoFiles` DROP INDEX `Uri`");

    $this->oDb->res("ALTER TABLE `RayVideoFiles` ADD UNIQUE (`Uri`)");


    if ($this->oDb->isFieldExists('RayVideo_commentsFiles', 'Uri'))
        $this->oDb->res("ALTER TABLE `RayVideo_commentsFiles` DROP INDEX `Uri`");

    $this->oDb->res("ALTER TABLE `RayVideo_commentsFiles` ADD UNIQUE (`Uri`)");

    
    return true;
