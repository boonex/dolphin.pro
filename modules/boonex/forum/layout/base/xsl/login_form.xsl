<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">

<xsl:include href="form_inputs.xsl" />

<xsl:template match="urls" /> 

<xsl:template match="login">

	    <div class="wnd_box">

			<div class="wnd_title">
                <h2>[L[Login]]</h2>
			</div>

			<div class="wnd_content">
			<form name="orca_login_form" onsubmit="var s=document.getElementById('js_code').innerHTML; eval(s); return document.ret;">

				<div>
					<xsl:apply-templates select="login_form" />
				</div>

				<div style="display:none;" id="js_code">
					var f = document.forms['orca_login_form'];
					document.ret = true;
					<xsl:for-each select="login_form/*">								
						var val<xsl:value-of select="name()" /> = f.elements['<xsl:value-of select="name()" />'].value;
						if (!val<xsl:value-of select="name()" />.match(<xsl:value-of select="regexp" />))
						{
							var e = document.getElementById('f_err_<xsl:value-of select="name()" />');
							e.innerHTML = '<xsl:value-of select="err" />';
							e.style.display = 'inline';
							document.ret = false;
						}
						else
						{
							var e = document.getElementById('f_err_<xsl:value-of select="name()" />');
							e.style.display = 'none';
						}
					</xsl:for-each>
					if (document.ret)
					{
						orca_login.loginFormSubmit (
						<xsl:for-each select="login_form/*">						
							f.elements['<xsl:value-of select="name()" />'].value<xsl:if test="position() != last()">,</xsl:if>
						</xsl:for-each>);
					}
					document.ret = false;
				</div>

				<input type="hidden" name="action" value="login_form_submit" />
				<input type="submit" name="sbmt" value="sbmt"  style="position:relative; left:-999px;"/>

				<div class="forum_default_padding">
                        <input type="submit" name="submit_form" value="[L[Submit]]" onclick="var x=document.getElementById('js_code').innerHTML; eval(x); return document.ret;" />
                        <input type="reset" value="[L[Cancel]]" onclick="f.hideHTML(); return false;" class="forum_default_margin_left" />
				</div>				

			</form>
		    </div>

		</div>

</xsl:template>

<xsl:template match="login_form">
	<xsl:for-each select="*">	
		<xsl:choose>

			<xsl:when test="type = 'text'">
				<xsl:call-template name="text" />
			</xsl:when>

			<xsl:when test="type = 'password'">
				<xsl:call-template name="password" />
			</xsl:when>

		</xsl:choose>
    </xsl:for-each>

</xsl:template>

</xsl:stylesheet>
