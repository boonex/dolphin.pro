<?php
/**
 * FileStream.php
 *
 * Copyright 2003-2015, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class handles the stream for the Amazon S# file system.
 *
 * @package MOXMAN_AmazonS3
 */
class MOXMAN_AmazonS3_FileStream implements MOXMAN_Vfs_IFileStream {
	private $buffer, $mode, $pos, $file;

	/** @ignore */
	public function __construct(MOXMAN_AmazonS3_File $file, $mode) {
		if ($mode == MOXMAN_Vfs_IFileStream::READ) {
			$this->buffer = $file->getFileSystem()->getClient()->getFileContents($file->getInternalPath());
		} else {
			$this->buffer = "";
		}

		$this->file = $file;
		$this->mode = $mode;
		$this->pos = 0;
	}

	/**
	 * Skip/jump over specified number of bytes from stream.
	 *
	 * @param int $bytes Number of bytes to skip.
	 * @return int Number of skipped bytes.
	 */
	public function skip($bytes) {
		$this->pos += $bytes;
		$targetPos = $this->pos;
		$this->pos = max($this->pos, strlen($this->buffer));

		return $targetPos - $this->pos;
	}

	/**
	 * Reads the specified number of bytes and returns an string with data.
	 *
	 * @param int $len Number of bytes to read, defaults to 1024.
	 * @return string Data read from stream or null if it's at the end of stream.
	 */
	public function read($len = 1024) {
		$data = substr($this->buffer, $this->pos, $len);
		if ($data === false) {
			$data = "";
		}

		$this->pos += strlen($data);

		return $data;
	}

	/**
	 * Reads all data avaliable in a stream and returns it as a string.
	 *
	 * @return string All data read from stream.
	 */
	public function readToEnd() {
		$data = "";

		while (($chunk = $this->read(4096)) !== "") {
			$data .= $chunk;
		}

		return $data;
	}

	/**
	 * Writes a string to a stream.
	 *
	 * @param string $buff String buffer to write to file.
	 * @param int $len Number of bytes from string to write.
	 */
	public function write($buff, $len = -1) {
		if ($len == -1) {
			$this->buffer .= $buff;
			return;
		}

		$this->buffer .= substr($buff, 0, $len);
	}

	/**
	 * Flush buffered data out to stream.
	 */
	public function flush() {
	}

	/**
	 * Closes the specified stream.
	 */
	public function close() {
		if ($this->mode == MOXMAN_Vfs_IFileStream::WRITE) {
			$this->file->getFileSystem()->getClient()->putFileContents($this->file->getInternalPath(), $this->buffer);
			$this->file->removeStatCache();
		}
	}
}

?>