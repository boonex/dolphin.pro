<?php
/**
 * FileList.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class handles the stream for the local file system.
 *
 * @package MOXMAN_Vfs_Cache
 */
class MOXMAN_Vfs_Cache_FileList extends MOXMAN_Vfs_FileList {
	public function __construct(MOXMAN_Vfs_Cache_File $file, MOXMAN_Vfs_IFileFilter $filter) {
		$this->orderBy = "name";
		$this->last = false;
		$this->file = $file;
		$this->filter = $filter;
	}

	public function getFiles() {
		$pdo = $this->file->getFileInfoStorage()->getPdo();
		$dirPath = $this->file->getPublicPath();
		$offset = $this->offset;
		$length = $this->length;
		$filter = $this->filter;

		if ($this->isOrdered) {
			return $this->files;
		}

		// Compile filters to SQL and build order by part
		$dirFilterSql = self::compileFilterToSql($this->filter, false);
		$fileFilterSql = self::compileFilterToSql($this->filter, true);
		$orderBySql = $this->getOrderBySql();

		// Count filtered dirs and files
		$dirCount = $pdo->i('SELECT COUNT(mc_id) FROM moxman_cache WHERE mc_path = :mc_path AND mc_size IS NULL' . $dirFilterSql, array("mc_path" => $dirPath));
		$fileCount = $pdo->i('SELECT COUNT(mc_id) FROM moxman_cache WHERE mc_path = :mc_path AND mc_size IS NOT NULL' . $fileFilterSql, array("mc_path" => $dirPath));

		if ($dirCount + $fileCount === 0) {
			// Update file list if there is nothing matching the path
			$itemCount = $pdo->i('SELECT COUNT(mc_id) FROM moxman_cache WHERE mc_path = :mc_path', array("mc_path" => $dirPath));
			if ($itemCount === 0) {
				$files = $this->file->getWrappedFile()->listFiles()->toArray();
				$this->file->getFileInfoStorage()->putFiles($files);

				if (count($files) == 0) {
					$this->isOrdered = true;
					$this->files = $files;
					$this->last = true;
					return $files;
				}
			}

			// Count filtered dirs and files again since they have been changed
			$dirCount = $pdo->i('SELECT COUNT(mc_id) FROM moxman_cache WHERE mc_path = :mc_path AND mc_size IS NULL' . $dirFilterSql, array("mc_path" => $dirPath));
			$fileCount = $pdo->i('SELECT COUNT(mc_id) FROM moxman_cache WHERE mc_path = :mc_path AND mc_size IS NOT NULL' . $fileFilterSql, array("mc_path" => $dirPath));
		}

		// Calculate length if needed
		if ($length === null) {
			$length = $dirCount + $fileCount;
		}

		// Get one extra file so we know if it's the last chunk or not
		$length++;
		$chunkLength = $length + 10;
		$files = array();

		// Get directories matching filter
		$dirOffset = 0;
		if ($offset < $dirCount) {
			while (count($files) < $length) {
				$result = $pdo->qrs('SELECT * FROM moxman_cache WHERE mc_path = :mc_path AND mc_size IS NULL' . $dirFilterSql . $orderBySql . ' LIMIT :start, :length', array(
					"mc_path" => $dirPath,
					"start" => $offset,
					"length" => $chunkLength
				));

				if (count($result) == 0) {
					break;
				}

				$offset += count($result);

				foreach ($result as $row) {
					$file = $this->rowToFile($row);

					if ($filter->accept($file)) {
						$files[] = $file;

						if (count($files) >= $length) {
							break;
						}
					}

					$dirOffset++;
				}
			}
		}

		$offset -= $dirCount;
		if ($offset < 0) {
			$offset = 0;
		}

		// Get files matching filter
		$fileOffset = 0;
		if ($fileCount > 0) {
			while (count($files) < $length) {
				$result = $pdo->qrs('SELECT * FROM moxman_cache WHERE mc_path = :mc_path AND mc_size IS NOT NULL' . $fileFilterSql . $orderBySql . ' LIMIT :start, :length', array(
					"mc_path" => $dirPath,
					"start" => $offset,
					"length" => $chunkLength
				));

				if (count($result) == 0) {
					break;
				}

				$offset += count($result);

				foreach ($result as $row) {
					$file = $this->rowToFile($row);

					if ($filter->accept($file)) {
						$files[] = $file;

						if (count($files) >= $length) {
							break;
						}
					}

					$fileOffset++;
				}
			}
		}

		$this->last = count($files) < $length;
		if (!$this->last) {
			$files = array_splice($files, 0, $length - 1);
		}

		$this->isOrdered = true;
		$this->files = $files;
		$this->offset += $dirOffset + $fileOffset;

		return $files;
	}

	private function rowToFile($row) {
		return $this->file->getFileSystem()->getFile($this->file->getPath() . '/' . $row["mc_name"], array(
			"name" => $row["mc_name"],
			"isDirectory" => $row["mc_attrs"][0] == 'd',
			"canRead" => $row["mc_attrs"][1] == 'r',
			"canWrite" => $row["mc_attrs"][2] == 'w',
			"size" => intval($row["mc_size"]),
			"lastModified" => strtotime($row["mc_last_modified"])
		));
	}

	private function getOrderBySql() {
		$orderSql = " ORDER BY ";

		switch ($this->orderBy) {
			case "name":
				$orderSql .= "mc_name";
				break;

			case "size":
				$orderSql .= "mc_size";
				break;

			case "lastmodified":
				$orderSql .= "mc_last_modified";
				break;

			case "extension":
				$orderSql .= "mc_extension";
				break;
		}

		if ($this->desc) {
			$orderSql .= " DESC";
		}

		return $orderSql;
	}

	public static function compileFilterToSql($filter, $isFile) {
		$filters = self::getFilters($filter);
		$extensions = array();
		$filterQuery = "";

		// Merge extensions
		if ($isFile) {
			foreach ($filters as $filter) {
				$filterExtensions = $filter->getIncludeExtensions();
				if ($filterExtensions) {
					$filterExtensions = explode(',', $filterExtensions);

					if (count($extensions)) {
						for ($i = 0; $i < count($extensions); $i++) {
							if (!in_array($extensions[$i], $filterExtensions)) {
								array_splice($extensions, $i, 1);
								$i--;
							}
						}
					} else {
						$extensions = array_unique($filterExtensions);
					}
				}
			}
		}

		// Compile wildcards
		foreach ($filters as $filter) {
			if ($filter instanceof MOXMAN_Vfs_BasicFileFilter) {
				$includeWildcardPattern = $filter->getIncludeWildcardPattern();
				if ($includeWildcardPattern) {
					if (!preg_match('/[%\"\'\x00-\x19]/', $includeWildcardPattern)) {
						if ($filterQuery) {
							$filterQuery .= " AND ";
						}

						$filterQuery .= "mc_name LIKE '%" . $includeWildcardPattern . "%'";
					}
				}
			}
		}

		// Compile extensions
		if (count($extensions)) {
			$extensionsQuery = "";
			foreach ($extensions as $extension) {
				if (!preg_match('/[%\"\'\x00-\x19]/', $extension)) {
					if ($extensionsQuery) {
						$extensionsQuery .= " OR ";
					}

					$extensionsQuery .= "LOWER(mc_extension) = '" . strtolower($extension) . "'";
				}
			}

			if ($filterQuery) {
				$filterQuery .= " AND ";
			}

			$filterQuery .= "(" . $extensionsQuery . ")";
		}

		if ($filterQuery) {
			return " AND " . $filterQuery;
		}

		return "";
	}

	public static function getFilters($filter) {
		$filters = array();

		if ($filter instanceof MOXMAN_Vfs_CombinedFileFilter) {
			$combinedFilters = $filter->getFilters();
			foreach ($combinedFilters as $filter) {
				$filters = array_merge(self::getFilters($filter), $filters);
			}
		} else {
			$filters[] = $filter;
		}

		return $filters;
	}
}
?>