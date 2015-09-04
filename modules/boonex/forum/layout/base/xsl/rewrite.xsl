<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">

    <xsl:variable name="base_url" select="/root/urls/base" />

    <xsl:variable name="rw_cat" select="concat($base_url, 'group/')" /><!-- cat_name_here --><xsl:variable name="rw_cat_ext" select="'.htm'" />

    <xsl:variable name="rw_forum" select="concat($base_url, 'forum/')" /><!-- forum_name_here --><xsl:variable name="rw_forum_page" select="'-'" /><!-- forum_page_here --><xsl:variable name="rw_forum_ext" select="'.htm'" />

    <xsl:variable name="rw_topic" select="concat($base_url, 'topic/')" /><!-- topic_name_here --><xsl:variable name="rw_topic_ext" select="'.htm'" />

    <xsl:variable name="rw_user" select="concat($base_url, 'user/')" /><!-- user_name_here --><xsl:variable name="rw_user_ext" select="'.htm'" />


    <xsl:variable name="rw_rss_forum" select="concat($base_url, 'rss/forum/')" /><!-- forum_name_here --><xsl:variable name="rw_rss_forum_ext" select="'.htm'" />
    <xsl:variable name="rw_rss_topic" select="concat($base_url, 'rss/topic/')" /><!-- topic_name_here --><xsl:variable name="rw_rss_topic_ext" select="'.htm'" />
    <xsl:variable name="rw_rss_user" select="concat($base_url, 'rss/user/')" /><!-- user_name_here --><xsl:variable name="rw_rss_user_ext" select="'.htm'" />
    <xsl:variable name="rw_rss_all" select="concat($base_url, 'rss/all.htm')" />

</xsl:stylesheet>


