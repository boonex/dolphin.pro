<?php
/**
 * StreamEventArgs.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class is passed out from various stream events for example when a file is uploaded/downloaded.
 *
 * @package MOXMAN_Vfs
 */
class MOXMAN_Vfs_StreamEventArgs extends MOXMAN_Util_EventArgs {
	/** @ignore */
	private $file, $httpContext;

	/**
	 * Constructs a new stream event.
	 *
	 * @param MOXMAN_Http_Context $httpContext Http context instance.
	 * @param MOXMAN_Vfs_IFile $file File instance.
	 */
	public function __construct(MOXMAN_Http_Context $httpContext, MOXMAN_Vfs_IFile $file) {
		$this->httpContext = $httpContext;
		$this->file = $file;
	}

	/**
	 * Sets the file associated with stream event.
	 *
	 * @param MOXMAN_Vfs_IFile $file File instance for stream event.
	 */
	public function setFile(MOXMAN_Vfs_IFile $file) {
		$this->file = $file;
	}

	/**
	 * Returns file associated with stream event.
	 *
	 * @return MOXMAN_Vfs_IFile File instance for stream event.
	 */
	public function getFile() {
		return $this->file;
	}

	/**
	 * Returns the http context used for the stream event.
	 * 
	 * @return MOXMAN_Http_Context HTTP context used for the stream event.
	 */
	public function getHttpContext() {
		return $this->httpContext;
	}
}

?>