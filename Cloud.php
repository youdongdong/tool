<?php

namespace app\object;

use app\model\Cloud as ModelCloud;

/**
 * 云盘
 */
class Cloud
{

    /**
     * 云盘模型
     */
    protected $model;

    /**
     * 构造方法
     */
    public function __construct(ModelCloud $cloud)
    {
        $this->model = $cloud;
    }

    /**
     * 获取模型数据
     */
    public function __get($name)
    {
        return $this->model->$name;
    }

    /**
     * 删除当前以及子集
     */
    public function delete()
    {
        try {
            //删除当前
            $this->model->delete();
            //删除子集
            $maps = [
                'userid' => $this->userid,
                'parentid' => $this->id,
            ];
            $children = ModelCloud::where($maps)->select();
            if($children->isEmpty()){
                return true;
            }
            //循环删除子集
            foreach ($children as $k => $cloud) {
                $this->model = $cloud;
                $this->delete();
            }
        } catch (\Throwable $th) {
            throw $th;
        }

        return true;
    }

}
