<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">

<xsl:template match="forums/forum">

        <tr cat="{@cat}">
			<td class="forum_table_column_first bx-def-padding-sec-top">

                <div class="forum_icon_title_desc">

                    <xsl:choose>
                        <xsl:when test="1 = @new">
                            <i class="forum_icon sys-icon comments"></i>
                        </xsl:when>
                        <xsl:otherwise>
                            <i class="forum_icon sys-icon comments-alt"></i>
                        </xsl:otherwise>
                    </xsl:choose>

                    <a class="forum_title bx-def-font-h3" onclick="return f.selectForum('{uri}', 0);"><xsl:attribute name="href"><xsl:value-of select="$rw_forum" /><xsl:value-of select="uri" /><xsl:value-of select="$rw_forum_page" />0<xsl:value-of select="$rw_forum_ext" /></xsl:attribute><xsl:value-of select="title" disable-output-escaping="yes" /></a>
                    <span>
                        <xsl:value-of select="desc" disable-output-escaping="yes" />
<!--
                        <span class="forum_stat bx-def-font-grayed">
                            <xsl:if test="last != ''">
                                <span class="forum_bullet"></span>
                                <xsl:call-template name="replace_hash">
                                    <xsl:with-param name="s" select="string('[L[last update: #]]')" />
                                    <xsl:with-param name="r" select="last" />
                                </xsl:call-template>
                            </xsl:if>
                        </span>
-->
                    </span>

                </div>

			</td>
            <td class="forum_table_column_stat bx-def-font-large bx-def-padding-sec-top">
        
                <xsl:call-template name="replace_hash">
                    <xsl:with-param name="s" select="string('[L[# topics]]')"/>
                    <xsl:with-param name="r" select="topics"/>
                </xsl:call-template>

            </td>
		</tr>

</xsl:template>

</xsl:stylesheet>

