<?php

namespace Applications\Services;

use EasyWeChat\Factory;
use EasyWeChat\MiniProgram\Application;

class MiniProgram
{
    public static function getConfig()
    {
        return require_once __DIR__.'/config.php';
    }

    public static function app(): Application
    {
        return Factory::miniProgram(self::getConfig());
    }

    public static function make()
    {
        return new static();
    }

    public function code2Session(string $code)
    {
        $sessionRes = MiniProgram::app()->auth->session($code);
        MiniProgram::checkResponse($sessionRes);
        return $sessionRes;
    }

    public static function checkResponse(array $res)
    {
        if (!empty($result['errcode'])) {
            throw new \RuntimeException($result['errmsg'], $result['errcode']);
        }
    }
}