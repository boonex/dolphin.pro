<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">

<xsl:template match="urls" />

<xsl:template match="profile">

    <xsl:call-template name="breadcrumbs" />

	<div id="f_header" style="height:auto;">
		<h2><xsl:value-of select="username" /></h2> 
		<span>
			<xsl:choose>
				<xsl:when test="1 = special">
					[L[Special Member]]
				</xsl:when>
				<xsl:otherwise>
					[L[Standard Member]]
				</xsl:otherwise>
			</xsl:choose>				
		</span>

		<div class="f_buttons">

			<xsl:if test="1 = 0">

			<div class="btn" onmouseover="this.style.backgroundPosition='0 25px'" onmouseout="this.style.backgroundPosition='0 0'">				
				<a href="javascript:void(0);" onclick=""><img src="{/root/urls/img}button_l.gif" /></a>
				<img src="{/root/urls/img}btn_icon_new_topic.gif" />
				<b>Button1</b>
			</div>

			<div title="rss feed" class="icn" onmouseover="this.style.backgroundPosition='0 24px'" onmouseout="this.style.backgroundPosition='0 0'" >
				<a href="javascript:void(0);" target="_blank"><img src="{/root/urls/img}button_l.gif" /></a>
				<img src="{/root/urls/img}btn_icon_rss.gif" />
			</div>

			</xsl:if>

		</div>

	</div>

	<div id="f_tbl">
		<ul class="tbl_hh">
			<li class="tbl_hh_profile">[L[Member Info]]</li>
		</ul>
		<div class="profile">
			<div class="avatar" style="position:absolute;">
				<img src="{avatar}" />
			</div>
			<div style="margin-left:57px;">
				[L[Joined:]] <xsl:value-of select="join_date" />
				<br />
				[L[Last Online:]] <xsl:value-of select="last_online" />
				<br />
				<br />
				[L[Posts:]] <xsl:value-of select="posts" />
				<br />
				[L[Last Post:]]
				<xsl:choose>
					<xsl:when test="string-length(user_last_post) &gt; 0">
						<xsl:value-of select="user_last_post" />
					</xsl:when>
					<xsl:otherwise>
						never
					</xsl:otherwise>
				</xsl:choose>					
				<br />	
				<xsl:if test="posts &gt; 0">
					<a href="javascript:void(0);" onclick="return f.search ('', 'tlts', 0, '{username}', 'topics');">[L[Find all posts by]] <xsl:value-of select="username" /></a>
				</xsl:if>
			</div>				
		</div>			
	</div>

</xsl:template>


</xsl:stylesheet>


