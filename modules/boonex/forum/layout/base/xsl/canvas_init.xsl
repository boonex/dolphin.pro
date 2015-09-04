<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">

<xsl:template name="canvas_init">

	<script>

		var urlXsl = '<xsl:value-of select="/root/urls/xsl" />';
        var urlImg = '<xsl:value-of select="/root/urls/img" />';
        var defTitle = "<xsl:value-of select="translate(/root/title,'&quot;','&#147;')" />";
        var isLoggedIn = '<xsl:value-of select="/root/logininfo/username" />'.length ? true : false;

        var xsl_mode = '<xsl:value-of select="/root/urls/xsl_mode" />';

        var f = new Forum ('<xsl:value-of select="base"/>', <xsl:value-of select="min_point"/>);        
		document.f = f;
		var orca_login = new Login ('<xsl:value-of select="base"/>', f);
		document.orca_login = orca_login;
		<xsl:if test="1 = /root/logininfo/admin">
			var orca_admin = new Admin ('<xsl:value-of select="base"/>', f);
			document.orca_admin = orca_admin;
        </xsl:if>
 
        function orcaInitInstance(inst) { };
       
    </script>

</xsl:template>

</xsl:stylesheet>
