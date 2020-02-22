<?php
/**
 * @author: Rafael Neri
 */
namespace ext4yii2\assets\converter;

use Yii;
use yii\caching\FileCache;
use ext4yii2\assets\Parser;

class Less extends Parser
{
	public $auto = false;
	public $files = [];

	/**
	 * Parse a Less file to CSS
	 */
	public function parse($src, $dst, $options)
	{
		$this->auto = isset($options['auto']) ? $options['auto'] : $this->auto;

		try
		{
			if ($this->auto)
			{
				/* @var FileCache $cacheMgr */
				$cacheMgr = Yii::createObject('yii\caching\FileCache');
				$cacheMgr->init();
				$cacheId = 'less#' . $dst;
				$cache = $cacheMgr->get($cacheId);
				if ($cache === false || (@filemtime($dst) < @filemtime($src)))
				{
					$cache = $src;
				}

				$newCache = $this->cachedCompile($cache);

				if (!is_array($cache) || ($newCache["updated"] > $cache["updated"]))
				{
					$cacheMgr->set($cacheId, $newCache);
					file_put_contents($dst, $newCache['compiled']);
				}
			}
			else
			{
				$cache = $this->compileFile($src);
				file_put_contents($dst, $cache);
			}
		}
		catch (Exception $e)
		{
			throw new Exception(__CLASS__ . ': Failed to compile less file : ' . $e->getMessage() . '.');
		}
	}


	public function cachedCompile($in, $force = false) {
		// assume no root
		$root = null;

		if (is_string($in)) {
			$root = $in;
		} elseif (is_array($in) and isset($in['root'])) {
			if ($force or ! isset($in['files'])) {
				// If we are forcing a recompile or if for some reason the
				// structure does not contain any file information we should
				// specify the root to trigger a rebuild.
				$root = $in['root'];
			} elseif (isset($in['files']) and is_array($in['files'])) {
				foreach ($in['files'] as $fname => $ftime ) {
					if (!file_exists($fname) or filemtime($fname) > $ftime) {
						// One of the files we knew about previously has changed
						// so we should look at our incoming root again.
						$root = $in['root'];
						break;
					}
				}
			}
		} else {
			// TODO: Throw an exception? We got neither a string nor something
			// that looks like a compatible lessphp cache structure.
			return null;
		}

		if ($root !== null) {
			// If we have a root value which means we should rebuild.
			$out = array();
			$out['root'] = $root;
			$out['compiled'] = $this->compileFile($root);
			$out['files'] = $this->files;
			$out['updated'] = time();
			return $out;
		} else {
			// No changes, pass back the structure
			// we were given initially.
			return $in;
		}

	}

	public function compileFile($file)
	{
		$cache = '';

		if(\file_exists($file)) {
			$this->files[] = $file;

			$parser = new Less_Parser();
			$parser->parseFile($file, '');
			$cache = $parser->getCss();
		}

		return $cache;
	}
}