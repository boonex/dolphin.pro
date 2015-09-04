
<cffunction name="HMAC_SHA256" returntype="string" access="private" output="false" hint="Calculates hash message authentication code using SHA256 algorithm.">

	<cfargument name="Data" type="string" required="true" />
	<cfargument name="Key" type="string" required="true" />
	<cfargument name="Bits" type="numeric" required="false" default="256" />

	<cfset var i = 0 />
	<cfset var HexData = "" />
	<cfset var HexKey = "" />
	<cfset var KeyLen = 0 />
	<cfset var KeyI = "" />
	<cfset var KeyO = "" />

	<cfset HexData = BinaryEncode(CharsetDecode(Arguments.data, "iso-8859-1"), "hex") />
	<cfset HexKey = BinaryEncode(CharsetDecode(Arguments.key, "iso-8859-1"), "hex") />
	<cfset KeyLen = Len(HexKey)/2 />

	<cfif KeyLen gt 64>
		<cfset HexKey = Hash(CharsetEncode(BinaryDecode(HexKey, "hex"), "iso-8859-1"), "SHA-256", "iso-8859-1") />
		<cfset KeyLen = Len(HexKey)/2 />
	</cfif>

	<cfloop index="i" from="1" to="#KeyLen#">
		<cfset KeyI = KeyI & Right("0"&FormatBaseN(BitXor(InputBaseN(Mid(HexKey,2*i-1,2),16),InputBaseN("36",16)),16),2) />
		<cfset KeyO = KeyO & Right("0"&FormatBaseN(BitXor(InputBaseN(Mid(HexKey,2*i-1,2),16),InputBaseN("5c",16)),16),2) />
	</cfloop>

	<cfset KeyI = KeyI & RepeatString("36",64-KeyLen) />
	<cfset KeyO = KeyO & RepeatString("5c",64-KeyLen) />

	<cfset HexKey = Hash(CharsetEncode(BinaryDecode(KeyI&HexData, "hex"), "iso-8859-1"), "SHA-256", "iso-8859-1") />
	<cfset HexKey = Hash(CharsetEncode(BinaryDecode(KeyO&HexKey, "hex"), "iso-8859-1"), "SHA-256", "iso-8859-1") />

	<cfreturn Left(HexKey,arguments.Bits/4) />

</cffunction>

<cfapplication sessionmanagement="yes">
<cfcontent type="application/json; charset=UTF-8">

<cfset secretKey = "">

<cfparam name="hash" default="" type="string">
<cfparam name="seed" default="" type="string">

<cfif not structkeyexists(session, "isLoggedIn")>
	{"error": {"code": 140, "message": "Session not defined."}}
	<cfexit>
</cfif>

<cfif secretKey eq "">
	{"error": {"code": 130, "message": "No secret key was set."}}
	<cfexit>
</cfif>

<cfif hash eq "" OR seed eq "">
	{"error": {"code": 120, "message": "Error in input."}}
	<cfexit>
</cfif>

<cfset localHash = LCase(HMAC_SHA256(seed, secretKey))>

<cfif localHash eq hash && SESSION.isLoggedIn eq True>
	{"result": {"filesystem_rootpath":""}}
	<cfexit>
</cfif>

<cfif localHash eq hash>
	{"error": {"code": 100, "message": "No session found."}}
	<cfexit>
<cfelse>
	{"error": {"code": 110, "message": "Hash did not match."}}
	<cfexit>
</cfif>