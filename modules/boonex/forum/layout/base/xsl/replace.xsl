<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">

<xsl:template name="replace_hash">
  <xsl:param name="s"/>
  <xsl:param name="r"/>
  <xsl:choose>
    <xsl:when test="contains($s,'#')">
      <xsl:value-of select="concat(substring-before($s,'#'),$r)"/>
      <xsl:call-template name="replace_hash">
        <xsl:with-param name="s" select="substring-after($s,'#')"/>
        <xsl:with-param name="r" select="$r"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="$s"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="replace_hash_node">
  <xsl:param name="s"/>
  <xsl:param name="r"/>
  <xsl:choose>
    <xsl:when test="contains($s,'#')">
      <xsl:value-of select="substring-before($s,'#')"/>
      <xsl:copy-of select="$r" />
      <xsl:call-template name="replace_hash_node">
        <xsl:with-param name="s" select="substring-after($s,'#')"/>
        <xsl:with-param name="r" select="$r"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:copy-of select="$s" />
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="replace_hash_percent_node">
  <xsl:param name="s"/>
  <xsl:param name="hash"/>
  <xsl:param name="percent"/>
  <xsl:choose>
    <xsl:when test="contains($s,'%')">
      <xsl:call-template name="replace_hash_node">
        <xsl:with-param name="s" select="concat(substring-before($s,'%'),$percent)"/>
        <xsl:with-param name="r" select="$hash"/>
      </xsl:call-template>
      <xsl:call-template name="replace_hash_node">
        <xsl:with-param name="s" select="substring-after($s,'%')"/>
        <xsl:with-param name="r" select="$hash"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="replace_hash_node">
        <xsl:with-param name="s" select="$s"/>
        <xsl:with-param name="r" select="$hash"/>
      </xsl:call-template>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="replace_hash_percent">
  <xsl:param name="s"/>
  <xsl:param name="hash"/>
  <xsl:param name="percent"/>
  <xsl:choose>
    <xsl:when test="contains($s,'%')">
      <xsl:call-template name="replace_hash">
        <xsl:with-param name="s" select="concat(substring-before($s,'%'),$percent)"/>
        <xsl:with-param name="r" select="$hash"/>
      </xsl:call-template>
      <xsl:call-template name="replace_hash">
        <xsl:with-param name="s" select="substring-after($s,'%')"/>
        <xsl:with-param name="r" select="$hash"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="replace_hash">
        <xsl:with-param name="s" select="$s"/>
        <xsl:with-param name="r" select="$hash"/>
      </xsl:call-template>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

</xsl:stylesheet>


