<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">

<xsl:template match="error">

    <div style="padding:50px;">
    <div style="padding:20px; background-color:red; color:white; width:600px; margin-left:auto; margin-right:auto; border:1px solid black; font-weight:bold;">

        <xsl:apply-templates />
	
    </div>
    </div>

</xsl:template>

</xsl:stylesheet>


