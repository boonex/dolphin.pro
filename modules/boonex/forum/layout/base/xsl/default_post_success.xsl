<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">

<xsl:template match="/">

    <xsl:call-template name="box">
        <xsl:with-param name="title">[L[New topic created]]</xsl:with-param>
        <xsl:with-param name="content">

            <div class="forum_centered_msg">
                <b>[L[New topic created]]</b>
                <br />
                <a href="javascript:void(0);" onclick="f.selectForum('{forum/uri}', 0);">[L[return to forum index]]</a>
            </div>

        </xsl:with-param>
    </xsl:call-template>

</xsl:template>

</xsl:stylesheet>


