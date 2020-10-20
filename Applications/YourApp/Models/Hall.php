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
        Gateway::sendToClient($cmd->getClientId(), "hello {$cmd->getClientId()}, you will go to hall");

        $client = new Client();
        $client->setClientId($cmd->getClientId());

        $payload = $cmd->getPayload();

        $client->setPlayerId($payload['userInfo']['nickName']);


        $this->addClient($client);

        $app = MiniProgram::make();

        $sessionRes = $app->auth->session($payload['code']);
        MiniProgram::checkResponse($sessionRes);

    }
}