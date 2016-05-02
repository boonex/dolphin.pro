<?php

require_once( BX_DIRECTORY_PATH_BASE . 'scripts/BxBaseSharedMediaView.php' );

class BxTemplSharedMediaView extends BxBaseSharedMediaView
{
    function __construct($iFile, $sMediaType)
    {
        parent::__construct($iFile, $sMediaType);
    }
}
