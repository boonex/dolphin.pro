<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:exsl="http://exslt.org/common" extension-element-prefixes="exsl">

<xsl:template match="live_tracker">

    <xsl:variable name="menu_links">
        <btn href="javascript:void(0);" onclick="return f.newTopic('0')" icon="{/root/urls/img}btn_icon_new_topic.gif">[L[New Topic]]</btn>
    </xsl:variable>

    <xsl:call-template name="box">
        <xsl:with-param name="title">[L[Forums Spy]]</xsl:with-param>
        <xsl:with-param name="content">

            <div id="live_tracker" class="live_tracker bx-def-bc-padding">
                <xsl:apply-templates select="post" />    
            </div>

        </xsl:with-param>
        <xsl:with-param name="menu" select="exsl:node-set($menu_links)/*" />
    </xsl:call-template>

<script>
	var ret = f.livePost(<xsl:value-of select="./post/@ts" />);
</script>

</xsl:template>


<xsl:template match="post">
    <div id="live_post_{@id}" class="live_post bx-def-margin-sec-top-auto">		

        <xsl:if test="string-length(avatar_medium) &gt; 0">
            <img class="lp_img thumbnail_image_file bx-def-thumbnail bx-def-shadow" src="{avatar_medium}" />
        </xsl:if>
        <xsl:if test="string-length(avatar_medium) = 0">
            <div class="lp_img thumbnail_image">
                <p class="thumbnail_image_letter bx-def-border bx-def-thumbnail bx-def-shadow">
                    <xsl:value-of select="substring(profile_title,1,1)" />
                </p>
            </div>
        </xsl:if>


		<div class="lp_txt"><xsl:value-of select="text" disable-output-escaping="yes" /></div>
        <div class="lp_u">
            <a href="{profile}" onclick="{onclick}"><xsl:value-of select="profile_title" /></a>
            [L[said in]]
            <span class="lp_bc">
                <a onclick="return f.selectForumIndex('{cat/@uri}')"><xsl:attribute name="href"><xsl:value-of select="$rw_cat" /><xsl:value-of select="cat/@uri" /><xsl:value-of select="$rw_cat_ext" /></xsl:attribute><xsl:value-of select="cat" /></a>
                &#8250;
                <a onclick="return f.selectForum('{forum/@uri}')"><xsl:attribute name="href"><xsl:value-of select="$rw_forum" /><xsl:value-of select="forum/@uri" /><xsl:value-of select="$rw_forum_page" />0<xsl:value-of select="$rw_forum_ext" /></xsl:attribute><xsl:value-of select="forum" /></a>
                &#8250;
    			<a onclick="return f.selectTopic('{topic/@uri}')"><xsl:attribute name="href"><xsl:value-of select="$rw_topic" /><xsl:value-of select="topic/@uri" /><xsl:value-of select="$rw_topic_ext" /></xsl:attribute><xsl:value-of select="topic" disable-output-escaping="yes" /></a>
            </span>
        </div>
        <div class="lp_date bx-def-font-large"><xsl:copy-of select="date" /></div>
	</div>
</xsl:template>

</xsl:stylesheet>

