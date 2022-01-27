<?php

namespace app\object;

class Image
{
    protected $ext = 'png';

    public function __construct()
    {
    }

    /**
     * 创建图片缩略图.
     *
     * @param string $img_path  原图路径
     * @param string $save_path 新图片保存路径
     * @param string $percent   缩放比例,百分比整数
     * @param string $ext       后缀
     */
    public function createThumbnail(string $img_path, string $save_path, int $percent = 10, string $ext = null)
    {
        try {
            //原图片信息
            $img_info = $this->getImgInfo($img_path);
            
            //生成空白指定大小的缩略图文件
            $new_width = $img_info['width'] * $percent / 100;
            $new_height = $img_info['height'] * $percent / 100;
            $new_img = imagecreatetruecolor($new_width, $new_height);
            
            //复制原图到生成的新图中
            imagecopyresampled($new_img, $img_info['img'], 0, 0, 0, 0, $new_width, $new_height, $img_info['width'], $img_info['height']);

            //保存图片
            $save_path = $this->getSavePath($img_path, $save_path, $ext);
            $fun_name = $this->getFuntionNameOfSave($img_info['ext']);
            $fun_name($new_img, $save_path);
            //释放内存
            imagedestroy($new_img);
            imagedestroy($img_info['img']);
        } catch (\Throwable $th) {
            throw $th;
        }

        return $save_path;
    }

    /***********************************************************************************************. */

    /**
     * 获取保存地址
     *
     * @param string $img_path  原图片地址
     * @param string $save_path 新图保存地址
     * @param string $ext       后缀
     */
    private function getSavePath(string $img_path, string $save_path, string $ext = null)
    {
        if (mb_substr($save_path, -1, 1) == '/') {
            $path_info = pathinfo($img_path);
            $ext = $ext ?: $path_info['extension'];

            return $save_path.md5($path_info['filename']).'.'.$ext;
        }

        return $save_path;
    }

    /**
     * 获取指定路径图片信息.
     */
    private function getImgInfo(string $img_path)
    {
        $data = [];
        //获取图片大小
        $info = getimagesize($img_path);
        //图片后缀
        $ext = image_type_to_extension($info[2], false);
        //创建新图像
        $fun_name = $this->getFuntionNameOfCreate($ext);
        $img_src = $fun_name($img_path);

        $data['width'] = $info[0];
        $data['height'] = $info[1];
        $data['ext'] = $ext;
        $data['img'] = $img_src;

        return $data;
    }

    /**
     * 根据后缀名获取创建新图像的函数名.
     */
    private function getFuntionNameOfCreate(string $ext)
    {
        return 'imagecreatefrom'.$ext;
    }

    /**
     * 根据后缀名获取保存新图像的函数名.
     */
    private function getFuntionNameOfSave(string $ext)
    {
        return 'image'.$ext;
    }
}
