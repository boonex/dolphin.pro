<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */
class Handler
{
    protected $dontReport = [

    ];

    /**
     * @param Throwable|Exception $e
     */
    public function handle($e)
    {
        if (in_array(get_class($e), $this->dontReport)) {
            return;
        }

        if ($e instanceof PDOException) {
            // lets only email for DB failures
            $this->email($e);
        }

        // default to true if not defined, so we can debug better
        $bFullError = (!defined('BX_DOL_FULL_ERROR')) ? true : BX_DOL_FULL_ERROR;
        $this->render($e, $bFullError);
    }

    /**
     * @param Throwable|Exception $e
     * @param boolean             $bFullMsg display full error message with back trace
     */
    protected function render($e, $bFullMsg = false)
    {
        ob_start();

        ?>
        <html>
        <body>
        <?php if (!$bFullMsg): ?>
            <div style="border:2px solid red;padding:4px;width:600px;margin:0px auto;">
                <div style="text-align:center;background-color:transparent;color:#000;font-weight:bold;">
                    <?= _t('_Exception_user_msg') ?>
                </div>
            </div>
        <?php else: ?>
            <div style="border:2px solid red;padding:10px;width:90%;margin:0px auto;">
                <h2 style="margin-top: 0px;"><?= _t('_Exception_uncaught_msg') ?></h2>
                <h3>Details</h3>
                <table style="table-layout: fixed;">
                    <tr>
                        <td style="font-weight: bold;">Type</td>
                        <td><?= get_class($e) ?></td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Message</td>
                        <td><?= $e->getMessage() ?></td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">File</td>
                        <td><?= $e->getFile() ?></td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Line</td>
                        <td><?= $e->getLine() ?></td>
                    </tr>
                </table>
                <h3>Trace</h3>
                <code>
                    <?= nl2br($e->getTraceAsString()) ?>
                </code>
            </div>
        <?php endif; ?>
        </body>
        </html>
        <?php

        $sOutput = ob_get_clean();
        bx_show_service_unavailable_error_and_exit($sOutput);
    }

    /**
     * @param Throwable|Exception $e
     */
    protected function email($e)
    {
        $sMailBody = _t('_Exception_uncaught_in_msg') . " " . BX_DOL_URL_ROOT . "<br /><br /> \n";
        $sMailBody .= "Type: " . get_class($e) . "<br /><br /> ";
        $sMailBody .= "Message: " . $e->getMessage() . "<br /><br /> ";
        $sMailBody .= "File: " . $e->getFile() . "<br /><br /> ";
        $sMailBody .= "Line: " . $e->getLine() . "<br /><br /> ";
        $sMailBody .= "Debug backtrace:\n <pre>" . htmlspecialchars_adv(nl2br($e->getTraceAsString())) . "</pre> ";
        $sMailBody .= "<hr />Called script: " . $_SERVER['PHP_SELF'] . "<br /> ";
        $sMailBody .= "<hr />Request parameters: <pre>" . print_r($_REQUEST, true) . " </pre>";
        $sMailBody .= "--\nAuto-report system\n";

        if (!defined('BX_DOL_REPORT_EMAIl')) {
            global $site;
            $bugReportEmail = $site['bugReportMail'];
        } else {
            $bugReportEmail = BX_DOL_REPORT_EMAIL;
        }

        sendMail(
            $bugReportEmail,
            _t('_Exception_uncaught_in_msg') . " " . BX_DOL_URL_ROOT,
            $sMailBody,
            0,
            [],
            'html',
            false,
            true
        );
    }
}