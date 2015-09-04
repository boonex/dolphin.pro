<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">

<xsl:template name="text">

		<xsl:variable name="name" select="name()" />
	
        <fieldset class="form_field_row"><legend><xsl:value-of select="title" /></legend>
			<xsl:element name="input">
				<xsl:attribute name="type">text</xsl:attribute>
				<xsl:attribute name="name"><xsl:value-of select="$name" /></xsl:attribute>
				<xsl:attribute name="value"><xsl:value-of select="val" /></xsl:attribute>
				<xsl:for-each select="attributes/*">
					<xsl:attribute name="{name()}"><xsl:value-of select="." /></xsl:attribute>
				</xsl:for-each>
			</xsl:element>
            <br />

			<span id="f_err_{$name}" class="err">&#160;</span>
		</fieldset> 
		<br />

</xsl:template>


<xsl:template name="password">

		<xsl:variable name="name" select="name()" />

        <fieldset class="form_field_row"><legend><xsl:value-of select="title" /></legend>
			<xsl:element name="input">
				<xsl:attribute name="type">password</xsl:attribute>
				<xsl:attribute name="name"><xsl:value-of select="$name" /></xsl:attribute>
				<xsl:attribute name="value"><xsl:value-of select="val" /></xsl:attribute>
				<xsl:for-each select="attributes/*">
					<xsl:attribute name="{name()}"><xsl:value-of select="." /></xsl:attribute>
				</xsl:for-each>
			</xsl:element>
            <br />
			<span id="f_err_{$name}" class="err">&#160;</span>
        </fieldset>
		<br />

</xsl:template>

</xsl:stylesheet>
