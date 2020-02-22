<?php
namespace ext4yii2\assets;

use yii\base\BaseObject;

abstract class Parser extends BaseObject
{
    /**
     * Parse a asset file.
     *
     * @param string $src source file path
     * @param string $dst destination file path
     * @param array $options parser options
     * @return mixed
     */
    abstract public function parse($src, $dst, $options);
}
