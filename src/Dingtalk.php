<?php

namespace app\object;

use Exception;

class Dingtalk
{
    //临时授权码
    protected $code = '';
    //appkey
    protected $appKey = '';
    //appsecret
    protected $appSecret = '';
    //userid
    protected $userid = '';
    //钉钉客户端
    protected $dd = '';
    //获取access_token的baseurl
    protected $tokenUrl = 'https://oapi.dingtalk.com/gettoken';
    //获取userinfo的baseurl
    protected $userInfoUrl = 'https://oapi.dingtalk.com/user/getuserinfo';
    //获取user详细信息
    protected $userDetailUrl = 'https://oapi.dingtalk.com/topapi/v2/user/get';

    /**
     * 设置授权码
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    public function __construct()
    {
        $this->appKey = 'dingyloylffkvgzo1b2f';
        $this->appSecret = 'ZXLyBM7xXIMV8QDoRxc7xm-yi09CX0rmpXglNql5gNLB9U2W7UJrnd4wJVnorzpv';
        $this->dd = new \DingTalkClient(\DingTalkConstant::$CALL_TYPE_OAPI, \DingTalkConstant::$METHOD_GET, \DingTalkConstant::$FORMAT_JSON);
    }

    /**
     * 获取access_token.
     */
    public function getAccessToken()
    {
        $req = new \OapiGettokenRequest();
        $req->setAppkey($this->appKey);
        $req->setAppsecret($this->appSecret);
        $resp = $this->dd->execute($req, null, $this->tokenUrl);
        if ($resp->errcode != 0) {
            throw new Exception($resp->errmsg);
        }

        return $resp->access_token;
    }

    /**
     * 获取userid.
     */
    public function getUserInfo()
    {
        $req = new \OapiUserGetuserinfoRequest();
        $req->setCode($this->code);
        $resp = $this->dd->execute($req, $this->getAccessToken(), $this->userInfoUrl);
        $this->userid = isset($resp->userid) ?: '';
        if ($resp->errcode != 0) {
            throw new Exception($resp->errmsg);
        }

        return $resp;
    }

    /**
     * 获取user详细信息.
     */
    public function getUserDetail()
    {
        $userid = $this->userid ?: $this->getUserInfo()->userid;
        $req = new \OapiV2UserGetRequest();
        $req->setLanguage('zh-CN');
        $req->setUserid($userid);
        $resp = $this->dd->execute($req, $this->getAccessToken(), $this->userDetailUrl);
        if (!isset($resp->errcode) || $resp->errcode != 0) {
            throw new Exception(isset($resp->msg) ? $resp->msg : $resp->errmsg);
        }

        return $resp;
    }
}
