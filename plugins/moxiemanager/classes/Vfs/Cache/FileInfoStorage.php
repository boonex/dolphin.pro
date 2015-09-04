<?php
/**
 * FileInfoStorage.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Manages file info items in a storage container like a SQL database.
 *
 * @package MOXMAN_Vfs_Cache
 */
class MOXMAN_Vfs_Cache_FileInfoStorage {
	private static $instance;
	private $pdo;

	public function getInfo(MOXMAN_Vfs_IFile $file) {
		$pdo = $this->getPdo();
		if (!$pdo) {
			return null;
		}

		$path = $this->getIndexPath($file);
		$this->log("[cache] getInfo path=" . $path);

		$stmt = $pdo->q(
			"SELECT mc_name, mc_attrs, mc_size, mc_last_modified FROM " .
			"moxman_cache WHERE mc_path = :mc_path AND mc_name = :mc_name",
			array(
				"mc_path" => dirname($path),
				"mc_name" => basename($path)
			)
		);

		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($result) {
			return $this->rowToInfo($result);
		}

		return null;
	}

	public function putFiles($files) {
		$pdo = $this->getPdo();
		if (!$pdo) {
			return null;
		}

		if (count($files) == 0) {
			return null;
		}

		$parentPath = $this->getIndexPath($files[0]->getParentFile());
		$name = "";
		$attrs = "";
		$size = "";
		$lastModified = "";
		$cachedTime = date('Y-m-d H:i:s', time());
		$inserts = 0;
		$items = array();

		$excludedNames = array();
		$result = $pdo->qrs("SELECT mc_name FROM moxman_cache WHERE mc_path = :mc_path", array("mc_path" => $parentPath));
		foreach ($result as $row) {
			$excludedNames[$row["mc_name"]] = true;
		}

		$stmt = $pdo->prepare(
			"INSERT INTO moxman_cache(mc_path, mc_name, mc_extension, mc_attrs, mc_size, mc_last_modified, mc_cached_time) " . "
			VALUES(:mc_path, :mc_name, :mc_extension, :mc_attrs, :mc_size, :mc_last_modified, :mc_cached_time)"
		);

		$stmt->bindParam(':mc_path', $parentPath);
		$stmt->bindParam(':mc_name', $name);
		$stmt->bindParam(':mc_extension', $extension);
		$stmt->bindParam(':mc_attrs', $attrs);
		$stmt->bindParam(':mc_size', $size);
		$stmt->bindParam(':mc_last_modified', $lastModified);
		$stmt->bindParam(':mc_cached_time', $cachedTime);

		$time = microtime(true);

		$pdo->beginTransaction();

		foreach ($files as $file) {
			$name = $file->getName();

			if (!isset($excludedNames[$name])) {
				$inserts++;
				$attrs = $file->isDirectory() ? "d" : "-";
				$attrs .= $file->canRead() ? "r" : "-";
				$attrs .= $file->canWrite() ? "w" : "-";
				$attrs .= "-";
				$extension = pathinfo($name, PATHINFO_EXTENSION);
				$size = $file->isFile() ? $file->getSize() : null;
				$lmod = $file->getLastModified();
				$lastModified = date('Y-m-d H:i:s', $lmod);
				$stmt->execute();

				$items[] = array(
					"name" => $name,
					"isDirectory" => $attrs[0] == 'd',
					"canRead" => $attrs[1] == 'r',
					"canWrite" => $attrs[2] == 'w',
					"size" => $size,
					"lastModified" => $lmod
				);
			}
		}

		$stmt = $pdo->prepare(
			"UPDATE moxman_cache SET mc_attrs = :mc_attrs, mc_size = :mc_size, mc_last_modified = :mc_last_modified," .
			"mc_cached_time = :mc_cached_time WHERE mc_path = :mc_path AND mc_name = :mc_name"
		);

		$stmt->bindParam(':mc_path', $parentPath);
		$stmt->bindParam(':mc_name', $name);
		$stmt->bindParam(':mc_attrs', $attrs);
		$stmt->bindParam(':mc_size', $size);
		$stmt->bindParam(':mc_last_modified', $lastModified);
		$stmt->bindParam(':mc_cached_time', $cachedTime);

		foreach ($excludedNames as $name) {
			$name = $file->getName();
			$attrs = $file->isDirectory() ? "d" : "-";
			$attrs .= $file->canRead() ? "r" : "-";
			$attrs .= $file->canWrite() ? "w" : "-";
			$attrs .= "-";
			$size = $file->isFile() ? $file->getSize() : null;
			$lmod = $file->getLastModified();
			$lastModified = date('Y-m-d H:i:s', $lmod);
			$stmt->execute();

			$items[] = array(
				"name" => $name,
				"isDirectory" => $attrs[0] == 'd',
				"canRead" => $attrs[1] == 'r',
				"canWrite" => $attrs[2] == 'w',
				"size" => $size,
				"lastModified" => $lmod
			);
		}

		$pdo->commit();

		$this->log("[cache] putFiles inserts=" . $inserts . ", updates=" .  count($excludedNames) . " took " . round(microtime(true) - $time, 6) . "s");

		return $items;
	}

	public function putFile(MOXMAN_Vfs_IFile $file) {
		$pdo = $this->getPdo();
		if (!$pdo) {
			return null;
		}

		$parentFile = $file->getParentFile();
		if (!$parentFile) {
			$info = array(
				"name" => $file->getFileSystem()->getRootName(),
				"isDirectory" => true,
				"canRead" => $file->canRead(),
				"canWrite" => $file->canWrite(),
				"size" => 0,
				"lastModified" => 0
			);

			return $info;
		}

		$parentPath = $this->getIndexPath($parentFile);

		$attrs = $file->isDirectory() ? "d" : "-";
		$attrs .= $file->canRead() ? "r" : "-";
		$attrs .= $file->canWrite() ? "w" : "-";
		$attrs .= "-";
		$size = $file->getSize();
		$lastModified = $file->getLastModified();

		$numItems = $pdo->i(
			"SELECT COUNT(mc_id) FROM moxman_cache WHERE mc_path = :mc_path AND mc_name = :mc_name",
			array(
				"mc_path" => $parentPath,
				"mc_name" => $file->getName()
			)
		);

		if ($numItems > 0) {
			$pdo->q(
				"UPDATE moxman_cache SET mc_attrs = :mc_attrs, mc_size = :mc_size, " .
				"mc_last_modified = :mc_last_modified, mc_cached_time = :mc_cached_time WHERE mc_path = :mc_path AND mc_name = :mc_name",
				array(
					"mc_attrs" => $attrs,
					"mc_size" => $file->isFile() ? $size : null,
					"mc_last_modified" => date('Y-m-d H:i:s', $lastModified),
					"mc_cached_time" => date('Y-m-d H:i:s', time()),
					"mc_path" => $parentPath,
					"mc_name" => $file->getName()
				)
			);

			$this->log("[cache] putFile update");
		} else {
			$pdo->q(
				"INSERT INTO moxman_cache(mc_path, mc_name, mc_extension, mc_attrs, mc_size, mc_last_modified, mc_cached_time) " .
				"VALUES(:mc_path, :mc_name, :mc_extension, :mc_attrs, :mc_size, :mc_last_modified, :mc_cached_time)",
				array(
					"mc_path" => $parentPath,
					"mc_name" => $file->getName(),
					"mc_extension" => pathinfo($file->getName(), PATHINFO_EXTENSION),
					"mc_attrs" => $attrs,
					"mc_size" => $file->isFile() ? $size : null,
					"mc_last_modified" => date('Y-m-d H:i:s', $lastModified),
					"mc_cached_time" => date('Y-m-d H:i:s', time())
				)
			);

			$this->log("[cache] putFile insert");
		}

		$info = array(
			"name" => $file->getName(),
			"isDirectory" => $attrs[0] == 'd',
			"canRead" => $attrs[1] == 'r',
			"canWrite" => $attrs[2] == 'w',
			"size" => $size,
			"lastModified" => $lastModified
		);

		return $info;
	}

	public function listInfoItems(MOXMAN_Vfs_IFile $file, $filterQuery = "") {
		$pdo = $this->getPdo();
		if (!$pdo) {
			return null;
		}

		$path = $this->getIndexPath($file);

		$sql = "SELECT mc_name, mc_attrs, mc_size, mc_last_modified FROM ";
		$sql .= "moxman_cache WHERE mc_path = :mc_path ";
		$sql .= $filterQuery;

		$result = $pdo->qrs(
			$sql,
			array(
				"mc_path" => $path
			)
		);

		// Don't use cached empty dirs
		if (count($result) == 0) {
			if ($filterQuery) {
				return array();
			}

			return null;
		}

		$items = array();
		foreach ($result as $row) {
			$items[] = $this->rowToInfo($row);
		}

		return $items;
	}

	public function deleteFile(MOXMAN_Vfs_IFile $file) {
		$this->deletePath($this->getIndexPath($file));
		$this->log("[cache] deleteFile");
	}

	public function updateFileList(MOXMAN_Vfs_IFile $file) {
		if ($file instanceof MOXMAN_Vfs_Cache_File) {
			$items = $this->listInfoItems($file);
			if ($items) {
				$path = $this->getIndexPath($file->getWrappedFile());
				$paths = array();
				foreach ($items as $item) {
					$paths[$path . "/" . $item["name"]] = true;
				}

				$files = $file->getWrappedFile()->listFiles()->toArray();
				for ($i = 0; $i < count($files); $i++) {
					$filePath = $this->getIndexPath($files[$i]);

					if (isset($paths[$filePath])) {
						unset($paths[$filePath]);
					}
				}

				$this->putFiles($files);

				foreach (array_keys($paths) as $path) {
					$this->deletePath($path);
				}

				if (count($paths)) {
					$this->log("[cache] updateFileList deleted=" . count($paths));
				}
			}
		}
	}

	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new MOXMAN_Vfs_Cache_FileInfoStorage();
		}

		return self::$instance;
	}

	// Private methods

	private function deletePath($path) {
		$pdo = $this->getPdo();
		if (!$pdo) {
			return;
		}

		$result = $pdo->q(
			"DELETE FROM moxman_cache WHERE mc_path LIKE :mc_path",
			array(
				"mc_path" => $path . '%'
			)
		);

		$result = $pdo->q(
			"DELETE FROM moxman_cache WHERE mc_path = :mc_path AND mc_name = :mc_name",
			array(
				"mc_path" => dirname($path),
				"mc_name" => basename($path)
			)
		);
	}

	private function rowToInfo($result) {
		return array(
			"name" => $result["mc_name"],
			"isDirectory" => $result["mc_attrs"][0] == 'd',
			"canRead" => $result["mc_attrs"][1] == 'r',
			"canWrite" => $result["mc_attrs"][2] == 'w',
			"size" => intval($result["mc_size"]),
			"lastModified" => strtotime($result["mc_last_modified"])
		);
	}

	private function getIndexPath(MOXMAN_Vfs_IFile $file) {
		$path = $file->getPath();
		$rootName = $file->getFileSystem()->getRootName();
		$publicPath = MOXMAN_Util_PathUtils::combine(
			$rootName !== "/" ? "/" . $rootName : $rootName,
			substr($path, strlen($file->getFileSystem()->getRootPath()))
		);

		return $publicPath;
	}

	public function getPdo() {
		if (!$this->pdo) {
			if (!class_exists('PDO')) {
				return null;
			}

			try {
				$this->pdo = new MOXMAN_Util_Pdo(MOXMAN::getConfig()->get("cache.connection"));
			} catch (PDOException $e) {
				// Ignore exceptions about missing driver
				if ($e->getMessage() === "could not find driver") {
					return null;
				}
			}

			if ($this->pdo && $this->pdo->getDriverName() == "sqlite") {
				// Check if database could be created return null if it failed
				if (!file_exists($this->pdo->getSqliteFilePath())) {
					return null;
				}

				// If it's empty fill it with the schema
				if (filesize($this->pdo->getSqliteFilePath()) === 0) {
					$statements = explode(';', file_get_contents(dirname(__FILE__) . "/schema-sqlite3.sql"));

					foreach ($statements as $sql) {
						$this->pdo->q($sql);
					}
				}
			}
		}

		return $this->pdo;
	}

	private function log($message) {
		$logger = MOXMAN::getLogger();

		if ($logger) {
			MOXMAN::getLogger()->debug($message);
		}
	}
}
?>