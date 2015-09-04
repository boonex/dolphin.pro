<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">

<xsl:template match="smiles">
	<div style="margin:10px;">
		<xsl:apply-templates select="smicon" />
	</div>
</xsl:template>

<xsl:template match="smicon">
	<div style="float:left; margin:5px;"><img src="{.}" /></div>
</xsl:template>

</xsl:stylesheet>


