<?php

namespace app\object;

use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;

/**
 * 视频.
 */
class Video
{
    protected $imgExt = 'jpg';

    public function __construct()
    {
    }

    /**
     * 获取视频指定秒的图像.
     */
    public function getImage(string $video_path, string $save_path, int $seconds = 3, string $ext = null)
    {
        try {
            //创建初始化
            $ffmpeg = FFMpeg::create();

            //打开视频
            $video = $ffmpeg->open($video_path);

            //获取帧
            $frame = $video->frame(TimeCode::fromSeconds($seconds));
            //保存帧
            $save_path = $this->getSavePath($video_path, $save_path, $ext);
            $frame->save($save_path);
        } catch (\Throwable $th) {
            throw $th;
        }

        return $save_path;
    }

    /***********************************************************************************************. */

    /**
     * 获取保存地址
     *
     * @param string $save_path 新保存地址
     * @param string $ext       后缀
     */
    private function getSavePath(string $video_path, string $save_path, string $ext = null)
    {
        if (mb_substr($save_path, -1, 1) == '/') {
            $path_info = pathinfo($video_path);
            $ext = $ext ?: $this->imgExt;

            return $save_path.md5($path_info['filename']).'.'.$ext;
        }

        return $save_path;
    }
}
