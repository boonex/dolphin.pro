<?php

require_once( BX_DIRECTORY_PATH_BASE . 'scripts/BxBaseSharedMediaView.php' );

class BxTemplSharedMediaView extends BxBaseSharedMediaView
{
    function BxTemplSharedMediaView($iFile, $sMediaType)
    {
        BxBaseSharedMediaView::BxBaseSharedMediaView($iFile, $sMediaType);
    }
}
