<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">

<xsl:template match="urls" />

<xsl:template match="new_topic">

    <xsl:call-template name="box">
        <xsl:with-param name="title">[L[Post Reply:]]</xsl:with-param>
        <xsl:with-param name="content">

            <div class="bx-def-bc-padding">

			    <form action="{/root/urls/base}" enctype="multipart/form-data" method="post" name="post_reply" target="post_reply" onsubmit="return f.checkPostTopicValues(null, this.topic_text, this.signature, null, false)">

					<input type="hidden" name="action" value="post_reply" />

					<xsl:element name="input">
						<xsl:attribute name="type">hidden</xsl:attribute>
						<xsl:attribute name="name">forum_id</xsl:attribute>
						<xsl:attribute name="value"><xsl:value-of select="forum/id" /></xsl:attribute>
					</xsl:element>

					<xsl:element name="input">
						<xsl:attribute name="type">hidden</xsl:attribute>
						<xsl:attribute name="name">topic_id</xsl:attribute>
						<xsl:attribute name="value"><xsl:value-of select="topic/id" /></xsl:attribute>		
					</xsl:element>

                    <div class="forum_field_error_message" style="display:none" id="err_topic_text">[L[Topic text Error]]</div>

					<textarea id="tinyEditor" name="topic_text" style="width:100%; height:316px;">&#160;</textarea>

                    <xsl:call-template name="attachments">
                        <xsl:with-param name="files"></xsl:with-param>
                    </xsl:call-template>

                    <xsl:call-template name="signature">
                        <xsl:with-param name="text" select="signature" />
                    </xsl:call-template>

                    <div class="bx-def-margin-top">

                        <input type="submit" name="post_submit" value="[L[Submit]]" onclick="tinyMCE.triggerSave();" class="bx-btn" />
                        <input type="reset" name="cancel" value="[L[Cancel]]" onclick="return f.cancelReply()" class="bx-btn" />
                        <div class="clear_both"></div>

                    </div>

    			</form>

			    <iframe width="1" height="1" border="0" name="post_reply" style="border:none;" ></iframe>

            </div>

        </xsl:with-param>
    </xsl:call-template>

</xsl:template>

</xsl:stylesheet>


