<?php
/**
 * IFileStream.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This interface is to be implemented by various file systems to provide
 * access to the binary data of the file.
 *
 * @package MOXMAN_Vfs
 */
interface MOXMAN_Vfs_IFileStream {
	/**
	 * Read mode.
	 */
	const READ = 'rb';

	/**
	 * Write mode.
	 */
	const WRITE = 'wb';

	/**
	 * Append to mode.
	 */
	const APPEND = 'ab+';

	/**
	 * Skip/jump over specified number of bytes from stream.
	 *
	 * @param int $bytes Number of bytes to skip.
	 * @return int Number of skipped bytes.
	 */
	public function skip($bytes);

	/**
	 * Reads the specified number of bytes and returns an string with data.
	 *
	 * @param int $len Number of bytes to read, defaults to 1024.
	 * @return string Data read from stream or null if it's at the end of stream.
	 */
	public function read($len = 1024);

	/**
	 * Reads all data avaliable in a stream and returns it as a string.
	 *
	 * @return string All data read from stream.
	 */
	public function readToEnd();

	/**
	 * Writes a string to a stream.
	 *
	 * @param string $buff String buffer to write to file.
	 * @param int $len Number of bytes from string to write.
	 */
	public function write($buff, $len = -1);

	/**
	 * Flush buffered data out to stream.
	 */
	public function flush();

	/**
	 * Closes the specified stream. This will first flush the stream before closing.
	 */
	public function close();
}

?>