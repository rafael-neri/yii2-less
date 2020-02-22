<?php
/**
 * @author: Rafael Neri
 */

namespace ext4yii2\assets;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;


class Converter extends \yii\web\AssetConverter
{
	protected $defaultParsersOptions =
	[

		'less'				=>
		[
			'class'			=>	'ext4yii2\assets\converter\Less',
			'output'		=>	'css',
			'options'		=>
			[
				'auto'		=>	true
			]
		]
	];

	public $parsers = [];

	public $force = false; // Set to true of need always rebuild less files

	public function convert($asset, $basePath)
	{
		$this->parsers = ArrayHelper::merge($this->defaultParsersOptions, $this->parsers);

		$pos = strrpos($asset, '.');
		if ($pos === false){
			return parent::convert($asset, $basePath);
		}

		$ext = substr($asset, $pos + 1);

		if (!isset($this->parsers[$ext])) {
			return parent::convert($asset, $basePath);
		}

		$resultFile = FileHelper::normalizePath(DIRECTORY_SEPARATOR . substr($asset, 0, $pos + 1) . $this->parsers[$ext]['output']);

		$from = $basePath . DIRECTORY_SEPARATOR . $asset;
		$to = $basePath . $resultFile;

		if (!$this->needRecompile($from, $to)) {
			return trim($resultFile, DIRECTORY_SEPARATOR);
		}

		$this->checkDestinationDir($basePath, $resultFile);

		$asConsoleCommand = isset($this->parsers[$ext]['asConsoleCommand']) && $this->parsers[$ext]['asConsoleCommand'];
		if ($asConsoleCommand)
		{
			if (isset($this->commands[$ext]))
			{
				list ($distExt, $command) = $this->commands[$ext];
				$this->runCommand($command, $basePath, $asset, $resultFile);
			}
		}
		else
		{
			$class=$this->parsers[$ext]['class'];
			$parser = new $class($this->parsers[$ext]['options']);
			$parserOptions = isset($this->parsers[$ext]['options']) ? $this->parsers[$ext]['options'] : array();
			$parser->parse($from, $to, $parserOptions);
		}

		if (YII_DEBUG) {
			Yii::info("Converted $asset into $resultFile ", __CLASS__);
		}

		$resultFile = trim(FileHelper::normalizePath($resultFile, '/'), '/');

		return $resultFile;
	}

	public function needRecompile($from, $to)
	{
		return $this->force || (@filemtime($to) < filemtime($from));
	}

	public function checkDestinationDir($basePath, $file)
	{
		$distDir = dirname($basePath . $file);

		if (!is_dir($distDir)) {
			mkdir($distDir, 0777, true);
		}
	}
}