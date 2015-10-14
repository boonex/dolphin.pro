<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">

    <xsl:template name="paginate">
        <xsl:param name="pages" />

        <xsl:variable name="start" select="pages/p[@c=1]/@start" />
        <xsl:variable name="per_page" select="pages/@per_page" />        
        <xsl:variable name="num" select="pages/@num" />        

        <xsl:if test="$num &gt; $pages/@per_page">
            <div class="paginate paginate_simple bx-def-padding-right bx-def-padding-left clearfix">
                <div class="info_section">
                    <xsl:value-of select="$start + 1" />-<xsl:if test="$start + $per_page &gt; $num"><xsl:value-of select="$num" /></xsl:if><xsl:if test="$start + $pages/@per_page &lt;= $num"><xsl:value-of select="$start + $per_page" /></xsl:if>
                    <xsl:text> </xsl:text><span>of</span><xsl:text> </xsl:text>
                    <xsl:value-of select="pages/@num" />
                </div>

                <div class="pages_section">
                    <xsl:apply-templates select="pages/p[@start &gt;= ($start - 2*$per_page) and @start &lt;= ($start + 2*$per_page)]" />
                </div>
            </div>
        </xsl:if>
    </xsl:template>

    <xsl:template name="paginate_number">
        <xsl:param name="if_first" />
        <xsl:param name="if_last" />
        <xsl:param name="link_next" />
        <xsl:param name="onclick_next" />
        <xsl:param name="link_last" />    
        <xsl:param name="onclick_last" />
        <xsl:param name="link_first" />
        <xsl:param name="onclick_first" />
        <xsl:param name="link_prev" />    
        <xsl:param name="onclick_prev" />
        <xsl:param name="link_curr" />    
        <xsl:param name="onclick_curr" />        
        <xsl:param name="start" />
        <xsl:param name="start_last" />
        <xsl:param name="title" />
        <xsl:param name="c" />

        <xsl:if test="$if_first">
            
            <xsl:if test="0 = $start">
                <div class="paginate_btn bx-def-border bx-def-round-corners">
                    <span>
                        <i class="sys-icon backward">&#160;</i> 
                    </span>
                </div>
            </xsl:if>
            <xsl:if test="0 != $start">
<!--
                <div class="paginate_btn bx-def-border bx-def-round-corners">
                    <a title="First page" href="{$link_first}" onclick="{$onclick_first}">
                        <i class="sys-icon step-backward">&#160;</i>
                    </a>
                </div>
-->
                <div class="paginate_btn bx-def-border bx-def-round-corners">
                    <a title="Previous page" href="{$link_prev}" onclick="{$onclick_prev}">
                        <i class="sys-icon backward">&#160;</i> 
                    </a>
                </div>
            </xsl:if>

        </xsl:if>

    	<xsl:if test="$c = 0">
            <div class="paginate_page not_active bx-def-border bx-def-round-corners">
                <a href="{$link_curr}" onclick="{$onclick_curr}"><xsl:value-of select="$title" /></a>
            </div>
	    </xsl:if>
    	<xsl:if test="$c = 1">
            <div class="paginate_page active bx-def-border bx-def-round-corners">
                <span><xsl:value-of select="$title" /></span>
            </div>
        </xsl:if>

        <xsl:if test="$if_last">        

            <xsl:if test="$start &gt;= $start_last">
                <div class="paginate_btn bx-def-border bx-def-round-corners">
                    <span>
                        <i class="sys-icon forward">&#160;</i>
                    </span>
                </div>
            </xsl:if>
            <xsl:if test="$start &lt; $start_last">
                <div class="paginate_btn bx-def-border bx-def-round-corners">
                    <a title="Next page" href="{$link_next}" onclick="{$onclick_next}">
                        <i class="sys-icon forward">&#160;</i>
                    </a>
                </div>
<!--
                <div class="paginate_btn bx-def-border bx-def-round-corners">
                    <a title="Last page" href="{$link_last}" onclick="{$onclick_last}">
                        <i class="sys-icon step-forward">&#160;</i>
                    </a>
                </div>
-->
            </xsl:if>                    

        </xsl:if>

    </xsl:template>

</xsl:stylesheet>

