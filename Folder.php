<?php

namespace app\object;

use app\model\Cloud as ModelCloud;

class Folder
{
    /**
     * 根目录.
     */
    protected $root = '';
    /**
     * 文件夹路径(除去根目录).
     */
    protected $path = '';
    /**
     * 文件夹路径数组(除去根目录).
     */
    protected $pathArr = [];
    /**
     * 文件夹名称.
     */
    private $name = '';
    /**
     * 类型.
     */
    protected $type = ModelCloud::TYPE_FOLDER;

    /**
     * 设置根目录.
     */
    public function setRoot(string $root)
    {
        $this->root = trim($root, '/');
        if (!is_dir($this->getRoot())) {
            mkdir($this->getRoot());
        }
    }

    /**
     * 设置文件夹路径(除去根目录).
     */
    protected function setPath(string $path)
    {
        $this->path = trim($path, '/');
        $this->pathArr = explode('/', $this->path);
        $this->name = end($this->pathArr);

        if (is_file($this->getCompletePath())) {
            $this->type = 'file';
        }
    }

    /**
     * 获取完整根目录.
     *
     * @param bool $origin 是否获取原始root
     */
    public function getRoot(bool $origin = false)
    {
        if ($origin) {
            return $this->root;
        } else {
            return str_replace('\\', '/', app()->getRootPath()).$this->root;
        }
    }

    /**
     * 获取完整文件夹路径.
     */
    public function getCompletePath()
    {
        return $this->getRoot().'/'.$this->path;
    }

    /**
     * 创建文件夹、
     *
     * @param string $path 去掉根目录的路径
     */
    public function create(string $path)
    {
        try {
            //路径转数组
            $this->setPath($path);

            $tmp_path = $this->getRoot();
            foreach ($this->pathArr as $k => $v) {
                $tmp_path .= '/'.$v;
                //不存在则新建
                if (!is_dir($tmp_path)) {
                    mkdir($tmp_path);
                }
            }
        } catch (\Throwable $th) {
            throw $th;
        }

        return true;
    }

    /**
     * 重命名文件夹.
     *
     * @param string $path     文件夹路径
     * @param string $new_name 新名称
     */
    public function rename(string $path, string $new_name)
    {
        try {
            //完整路径
            $this->setPath($path);
            $old_path = $this->getCompletePath();

            //新路径
            $new_path = str_replace($this->name, $new_name, $old_path);

            //重命名
            rename($old_path, $new_path);
            $this->name = $new_name;
        } catch (\Throwable $th) {
            throw $th;
        }

        return true;
    }

    /**
     * 删除文件夹.
     */
    public function delete(string $path)
    {
        try {
            $this->setPath($path);
            $path = $this->getCompletePath();

            //区别文件夹与文件
            if ($this->type == ModelCloud::TYPE_FOLDER) {
                $this->deleteDir($path);
            } else {
                unlink($path);
            }
        } catch (\Throwable $th) {
            throw $th;
        }

        return true;
    }

    /*****************************************************************************************内部方法 */

    /**
     * 删除文件夹以及子集.
     *
     * @param string $path 文件夹完整路径
     */
    protected function deleteDir(string $path)
    {
        if (!is_dir($path)) {
            return false;
        }

        //扫描文件夹内的文件和文件夹
        $p = scandir($path);

        //元素小于等于二说明是空文件夹
        if (count($p) <= 2) {
            return rmdir($path);
        }

        //不是空文件夹则清空
        foreach ($p as $k => $v) {
            //排除目录中的.和..
            if ($v != '.' && $v != '..') {
                $tmp_path = $path.'/'.$v;

                //如果是目录则递归子目录，继续操作
                if (is_dir($tmp_path)) {
                    //子目录中操作删除文件夹和文件
                    $this->deleteDir($tmp_path);
                } else {
                    //如果是文件直接删除
                    unlink($tmp_path);
                }
            }
        }

        rmdir($path);

        return true;
    }
}
