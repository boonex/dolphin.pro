<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:exsl="http://exslt.org/common" extension-element-prefixes="exsl">

<xsl:include href="default_error2.xsl" />
<xsl:include href="rewrite.xsl" />
<xsl:include href="paginate.xsl" />
<xsl:include href="avatar.xsl" />

<xsl:template match="urls" />

<xsl:template match="search">

    <xsl:variable name="menu_links">		
        <btn href="javascript:void(0);" onclick="return f.newTopic('0')" icon="{/root/urls/img}btn_icon_new_topic.gif">[L[New Topic]]</btn>
        <btn href="javascript:void(0);" onclick="return f.showSearch()" icon="">[L[New Search]]</btn>
    </xsl:variable>

    <xsl:call-template name="box">
        <xsl:with-param name="title">[L[Search Results For:]] '<xsl:value-of select="search_text" />'</xsl:with-param>
        <xsl:with-param name="content">

            <xsl:if test="0 = count(sr)">
                <div style="text-align:center;" class="bx-def-font-large bx-def-bc-padding">
                    [L[There are no search results.]] <br />
                    [L[Please try search again.]]
                </div>
            </xsl:if>
            <xsl:if test="0 != count(sr)">
                <div class="bx-def-bc-padding">
                    <table class="forum_table_list forum_table_search_results">
                        <xsl:apply-templates select="sr" />
                    </table>
                </div>
                <xsl:if test="pages/p">
                    <xsl:call-template name="paginate">
                        <xsl:with-param name="pages" select="pages" />
                    </xsl:call-template>
                </xsl:if>
            </xsl:if>

        </xsl:with-param>
        <xsl:with-param name="menu" select="exsl:node-set($menu_links)/*" />
    </xsl:call-template>

</xsl:template>


<xsl:template match="sr">
    <tr>
        <td colspan="3"><div class="bx-def-hr bx-def-margin-sec-top bx-def-margin-sec-bottom"></div></td>
    </tr>
	<tr>
		<td style="width:70%;" class="forum_table_column_first forum_table_fixed_height" valign="top">

            <div class="forum_search_row">
                
                <xsl:if test="0 != string-length(p)">
                    <a class="colexp2" href="javascript: void(0);" onclick="return f.expandPost('p_{p/@id}');"><div class="colexp2"><i class="sys-icon">&#61525;</i></div></a>
                </xsl:if>
                
                <span>
                    <xsl:value-of select="c" /> 
                    <xsl:if test="0 != string-length(f)">
                        &#160;&#8250;&#160; 
                    </xsl:if>
                    <xsl:value-of select="f" /> 
                </span>
                <br />
                <span>
                    <a style="" href="{$rw_topic}{t/@uri}{$rw_topic_ext}" onclick="return f.selectTopic('{t/@uri}');"><xsl:value-of select="t" disable-output-escaping="yes" /></a>
                </span>

            </div>

                <div id="p_{p/@id}" style="display:none" class="bx-def-padding-sec-top">
                    <xsl:choose>
                        <xsl:when test="/root/urls/xsl_mode = 'server'">
                            <xsl:value-of select="p" disable-output-escaping="yes" />
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:choose>
                                <xsl:when test="system-property('xsl:vendor')='Transformiix'">
                                    <div id="{p/@id}_foo" style="display:none;"><xsl:value-of select="p" /></div>
                                    <script type="text/javascript">
                                        var id = '<xsl:value-of select="p/@id" />';
                                        <![CDATA[
                                        var s = document.getElementById(id + '_foo').innerHTML;
                                        s = s.replace(/&#160;/gm, ' ');
                                        s = s.replace(/\x26gt;/gm, '\x3e');
                                        s = s.replace(/\x26lt;/gm, '\x3c');
                                        document.getElementById('p_' + id).innerHTML = s;
                                        ]]>
                                    </script>
                                </xsl:when>
                                <xsl:when test="system-property('xsl:vendor')='Microsoft'">
                                    <xsl:value-of select="p" disable-output-escaping="yes" />
                                </xsl:when>
                                <xsl:otherwise>
                                    <xsl:value-of select="p" disable-output-escaping="yes" />
                                </xsl:otherwise>
                            </xsl:choose>
                        </xsl:otherwise>
                    </xsl:choose>
                </div>

		</td>
		<td style="width:15%;" class="forum_table_column_others forum_search_cell_author" valign="top">

            <xsl:call-template name="avatar">
                <xsl:with-param name="size" select="'32'" />
                <xsl:with-param name="href" select="u/profile" />
                <xsl:with-param name="thumb" select="u/avatar" />
                <xsl:with-param name="username" select="u/@name" />
                <xsl:with-param name="fullname" select="u/profile_title" />
            </xsl:call-template>

            <xsl:choose>
                <xsl:when test="string-length(u/profile) &gt; 0">
                    <a class="bx-def-margin-sec-left" href="{u/profile}"><xsl:value-of select="u/profile_title" /></a>
                </xsl:when>
                <xsl:otherwise>
                    <span class="bx-def-margin-sec-left"><xsl:value-of select="u/profile_title" /></span>
                </xsl:otherwise>
            </xsl:choose>
                        
        </td>
		<td style="width:15%;" class="forum_table_column_others forum_search_cell_date" valign="top">
            <xsl:value-of select="@date" />
        </td>
	</tr>
</xsl:template>


<xsl:template match="pages/p">

    <xsl:variable name="start" select="../../pages/p[@c=1]/@start" />
    <xsl:variable name="per_page" select="../../pages/@per_page" />        
    <xsl:variable name="num" select="../../pages/@num" />        
    <xsl:variable name="start_last" select="../p[position() = last()]/@start" />
    <xsl:variable name="start_prev" select="../p[@c = 1]/@start - $per_page" />
    <xsl:variable name="start_next" select="../p[@c = 1]/@start + $per_page" />
    <xsl:variable name="apos">'</xsl:variable>
    <xsl:variable name="apos_comma">','</xsl:variable>
    <xsl:variable name="params_func" select="concat($apos, ../../search_params/text, $apos_comma, ../../search_params/type, $apos_comma, ../../search_params/forum, $apos_comma, ../../search_params/user, $apos_comma, ../../search_params/disp, $apos)" />

    <xsl:call-template name="paginate_number">
        <xsl:with-param name="if_first" select="position() = 1" />
        <xsl:with-param name="if_last" select="position() = last()" />

        <xsl:with-param name="link_first" select="'javascript:void(0);'" />
        <xsl:with-param name="onclick_first">return document.f.search (<xsl:value-of select="$params_func" />, '0')</xsl:with-param>

        <xsl:with-param name="link_prev" select="'javascript:void(0);'" />
        <xsl:with-param name="onclick_prev">return document.f.search (<xsl:value-of select="$params_func" />, '<xsl:value-of select="$start_prev" />')</xsl:with-param>        

        <xsl:with-param name="link_next" select="'javascript:void(0);'" />
        <xsl:with-param name="onclick_next">return document.f.search (<xsl:value-of select="$params_func" />, '<xsl:value-of select="$start_next" />')</xsl:with-param>

        <xsl:with-param name="link_last" select="'javascript:void(0);'" />
        <xsl:with-param name="onclick_last">return document.f.search (<xsl:value-of select="$params_func" />, '<xsl:value-of select="$start_last" />')</xsl:with-param>

        <xsl:with-param name="link_curr" select="'javascript:void(0);'" />
        <xsl:with-param name="onclick_curr">return document.f.search (<xsl:value-of select="$params_func" />, '<xsl:value-of select="@start" />')</xsl:with-param>

        <xsl:with-param name="start" select="$start" />
        <xsl:with-param name="start_last" select="$start_last" />
        <xsl:with-param name="title" select="." />
        <xsl:with-param name="c" select="@c" />
    </xsl:call-template>

</xsl:template>

</xsl:stylesheet>


