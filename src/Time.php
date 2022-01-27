<?php

namespace app\object;

class Time
{
    protected static $instance;

    protected $startTime = 0;
    protected $middleTime = 0;
    protected $endTime = 0;

    protected $times = [];

    public static function getInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function initialize()
    {
        $this->startTime = 0;
        $this->middleTime = 0;
        $this->endTime = 0;
        $this->times = [];
    }

    public static function start($dump = false)
    {
        //初始化
        $time = self::getInstance();
        $time->initialize();

        $timestamp = $time->getTime();
        $time->times[] = $timestamp;
        if ($dump) {
            dump($timestamp);
        }

        return $timestamp;
    }

    public static function add($dump = true)
    {
        $time = self::getInstance();
        $timestamp = $time->getTime();
        $time->times[] = $timestamp;
        $diff_time = end($time->times) - prev($time->times);
        if ($dump) {
            dump($diff_time);
        }

        return $diff_time;
    }

    public static function end($dump = true)
    {
        $time = self::getInstance();
        $timestamp = $time->getTime();
        $time->times[] = $timestamp;
        $diff_time = end($time->times) - reset($time->times);
        if ($dump) {
            dump('总耗时'.$diff_time);
        }

        return $diff_time;
    }

    /******************************************************************************************************. */

    /**
     * 获取时间(包含毫秒).
     *
     * @param bool $only_ms 是否只返回毫秒
     */
    protected function getTime(bool $only_ms = false)
    {
        //获取秒和毫秒
        [$ms, $s] = explode(' ', microtime());
        //小数毫秒转为3位整数
        $int_ms = round($ms * 1000);
        //不足三位则
        $ms = $int_ms < 100 ? '0'.$int_ms : $int_ms;
        if ($only_ms) {
            $res = $ms;
        } else {
            $res = (int) ($s.$ms);
        }

        return $res;
    }

    /**
     * 使$value保持在$num位数.
     *
     * @param int|string $value 值
     * @param int        $num   位数
     */
    protected function formatNum($value, int $num)
    {
        if ((is_string($value) && strlen($value) < $num) || (is_integer($value) && $value < $this->getMinNum($num))) {
            $value = '0'.$value;

            return $this->formatNum($value, $num);
        }

        return $value;
    }

    /**
     * 获取指定位数的最小值
     */
    protected function getMinNum(int $num)
    {
        $min = '1';
        for ($i = 1; $i < $num; ++$i) {
            $min .= '0';
        }

        return (int) $min;
    }
}
