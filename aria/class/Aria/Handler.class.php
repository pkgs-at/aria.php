<?php

require_once 'Aria/Application.class.php';

abstract class Aria_Handler {

	private $application;
	private $path;
	private $httpStatusCode;

	public function initialize(Aria_Application $application, $path) {
		$this->application = $application;
		$this->path = $path;
		$this->httpStatusCode = NULL;
	}

	public function getApplication(){
		return $this->application;
	}

	public function getPath() {
		return $this->path;
	}

	public function sendHttpNoCache() {
		header('Expires: Thu, 01 Dec 1994 16:00:00 GMT', TRUE);
		header('Cache-control: no-cache', TRUE);
		header('Pragma: no-cache', FALSE);
	}

	public function sendHttpStatus($code = 200, $reason = 'OK', $message = NULL) {
		$protocol = $_SERVER['SERVER_PROTOCOL'];
		if ($protocol == 'INCLUDED') $protocol = 'HTTP/1.0';
		if ($message) {
			header("$protocol $code $reason: $message", TRUE, $code);
		}
		else {
			header("$protocol $code $reason", TRUE, $code);
		}
		$this->httpStatusCode = $code;
	}

	public function sendHttpSucceed($message = NULL) {
		$this->sendHttpStatus(200, 'OK', $message);
	}

	public function sendHttpErrorBadRequest($message = NULL) {
		$this->sendHttpStatus(400, 'Bad Request', $message);
	}

	public function sendHttpErrorUnauthorized($message = NULL) {
		$this->sendHttpStatus(401, 'Unauthrized', $message);
	}

	public function sendHttpErrorForbidden($message = NULL) {
		$this->sendHttpStatus(403, 'Forbidden', $message);
	}

	public function sendHttpErrorNotFound($message = NULL) {
		$this->sendHttpStatus(404, 'Not Found', $message);
	}

	public function sendHttpError($message = NULL) {
		$this->sendHttpStatus(500, 'Internal Server Error', $message);
	}

	public function sendHttpErrorNotImplemented($message = NULL) {
		$this->sendHttpStatus(501, 'Not Implemented', $message);
	}

	public function sendHttpErrorServiceUnavailable($message = NULL) {
		$this->sendHttpStatus(503, 'Service Unavailable', $message);
	}

	public function sendHttpMoved($location) {
		header("Location: $location", TRUE, 301);
	}

	public function sendHttpRedirect($location) {
		header("Location: $location", TRUE, 302);
	}

	public function getHttpStatusCode() {
		return $this->httpStatusCode;
	}

	public abstract function handle($parameters);

	#Virtual
	public function error(Exception $throwable) {
		throw $throwable;
	}

}

