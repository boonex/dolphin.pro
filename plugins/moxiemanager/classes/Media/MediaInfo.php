<?php
/**
 * MediaInfo.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class returns information about media types. Such as width/height/depth etc.
 *
 * @package MOXMAN_Media
 */
class MOXMAN_Media_MediaInfo {
	/**
	 * Returns an array with media info.
	 *
	 * @param String $path Path to file to get the media info for.
	 * @return Array Name/value array with media info.
	 */
	public static function getInfo($path) {
		if (!file_exists($path)) {
			return null;
		}

		$ext = strtolower(MOXMAN_Util_PathUtils::getExtension($path));

		switch ($ext) {
			case "png":
				return self::getPngInfo($path);

			default:
				$size = @getimagesize($path);

				if ($size) {
					return array("width" => $size[0], "height" => $size[1]);
				}
		}

		return null;
	}

	// @codeCoverageIgnoreStart

	/** @ignore */
	private static function getPngInfo($path) {
		$info = null;

		$fp = fopen($path, "rb");
		if ($fp) {
			$magic = fread($fp, 8);
			if ($magic === "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A" ) { // Is PNG
				// Read chunks
				do {
					$buff = fread($fp, 4);

					if (strlen($buff) != 4) {
						break;
					}

					$chunk = unpack('Nlen', $buff);
					$chunk['type'] = fread($fp, 4);

					if (strlen($chunk['type']) != 4) {
						break;
					}

					// Found header then read it
					if ($chunk['type'] == 'IHDR') {
						$header = unpack('Nwidth/Nheight/Cbits/Ctype/Ccompression/Cfilter/Cinterlace', fread($fp, 13));
						break;
					}

					// Jump to next chunk and skip CRC
					fseek($fp, $chunk['len'] + 4, SEEK_CUR);
				} while ($buff !== null);

				$info = array(
					"width" => $header["width"],
					"height" => $header["height"],
					"depth" => $header['type'] == 3 ? 8 : 32
				);
			}

			fclose($fp);
		}

		return $info;
	}

	// @codeCoverageIgnoreEnd
}
?>