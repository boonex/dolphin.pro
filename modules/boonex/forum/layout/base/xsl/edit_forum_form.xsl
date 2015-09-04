<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">

<xsl:template match="urls" />

<xsl:template match="forum">

	<div class="wnd_box">
	    <div style="display:none;" id="js_code">
            var f = document.forms['orca_edit_forum'];						
            orca_admin.editForumSubmit (
                f.elements['cat_id'].value, 
                '<xsl:value-of select="cat_uri" />',
                f.elements['forum_id'].value,
                f.elements['forum_title'].value,
                f.elements['forum_desc'].value,
                f.elements['forum_type'].value,
				f.elements['forum_order'].value
            );
		</div>			

        <div class="wnd_title">
            <h2>
                <xsl:if test="@forum_id &gt; 0">[L[Edit forum]]</xsl:if>
                <xsl:if test="0 = @forum_id">[L[New forum]]</xsl:if>
            </h2>
        </div>			

		<div class="wnd_content bx-def-padding">
            <form name="orca_edit_forum" onsubmit="var x=document.getElementById('js_code').innerHTML; eval(x); return false;">

                <fieldset class="form_field_row bx-def-margin-bottom"><legend>[L[Forum title:]]</legend>
                    <input class="forum_form_field bx-def-font-large bx-def-round-corners-with-border" type="text" name="forum_title" value="{title}" /> 
                </fieldset>

                <fieldset class="form_field_row bx-def-margin-bottom"><legend>[L[Forum description:]]</legend>
                    <input class="forum_form_field bx-def-font-large bx-def-round-corners-with-border" type="text" name="forum_desc" value="{desc}" /> 
                </fieldset>

                <fieldset class="form_field_row bx-def-margin-bottom"><legend>[L[Forum order:]]</legend>
                    <input class="forum_form_field bx-def-font-large bx-def-round-corners-with-border" type="text" name="forum_order" value="{order}" /> 
                </fieldset>

                <fieldset class="form_field_row bx-def-margin-bottom"><legend>[L[Forum type:]]</legend>
                    <select name="forum_type" class="bx-def-font-large">
                        <xsl:element name="option">
                            <xsl:attribute name="value">public</xsl:attribute>
                            <xsl:if test="'public' = type">
                                <xsl:attribute name="selected">selected</xsl:attribute>
                            </xsl:if>
                            [L[public]]
                        </xsl:element>
                        <xsl:element name="option">
                            <xsl:attribute name="value">private</xsl:attribute>
                            <xsl:if test="'private' = type"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
                            [L[private]]
                        </xsl:element>
                    </select>
                </fieldset>

				<input type="hidden" name="forum_id" value="{@forum_id}" />
				<input type="hidden" name="cat_id" value="{cat_id}" />
				<input type="hidden" name="action" value="edit_forum_submit" />

				<fieldset class="form_field_row">
                    <input type="submit" name="submit_form" value="[L[Submit]]" onclick="var x=document.getElementById('js_code').innerHTML; eval(x); return false;" class="bx-btn" />
                    <input type="reset" value="[L[Cancel]]" onclick="f.hideHTML(); return false;" class="bx-btn bx-def-margin-sec-left"  />
                    <div class="clear_both"></div>
                </fieldset>

			</form>
        </div>

	</div>

</xsl:template>

</xsl:stylesheet>


