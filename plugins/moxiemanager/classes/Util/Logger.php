<?php
/**
 * Logger.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Logging utility class. This class handles basic logging with levels, log rotation and custom log formats. It's
 * designed to be compact but still powerful and flexible.
 *
 * @package MOXMAN_Util
 */
class MOXMAN_Util_Logger {
	/** @ignore */
	private $level, $path, $fileName, $format, $maxSize, $maxSizeBytes, $dateFormat, $filter;

	/**
	 * Debug level.
	 */
	const LEVEL_DEBUG = 0;

	/**
	 * Info level.
	 */
	const LEVEL_INFO = 10;

	/**
	 * Warning level.
	 */
	const LEVEL_WARN = 20;

	/**
	 * Error level.
	 */
	const LEVEL_ERROR = 30;

	/**
	 * Fatal level.
	 */
	const LEVEL_FATAL = 40;

	/**
	 * Constructs a new logger instance.
	 *
	 * @param array $config Name/value array with config settings.
	 */
	public function __construct($config = array()) {
		$config = array_merge(array(
			"path" => ".",
			"filename" => "{level}.log",
			"format" => "[{time}] [{level}] {message}",
			"max_size" => "100k",
			"max_files" => 10,
			"level" => "debug",
			"date_format" => "Y-m-d H:i:s",
			"filter" => ""
		), $config);

		$this->setLevel($config["level"]);
		$this->setPath($config["path"]);
		$this->setFileName($config["filename"]);
		$this->setFormat($config["format"]);
		$this->setMaxSize($config["max_size"]);
		$this->setMaxFiles($config["max_files"]);
		$this->setDateFormat($config["date_format"]);
		$this->setFilter($config["filter"]);
	}

	/**
	 * Sets the current log level, use the constants.
	 *
	 * @param int $level Log level instance for example DEBUG.
	 */
	public function setLevel($level) {
		if (is_string($level)) {
			switch (strtolower($level)) {
				case "debug":
					$this->level = self::LEVEL_DEBUG;
					break;

				case "info":
					$this->level = self::LEVEL_INFO;
					break;

				case "warn":
				case "warning":
					$this->level = self::LEVEL_WARN;
					break;

				case "error":
					$this->level = self::LEVEL_ERROR;
					break;

				case "fatal":
					$this->level = self::LEVEL_FATAL;
					break;

				default:
					$this->level = self::LEVEL_FATAL;
			}
		}
	}

	/**
	 * Returns the current log level for example LEVEL_DEBUG.
	 *
	 * @return int Current log level for example LEVEL_DEBUG.
	 */
	public function getLevel() {
		return $this->level;
	}

	/**
	 * Sets the current log directory path.
	 *
	 * @param string $path Path to directory to put the logs in.
	 */
	public function setPath($path) {
		$this->path = $path;
	}

	/**
	 * Returns the current log directory path.
	 *
	 * @return String Current log directory path.
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * Sets the file name or file name pattern to use to save the log files.
	 *
	 * @param string $fileName File name or file name pattern to store the log data in.
	 */
	public function setFileName($fileName) {
		$this->fileName = $fileName;
	}

	/**
	 * Returns the current log file name or file name pattern.
	 *
	 * @return String Current log file name or file name pattern.
	 */
	public function getFileName() {
		return $this->fileName;
	}

	/**
	 * Set log format the items are: {level}, {time} and {message}
	 *
	 * @param string $format Log message format string.
	 */
	public function setFormat($format) {
		$this->format = $format;
	}

	/**
	 * Returns the log message format.
	 *
	 * @return String Log message format.
	 */
	public function getFormat() {
		return $this->format;
	}

	/**
	 * Sets the max log file size. If the log file gets larger than this size it will be rolled.
	 *
	 * @param string $size Size string to parse could be for example: 10mb, 10k or 10.
	 */
	public function setMaxSize($size) {
		// Fix log max size
		$logMaxSizeBytes = intval(preg_replace("/[^0-9]/", "", $size));

		// Is KB
		if (strpos((strtolower($size)), "k") > 0) {
			$logMaxSizeBytes *= 1024;
		}

		// Is MB
		if (strpos((strtolower($size)), "m") > 0) {
			$logMaxSizeBytes *= (1024 * 1024);
		}

		$this->maxSizeBytes = $logMaxSizeBytes;
		$this->maxSize = $size;
	}

	/**
	 * Returns the current filter regexp.
	 *
	 * @return String Filter regexp.
	 */
	public function getFilter() {
		return $this->filter;
	}

	/**
	 * Sets the filter regexp.
	 *
	 * @param string $filter Filter to apply.
	 */
	public function setFilter($filter) {
		$this->filter = $filter;
	}

	/**
	 * Returns the current max file size value.
	 *
	 * @return String Current max size value.
	 */
	public function getMaxSize() {
		return $this->maxSizeBytes;
	}

	/**
	 * Sets the number of files to roll before the last one gets deleted.
	 *
	 * @param int $maxFiles Number of files to roll.
	 */
	public function setMaxFiles($maxFiles) {
		$this->maxFiles = $maxFiles;
	}

	/**
	 * Returns the current max files value.
	 *
	 * @return int Current max files value.
	 */
	public function getMaxFiles() {
		return $this->maxFiles;
	}

	/**
	 * Sets the date format to put in the log files.
	 *
	 * @param string $format Data format string to use in log files.
	 */
	public function setDateFormat($format) {
		$this->dateFormat = $format;
	}

	/**
	 * Returns the current data format string.
	 *
	 * @return String Current date format string.
	 */
	public function getDateFormat() {
		return $this->dateFormat;
	}

	/**
	 * Adds a debug message to the log file.
	 *
	 * @param Mixed $msg One or more arguments to combine into a log message.
	 */
	public function debug($msg) {
		$args = func_get_args();
		$this->logMsg(self::LEVEL_DEBUG, $args);
	}

	/**
	 * Adds a info message to the log file.
	 *
	 * @param Mixed $msg One or more arguments to combine into a log message.
	 */
	public function info($msg) {
		$args = func_get_args();
		$this->logMsg(self::LEVEL_INFO, $args);
	}

	/**
	 * Adds a warning message to the log file.
	 *
	 * @param Mixed $msg One or more arguments to combine into a log message.
	 */
	public function warn($msg) {
		$args = func_get_args();
		$this->logMsg(self::LEVEL_WARN, $args);
	}

	/**
	 * Adds a error message to the log file.
	 *
	 * @param Mixed $msg One or more arguments to combine into a log message.
	 */
	public function error($msg) {
		$args = func_get_args();
		$this->logMsg(self::LEVEL_ERROR, $args);
	}

	/**
	 * Adds a fatal message to the log file.
	 *
	 * @param Mixed $msg One or more arguments to combine into a log message.
	 */
	public function fatal($msg) {
		$args = func_get_args();
		$this->logMsg(self::LEVEL_FATAL, $args);
	}

	/**
	 * Returns true/false if the debug level is available.
	 *
	 * @return Boolean true/false if the level is higher or equals to debug.
	 */
	public function isDebugEnabled() {
		return $this->level <= self::LEVEL_DEBUG;
	}

	/**
	 * Returns true/false if the info level is available.
	 *
	 * @return Boolean true/false if the level is higher or equals to info.
	 */
	public function isInfoEnabled() {
		return $this->level <= self::LEVEL_INFO;
	}

	/**
	 * Returns true/false if the warn level is available.
	 *
	 * @return Boolean true/false if the level is higher or equals to warn.
	 */
	public function isWarnEnabled() {
		return $this->level <= self::LEVEL_WARN;
	}

	/**
	 * Returns true/false if the error level is available.
	 *
	 * @return Boolean true/false if the level is higher or equals to error.
	 */
	public function isErrorEnabled() {
		return $this->level <= self::LEVEL_ERROR;
	}

	/**
	 * Returns true/false if the fatal level is available.
	 *
	 * @return Boolean true/false if the level is higher or equals to fatal.
	 */
	public function isFatalEnabled() {
		return $this->level <= self::LEVEL_FATAL;
	}

	/**
	 * Writes a stack trace to the debug log.
	 */
	public function debugStackTrace() {
		if ($this->isDebugEnabled()) {
			$trace = debug_backtrace();
			$message = "Stacktrace:";
			foreach ($trace as $item) {
				if (isset($item["file"]) && isset($item["line"])) {
					$message .= "\n" . $item["file"] . ":" . $item["line"];
				}
			}

			$this->debug($message);
		}
	}

	/** @ignore */
	private function logMsg($level, $args) {
		if ($level < $this->level) {
			return;
		}

		$message = "";
		foreach ($args as $arg) {
			// Separate arguments with commas
			if ($message) {
				$message .= ", ";
			}

			// Convert true/false to string
			if (is_bool($arg)) {
				$arg = $arg ? "true" : "false";
			}

			// Convert exception to string
			if ($arg instanceof Exception) {
				$arg = $arg->getMessage();
			}

			$message .= $arg;
		}

		// See if message matches the filter
		if ($this->filter && !preg_match($this->filter, $message)) {
			return;
		}

		$logFile = $this->path . "/" . $this->fileName;

		// Ignore if we have no write access
		// TODO: Might want to push out an exception (eternity loop?) here or just die.
		//if (!is_writable($logFile))
		//	return;

		switch ($level) {
			case self::LEVEL_DEBUG:
				$levelName = "DEBUG";
				break;

			case self::LEVEL_INFO:
				$levelName = "INFO";
				break;

			case self::LEVEL_WARN:
				$levelName = "WARN";
				break;

			case self::LEVEL_ERROR:
				$levelName = "ERROR";
				break;

			case self::LEVEL_FATAL:
				$levelName = "FATAL";
				break;
		}

		$logFile = str_replace('{level}', strtolower($levelName), $logFile);

		$text = $this->format;
		$text = str_replace('{time}', date($this->dateFormat), $text);
		$text = str_replace('{level}', strtolower($levelName), $text);
		$text = str_replace('{message}', $message, $text);
		$message = $text . "\r\n";

		// Check filesize
		$roll = false;
		if (file_exists($logFile)) {
			$size = filesize($logFile);

			if ($size + strlen($message) > $this->maxSizeBytes) {
				$roll = true;
			}
		}

		// Roll if the size is right
		if ($roll) {
			for ($i = $this->maxFiles; $i >= 1; $i--) {
				$rfile = $logFile . "." . $i;
				$nfile = $logFile . "." . ($i + 1);

				if (file_exists($rfile)) {
					rename($rfile, $nfile);
				}
			}

			rename($logFile, $logFile . ".1");

			// Delete last logfile
			$delfile = $logFile . "." . ($this->maxFiles + 1);
			if (file_exists($delfile)) {
				unlink($delfile);
			}
		}

		// Append log line
		if (($fp = fopen($logFile, "a")) != null) {
			fputs($fp, $message);
			fflush($fp);
			fclose($fp);
		}
	}
}

?>