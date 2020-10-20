<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

use Applications\YourApp\Cmd;
use \GatewayWorker\Lib\Gateway;

/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{
    static public $eventHandler;

    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     * 
     * @param int $client_id 连接id
     */
    public static function onConnect($client_id)
    {
        Gateway::sendToCurrentClient(Cmd::make(Cmd::CONNECT, "connection successful")->toString());
    }

   /**
    * 当客户端发来消息时触发
    * @param int $client_id 连接id
    * @param mixed $message 具体消息
    */
   public static function onMessage($client_id, $message)
   {
       try {
           $cmd = Cmd::makeFromMessage($message, $client_id);

           if (is_null($cmd)) {
               Gateway::sendToCurrentClient(Cmd::makeMessage(Cmd::ERROR, "指令错误，未获取到指令信息 ".$message));
               return;
           }

           $method = "on".ucfirst($cmd->getCmd());

           if (! method_exists(self::$eventHandler, $method)) {
               Gateway::sendToCurrentClient(Cmd::makeMessage(Cmd::ERROR, "指令错误，未知指令 {$cmd->getCmd()}"));
               return;
           }

           self::$eventHandler->{$method}($cmd);
       } catch (\Throwable $e) {
           Gateway::sendToCurrentClient(Cmd::makeMessage(Cmd::ERROR, "出错啦 ".$e->getMessage()));
       }
   }
   
   /**
    * 当用户断开连接时触发
    * @param int $client_id 连接id
    */
   public static function onClose($client_id)
   {
       Gateway::sendToCurrentClient(Cmd::make(Cmd::CLOSE, "goodbye {$client_id}")->toString());
   }
}
