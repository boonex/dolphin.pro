<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="html" encoding="utf-8" indent="yes" />

<xsl:include href="canvas_includes.xsl" />
<xsl:include href="../../base/xsl/canvas_init.xsl" />

<xsl:template match="root">

    <xsl:value-of select="/root/header" disable-output-escaping="yes" />

    <xsl:call-template name="canvas_init" />
	

				<div id="orca_main">
                    <xsl:value-of select="/root/before_content" disable-output-escaping="yes" /> 
						<xsl:if test="not(string-length(page/onload))">
						    <xsl:apply-templates select="page" />
                        </xsl:if>
                    <xsl:value-of select="/root/after_content" disable-output-escaping="yes" />
				</div>

    <xsl:value-of select="/root/footer" disable-output-escaping="yes" />


</xsl:template>

</xsl:stylesheet>
