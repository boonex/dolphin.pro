<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">

    <xsl:template name="avatar">
        <xsl:param name="size" />
        <xsl:param name="href" />
        <xsl:param name="thumb" />
        <xsl:param name="username" />
        <xsl:param name="fullname" />

        <div class="thumbnail_image" style="width:{$size}px; height:{$size}px;">

            <xsl:choose>
                <xsl:when test="string-length($href) &gt; 0">
                    <a href="{$href}" title="{$username}">
                        <xsl:call-template name="avatar_image">
                            <xsl:with-param name="size" select="$size" />
                            <xsl:with-param name="href" select="$href" />
                            <xsl:with-param name="thumb" select="$thumb" />
                            <xsl:with-param name="username" select="$username" />
                            <xsl:with-param name="fullname" select="$fullname" />
                        </xsl:call-template>
                    </a>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:call-template name="avatar_image">
                        <xsl:with-param name="size" select="$size" />
                        <xsl:with-param name="href" select="$href" />
                        <xsl:with-param name="thumb" select="$thumb" />
                        <xsl:with-param name="username" select="$username" />
                        <xsl:with-param name="fullname" select="$fullname" />
                    </xsl:call-template>
                </xsl:otherwise>
            </xsl:choose>

        </div>

    </xsl:template>

    <xsl:template name="avatar_image">
        <xsl:param name="size" />
        <xsl:param name="href" />
        <xsl:param name="thumb" />
        <xsl:param name="username" />
        <xsl:param name="fullname" />

        <xsl:choose>
            <xsl:when test="string-length($thumb) &gt; 0">
                <img class="thumbnail_image_file bx-def-thumbnail bx-def-shadow" alt="{$username}" style="background-image:url({$thumb}); width:{$size}px; height:{$size}px;" src="{/root/urls/img}sp.gif"/>
            </xsl:when>
            <xsl:otherwise>
			    <p style="width:{$size}px; height:{$size}px; font-size:{$size*0.65}px; line-height:{$size}px;" class="thumbnail_image_letter bx-def-border bx-def-thumbnail bx-def-shadow"><xsl:value-of select="substring($fullname,1,1)"/></p>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

</xsl:stylesheet>

