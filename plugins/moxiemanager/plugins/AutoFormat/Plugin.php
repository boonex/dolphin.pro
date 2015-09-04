<?php
/**
 * Plugin.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * ....
 *
 * Format parameters:
 *  %f - Filename.
 *  %e - Extension.
 *  %w - Image width.
 *  %h - Image height.
 *  %tw - Target width.
 *  %th - Target height.
 *  %ow - Original width.
 *  %oh - Original height.
 *
 *  Example: 320x240|gif=%f_%w_%h.gif,320x240=%f_%w_%h.%e
 *
 */
class MOXMAN_AutoFormat_Plugin implements MOXMAN_IPlugin {
	public function init() {
		MOXMAN::getPluginManager()->get("core")->bind("FileAction", "onFileAction", $this);
		MOXMAN::getPluginManager()->get("core")->bind("BeforeFileAction", "onBeforeFileAction", $this);
	}

	public function onFileAction(MOXMAN_Vfs_FileActionEventArgs $args) {
		switch ($args->getAction()) {
			case MOXMAN_Vfs_FileActionEventArgs::ADD:
				if (!isset($args->getData()->format) && !isset($args->getData()->thumb)) {
					$this->applyFormat($args->getFile());
				}
				break;
		}
	}

	public function onBeforeFileAction(MOXMAN_Vfs_FileActionEventArgs $args) {
		switch ($args->getAction()) {
			case MOXMAN_Vfs_FileActionEventArgs::DELETE:
				if (!isset($args->getData()->format) && !isset($args->getData()->thumb)) {
					$this->removeFormat($args->getFile());
				}
				break;
		}
	}

	/**
	 * Applies formats to an image.
	 *
	 * @param MOXMAN_Vfs_IFile $file File to generate images for.
	 */
	public function applyFormat(MOXMAN_Vfs_IFile $file) {
		if (!$file->exists() || !MOXMAN_Media_ImageAlter::canEdit($file)) {
			return;
		}

		$config = $file->getConfig();
		$format = $config->get("autoformat.rules", "");
		$quality = $config->get("autoformat.jpeg_quality", 90);

		// @codeCoverageIgnoreStart
		if (!$format) {
			return;
		}
		// @codeCoverageIgnoreEnd

		// Export to temp file
		$tempFilePath = MOXMAN::getFileSystemManager()->getLocalTempPath($file);
		$file->exportTo($tempFilePath);

		$chunks = preg_split('/,/', $format, 0, PREG_SPLIT_NO_EMPTY);
		$imageInfo = MOXMAN_Media_MediaInfo::getInfo($tempFilePath);
		$width = $imageInfo["width"];
		$height = $imageInfo["height"];

		foreach ($chunks as $chunk) {
			$parts = explode('=', $chunk);
			$actions = array();

			$fileName = preg_replace('/\..+$/', '', $file->getName());
			$extension = preg_replace('/^.+\./', '', $file->getName());
			$targetWidth = $newWidth = $width;
			$targetHeight = $newHeight = $height;

			$items = explode('|', $parts[0]);
			foreach ($items as $item) {
				switch ($item) {
					case "gif":
					case "jpg":
					case "jpeg":
					case "png":
						$extension = $item;
						break;

					default:
						$matches = array();

						if (preg_match('/\s?([0-9|\*]+)\s?x([0-9|\*]+)\s?/', $item, $matches)) {
							$actions[] = "resize";
							$targetWidth = $matches[1];
							$targetHeight = $matches[2];

							if ($targetWidth == '*') {
								// Width is omitted
								$targetWidth = floor($width / ($height / $targetHeight));
							}

							if ($targetHeight == '*') {
								// Height is omitted
								$targetHeight = floor($height / ($width / $targetWidth));
							}
						}
				}
			}

			// Scale it
			if ($targetWidth != $width || $targetHeight != $height) {
				$scale = min($targetWidth / $width, $targetHeight / $height);
				$newWidth = $scale > 1 ? $width : floor($width * $scale);
				$newHeight = $scale > 1 ? $height : floor($height * $scale);
			}

			// Build output path
			$outPath = $parts[1];
			$outPath = str_replace("%f", $fileName, $outPath);
			$outPath = str_replace("%e", $extension, $outPath);
			$outPath = str_replace("%ow", "" . $width, $outPath);
			$outPath = str_replace("%oh", "" . $height, $outPath);
			$outPath = str_replace("%tw", "" . $targetWidth, $outPath);
			$outPath = str_replace("%th", "" . $targetHeight, $outPath);
			$outPath = str_replace("%w", "" . $newWidth, $outPath);
			$outPath = str_replace("%h", "" . $newHeight, $outPath);
			$outFile = MOXMAN::getFileSystemManager()->getFile($file->getParent(), $outPath);

			// Make dirs
			$parents = array();
			$parent = $outFile->getParentFile();
			while ($parent) {
				if ($parent->exists()) {
					break;
				}

				$parents[] = $parent;
				$parent = $parent->getParentFile();
			}

			for ($i = count($parents) - 1; $i >= 0; $i--) {
				$parents[$i]->mkdir();

				$args = new MOXMAN_Vfs_FileActionEventArgs(MOXMAN_Vfs_FileActionEventArgs::ADD, $parents[$i]);
				$args->getData()->format = true;
				MOXMAN::getPluginManager()->get("core")->fire("FileAction", $args);
			}

			if (count($actions) > 0) {
				foreach ($actions as $action) {
					switch ($action) {
						case 'resize':
							$imageAlter = new MOXMAN_Media_ImageAlter();
							$imageAlter->load($tempFilePath);
							$imageAlter->resize($newWidth, $newHeight);

							$outFileTempPath = MOXMAN::getFileSystemManager()->getLocalTempPath($outFile);
							$imageAlter->save($outFileTempPath, $quality);
							$outFile->importFrom($outFileTempPath);

							$args = new MOXMAN_Vfs_FileActionEventArgs(MOXMAN_Vfs_FileActionEventArgs::ADD, $outFile);
							$args->getData()->format = true;
							MOXMAN::getPluginManager()->get("core")->fire("FileAction", $args);
							break;
					}
				}
			} else {
				$imageAlter = new MOXMAN_Media_ImageAlter();
				$imageAlter->load($tempFilePath);

				$outFileTempPath = MOXMAN::getFileSystemManager()->getLocalTempPath($outFile);
				$imageAlter->save($outFileTempPath, $quality);
				$outFile->importFrom($outFileTempPath);

				$args = new MOXMAN_Vfs_FileActionEventArgs(MOXMAN_Vfs_FileActionEventArgs::ADD, $outFile);
				$args->getData()->format = true;
				MOXMAN::getPluginManager()->get("core")->fire("FileAction", $args);
			}
		}
	}

	/**
	 * Removes formats from an image.
	 *
	 * @param MOXMAN_Vfs_IFile $file File to generate images for.
	 */
	public function removeFormat(MOXMAN_Vfs_IFile $file) {
		if (!$file->exists() || !MOXMAN_Media_ImageAlter::canEdit($file)) {
			return;
		}

		$config = $file->getConfig();
		$format = $config->get("autoformat.rules", "");

		if ($config->get("autoformat.delete_format_images", true) === false) {
			return;
		}

		// @codeCoverageIgnoreStart
		if (!$format) {
			return;
		}
		// @codeCoverageIgnoreEnd

		// Export to temp file
		$tempFilePath = MOXMAN::getFileSystemManager()->getLocalTempPath($file);
		$file->exportTo($tempFilePath);

		$chunks = preg_split('/,/', $format, 0, PREG_SPLIT_NO_EMPTY);
		$imageInfo = MOXMAN_Media_MediaInfo::getInfo($tempFilePath);
		$width = $imageInfo["width"];
		$height = $imageInfo["height"];

		foreach ($chunks as $chunk) {
			$parts = explode('=', $chunk);

			$fileName = preg_replace('/\..+$/', '', $file->getName());
			$extension = preg_replace('/^.+\./', '', $file->getName());
			$targetWidth = $newWidth = $width;
			$targetHeight = $newHeight = $height;

			$items = explode('|', $parts[0]);
			foreach ($items as $item) {
				switch ($item) {
					case "gif":
					case "jpg":
					case "jpeg":
					case "png":
						$extension = $item;
						break;

					default:
						$matches = array();

						if (preg_match('/\s?([0-9|\*]+)\s?x([0-9|\*]+)\s?/', $item, $matches)) {
							$targetWidth = $matches[1];
							$targetHeight = $matches[2];

							if ($targetWidth == '*') {
								// Width is omitted
								$targetWidth = floor($width / ($height / $targetHeight));
							}

							if ($targetHeight == '*') {
								// Height is omitted
								$targetHeight = floor($height / ($width / $targetWidth));
							}
						}
				}
			}

			// Scale it
			if ($targetWidth != $width || $targetHeight != $height) {
				$scale = min($targetWidth / $width, $targetHeight / $height);
				$newWidth = $scale > 1 ? $width : floor($width * $scale);
				$newHeight = $scale > 1 ? $height : floor($height * $scale);
			}

			// Build output path
			$outPath = $parts[1];
			$outPath = str_replace("%f", $fileName, $outPath);
			$outPath = str_replace("%e", $extension, $outPath);
			$outPath = str_replace("%ow", "" . $width, $outPath);
			$outPath = str_replace("%oh", "" . $height, $outPath);
			$outPath = str_replace("%tw", "" . $targetWidth, $outPath);
			$outPath = str_replace("%th", "" . $targetHeight, $outPath);
			$outPath = str_replace("%w", "" . $newWidth, $outPath);
			$outPath = str_replace("%h", "" . $newHeight, $outPath);
			$outFile = MOXMAN::getFileSystemManager()->getFile($file->getParent(), $outPath);

			if ($outFile->exists()) {
				$outFile->delete();
			}
		}
	}

}

// Add plugin
MOXMAN::getPluginManager()->add("autoformat", new MOXMAN_AutoFormat_Plugin());

?>