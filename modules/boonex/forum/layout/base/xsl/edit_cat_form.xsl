<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">

<xsl:template match="urls" />

<xsl:template match="cat">

	<div class="wnd_box">
        <div style="display:none;" id="js_code">
            var f = document.forms['orca_edit_cat'];
            orca_admin.editCatSubmit (f.elements['cat_id'].value, f.elements['cat_name'].value, f.elements['cat_order'].value, f.elements['cat_expanded'].checked);
        </div>

        <div class="wnd_title">
            <h2>
                <xsl:if test="@cat_id &gt; 0">[L[Edit group]]</xsl:if>
                <xsl:if test="0 = @cat_id">[L[New group]]</xsl:if>
            </h2>
        </div>			

        <div class="wnd_content bx-def-padding">
            <form name="orca_edit_cat" onsubmit="var x=document.getElementById('js_code').innerHTML; eval(x); return false;">

                <fieldset class="form_field_row bx-def-margin-bottom"><legend>[L[Group name:]]</legend>
                    <input class="forum_form_field bx-def-font-large bx-def-round-corners-with-border" type="text" name="cat_name" value="{cat_name}" />
                </fieldset>

                <fieldset class="form_field_row bx-def-margin-bottom"><legend>[L[Expand group by default:]]</legend>
                    <xsl:element name="input">
                        <xsl:attribute name="type">checkbox</xsl:attribute>
                        <xsl:attribute name="name">cat_expanded</xsl:attribute>
                        <xsl:if test="cat_expanded &gt; 0">
                            <xsl:attribute name="checked">checked</xsl:attribute>
                        </xsl:if>
                    </xsl:element>
                </fieldset>

                <fieldset class="form_field_row bx-def-margin-bottom"><legend>[L[Group order:]]</legend>
                    <input class="forum_form_field bx-def-font-large bx-def-round-corners-with-border" type="text" name="cat_order" value="{cat_order}" />
                </fieldset>

                <input type="hidden" name="cat_id" value="{@cat_id}" />
                <input type="hidden" name="action" value="edit_category_submit" />
                <fieldset class="form_field_row">
                    <input type="submit" name="submit_form" value="[L[Submit]]" onclick="var x=document.getElementById('js_code').innerHTML; eval(x); return false;" class="bx-btn" />
                    <input type="reset" value="[L[Cancel]]" onclick="f.hideHTML(); return false;" class="bx-btn bx-def-margin-sec-left" />
                    <div class="clear_both"></div>
                </fieldset>
            </form>
        </div>
    </div>

</xsl:template>

</xsl:stylesheet>
