<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */
class Handler
{
    protected $dontReport = [

    ];

    public function handle(Exception $e)
    {
        if (in_array(get_class($e), $this->dontReport)) {
            return;
        }

        if ($e instanceof PDOException) {
            // lets only email for DB failures
            //$this->email($e);
        }

        $this->render($e, (BX_DOL_FULL_ERROR === true));
    }

    protected function render(Exception $e, $bFullMsg = false)
    {
        ob_start();

        ?>
        <html>
        <body>
        <?php if (!$bFullMsg): ?>
            <div style="border:2px solid red;padding:4px;width:600px;margin:0px auto;">
                <div style="text-align:center;background-color:red;color:white;font-weight:bold;">
                    Something went wrong, please try reloading the page.
                </div>
            </div>
        <?php else: ?>
            <div style="border:2px solid red;padding:10px;width:90%;margin:0px auto;">
                <h2 style="margin-top: 0px;">An uncaught exception was thrown</h2>
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

    protected function email(Exception $e)
    {
        $sMailBody = "An uncaught exception was thrown in " . BX_DOL_URL_ROOT . "<br /><br /> \n";

        $sMailBody .= "Type: " . get_class($e) . "<br /><br /> ";

        $sMailBody .= "Message: " . $e->getMessage() . "<br /><br /> ";

        $sMailBody .= "File: " . $e->getFile() . "<br /><br /> ";

        $sMailBody .= "Line: " . $e->getLine() . "<br /><br /> ";

        $sMailBody .= "Debug backtrace:\n <pre>" . htmlspecialchars_adv(nl2br($e->getTraceAsString())) . "</pre> ";

        $sMailBody .= "<hr />Called script: " . $_SERVER['PHP_SELF'] . "<br /> ";

        $sMailBody .= "<hr />Request parameters: <pre>" . print_r($_REQUEST, true) . " </pre>";

        $sMailBody .= "--\nAuto-report system\n";

        sendMail(
            BX_DOL_REPORT_EMAIL,
            "An uncaught exception was thrown" . BX_DOL_URL_ROOT,
            $sMailBody,
            0,
            [],
            'html',
            true,
            true
        );
    }
}