<?php
/**
 * Quota.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * ...
 */
class MOXMAN_Quota_Plugin implements MOXMAN_IPlugin {
	private $currentSize;

	public function init() {
		MOXMAN::getPluginManager()->get("core")->bind("BeforeFileAction", "onBeforeFileAction", $this);
		MOXMAN::getPluginManager()->get("core")->bind("FileAction", "onFileAction", $this);
	}

	public function onBeforeFileAction(MOXMAN_Vfs_FileActionEventArgs $args) {
		switch ($args->getAction()) {
			case MOXMAN_Vfs_FileActionEventArgs::DELETE:
				$file = $args->getFile();
				$maxSize = $this->parseSize($file->getConfig()->get("quota.max_size", 0));

				if ($maxSize > 0) {
					$this->currentSize = MOXMAN::getUserStorage()->get("quota.size", 0);
					$this->currentSize = max(0, $this->currentSize - $file->getSize());

					if (MOXMAN::getLogger()) {
						MOXMAN::getLogger()->debug("[quota] Removed: " . $file->getPublicPath() . " (" . $this->formatSize($file->getSize()) . ").");
					}
				}
				break;

			case MOXMAN_Vfs_FileActionEventArgs::COPY:
			case MOXMAN_Vfs_FileActionEventArgs::ADD:
				if (!isset($args->getData()->thumb)) {
					$file = $args->getFile();
					$targetFile = $args->getTargetFile();

					if (!$file) {
						return;
					}

					$publicPath = ($targetFile) ? $targetFile->getPublicPath() : $file->getPublicPath();
					$maxSize = $this->parseSize($file->getConfig()->get("quota.max_size", 0));

					if ($maxSize === 0) {
						return;
					}

					$fileSize = 0;

					// Get size of source directory in copy operation
					if ($args->getAction() == MOXMAN_Vfs_FileActionEventArgs::COPY && $file->isDirectory()) {
						$fileSize = $this->getDirectorySize($file);
					} else if (isset($args->getData()->fileSize)) {
						$fileSize = $args->getData()->fileSize;
					}

					$this->currentSize = MOXMAN::getUserStorage()->get("quota.size", 0);
					if ($this->currentSize + $fileSize > $maxSize) {
						throw new MOXMAN_Exception(
							"Quota exceeded when adding file: " . $publicPath . " (" .
							$this->formatSize($this->currentSize + $fileSize) .
							" > " .
							$this->formatSize($maxSize) . ")."
						);
					}

					$this->currentSize += $fileSize;

					if (MOXMAN::getLogger()) {
						MOXMAN::getLogger()->debug("[quota] Added: " . $file->getPublicPath() . " (" . $this->formatSize($fileSize) . ").");
					}
				}
				break;
		}
	}

	public function onFileAction(MOXMAN_Vfs_FileActionEventArgs $args) {
		switch ($args->getAction()) {
			case MOXMAN_Vfs_FileActionEventArgs::DELETE:
			case MOXMAN_Vfs_FileActionEventArgs::COPY:
			case MOXMAN_Vfs_FileActionEventArgs::ADD:
				MOXMAN::getUserStorage()->put("quota.size", max(0, $this->currentSize));
				break;
		}
	}

	private function getDirectorySize($file) {
		$size = 0;
		$files = $file->listFiles();

		foreach ($files as $file) {
			if ($file->isFile()) {
				$size += $file->getSize();
			} else {
				$size += $this->getDirectorySize($file);
			}
		}

		return $size;
	}

	// @codeCoverageIgnoreStart

	private function parseSize($size) {
		$bytes = floatval(preg_replace('/[^0-9\\.]/', "", $size));

		if (strpos((strtolower($size)), "k") > 0) {
			$bytes *= 1024;
		}

		if (strpos((strtolower($size)), "m") > 0) {
			$bytes *= (1024 * 1024);
		}

		return round($bytes);
	}

	private function formatSize($size) {
		if ($size >= 1048576) {
			return round($size / 1048576, 1) . " MB";
		}

		if ($size >= 1024) {
			return round($size / 1024, 1) . " KB";
		}

		return $size . " b";
	}

	// @codeCoverageIgnoreEnd
}

// Add plugin
MOXMAN::getPluginManager()->add("quota", new MOXMAN_Quota_Plugin());

?>