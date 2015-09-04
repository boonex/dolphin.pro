<%@page import="java.io.*,java.security.*,javax.crypto.*,javax.crypto.spec.*,java.util.*" %><%
String secretKey = "";
String seed = request.getParameter("seed");
String hash = request.getParameter("hash");
Boolean isLoggedIn = ("" + session.getAttribute("moxiemanager.isLoggedIn")).equals("true");
PrintWriter writer = response.getWriter();

response.setContentType("application/json; charset=UTF-8");

if (seed == null || hash == null) {
	printError(120, "Error in input", writer);
	return;
}

if (secretKey.length() == 0) {
	printError(130, "No secret key was set", writer);
	return;
}

if (!isLoggedIn) {
	printError(180, "Not authenticated", writer);
	return;
}

if (!hmacSHA256(secretKey, seed).equals(hash)) {
	printError(190, "Hash mismatch", writer);
	return;
}

StringBuffer sessionBuffer = new StringBuffer();
for (Enumeration e = session.getAttributeNames(); e.hasMoreElements(); ) {     
	String attribName = (String) e.nextElement();
	Object attribValue = session.getAttribute(attribName);

	if (!attribName.startsWith("moxiemanager.")) {
		continue;
	}

	if (sessionBuffer.length() > 0) {
		sessionBuffer.append(',');
	}

    sessionBuffer.append("\"" + escape(attribName.substring(13)) + "\": " + "\"" + escape(attribValue.toString()) + "\"");
}

writer.print("{\"result\": {" + sessionBuffer + "}}");
%><%!
public void printError(int code, String message, PrintWriter writer) {
	writer.print("{\"error\": {\"code\": " + code + ", \"message\": \"" + escape(message) + "\"}}");
}

public String escape(String str) {
	StringBuffer buffer = new StringBuffer(str.length());
	char[] chars = str.toCharArray();

	for (int i = 0; i < chars.length; i++) {
		if (chars[i] > 128 || Character.isISOControl(chars[i])) {
			buffer.append("\\u");
			buffer.append(Character.toString(chars[i]));
			continue;
		}

		switch (chars[i]) {
			case '\'':
				buffer.append("\\\'");
				break;

			case '\"':
				buffer.append("\\\"");
				break;

			case '\\':
				buffer.append("\\\\");
				break;

			default:
				buffer.append(chars[i]);
				break;
		}
	}

	return buffer.toString();
}

public String hmacSHA256(String key, String message) {
	byte[] keyb = key.getBytes();

	MessageDigest sha256 = null;

	try {
		sha256 = MessageDigest.getInstance("SHA-256");
	} catch (NoSuchAlgorithmException e) {
		throw new java.lang.AssertionError("SHA-256 algorithm not found.");
	}

	if (keyb.length > 64) {
		sha256.update(keyb);
		keyb = sha256.digest();
		sha256.reset();
	}

	byte block[] = new byte[64];
	for (int i = 0; i < keyb.length; ++i) {
		block[i] = keyb[i];
	}
								   
	for (int i = keyb.length; i < block.length; ++i) {
		block[i] = 0;
	}

	for (int i = 0; i < 64; ++i) {
		block[i] ^= 0x36;
	}

	sha256.update(block);

	try {
		sha256.update(message.getBytes("UTF-8"));
	} catch (UnsupportedEncodingException e) {
		throw new java.lang.AssertionError("UTF-8 encoding not supported.");
	}

	byte[] hash = sha256.digest();
	sha256.reset();

	for (int i = 0; i < 64; ++i) {
		block[i] ^= (0x36 ^ 0x5c);
	}

	sha256.update(block);
	sha256.update(hash);
	hash = sha256.digest();

	char[] hexadecimals = new char[hash.length * 2];
	for (int i = 0; i < hash.length; ++i) {
		for (int j = 0; j < 2; ++j) {
			int value = (hash[i] >> (4 - 4 * j)) & 0xf;
			char base = (value < 10) ? ('0') : ('a' - 10);
			hexadecimals[i * 2 + j] = (char)(base + value);
		}
	}

	return new String(hexadecimals);
}
%>