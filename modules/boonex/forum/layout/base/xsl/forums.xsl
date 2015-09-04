<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:exsl="http://exslt.org/common" extension-element-prefixes="exsl">

<xsl:include href="cat_forums.xsl" />

<xsl:template match="root">
    <xsl:apply-templates select="forums/forum" />
</xsl:template>

</xsl:stylesheet>
