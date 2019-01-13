<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:exsl="http://exslt.org/common" extension-element-prefixes="exsl">

<xsl:template match="urls" />

<xsl:template match="topic">

    <tr>
        <td colspan="2"><div class="bx-def-hr bx-def-margin-sec-top bx-def-margin-sec-bottom"></div></td>
    </tr>
    <tr>
        <td class="forum_table_column_first forum_table_fixed_height">

            <div class="forum_icon_title_desc forum_icon_medium">

                <xsl:if test="string-length(first_u/avatar_medium) &gt; 0">
                    <img class="forum_user_icon forum_user_icon_medium thumbnail_image_file bx-def-thumbnail bx-def-shadow" src="{first_u/avatar_medium}" />
                </xsl:if>
                <xsl:if test="string-length(first_u/avatar_medium) = 0">
                    <div class="thumbnail_image">
                        <p class="thumbnail_image_letter bx-def-border bx-def-thumbnail bx-def-shadow">
                            <xsl:value-of select="substring(first_u/profile_title,1,1)" />
                        </p>
                    </div>
                </xsl:if>

                <a class="forum_topic_title bx-def-font-h2" href="{$rw_topic}{uri}{$rw_topic_ext}" onclick="return f.selectTopic('{uri}');">

                    <xsl:choose>
                        <xsl:when test="0 &lt; @new">
                            <i class="sys-icon bolt"></i>
                        </xsl:when>
                        <xsl:when test="0 &lt; @sticky">
                            <i class="sys-icon thumb-tack"></i>
                        </xsl:when>
                        <xsl:when test="0 &lt; @locked">
                            <i class="sys-icon lock"></i>
                        </xsl:when> 
                    </xsl:choose>

                    <xsl:value-of select="title" disable-output-escaping="yes" />

                </a>

                <span class="bx-def-font-small bx-def-font-grayed">
                    <span class="forum_stat">                    
                        <xsl:call-template name="replace_hash_percent_node">
                            <xsl:with-param name="s" select="string('[L[created by # %]]')"/>
                            <xsl:with-param name="hash" select="first_u/profile_link"/>
                            <xsl:with-param name="percent" select="first_d"/>
                        </xsl:call-template>
                        <span class="forum_bullet"></span>
                        <xsl:call-template name="replace_hash_percent_node">
                            <xsl:with-param name="s" select="string('[L[last reply by # %]]')"/>
                            <xsl:with-param name="hash" select="last_u/profile_link"/>
                            <xsl:with-param name="percent" select="last_d"/>
                        </xsl:call-template>
                    </span>
                </span>
                <span class="forum_topic_ext_info">
                    <xsl:value-of select="desc" disable-output-escaping="yes" />
                </span>
            </div>

        </td>
        <td class="forum_table_column_stat bx-def-font-h2">

            <xsl:call-template name="replace_hash">
                <xsl:with-param name="s" select="string('[L[# posts num]]')"/>
                <xsl:with-param name="r" select="count"/>
            </xsl:call-template>

        </td>
    </tr>

</xsl:template>

</xsl:stylesheet>

