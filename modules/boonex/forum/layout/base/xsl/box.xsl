<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">

    <xsl:template name="box">
        <xsl:param name="title" />
        <xsl:param name="content" />
        <xsl:param name="menu" />
        <xsl:variable name="uid" select="generate-id(.)" />
        <div class="disignBoxFirst bx-def-margin-top bx-def-border">
            <div class="boxFirstHeader bx-def-bh-margin">
                <div class="dbTitle">
                    <xsl:value-of select="$title" />
                </div>

                <xsl:if test="$menu">
                    <div id="dbTopMenu{$uid}" class="dbTopMenu">
                        <div class="dbTmLeft bx-def-padding-sec-right">
                            <a href="javascript:void(0)" onmouseover="javascript:moveScrollLeftAuto('dbTmContent{$uid}', true)" onmouseout="javascript:moveScrollLeftAuto('dbTmContent{$uid}', false)">&#8249;</a>
                        </div>                        
                        <div class="dbTmCenter">
                            <div id="dbTmContent{$uid}" class="dbTmContent">
                                <table class="dbTmCntLine">
                                    <tr>

                                        <xsl:for-each select="$menu">
                                            <td>
                                                <xsl:attribute name="class"><xsl:choose><xsl:when test="@active and @active = 'yes'">active</xsl:when><xsl:otherwise>notActive</xsl:otherwise></xsl:choose></xsl:attribute>
                                                <xsl:choose>
                                                    <xsl:when test="@active and @active = 'yes'">
                                                        <span><xsl:value-of select="." /></span>
                                                    </xsl:when>
                                                    <xsl:otherwise>
                                                        <a class="top_members_menu" onclick="{@onclick}" href="{@href}"><xsl:value-of select="." /></a>
                                                    </xsl:otherwise>
                                                </xsl:choose>
                                            </td>
                                            <xsl:if test="position()!=last()">
                                                <td><span class="forum_bullet"></span></td>
                                            </xsl:if>
                                        </xsl:for-each>

                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="dbTmRight bx-def-padding-sec-left">
                            <a href="javascript:void(0)" onmouseover="javascript:moveScrollRightAuto('dbTmContent{$uid}', true)" onmouseout="javascript:moveScrollRightAuto('dbTmContent{$uid}', false)">&#8250;</a>
                        </div>                        
                    </div>
                </xsl:if>

                <div class="clear_both">&#160;</div>

                <script type="text/javascript" language="javascript">
                    $('#dbTopMenu<xsl:value-of select="$uid" />').addClass('dbTopMenuHidden').parents('.disignBoxFirst').ready(function() {
                        dbTopMenuLoad('<xsl:value-of select="$uid" />');
                    });
                </script>

            </div>
            <div class="boxContent">
                <xsl:copy-of select="$content" />
            </div>
        </div>
    </xsl:template>

</xsl:stylesheet>

