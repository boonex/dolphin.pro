<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">

<xsl:include href="rewrite.xsl" />
<xsl:include href="replace.xsl" />

<xsl:template match="urls" />

<xsl:template match="forum">

		<tr cat="{@cat}">
            <td style="width:70%;" class="forum_table_column_first bx-def-padding-sec-top">
                <div style="position:relative;">

                    <div class="forum_icon_title_desc">

                        <xsl:choose>
                            <xsl:when test="1 = @new">
                                <i class="forum_icon sys-icon comments"></i>
                            </xsl:when>
                            <xsl:otherwise>
                                <i class="forum_icon sys-icon comments-alt"></i>
                            </xsl:otherwise>
                        </xsl:choose>

                        <a class="forum_title bx-def-font-h3" href="{$rw_forum}{uri}{$rw_forum_page}0{$rw_forum_ext}" onclick="return f.selectForum('{uri}', 0);"><xsl:value-of select="title" disable-output-escaping="yes" /></a>

                        <span>
                            <xsl:value-of select="desc" disable-output-escaping="yes" />
                            <span class="forum_stat bx-def-font-grayed">
                                <span class="forum_bullet"></span>
                                <xsl:call-template name="replace_hash">
                                    <xsl:with-param name="s" select="string('[L[# topics]]')"/>
                                    <xsl:with-param name="r" select="topics"/>
                                </xsl:call-template>
                                <span class="forum_bullet"></span>
                                <xsl:call-template name="replace_hash">
                                    <xsl:with-param name="s" select="string('[L[# posts]]')"/>
                                    <xsl:with-param name="r" select="posts"/>
                                </xsl:call-template>
                                <xsl:if test="last != ''">
                                    <span class="forum_bullet"></span>
                                    <xsl:call-template name="replace_hash">
                                        <xsl:with-param name="s" select="string('[L[last update: #]]')"/>
                                        <xsl:with-param name="r" select="last"/>
                                    </xsl:call-template>
                                </xsl:if>
                            </span>

                        </span>

                    </div>

                    <div style="position:absolute; right:10px; top:5px; width:80px;">
                        <div title="[L[edit]]" class="icn" onmouseover="this.style.backgroundPosition='0 24px'" onmouseout="this.style.backgroundPosition='0 0'" >
                            <a href="javascript:void(0);" onclick="orca_admin.editForum({@id})"><img src="{/root/urls/img}button_l.gif" /></a>
                            <img src="{/root/urls/img}btn_icon_edit.gif" />
                        </div>
                        <div title="[L[delete]]" class="icn" onmouseover="this.style.backgroundPosition='0 24px'" onmouseout="this.style.backgroundPosition='0 0'" >
                            <a href="javascript:void(0);" onclick="orca_admin.delForum({@id})"><img src="{/root/urls/img}button_l.gif" /></a>
                            <img src="{/root/urls/img}btn_icon_delete.gif" />
                        </div>					
                    </div>
                </div>

			</td>
		</tr>

</xsl:template>

</xsl:stylesheet>


