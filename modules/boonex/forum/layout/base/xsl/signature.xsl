<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">

    <xsl:template name="signature">
        <xsl:param name="text" />

                    <div class="bx-def-margin-top">
                        <a href="javascript:void(0);" onclick="jQuery('#forum_signature').toggle(f._speed);">[L[Change Signature]]</a>
                        <div id="forum_signature" style="display:none;">
                            <div class="forum_field_error_message" style="display:none" id="err_signature">[L[Signature text Error]]</div>
                            <input class="forum_form_field bx-def-font-large bx-def-round-corners-with-border"  type="text" name="signature" style="width:70%;" value="{$text}" maxlength="100" />
                        </div>
                    </div>

    </xsl:template>

</xsl:stylesheet>
