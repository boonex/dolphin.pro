<?php
/**
 * Oauth2.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */
/**
 * This class wrapps Oauth2 protocols.
 *
 * @codeCoverageIgnore
 */
class MOXMAN_Auth_Oauth2 {
	private $responseType;
	private $clientSecret, $clientId;
	private $action, $scope, $state;
	private $token, $refreshToken;
	private $idToken, $requestClient;

	/**
	 * Constructs a new Oauth2 instance.
	 *
	 * @param Array $config Config options.
	 *
	 * Required options
	 * $config["auth_url"] = "https://accounts.google.com/o/oauth2/auth";
	 * $config["scope"] = "https://www.googleapis.com/auth/userinfo.profile";
 	 * $config["callback"] = "http://callbackurl";
	 * $config["client_id"] = "ID";
	 * $config["client_secret"] = "SECRET";
	 * $config["token_url"] = "https://accounts.google.com/o/oauth2/token";
	 * $config["response_type"] = "code";
	 * $config["state"] = "service_name";
	 *
	 */
	public function __construct($config) {
		$this->scope = $config["scope"];
		$this->clientSecret = $config["client_secret"];
		$this->clientId = $config["client_id"];
		$this->action = "GET";
		$this->callback = $config["callback"];
		$this->state = $config["state"];
		$this->responseType = $config["response_type"];
		$this->authUrl = $config["auth_url"];
		$this->tokenUrl = $config["token_url"];
	}

	/**
	 * First step in auth
	 *
	 * @param Bool $force True/false to force user interaction or not.
	 *
	 */
	public function authorize($force = false) {
		// Make connection
		$approval = "";
		if ($force) {
			$approval = "approval_prompt=force&";
		}

		$parameters = array();
		$parameters["type"] = "web_server";
		$parameters["client_id"] = $this->clientId;
		$parameters["redirect_uri"] = $this->callback;
		$parameters["response_type"] = $this->responseType;

		if ($this->scope) {
			$parameters['scope'] = $this->scope;
		}

		return $this->authUrl . "?access_type=offline&state=". $this->state ."&" . $approval . http_build_query($parameters);
		//header("Location: ". $this->auth_url . "?access_type=offline&state=". $this->state ."&" . $approval . http_build_query($parameters));
	}

	/**
	 * Refresh access_token
	 *
	 */
	public function refresh() {
		$urlParts = parse_url($this->tokenUrl);
		$path = $urlParts["path"];
		$params = array();
		$params["client_id"] = $this->clientSecret;
		$params["client_secret"] = $this->clientId;
		$params["grant_type"] = "refresh_token";
		$params["refresh_token"] = $this->refreshToken;

		$client = new MOXMAN_Http_HttpClient($urlParts["scheme"] ."://". $urlParts["host"]);
		$client->setLogLevel(0);

		$request = $client->createRequest($path, "POST");
		$response = $request->send($params);

		return $response->getBody();
	}

	/**
	 * Validate token
	 * @param  string $token Token should be gotten by $_GET["code"] from redirect.
	 */
	public function validate($token) {
		$this->token = $token;
		$urlParts = parse_url($this->tokenUrl);
		$path = $urlParts["path"];

		$parameters = array();
		$parameters["grant_type"] = "authorization_code";
		$parameters["code"] = $token;
		$parameters["client_id"] = $this->clientId;
		$parameters["client_secret"] = $this->clientSecret;
		$parameters["redirect_uri"] = $this->callback;

		$client = new MOXMAN_Http_HttpClient($urlParts["scheme"] ."://". $urlParts["host"]);
		$client->setLogLevel(0);

		$request = $client->createRequest($path, "POST");
		$response = $request->send($parameters);

		$body = $response->getBody();
		$client->close();
		$data = json_decode($body);

		$this->refreshToken = $data->refreshToken;
		$this->token = $data->accessToken;
		$this->expires = $data->expiresIn;
		$this->idToken = $data->idToken;

		return $data;
	}

	/**
	 * Sign request
	 * @param  Object $request Request object to add header too.
	 * @return Object          Request object with added header.
	 */
	public function sign($request) {
		if ($request) {
			$request->setHeader("Authorization", "Bearer ". $this->token);
		}

		return $request;
	}

	/**
	 * Request target by url
	 * @param  string $url URL Target.
	 * @param  Array  $data Optional, data to send to target in POST.
	 * @return string      Response of request to target.
	 */
	public function request($url, $method, $data=false) {
		$urlParts = parse_url($url);
		$path = $urlParts["path"];
		$query = isset($urlParts["query"]) ? $urlParts["query"] : "";

		if (!$this->requestClient) {
			$this->requestClient = new MOXMAN_Http_HttpClient($urlParts["scheme"] . "://" . $urlParts["host"]);
		}

		$this->requestClient->setLogLevel(0);

		$request = $this->requestClient->createRequest($path . "?" . $query, $method);
		$request = $this->sign($request);
		$response = $request->send($data);
		$body = $response->getBody();

		return $body;
	}

	/**
	 * Set Auth Token Manually
	 * @param  string $token Access Token.
	 */
	public function setToken($token) {
		$this->token = $token;
	}
}
?>