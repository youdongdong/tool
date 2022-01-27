<?php

namespace tool\common;

use think\Model;
use think\helper\Str;

/**
 * 生成where查询条件
 */
class Maps
{
    /**
     * 生成的查询条件
     */
    protected $maps = [];
    /**
     * 请求参数
     */
    protected $input = [];

    protected static $instance;

    protected function __construct()
    {
    }

    /**
     * 初始化
     * 
     * @param array $input 请求参数
     */
    public static function getInstance(array $input = [])
    {
        if (!self::$instance instanceof self) {
            self::$instance = new static();
        }
        self::initialize($input);

        return self::$instance;
    }
    /**
     * 初始化
     */
    protected static function initialize($input)
    {
        $instance = self::$instance;
        $instance->input = $input;
        $instance->maps = [];

        return true;
    }

    /**
     * 请求参数里模糊查找
     * 
     * @param int|string|array $data  key
     * @param int|string $value  value
     */
    public function like($data, $value = null)
    {
        //参数转数组
        $array = $this->getArray($data, $value);
        //循环处理
        foreach ($array as $k => $v) {
            //如果存在且有值
            if (isset($this->input[$v]) && !empty($this->input[$v])) {
                $map = [$k, 'like', "%" . $this->input[$v] . "%"];
                $this->add($map);
            }
        }

        return $this;
    }
    /**
     * 直接模糊查找
     * 
     * @param int|string|array $data  key
     * @param int|string $value  value
     */
    public function execLike($data, $value = null)
    {
        $array = $this->getArray($data, $value);

        foreach ($array as $k => $v) {
            $map = [$k, 'like', "%" . $v . "%"];
            $this->add($map);
        }

        return $this;
    }
    /**
     * 多个模糊查找
     * 
     * @param int|string|array $data  key
     * @param int|string $value  value
     */
    public function eachLike($data, $value)
    {
        if (isset($this->input[$value]) && !empty($this->input[$value])) {
            foreach ($data as $field) {
                $map = [$field, 'like', "%" . $this->input[$value] . "%"];
                $this->add($map);
            }
        }

        return $this;
    }

    /**
     * 等于
     */
    public function equal($data, $value = null)
    {
        $array = $this->getArray($data, $value);
        foreach ($array as $k => $v) {
            if (isset($this->input[$v]) && ((is_string($this->input[$v]) && !empty($this->input[$v]))
                ||
                (is_numeric($this->input[$v]) && $this->input[$v] >= 0))) {
                $map = [$k, '=', $this->input[$v]];
                $this->add($map);
            }
        }

        return $this;
    }
    /**
     * 直接等于
     */
    public function execEqual($data, $value = null)
    {
        $array = $this->getArray($data, $value);

        foreach ($array as $k => $v) {
            $map = [$k, '=', $v];
            $this->add($map);
        }

        return $this;
    }


    /**
     * 之间
     * 
     * @param bool $timestamp  是否转为时间戳
     */
    public function between($data, array $value = [], bool $timestamp = false)
    {

        $array = $this->getArray($data, $value);

        foreach ($array as $k => $v) {
            if (empty($v) || $k == $v) {
                $v = ['start_time', 'end_time'];
            }
            if (
                isset($this->input[$v[0]]) &&
                ((is_string($this->input[$v[0]]) && !empty($this->input[$v[0]])) || (is_numeric($this->input[$v[0]]) && $this->input[$v[0]] >= 0))
                &&
                isset($this->input[$v[1]]) && !empty($this->input[$v[1]])
            ) {
                $values = [$this->input[$v[0]], $this->input[$v[1]]];
                $map = [$k, 'between', $timestamp ? $this->arrStrToTime($values) : $values];
                $this->add($map);
            }
        }

        return $this;
    }
    /**
     * exec之间
     */
    public function execBetween($data, array $value = [], bool $timestamp = false)
    {
        $array = $this->getArray($data, $value);

        foreach ($array as $k => $v) {
            $map = [$k, 'between', $timestamp ? $this->arrStrToTime($v) : $v];
            $this->add($map);
        }

        return $this;
    }
    /**
     * 大于或大于等于
     */
    public function gt($data,  $value = null, bool $egt = false)
    {
        $array = $this->getArray($data, $value);

        foreach ($array as $k => $v) {
            if (isset($this->input[$v]) && !empty($this->input[$v])) {
                $map = [$k, $egt ? '>=' : '>', $this->input[$v]];
                $this->add($map);
            }
        }

        return $this;
    }
    /**
     * exec大于或大于等于
     */
    public function execGt($data,  $value = null, bool $egt = false)
    {
        $array = $this->getArray($data, $value);

        foreach ($array as $k => $v) {
            $map = [$k, $egt ? '>=' : '>', $v];
            $this->add($map);
        }

        return $this;
    }
    /**
     * 小于或小于等于
     */
    public function lt($data,  $value = null, bool $elt = false)
    {
        $array = $this->getArray($data, $value);

        foreach ($array as $k => $v) {
            if (isset($this->input[$v]) && !empty($this->input[$v])) {
                $map = [$k, $elt ? '<=' : '<', $this->input[$v]];
                $this->add($map);
            }
        }

        return $this;
    }
    /**
     * exec小于或小于等于
     */
    public function execLt($data,  $value = null, bool $elt = false)
    {
        $array = $this->getArray($data, $value);

        foreach ($array as $k => $v) {
            $map = [$k, $elt ? '<=' : '<', $v];
            $this->add($map);
        }

        return $this;
    }
    /**
     * in
     * 
     * @param string|array $data
     * @param array $value
     */
    public function in($data, array  $value = null)
    {
        $array = $this->getArray($data, $value);

        foreach ($array as $k => $v) {
            if (isset($this->input[$v]) && !empty($this->input[$v])) {
                if (!is_array($this->input[$v])) {
                    continue;
                }
                $map = [$k, 'in', $this->input[$v]];
                $this->add($map);
            }
        }

        return $this;
    }
    /**
     * 直接in
     */
    public function execIn($data, array $value = [])
    {
        $array = $this->getArray($data, $value);

        foreach ($array as $k => $v) {
            if (empty($v)) {
                continue;
            }
            $map = [$k, 'in', $v];
            $this->add($map);
        }

        return $this;
    }

    /**
     * 联表的模糊查询条件
     * 
     * @param string $field 当前表的字段
     * @param string $key 对应表的字段
     * @param string $value 请求对应的参数
     * @param Model $model 对应表的model
     * @param string $column 对应表的取值
     * 
     * @return self
     */
    public function joinIn(string $field, string $key, string $value, string $model, string $column = 'id')
    {
        $array = [];
        if (strpos($model, '\\') === false) {
            $model = "\app\model\\" . ucfirst(Str::camel($model));
        }
        if (isset($this->input[$value]) && !empty($this->input[$value])) {
            $array =  $model::where($key, 'like', "%" . $this->input[$value] . "%")->column($column);
        }
        if (!empty($array)) {
            $map = [$field, 'in', $array];
            $this->add($map);
        }

        return $this;
    }

    /**
     * 返回生成的查询条件
     * 
     * @return array 
     */
    public function get(): array
    {
        return $this->maps;
    }


    /******************************内部方法*********************************/

    /**
     * 添加到条件数组里
     */
    public function add(array $map)
    {
        if (!in_array($map, $this->maps)) {
            $this->maps[] = $map;
        }

        return true;
    }

    /**
     * 转数组
     */
    /* protected function getArr($key, $value = null)
    {
        $data = [];
        if (is_array($key)) {
            $data = $key;
        } else {
            if ($value === null) {
                $value = $key;
            }
            $data[$key] = $value;
        }

        return $data;
    } */
    /**
     * 转数组
     * 
     * @param array|string $data
     * @param string $value
     */
    protected function getArray($data, $value = null)
    {
        $array = [];
        if (is_array($data)) { //数组处理
            foreach ($data as $k => $v) {
                if (is_numeric($k)) {
                    $k  = $v;
                }
                $array[$k] = $v;
            }
        } else { //不是数组则转数组
            if ($value === null) {
                $value = $data;
            }
            $array[$data] = $value;
        }

        return $array;
    }
    /**
     * 数组元素转时间戳
     * 
     * @param array $array  一维数组
     */
    protected function arrStrToTime(array $array)
    {
        $data = [];
        foreach ($array as $k => $v) {
            $data[] = strtotime($v);
        }

        return $data;
    }
}