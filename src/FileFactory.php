<?php

namespace tool;

use tool\file\Image;
use tool\file\Video;

class FileFactory
{
    /**图片文件 */
    const TYPE_IMAGE = 'image';
    /**视频文件 */
    const TYPE_VIDEO = 'video';

    protected $fileType;

    protected static $instance;

    public function __construct()
    {
    }

    public static function getInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * 创建图片或者视频缩略图.
     *
     * @param string $save_path 新图片保存路径
     * @param string $percent   缩放比例,百分比整数
     * @param string $ext       后缀
     */
    public static function createThumbnail(string $file_path, string $save_path, int $percent = 10, int $seconds = 3, string $ext = null)
    {
        try {
            $factory = self::getInstance();

            //获取文件类型
            $factory->checkFileType($file_path);

            //视频提取帧
            if ($factory->fileType == self::TYPE_VIDEO) {
                $video = new Video();
                $file_path = $video->getImage($file_path, $save_path, $seconds, $ext);
            }

            //压缩图片
            $image = new Image();
            $save_path = $image->createThumbnail($file_path, $save_path, $percent, $ext);
        } catch (\Throwable $th) {
            throw $th;
        }

        return $save_path;
    }

    /**************************************************************************. */

    /**
     * 检查文件类型.
     *
     * @param string $path 文件路径
     */
    public function checkFileType(string $path)
    {
        $mime_type = mime_content_type($path);
        if (strstr($mime_type, 'image/')) {
            $this->fileType = 'image';
        } elseif (strstr($mime_type, 'video/')) {
            $this->fileType = 'video';
        } else {
            throw new \Exception('暂不支持该类型文件');
        }

        return true;
    }
}
