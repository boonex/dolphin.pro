<?php

bx_import('BxBaseCaptchaReCAPTCHA');

/**
 * @see BxDolCaptcha
 */
class BxTemplCaptchaReCAPTCHA extends BxBaseCaptchaReCAPTCHA
{
    public function __construct ($aObject, $oTemplate = false)
    {
        parent::__construct ($aObject, $oTemplate);
    }
}
