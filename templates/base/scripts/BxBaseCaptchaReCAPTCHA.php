<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolCaptcha');

/**
 * reCAPTCHA representation.
 * @see BxDolCaptcha
 */
class BxBaseCaptchaReCAPTCHA extends BxDolCaptcha
{
    protected $_bJsCssAdded = false;
    protected $_oTemplate;

    protected $_sSkin = 'custom';
    protected $_error = null;
    protected $_sProto = 'http';
    protected $_sKeyPublic;
    protected $_sKeyPrivate;

    public function __construct ($aObject, $oTemplate)
    {
        parent::__construct ($aObject);

        if ($oTemplate)
            $this->_oTemplate = $oTemplate;
        else
            $this->_oTemplate = $GLOBALS['oSysTemplate'];

        $this->_sKeyPublic = getParam('sys_recaptcha_key_public');
        $this->_sKeyPrivate = getParam('sys_recaptcha_key_private');

        $this->_sProto = bx_proto();
    }

    /**
     * Display captcha.
     */
    public function display ($bDynamicMode = false)
    {
        $sId = 'sys-captcha-' . time() . rand(0, PHP_INT_MAX);
        $sInit = "
            Recaptcha.create('" . $this->_sKeyPublic . "', '" . $sId . "', {
                lang: '" . bx_lang_name() . "',
                theme: '" . $this->_sSkin . "',
                custom_theme_widget: '" . $sId . "'
            });
        ";

        if ($bDynamicMode) {

            $sCode = "
            <script>
                if ('undefined' == typeof(window.Recaptcha)) {
                    $.getScript('{$this->_sProto}://www.google.com/recaptcha/api/js/recaptcha_ajax.js', function(data, textStatus, jqxhr) {
                        $sInit
                    });
                } else {
                    $sInit
                }
            </script>";

        } else {

            $sCode = "
            <script>
                $(document).ready(function () {
                    $sInit
                });
            </script>";

        }

        return $this->_addJsCss($bDynamicMode) . $GLOBALS['oSysTemplate']->parseHtmlByName('reCaptcha.html', array('id' => $sId)) . $sCode;
    }

    /**
     * Check captcha.
     */
    public function check ()
    {
        require_once(BX_DIRECTORY_PATH_PLUGINS . 'recaptcha/recaptchalib.php');

        $oResp = recaptcha_check_answer(
            $this->_sKeyPrivate,
            $_SERVER["REMOTE_ADDR"],
            $this->getUserResponse (),
            process_pass_data(bx_get('recaptcha_response_field'))
        );

        if (!$oResp->is_valid) {
            $this->_error = $oResp->error;
            return false;
        }

        return true;
    }

    /**
     * Return text entered by user
     */
    public function getUserResponse ()
    {
        return process_pass_data(bx_get('recaptcha_challenge_field'));
    }

    /**
     * Check if captcha is available, like all API keys are specified.
     */
    public function isAvailable ()
    {
        return !empty($this->_sKeyPublic) && !empty($this->_sKeyPrivate);
    }

    /**
     * Add css/js files which are needed for display and functionality.
     */
    protected function _addJsCss($bDynamicMode = false)
    {
        if ($bDynamicMode)
            return '';
        if ($this->_bJsCssAdded)
            return '';
        $this->_oTemplate->addJs($this->_sProto . '://www.google.com/recaptcha/api/js/recaptcha_ajax.js');
        $this->_bJsCssAdded = true;
        return '';
    }
}
