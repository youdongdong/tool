<?php

namespace tool\file;

use think\File as ThinkFile;

/**
 * 文件.
 */
class File extends Folder
{
    /**
     * 文件对象
     */
    protected $file;

    /**
     * 构造方法.
     */
    public function __construct(ThinkFile $file)
    {
        $this->file = $file;
    }

    /**
     * 调用文件对象方法.
     */
    public function __call($name, $param)
    {
        return $this->file->$name();
    }

    /**
     * 移动文件.
     *
     * @param string $path 移动到的路径
     */
    public function move(string $path)
    {
        try {
            //新的完整路径
            $path = $this->setPath($path);
            $new_path = $this->getCompletePath();

            //移动文件夹
            move_uploaded_file($this->getPathname(), $new_path);
        } catch (\Throwable $th) {
            throw $th;
        }

        return true;
    }
}
