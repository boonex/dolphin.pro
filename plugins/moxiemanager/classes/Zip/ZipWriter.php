<?php
/**
 * ZipWriter.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class can generate zip files fast by using store instead of deflate
 * compression.
 *
 * @package MOXMAN_Zip
 */
class MOXMAN_Zip_ZipWriter {
	/** @ignore */
	private $compressionLevel, $entries, $entryLookup;

	/**
	 * Constructs a new ZipWriter instance.
	 *
	 * @param Array $settings Optional array with settings for the zip writer.
	 */
	public function __construct($settings = array()) {
		$this->entries = array();
		$this->entryLookup = array();
		$this->excludePattern = "";
		$this->setCompressionLevel(isset($settings["compressionLevel"]) ? $settings["compressionLevel"] : 9);

		if (isset($settings["exclude_pattern"])) {
			$this->excludePattern = $settings["exclude_pattern"];
		}
	}

	/**
	 * Sets the compression level 0 equals simple storage and 9 is the maximum compression.
	 *
	 * @param int $level Compression level to use 0 = store, 9 = max
	 */
	public function setCompressionLevel($level) {
		$this->compressionLevel = $level;
	}

	/**
	 * Adds a file to the zip.
	 *
	 * @param string $zipPath Path within zip file.
	 * @param string $path Path of local file to add.
	 */
	public function addFile($zipPath, $path) {
		if (is_dir($path)) {
			$this->addDirectory($zipPath, $path);
		} else {
			$this->addEntry($zipPath, (object) array("file" => $path));
		}
	}

	/**
	 * Adds a file with data to the zip.
	 *
	 * @param string $zipPath Path within zip file.
	 * @param string $data Data to set as file contents.
	 */
	public function addFileData($zipPath, $data) {
		$this->addEntry($zipPath, (object) array("data" => $data));
	}

	/**
	 * Adds a directory to the zip.
	 *
	 * @param string $zipPath Path within zip file.
	 * @param string $path Optional local file system path to add into zip.
	 */
	public function addDirectory($zipPath, $path = null) {
		if ($path) {
			// List files from disk
			$files = $this->listTree($path);

			foreach ($files as $file) {
				$this->addEntry($this->combine($zipPath, substr($file, strlen($path))), (object) array("file" => $file));
			}
		} else {
			$this->addEntry($zipPath);
		}
	}

	/**
	 * Adds an entry item.
	 *
	 * @param string $zipPath Path within zip file.
	 * @param array $entry Optional entry array to add.
	 */
	public function addEntry($zipPath, $entry = null) {
		if ($this->excludePattern && preg_match($this->excludePattern, $zipPath)) {
			return;
		}

		if (!$entry) {
			$entry = (object) array();
		}

		$entry->path = preg_replace('/^\/|\/$/', '', $zipPath);
		$this->entries[] = $entry;
	}

	/**
	 * Returns the zip as a string that can be stored or streamed.
	 *
	 * @return string Zip file as a string.
	 */
	public function toString() {
		$zipData = "";
		$entries = $this->entries;

		for ($i = 0, $l = count($entries); $i < $l; $i++) {
			$entry = $entries[$i];

			if (!isset($entry->extra)) {
				$entry->extra = "";
			}

			if (!isset($entry->comment)) {
				$entry->comment = "";
			}

			$compressionMethod = $size = $compressedSize = $crc32 = 0;
			$data = isset($entry->data) ? $entry->data : "";
			$modificationDate = isset($entry->mtime) ? $entry->mtime : time();

			if (isset($entry->file)) {
				$localPath = $this->utf8ToNative($entry->file);
				$isFile = is_file($localPath);
				$modificationDate = filemtime($localPath);

				if ($isFile) {
					$data = file_get_contents($localPath);
				}
			} else {
				$isFile = isset($entry->is_file) ? $entry->is_file : false;
			}

			if (strlen($data) > 0) {
				$size = strlen($data);
				$crc32 = crc32($data);
				$isFile = true;

				if ($this->compressionLevel > 0) {
					$data = @gzdeflate($data, $this->compressionLevel);
					$compressionMethod = 0x0008;
				} else {
					$compressionMethod = 0x0000; // Store
				}

				$compressedSize = strlen($data);
			}

			// Convert unix time to dos time
			$date = getdate($modificationDate);
			$mtime = ($date['hours'] << 11) + ($date['minutes'] << 5) + $date['seconds'] / 2;
			$mdate = (($date['year'] - 1980) << 9) + ($date['mon'] << 5) + $date['mday'];

			// Setup filename
			try {
				$entry->path = iconv('UTF-8', 'cp437//TRANSLIT', $entry->path);
			} catch (Exception $e) {
				// Most likely a non western UTF-8 character
				$entry->utf8 = true;
			}

			$fileName = $entry->path;

			if (!$isFile) {
				$entry->path = $fileName = $fileName . "/";
			}

			$fileNameLength = strlen($fileName);

			// Setup extra field
			$extra = $entry->extra;
			$extraLength = strlen($extra);
			$entry->offset = strlen($zipData);

			// Write local file header
			$zipData .= pack("VvvvvvVVVvv",
				0x04034b50, // Local File Header Signature
				0x0014, // Version needed to extract
				0x0002, // General purpose bit flag
				$compressionMethod, // Compression method (deflate)
				$mtime, // Last mod file time (MS-DOS)
				$mdate, // Last mod file date (MS-DOS)
				$crc32, // CRC-32
				$compressedSize, // Compressed size
				$size, // Uncompressed size
				$fileNameLength, // Filename length
				$extraLength // Extra field length
			);

			// Write variable data
			$zipData .= $fileName;
			$zipData .= $extra;
			$zipData .= $data;

			$entry->cmethod = $compressionMethod;
			$entry->mtime = $mtime;
			$entry->mdate = $mdate;
			$entry->crc32 = $crc32;
			$entry->csize = $compressedSize;
			$entry->size = $size;
			$entry->eattr = $isFile ? 0x00000020 : 0x00000030;

			$entries[$i] = $entry;
		}

		$startOffset = strlen($zipData);
		$centralDirSize = 0;

		// Write central directory information
		for ($i = 0, $l = count($entries); $i < $l; $i++) {
			$entry = $entries[$i];

			$generalPurposeBits = 0;
			$generalPurposeBits |= 0x0002;

			// Mark file name as UTF-8, the comment will have to be in UTF-8 as well
			if (isset($entry->utf8)) {
				$generalPurposeBits |= 0x0800;
			}

			// Add central directory file header
			$zipData .= pack("VvvvvvvVVVvvvvvVV",
				0x02014b50, // Central file header signature
				0x0014, // Version made by
				0x0014, // Version extracted
				$generalPurposeBits, // General purpose bit flag
				$entry->cmethod, // Compression method (deflate)
				$entry->mtime, // Last mod file time (MS-DOS)
				$entry->mdate, // Last mod file date (MS-DOS)
				$entry->crc32, // CRC-32
				$entry->csize, // Compressed size
				$entry->size, // Uncompressed size
				strlen($entry->path), // Filename length
				strlen($entry->extra), // Extra field length
				strlen($entry->comment), // Comment length
				0, // Disk
				0, // Internal file attributes
				$entry->eattr, // External file attributes
				$entry->offset // Relative offset of local file header
			);

			// Write filename, extra field and comment
			$zipData .= $entry->path;
			$zipData .= $entry->extra;
			$zipData .= $entry->comment;

			// Central directory info size + file name length + extra field length + comment length
			$centralDirSize += 46 + strlen($entry->path) + strlen($entry->extra) + strlen($entry->comment);
		}

		$comment = "";
		$commentLength = 0;

		// Write end of central directory record
		$zipData .= pack("VvvvvVVv",
			0x06054b50, // End of central directory signature
			0, // Number of this disk
			0, // Disk where central directory starts
			count($entries), // Number of central directory records on this disk
			count($entries), // Total number of central directory records
			$centralDirSize, // Size of central directory (bytes)
			$startOffset, // Offset of start of central directory, relative to start of archive
			$commentLength // Zip file comment length
		);

		// Write comment
		$zipData .= $comment;

		return $zipData;
	}

	/**
	 * Combines two paths into one path.
	 *
	 * @param string $path1 Path to be on the left side.
	 * @param string $path2 Path to be on the right side.
	 * @return String Combined path string.
	 */
	private function combine($path1, $path2) {
		$path1 = preg_replace('/\[\/]$/', '', str_replace(DIRECTORY_SEPARATOR, '/', $path1));

		if (!$path2) {
			return $path1;
		}

		$path2 = preg_replace('/^\\//', '', str_replace(DIRECTORY_SEPARATOR, '/', $path2));

		return $path1 . '/' . $path2;
	}

	/** @ignore */
	private function listTree($path) {
		$files = array();
		$files[] = $path;

		if ($dir = opendir($path)) {
			while (false !== ($file = readdir($dir))) {
				if ($file == "." || $file == "..") {
					continue;
				}

				$file = $path . "/" . $file;

				if (is_dir($file)) {
					$files = array_merge($files, $this->listTree($file));
				} else {
					$files[] = $file;
				}
			}

			closedir($dir);
		}

		return $files;
	}

	// @codeCoverageIgnoreStart
	private function utf8ToNative($path) {
		if (DIRECTORY_SEPARATOR == "\\") {
			$path = mb_convert_encoding($path, "Windows-1252", "UTF-8");

			// Detect any characters outside the Win32 filename byte range
			if (strpos($path, '?') !== false) {
				throw new Exception("PHP doesn't support the specified characters on Windows.");
			}
		}

		return $path;
	}
	// @codeCoverageIgnoreEnd
}

?>