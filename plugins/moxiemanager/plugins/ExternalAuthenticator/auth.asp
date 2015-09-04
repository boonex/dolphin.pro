<%@Language="VBScript" %>
<script language="JScript" runat="server" src="sha256.js"></script>
<!--#include file="json.asp"-->
<%
Private Const SECRET_KEY = ""

seed = "" & Request("seed")
hash = "" & Request("hash")

Response.ContentType = "application/json; charset=UTF-8"

If SECRET_KEY = "" Then
	Response.Write("{""error"": {""code"": 130, ""message"": ""No secret key was set.""}}")
	Response.End
End If

' Check if seed and hash matches then output all session variables with the moxiemanager_ prefix as a json array
If seed <> "" AND hash <> "" AND Session("moxiemanager_isLoggedIn") = True AND sha256_hmac(SECRET_KEY, seed) = hash Then
	Dim config
	Set config = jsObject()

	' Lets set a custom option, just an example
	'config("filesystem.rootpath") = "C:/Inetpub/wwwroot/test"

	' Session example
	' Session("moxiemanager_filesystem_rootpath") = "C:/Inetpub/wwwroot/test"

	For Each key in Session.Contents
		If InStr(key, "moxiemanager_") = 1 Then
			config(Mid(Replace(key, "_", "."), Len("moxiemanager_") + 1)) = Session(key)
		end If
	Next

	Response.Write("{""result"" : " & config.jsString & "}")
Else
	Response.Write("{""error"": {""code"": 120, ""message"": ""Error in input.""}}")
End if
%>