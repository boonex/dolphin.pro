<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">

    <xsl:include href="box.xsl" />
    
    <xsl:include href="../../base/xsl/home.xsl" />

<!--

<xsl:include href="cat_forums.xsl" />
<xsl:include href="live_tracker.xsl" />

<xsl:template match="urls" />
<xsl:template match="logininfo" />

<xsl:template match="page">

	<div id="f_header">
        <h2>
            [L[Forums Index]]
			<xsl:if test="1 = /root/logininfo/admin">
                <span>(
                    <a href="javascript: orca_admin.editCategories(); void(0);">[L[Manage Forum]]</a> | 
                    <a href="javascript: orca_admin.reportedPosts(); void(0);">[L[Reported Posts]]</a> | 
                    [L[Compile Langs]]:
                        <xsl:for-each select="../langs/lang">
                            <a href="javascript:void(0);" onclick="return orca_admin.compileLangs('{.}')"><xsl:value-of select="." /></a>
                            <xsl:if test="position() != last()">&#160;</xsl:if>
                        </xsl:for-each>
                    )</span>
			</xsl:if>            
        </h2>
	</div>

	<div id="f_tbl">
		<ul class="tbl_hh">
			<li class="tbl_h_forum">[L[Forums]]</li>
			<li class="tbl_h_topic">[L[Topics]]</li>
			<li class="tbl_h_date">[L[Latest Post]]</li>
		</ul>
        <xsl:apply-templates select="categs" />
	</div>

    <xsl:apply-templates select="live_tracker" />
	
</xsl:template>


<xsl:template match="categ">		

	<xsl:element name="ul">
		<xsl:attribute name="style">height:52px;</xsl:attribute>
		<xsl:attribute name="id">cat<xsl:value-of select="@id" /></xsl:attribute>
		<li class="tbl_c_forum">
            <a class="colexp" href="{$rw_cat}{@uri}{$rw_cat_ext}" onclick="return f.selectCat('{@uri}', 'cat{@id}');">
				<div class="colexp">
                    <xsl:if test="count(forums/forum) &gt; 0">
						<xsl:attribute name="style">background-position:0px -32px</xsl:attribute>
					</xsl:if>
					&#160;
				</div>
			</a>
            <a href="{$rw_cat}{@uri}{$rw_cat_ext}" onclick="return f.selectCat('{@uri}', 'cat{@id}');"><xsl:value-of select="title" disable-output-escaping="yes" /></a>
		</li>
	</xsl:element>
		
    <xsl:if test="count(forums/forum) &gt; 0">
        <div><xsl:apply-templates select="forums/forum" /></div>
	</xsl:if>

</xsl:template>

-->

</xsl:stylesheet>
