<?php
/**
 * FileStream.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class handles the stream for the local file system.
 *
 * @package MOXMAN_Vfs_Local
 */
class MOXMAN_Vfs_Local_FileStream implements MOXMAN_Vfs_IFileStream {
	/** @ignore */
	private $fp ,$file;

	/** @ignore */
	public function __construct(MOXMAN_Vfs_Local_File $file, $mode) {
		$this->file = $file;
		$this->fp = @fopen($file->getInternalPath(), $mode);
		if (!$this->fp) {
			throw new MOXMAN_Exception("Could not open file stream for file.");
		}
	}

	/**
	 * Skip/jump over specified number of bytes from stream.
	 *
	 * @param int $bytes Number of bytes to skip.
	 * @return int Number of skipped bytes.
	 */
	public function skip($bytes) {
		$pos = ftell($this->fp);

		fseek($this->fp, $bytes, SEEK_CUR);

		return ftell($this->fp) - $pos;
	}

	/**
	 * Reads the specified number of bytes and returns an string with data.
	 *
	 * @param int $len Number of bytes to read, defaults to 1024.
	 * @return string Data read from stream or null if it's at the end of stream.
	 */
	public function read($len = 1024) {
		$data = fread($this->fp, $len);

		// @codeCoverageIgnoreStart
		if ($data === false) {
			$data = "";
		}
		// @codeCoverageIgnoreEnd

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
			return fwrite($this->fp, $buff);
		}

		return fwrite($this->fp, $buff, $len);
	}

	/**
	 * Flush buffered data out to stream.
	 */
	public function flush() {
		fflush($this->fp);
	}

	/**
	 * Closes the specified stream. This will first flush the stream before closing.
	 */
	public function close() {
		if ($this->fp) {
			fclose($this->fp);
			$this->fp = null;
			MOXMAN_Vfs_Local_File::chmod($this->file);
		}
	}
}

?>