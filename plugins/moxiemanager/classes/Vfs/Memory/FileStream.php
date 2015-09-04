<?php
/**
 * FileStream.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Memory file stream implementation. Instances of this class is created by the open method of the memory file.
 *
 * @package MOXMAN_Vfs_Memory
 * @internal
 */
class MOXMAN_Vfs_Memory_FileStream implements MOXMAN_Vfs_IFileStream {
	/** @ignore */
	private $file, $pos;

	/**
	 * Constructs a new file stream instance for the specified file.
	 *
	 * @param MOXMAN_Vfs_Memory_File $file File instance to create the stream for.
	 * @param string $mode File stream mode READ|WRITE.
	 */
	public function __construct(MOXMAN_Vfs_Memory_File $file, $mode) {
		$this->file = $file;

		if (!$file->exists() && $mode === MOXMAN_Vfs_IFileStream::WRITE) {
			$this->file->getFileSystem()->addEntry($file->getPath(), array());
		}

		$this->size = strlen($this->file->getEntry()->data);
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

		if ($this->pos > $this->size) {
			$this->pos = $this->size;
		}

		return $this->pos;
	}

	/**
	 * Reads the specified number of bytes and returns an string with data.
	 *
	 * @param int $len Number of bytes to read, defaults to 1024.
	 * @return string Data read from stream or null if it's at the end of stream.
	 */
	public function read($len = 1024) {
		$data = substr($this->file->getEntry()->data, $this->pos, $len);
		$this->pos += strlen($data);

		if ($data === false) {
			$data = "";
		}

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
			$this->file->getEntry()->data .= $buff;
			return strlen($buff);
		}

		$this->file->getEntry()->data .= substr($buff, 0, $len);
		return $len;
	}

	/**
	 * Flush buffered data out to stream.
	 */
	public function flush() {
	}

	/**
	 * Closes the specified stream. This will first flush the stream before closing.
	 */
	public function close() {
	}
}

?>