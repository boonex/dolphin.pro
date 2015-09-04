<?php
/**
 * MemoryFileStream.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class can be used to read/write data in ram. Used by some remote file systems.
 *
 * @package MOXMAN_Vfs
 */
class MOXMAN_Vfs_MemoryFileStream implements MOXMAN_Vfs_IFileStream {
	/** @ignore */
	private $file, $fp, $mode, $localTempFilePath;

	/**
	 * Constructs a new memory file stream.
	 *
	 * @param MOXMAN_Vfs_IFile $file File instance for memory stream.
	 * @param string Stream mode READ or WRITE.
	 */
	public function __construct(MOXMAN_Vfs_IFile $file, $mode) {
		$this->mode = $mode;
		$this->file = $file;

		// Get local temp file path
		$fileSystemManager = MOXMAN::getFileSystemManager();
		$this->localTempFilePath = $fileSystemManager->getLocalTempPath($file);

		// Export to local temp file
		if ($mode === MOXMAN_Vfs_IFileStream::READ || $mode === MOXMAN_Vfs_IFileStream::APPEND) {
			$file->exportTo($this->localTempFilePath);
		}

		// Open local temp file for r,w or a
		$this->fp = fopen($this->localTempFilePath, $mode);
		if (!$this->fp) {
			throw new MOXMAN_Exception("Could not open file stream for file: " . $file->getPath());
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
		return fread($this->fp, $len);
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
			@fclose($this->fp);
			$this->fp = null;

			// Import local temp file
			if ($this->mode === MOXMAN_Vfs_IFileStream::WRITE || $this->mode === MOXMAN_Vfs_IFileStream::APPEND) {
				$this->file->importFrom($this->localTempFilePath);
			}
		}
	}
}

?>