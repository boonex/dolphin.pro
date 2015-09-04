<?php
/**
 * FileStream.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class handles the stream for the local file system.
 *
 * @package MOXMAN_Vfs_Cache
 */
class MOXMAN_Vfs_Cache_FileStream implements MOXMAN_Vfs_IFileStream {
	private $file, $wrappedStream, $mode;

	public function __construct($file, $wrappedStream, $mode) {
		$this->file = $file;
		$this->wrappedStream = $wrappedStream;
		$this->mode = $mode;
	}

	public function skip($bytes) {
		return $this->wrappedStream->skip($bytes);
	}

	public function read($len = 1024) {
		return $this->wrappedStream->read($len);
	}

	public function readToEnd() {
		return $this->wrappedStream->readToEnd();
	}

	public function write($buff, $len = -1) {
		return $this->wrappedStream->write($buff, $len);
	}

	public function flush() {
		$this->wrappedStream->flush();
	}

	public function close() {
		$this->wrappedStream->close();

		if ($this->mode != MOXMAN_Vfs_IFileStream::READ) {
			MOXMAN_Vfs_Cache_FileInfoStorage::getInstance()->putFile($this->file->getWrappedFile());
		}
	}
}

?>