<?php

/**
 * File System Functions
 *
 * File System Abstraction which automatically
 * works in a given root path
 *
 */
class FS
{
	static protected $rootPath = null;
	static protected $rootLength = 0;

	/**
	 * set application root directory
	 *
	 * @param string $rootPath
	 * @return void
	 */
	static public function setRoot(string $rootPath, $chdir = false): void
	{
		$realpath = \realpath($rootPath);

		if (!$realpath) {
			throw new \Exception('FilesystemFunctionsTrait "' . $rootPath . '" is not a valid directory.');
		}

		self::$rootPath = $realpath;
		self::$rootLength = \strlen($realpath);

		if ($chdir) {
			\chdir(self::$rootPath);
		}
	}

	static public function getRoot(): string
	{
		return self::$rootPath;
	}

	/**
	 * Format a given path so it's based on the applications root folder __ROOT__.
	 *
	 * Either add or remove __ROOT__ from path
	 *
	 * @param string $path
	 * @param bool $add true
	 * @return string
	 */
	static public function resolve(string $path, bool $remove = false): string
	{
		if (self::$rootPath == null) {
			throw new \Exception('FilesystemFunctionsTrait root path is not defined.');
		}

		/* strip it if it's present */
		$cleanPath = (\substr($path, 0, self::$rootLength) == self::$rootPath) ? \substr($path, self::$rootLength) : $path;

		/* stripped or added? */
		return ($remove) ? \rtrim($cleanPath, DIRECTORY_SEPARATOR) : self::$rootPath . DIRECTORY_SEPARATOR . \trim($cleanPath, DIRECTORY_SEPARATOR);
	}

	/**
	 * Find pathnames matching a pattern
	 *
	 * @param string $pattern
	 * @param int $flags
	 * @param bool $recursive false
	 * @return array
	 */
	static public function glob(string $pattern, int $flags = 0, bool $recursive = false, bool $strip = true): array
	{
		$files = ($recursive) ? self::_globr(self::resolve($pattern), $flags) : \glob(self::resolve($pattern), $flags);

		/* strip the root path */
		if ($strip) {
			foreach ($files as $idx => $file) {
				$files[$idx] = self::resolve($file, true);
			}
		}

		return $files;
	}

	/* internal recursive loop for globr */
	static protected function _globr(string $pattern, int $flags = 0): array
	{
		$files = \glob($pattern, $flags);

		foreach (\glob(\dirname($pattern) . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR | GLOB_NOSORT) as $directory) {
			/* recursive loop */
			$files = \array_merge($files, self::_globr($directory . DIRECTORY_SEPARATOR . \basename($pattern), $flags));
		}

		return $files;
	}

	/**
	 * Reads entire file into a string
	 *
	 * @param string $filename
	 * @return string
	 */
	static public function file_get_contents(string $filename): string
	{
		return \file_get_contents(self::resolve($filename));
	}

	/**
	 * Returns trailing name component of path
	 *
	 * @param string $path
	 * @param string $suffix
	 * @return string
	 */
	static public function basename(string $path, string $suffix = ''): string
	{
		return \basename(self::resolve($path), $suffix);
	}

	/**
	 * Returns information about a file path
	 *
	 * @param string $path
	 * @param int $options
	 * @return mixed
	 */
	static public function pathinfo(string $path, int $options = PATHINFO_DIRNAME | PATHINFO_BASENAME | PATHINFO_EXTENSION | PATHINFO_FILENAME) /* mixed */
	{
		$pathinfo = \pathinfo(self::resolve($path), $options);

		if (\is_array($pathinfo) && isset($pathinfo['dirname'])) {
			$pathinfo['dirname'] = self::resolve($pathinfo['dirname'], true);
		}

		return $pathinfo;
	}

	/**
	 * Reads a file and writes it to the output buffer.
	 *
	 * @param string $filename
	 * @return int
	 */
	static public function readfile(string $filename): int
	{
		return \readfile(self::resolve($filename));
	}

	/**
	 * dirname — Returns a parent directory's path
	 *
	 * @param string $path
	 * @param mixed int
	 * @return string
	 */
	static public function dirname(string $path, int $levels  = 1): string
	{
		return self::resolve(\dirname(self::resolve($path, true), $levels), true);
	}
	/**
	 * filesize — Gets file size
	 *
	 * @param string $filename
	 * @return int
	 */
	static public function filesize(string $filename): int
	{
		return \filesize(self::resolve($filename));
	}

	/**
	 * is_dir — Tells whether the filename is a directory
	 *
	 * @param string $filename
	 * @return bool
	 */
	static public function is_dir(string $filename): bool
	{
		return \is_dir(self::resolve($filename));
	}

	/**
	 * is_writable — Tells whether the filename is writable
	 *
	 * @param string $filename
	 * @return bool
	 */
	static public function is_writable(string $filename): bool
	{
		return \is_writable(self::resolve($filename));
	}

	/**
	 * chgrp — Changes file group
	 *
	 * @param string $filename
	 * @param mixed $group
	 * @return bool
	 */
	static public function chgrp(string $filename, $group): bool
	{
		return \chgrp(self::resolve($filename), $group);
	}

	/**
	 * chmod — Changes file mode
	 *
	 * @param string $filename
	 * @param mixed $mode
	 * @return bool
	 */
	static public function chmod(string $filename, $mode): bool
	{
		return \chmod(self::resolve($filename), $mode);
	}

	/**
	 * chown — Changes file owner
	 *
	 * @param string $filename
	 * @param string $user
	 * @return bool
	 */
	static public function chown(string $filename, string $user): bool
	{
		return \chown(self::resolve($filename), $user);
	}

	/**
	 * is_file — Tells whether the filename is a regular file
	 *
	 * @param string $filename
	 * @return bool
	 */
	static public function is_file(string $filename): bool
	{
		return \is_file(self::resolve($filename));
	}

	/**
	 * fileatime — Gets last access time of file
	 *
	 * @param string $filename
	 * @return int
	 */
	static public function fileatime(string $filename): int
	{
		return \fileatime(self::resolve($filename));
	}

	/**
	 * filectime — Gets inode change time of file
	 *
	 * @param string $filename
	 * @return int
	 */
	static public function filectime(string $filename): int
	{
		return \filectime(self::resolve($filename));
	}

	/**
	 * filemtime — Gets file modification time
	 *
	 * @param string $filename
	 * @return int
	 */
	static public function filemtime(string $filename): int
	{
		return \filemtime(self::resolve($filename));
	}

	/**
	 * filegroup — Gets file group
	 *
	 * @param string $filename
	 * @return int
	 */
	static public function filegroup(string $filename): int
	{
		return \filegroup(self::resolve($filename));
	}

	/**
	 * fileowner — Gets file owner
	 *
	 * @param string $filename
	 * @return int
	 */
	static public function fileowner(string $filename): int
	{
		return \fileowner(self::resolve($filename));
	}

	/**
	 * fileperms — Gets file permissions
	 *
	 * @param string $filename
	 * @return int
	 */
	static public function fileperms(string $filename): int
	{
		return \fileperms(self::resolve($filename));
	}

	/**
	 * fileinode — Gets file inode
	 *
	 * @param string $filename
	 * @return int
	 */
	static public function fileinode(string $filename): int
	{
		return \fileinode(self::resolve($filename));
	}

	/**
	 * filetype — Gets file type
	 *
	 * @param string $filename
	 * @return string
	 */
	static public function filetype(string $filename): string
	{
		return \filetype(self::resolve($filename));
	}

	/**
	 * parse_ini_file — Parse a configuration file
	 *
	 * @param string $filename
	 * @param bool $process_sections create a multidimensional array
	 * @param int $scanner_mode INI_SCANNER_NORMAL, INI_SCANNER_RAW, INI_SCANNER_TYPED
	 * @return mixed
	 */
	static public function parse_ini_file(string $filename, bool $process_sections = FALSE, int $scanner_mode = INI_SCANNER_NORMAL) /* mixed */
	{
		return \parse_ini_file(self::resolve($filename), $process_sections, $scanner_mode);
	}

	/**
	 * file_exists — Checks whether a file or directory exists
	 *
	 * @param string $filename
	 * @return bool
	 */
	static public function file_exists(string $filename): bool
	{
		return \file_exists(self::resolve($filename));
	}

	/**
	 * file — Reads entire file into an array
	 *
	 * @param string $filename
	 * @param int $flags
	 * @return array
	 */
	static public function file(string $filename, int $flags = 0): array
	{
		return \file(self::resolve($filename), $flags);
	}

	/**
	 * fopen — Opens file or URL
	 *
	 * @param string $filename
	 * @param string $mode
	 * @return resource
	 */
	static public function fopen(string $filename, string $mode) /* resource */
	{
		/* after you get back the resource there is no other reason to not use PHPs regular fclose, fgets, fwrite */
		return \fopen(self::resolve($filename), $mode);
	}

	/**
	 * file_put_contents — Write data to a file
	 *
	 * This should have thrown an error before not being able to write a file_exists
	 * This writes the file in a atomic fashion unless you use $flags
	 *
	 * @param string $pathname
	 * @param mixed $content
	 * @param int $flags
	 * @return mixed returns the number of bytes that were written to the file, or FALSE on failure.
	 */
	static public function file_put_contents(string $pathname, $content, int $flags = 0) /* mixed */
	{
		/* if they aren't using any special flags just make it atomic that way locks aren't needed or partially written files aren't read */
		return ($flags) ? \file_put_contents(self::resolve($pathname), $content, $flags) : self::atomic_file_put_contents($pathname, $content);
	}

	/**
	 * unlink — Deletes a file
	 *
	 * @param string $filename
	 * @return bool
	 */
	static public function unlink(string $filename): bool
	{
		self::remove_php_file_from_opcache($filename);

		return \unlink(self::resolve($filename));
	}

	/**
	 * rmdir — Removes directory
	 *
	 * @param string $dirname
	 * @return bool
	 */
	static public function rmdir(string $dirname, bool $recursive = false): bool
	{
		$dirname = self::resolve($dirname);

		return ($recursive) ? self::_rmdir($dirname) : \rmdir($dirname);
	}

	static protected function _rmdir(string $dirname): bool
	{
		$files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dirname, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);

		foreach ($files as $fileinfo) {
			$function = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
			$function($fileinfo->getRealPath());
		}

		return \rmdir($dirname);
	}

	/**
	 * mkdir — Makes directory
	 *
	 * @param string $pathname
	 * @param int $mode
	 * @param bool $recursive
	 * @return bool
	 */
	static public function mkdir(string $pathname, int $mode = 0777, bool $recursive = false): bool
	{
		$pathname = self::resolve($pathname);

		if (!\file_exists($pathname)) {
			$umask = \umask(0);
			$bool = \mkdir($pathname, $mode, $recursive);
			\umask($umask);
		} else {
			$bool = true;
		}

		return $bool;
	}

	/**
	 * rename — Renames a file or directory
	 *
	 * @param string $oldname
	 * @param string $newname
	 * @return bool
	 */
	static public function rename(string $oldname, string $newname): bool
	{
		return \rename(self::resolve($oldname), self::resolve($newname));
	}

	/**
	 * copy — Copies file
	 *
	 * @param string $source
	 * @param string $dest
	 * @return bool
	 */
	static public function copy(string $source, string $dest): bool
	{
		return \copy(self::resolve($source), self::resolve($dest));
	}

	/**
	 * New (but used automatically by unlink and atomic_file_put_contents)
	 *
	 * Invalidates a cached script
	 *
	 * @param string $pathname
	 * @return bool
	 */
	static public function remove_php_file_from_opcache(string $pathname): bool
	{
		$pathname = self::resolve($pathname);

		$success = true;

		/* flush from the cache */
		if (\function_exists('opcache_invalidate')) {
			$success = \opcache_invalidate($pathname, true);
		} elseif (\function_exists('apc_delete_file')) {
			$success = \apc_delete_file($pathname);
		}

		return $success;
	}

	/**
	 * New (but used automatically by file_put_contents when no flags are used)
	 *
	 * atomic_file_put_contents - atomic file_put_contents
	 *
	 * @param string $pathname
	 * @param mixed $content
	 * @return int returns the number of bytes that were written to the file.
	 */
	static public function atomic_file_put_contents(string $pathname, $content): int
	{
		/* create absolute path */
		$pathname = self::resolve($pathname);

		/* get the path where you want to save this file so we can put our file in the same directory */
		$dirname = \dirname($pathname);

		/* is this directory writeable */
		if (!is_writable($dirname)) {
			throw new \Exception($dirname . ' is not writable.');
		}

		/* create a temporary file with unique file name and prefix */
		$tmpfname = \tempnam($dirname, 'afpc_');

		/* did we get a temporary filename */
		if ($tmpfname === false) {
			throw new \Exception('Could not create temporary file ' . $tmpfname . '.');
		}

		/* write to the temporary file */
		$bytes = \file_put_contents($tmpfname, $content);

		/* did we write anything? */
		if ($bytes === false) {
			throw new \Exception('No bytes written by file_put_contents');
		}

		/* changes file permissions so php user can read/write and everyone else read */
		if (\chmod($tmpfname, 0644) === false) {
			throw new \Exception('Could not chmod temporary file ' . $tmpfname . '.');
		}

		/* move it into place - this is the atomic function */
		if (\rename($tmpfname, $pathname) === false) {
			throw new \Exception('Could not rename temporary file ' . $tmpfname . ' ' . $pathname . '.');
		}

		/* if it's cached we need to flush it out so the old one isn't loaded */
		self::remove_php_file_from_opcache($pathname);

		/* return the number of bytes written */
		return $bytes;
	}

	/**
	 * New
	 *
	 * var_export — Outputs or returns a parsable string PHP representation of a variable
	 *
	 * @param mixed $data
	 * @return string
	 * @throws string
	 */

	static public function var_export_php($data): string
	{
		if (\is_array($data) || \is_object($data)) {
			$string = '<?php return ' . \str_replace(['Closure::__set_state', 'stdClass::__set_state'], '(object)', \var_export($data, true)) . ';';
		} elseif (\is_scalar($data)) {
			$string = '<?php return "' . \str_replace('"', '\"', $data) . '";';
		} else {
			throw new \Exception('Unknown data type.');
		}

		return $string;
	}

	static public function var_export_file(string $pathname, $data): int
	{
		return self::atomic_file_put_contents($pathname, self::var_export_php($data));
	}
} /* end class */
