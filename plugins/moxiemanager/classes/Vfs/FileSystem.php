<?php
/**
 * FileSystem.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Abstract base class for a file system. This provides common logic and can be used
 * as a base for FileSystem implementations.
 *
 * @package MOXMAN_Vfs
 */
abstract class MOXMAN_Vfs_FileSystem {
	/**
	 * File config provider instance used to produce configs for files.
	 *
	 * @var MOXMAN_Vfs_IFileConfigProvider
	 */
	protected $configProvider;

	/**
	 * File system config to be used as a base for file instances specific config.
	 *
	 * @var MOXMAN_Util_Config
	 */
	protected $config;

	/**
	 * File url provider instance used to produce urls for files.
	 *
	 * @var MOXMAN_Vfs_IFileUrlProvider
	 */
	protected $urlProvider;

	/**
	 * File url resolver instance used to produce file instances out of urls.
	 *
	 * @var MOXMAN_Vfs_IFileUrlResolver
	 */
	protected $urlResolver;

	/**
	 * Name of the root for the file system.
	 *
	 * @var string
	 */
	protected $rootName;

	/**
	 * Root path for the file system.
	 *
	 * @var string
	 */
	protected $rootPath;

	/**
	 * File system scheme for example "ftp" this is normally used by custom file systems.
	 *
	 * @var string
	 */
	protected $scheme;

	/**
	 * Fie meta data provider. Used to get meta data instances for files.
	 *
	 * @var MOXMAN_Vfs_IFileMetaDataProvider
	 */
	protected $metaDataProvider;

	/**
	 * Constructs a new file system with a scheme, config and root.
	 *
	 * @param string $scheme Scheme to use for the file system.
	 * @param MOXMAN_Util_Config $config Config instance to use for the file system.
	 * @param string $root Root path for the file system.
	 */
	public function __construct($scheme, $config, $root) {
		$this->scheme = $scheme;
		$this->config = $config;

		// Parse name=path roots
		$root = explode('=', $root);

		if (count($root) == 2) {
			$this->rootName = $root[0];
			$this->rootPath = $root[1];
		} else {
			$name = $root[0];
			$path = $root[0];

			// Parse away scheme prefix from root
			if (preg_match('/^([a-z0-9]+):\/\/(.+)$/', $path, $matches)) {
				if ($matches[1] === $this->scheme) {
					$name = $matches[2];
				}
			}

			$this->rootName = $name === '/' ? $name : basename($name);
			$this->rootPath = $path;
		}
	}

	/**
	 * Returns the true/false if the file system can be cached or not.
	 *
	 * @return True/false if the file system is cacheable or not.
	 */
	public function isCacheable() {
		return true;
	}

	/**
	 * Returns the file system scheme for example "local".
	 *
	 * @return String File system scheme.
	 */
	public function getScheme() {
		return $this->scheme;
	}

	/**
	 * Returns the name of the root for the file system.
	 *
	 * @return String Name of the root for the file system.
	 */
	public function getRootName() {
		return $this->rootName;
	}

	/**
	 * Returns the path of the root for the file system.
	 *
	 * @return String Path for the root of the file system.
	 */
	public function getRootPath() {
		return $this->rootPath;
	}

	/**
	 * Returns the a file object for the root of the file system.
	 *
	 * @return MOXMAN_Vfs_IFile Root file for the file system.
	 */
	public function getRootFile() {
		return $this->getFile($this->rootPath);
	}

	/**
	 * Sets the meta data provider instance.
	 *
	 * @param MOXMAN_Vfs_IFileMetaDataProvider $provider Provider instance to use for the file system.
	 */
	public function setFileMetaDataProvider(MOXMAN_Vfs_IFileMetaDataProvider $provider) {
		$this->metaDataProvider = $provider;
	}

	/**
	 * Returns the meta data provider instance.
	 *
	 * @return MOXMAN_Vfs_IFileMetaDataProvider Meta data provider instance.
	 */
	public function getFileMetaDataProvider() {
		if (!$this->metaDataProvider) {
			$this->metaDataProvider = new MOXMAN_Vfs_BasicFileMetaDataProvider($this);
		}

		return $this->metaDataProvider;
	}

	/**
	 * Sets the config provider for the filesystem.
	 *
	 * @param MOXMAN_Vfs_IFileConfigProvider $provider Config provider instance to use for the file system.
	 */
	public function setFileConfigProvider(MOXMAN_Vfs_IFileConfigProvider $provider) {
		$this->configProvider = $provider;
	}

	/**
	 * Returns the config provider instance.
	 *
	 * @return MOXMAN_Vfs_IFileConfigProvider Config provider instance.
	 */
	public function getFileConfigProvider() {
		return $this->configProvider;
	}

	/**
	 * Sets the url provider instance.
	 *
	 * @param MOXMAN_Vfs_IFileUrlProvider $provider File Url provider to use to resolve files into urls.
	 */
	public function setFileUrlProvider(MOXMAN_Vfs_IFileUrlProvider $provider) {
		$this->urlProvider = $provider;
	}

	/**
	 * Returns the url provider instance.
	 *
	 * @return MOXMAN_Vfs_IFileUrlProvider File url provider.
	 */
	public function getFileUrlProvider() {
		return $this->urlProvider;
	}

	/**
	 * Sets the url resolver instance.
	 *
	 * @param MOXMAN_Vfs_IFileUrlResolver $resolver File Url resolver to use to resolve urls into files.
	 */
	public function setFileUrlResolver(MOXMAN_Vfs_IFileUrlResolver $resolver) {
		$this->urlResolver = $resolver;
	}

	/**
	 * Returns the url resolver instance.
	 *
	 * @return MOXMAN_Vfs_IFileUrlResolver File url resolver.
	 */
	public function getFileUrlResolver() {
		return $this->urlResolver;
	}

	/**
	 * Config instance for the file system.
	 *
	 * @return MOXMAN_Util_Config Config instance.
	 */
	public function getConfig() {
		return $this->config;
	}

	/**
	 * Returns a MOXMAN_Vfs_IFile instance based on the specified path.
	 *
	 * @param string $path Path of the file to retrive.
	 * @return MOXMAN_Vfs_IFile File instance for the specified path.
	 */
	//public abstract function getFile($path);

	/**
	 * Closes the file system.
	 */
	public function close() {
		if ($this->metaDataProvider) {
			$this->metaDataProvider->dispose();
			$this->metaDataProvider = null;
		}
	}
}

?>