<?php

bx_import('BxBaseEditorTinyMCE');

/**
 * @see BxDolEditor
 */
class BxTemplEditorTinyMCE extends BxBaseEditorTinyMCE
{
    public function __construct ($aObject, $oTemplate = false)
    {
        parent::__construct ($aObject, $oTemplate);
    }
}
