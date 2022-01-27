<?php

namespace tool\common;

use app\model\ChatContent as ModelChatContent;
use app\model\ChatRecord as ModelChatRecord;

/**
 * 聊天.
 */
class Chat
{
    //发送的信息
    protected $msg = '';
    //唯一用户id
    protected $user = 'a124562167465';
    //没有返回的回答
    protected $unKnow = ['我不知道怎么回答诶', '你再说一遍，我看我能不能听懂', '你能说明白点吗？', '对不起，不懂诶'];

    /**
     * 设置发送信息.
     */
    public function setMsg($msg)
    {
        $this->msg = $msg;
    }

    /**
     * 设置发送信息.
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * 获取回答.
     */
    public function getAnswer()
    {
        $msg = ModelChatContent::where('content', 'like', '%'.$this->msg.'%')->find();

        $answer = $msg ? $msg->answer_msg->content : $this->unKnow[mt_rand(0, count($this->unKnow) - 1)];

        $this->chatHistory($answer);

        return $answer;
    }

    /**
     * 获取图灵机器人回复.
     */
    public function tlchat()
    {
        $api = new \app\controller\index\Api(app());
        $res = $api->tl($this->msg, $this->user);
        $result = $res->getData();
        if ($result['code'] != 0) {
            $answer = $result['message'];
        } else {
            $answer = $res ? $res->getData()['data'][0]['reply'] : $this->unKnow[mt_rand(0, count($this->unKnow) - 1)];
        }

        $this->chatHistory($answer);

        return $answer;
    }

    /**
     * 保存聊天记录到数据库.
     */
    protected function chatHistory($answer)
    {
        try {
            $chat = new ModelChatRecord();
            $chat->saveAll([['content' => $this->msg], ['content' => $answer]]);
        } catch (\Throwable $e) {
            return json(make_return_arr(0, $e->getMessage()));
        }
    }
}
