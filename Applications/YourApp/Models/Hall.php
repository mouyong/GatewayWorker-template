<?php

namespace Applications\YourApp\Models;

use Applications\Services\MiniProgram;
use Applications\YourApp\Cmd;
use GatewayWorker\Lib\Gateway;

class Hall
{
    protected $clients = [];

    public function addClient(Client $client)
    {
        if (empty($this->clients[$client->getClientId()])) {
            $this->clients[$client->getClientId()] = $client;
        }
    }

    public function onLogin(Cmd $cmd)
    {
        $payload = $cmd->getPayload();

        $app = MiniProgram::make();
//        $sessionRes = $app->code2Session($payload['code']);
        $sessionRes = [
            'session_key' => 'TSy8dwWbKgaSO8YbI3mXUQ==',
            'openid' => 'osc8CuGLHASSHiuUqgVEHS_BEGoA',
        ];

        // @see https://developers.weixin.qq.com/miniprogram/dev/api/open-api/user-info/UserInfo.html
        $decryptData = $app::app()->encryptor->decryptData($sessionRes['session_key'], $payload['iv'], $payload['encryptedData']);

        // 声明 client 信息
        $client = new Client();
        $client->setClientId($cmd->getClientId());
        $client->setPlayerId($decryptData['openId']);

        // 将 client 添加到大厅中
        $this->addClient($client);

        Gateway::sendToCurrentClient(Cmd::makeMessage(Cmd::LOGIN_RESP, "登录成功，您好 {$decryptData['nickName']}"));
    }
}