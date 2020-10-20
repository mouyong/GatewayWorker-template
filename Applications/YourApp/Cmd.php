<?php

namespace Applications\YourApp;

use GatewayWorker\Lib\Gateway;

class Cmd
{
    const ERROR = 'error';
    const SUCCESS = 'success';

    const CONNECT = 'connect';
    const CLOSE = 'close';

    private $cmd;
    private $payload;
    private $client_id;

    public function __construct($cmd, $payload, $client_id = null)
    {
        $this->cmd = $cmd;
        $this->payload = $payload;
        $this->client_id =  $client_id;
    }

    public static function make($cmd, $payload, $client_id = null)
    {
        return new static($cmd, $payload, $client_id);
    }

    public static function makeFromMessage($message, $client_id = null)
    {
        $command = json_decode($message, true);


        if (empty($command)) {
            Gateway::sendToCurrentClient(static::makeMessage(Cmd::ERROR, "指令错误，未收到任何指令"));

            return null;
        }

        if (!isset($command['cmd'])) {
            Gateway::sendToCurrentClient(static::makeMessage(Cmd::ERROR, "请提供指令 mcd"));
        }

        return static::make($command['cmd'], $command['payload'], $client_id);
    }

    public static function makeMessage($cmd, $message)
    {
        $cmd = new Cmd($cmd, $message);

        return $cmd->toString();
    }

    public function getCmd()
    {
        return $this->cmd;
    }

    public function getPayload()
    {
        return $this->payload;
    }

    public function getClientId()
    {
        return $this->client_id;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        try {
            $payload = json_decode($this->payload, true);
        } catch (\Throwable $e) {
            $payload = $this->payload;
        }

        return json_encode([
            'cmd' => $this->cmd,
            'payload' => $payload,
        ], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    }

    public function toString()
    {
        return $this->__toString();
    }
}