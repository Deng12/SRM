<?php declare(strict_types=1);
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://swoft.org/docs
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Rpc\Service;

use App\Rpc\Lib\OrderInterface;
use Exception;
use RuntimeException;
use Swoft\Co;
use Swoft\Rpc\Server\Annotation\Mapping\Service;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class OrderService
 *
 * @since 2.0
 *
 * @Service()
 */
class OrderService implements OrderInterface
{
    /**
     * @param int   $id
     * @param mixed $type
     * @param int   $count
     *
     * @return array
     */
    public function createOrder(): array
    {

        date_default_timezone_set('PRC');

        $config = \Kafka\ProducerConfig::getInstance();
        $config->setMetadataRefreshIntervalMs(10000);
        $config->setMetadataBrokerList('192.168.232.204:9093');
        $config->setBrokerVersion('1.0.0');
        $config->setRequiredAck(1);
        $config->setIsAsyn(false);
        $config->setProduceInterval(500);
        $producer = new \Kafka\Producer();


        for($i = 0; $i < 1; $i++) {
            $result = $producer->send([
                [
                    'topic' => 'topicA',
                    'value' => 'topicA下面的第一条消息-消息222',
                    'key' => '',
                ],
            ]);
            var_dump($result);
        }

        return [];


        /*插入数据到订单表*/     /*实际场景中，这里要加事务*/
        $diff_struts_db = new \App\Components\Order\DiffStrutsDb();
        $insert_result = $diff_struts_db->insert(['data'=>['id'=>time()]]);


        /*
        消息推送到rabbitmq
        */
        $exchange = 'exchange_1';
        $queue = 'order_satistic_queue';

        //获得rabbitmq集群配置
        $config = bean('config')->get('rabbitmq.rabbitmq_1');

        //连接broker,创建一个rabbitmq连接
        $connection = new AMQPStreamConnection($config['host'], $config['port'], $config['login'], $config['password'], $config['vhost']);

        //创建一个通道
        $channel = $connection->channel();

        /*这个代码是rabbitmq高级特性：comfirm机制*/
        /*监听器*/
        //监听到推送成功就：
        $channel->set_ack_handler(
            function (AMQPMessage $message) {
                //update 订单表 set is_send_succ=ture
                echo "Message acked with content " . $message->body . PHP_EOL;

                /*这里省略以下逻辑（失败重试逻辑）：*/
                //把推送成功的记录起来。
                //插入到订单推送状态表，推送状态字段默认是0，推送成功才会把这个字段修改为1，推送失败这个字段的值就是0
                /*还要写一个脚本，去重试失败记录，也就是重试哪些推送状态字段的值是0的记录*/
            }
        );

        //监听到推送失败就：
        $channel->set_nack_handler(
            function (AMQPMessage $message) {
                //update 订单表 set is_send_succ=false
                echo "Message nacked with content " . $message->body . PHP_EOL;

                /*这里省略以下逻辑（失败重试逻辑）：*/
                //把推送失败的记录起来。
                //插入到订单推送状态表，推送状态字段默认是0，推送成功才会把这个字段修改为1，推送失败这个字段的值就是0
                /*还要写一个脚本，去重试失败记录，也就是重试哪些推送状态字段的值是0的记录*/
            }
        );

        //申明comfirm机制
        $channel->confirm_select();

        //申明队列
        $channel->queue_declare($queue, false, true, false, false);

        //申明交换机
        $channel->exchange_declare($exchange, AMQPExchangeType::FANOUT, false, false, true);
        //将交换机和队列绑定
        $channel->queue_bind($queue, $exchange);

        /*写死一条测试消息*/
        $messageBody = json_encode( [['sku_id'=>123,'num'=>2,'type'=>'crateOrder','order_no'=>'T3433335']]);

        /*把消息转化成rabbitmq消息格式*/
        $message = new AMQPMessage($messageBody, array('content_type' => 'text/plain', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));

        /*推送这条消息*/
        $channel->basic_publish($message, $exchange);

        $channel->wait_for_pending_acks(3);
        $channel->close();
        $connection->close();

        return [[$insert_result]];


        /*这里是之前（订单+商品）数据异构聚合的代码，暂时注释*/
        /*
        $exchange = 'router';
        $queue = 'qos_queue';

        //获得rabbitmq集群配置
        $config = bean('config')->get('rabbitmq.rabbitmq_1');

        //连接broker,创建一个rabbitmq连接
        $connection = new AMQPStreamConnection($config['host'], $config['port'], $config['login'], $config['password'], $config['vhost']);

        //在$connection连接中声明一个rabbitmq通道，消息基于该通道进行传输
        $channel = $connection->channel();

        //在通道中声明一个队列，队列名为$queue
        //参数介绍：
        //$queue = '', //队列名
        //$passive = false,如果用户仅仅想查询某一个队列是否已存在，如果不存在，不想建立该队列，仍然可以调用queue.declare，只不过需要将参数passive设为true，传给queue.declare，如果该队列已存在，则会返回true；如果不存在，则会返回Error，但是不会创建新的队列。
        //$durable = false,//是否持久化
        //$exclusive = false,//排他队列，如果一个队列被声明为排他队列，该队列仅对首次声明它的连接可见，并在连接断开时自动删除。这里需要注意三点：其一，排他队列是基于连接可见的，同一连接的不同信道是可以同时访问同一个连接创建的排他队列的。其二，“首次”，如果一个连接已经声明了一个排他队列，其他连接是不允许建立同名的排他队列的，这个与普通队列不同。其三，即使该队列是持久化的，一旦连接关闭或者客户端退出，该排他队列都会被自动删除的。这种队列适用于只限于一个客户端发送读取消息的应用场景。
        //$auto_delete = true,自动删除,和ack确认删除相反。
        $channel->queue_declare($queue, false, true, false, false);

        //声明一个交换机
        $channel->exchange_declare($exchange, AMQPExchangeType::DIRECT, false, true, false);

        //将交换机和队列绑定
        $channel->queue_bind($queue, $exchange);

        //sku_id商品名称
        //num:商品件数
        //type:类型
        //order_no：关联订单编号
        $messageBody = json_encode( [['sku_id'=>123,'num'=>2,'type'=>'crateOrder','order_no'=>'T3433335']]);

        $message = new AMQPMessage($messageBody, array('content_type' => 'text/plain', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));
        $channel->basic_publish($message, $exchange);

        $channel->close();
        $connection->close();

        return ['code' => 0,'msg'=>'create order success!'];*/
    }

    /**
     * @param int   $id
     * @param mixed $type
     * @param int   $count
     *
     * @return array
     */
    public function getList(int $id, $type, int $count = 10): array
    {

        //申明连接参数
        $config = [
            'host'=>'192.168.232.100',
            'vhost'=>'/',
            'port'=>5672,
            'login'=>'test',
            'password'=>'123456'
        ];

//连接broker,创建一个rabbitmq连接
        /*$cnn = new \AMQPConnection($config);*/
//$cnn = new \AMQPStreamConnection;
        $connection = new AMQPStreamConnection($config['host'], $config['port'], $config['login'], $config['password'], $config['vhost']);

        $channel = $connection->channel();

        $channel->queue_declare('qos_queue', false, true, false, false);

        $channel->basic_qos(null, 10000, null);



        $channel->basic_consume('qos_queue', '', false, false, false, false, function ($message)
        {
            echo 1111;
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
            echo 2222;
        });

/*
        while ($channel->is_consuming()) {
            // After 10 seconds there will be a timeout exception.
            $channel->wait(null, false, 10);
        }*/
        $channel->is_consuming();
        $channel->wait(null, false, 10);

        if(!$connection){
            echo "连接失败";
            exit();
        }else{
            //var_dump($connection);
        }

        return ['name' => ['list']];
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function delete(int $id): bool
    {
        return false;
    }

    /**
     * @return void
     */
    public function returnNull(): void
    {

        $exchange = 'router';
        $queue = 'qos_queue';

        //申明连接参数
        $config = [
            'host'=>'192.168.232.100',
            'vhost'=>'/',
            'port'=>5672,
            'login'=>'test',
            'password'=>'123456'
        ];

        //连接broker,创建一个rabbitmq连接
        /*$cnn = new \AMQPConnection($config);*/
        //$cnn = new \AMQPStreamConnection;
        $connection = new AMQPStreamConnection($config['host'], $config['port'], $config['login'], $config['password'], $config['vhost']);


        $channel = $connection->channel();

        /*
            The following code is the same both in the consumer and the producer.
            In this way we are sure we always have a queue to consume from and an
                exchange where to publish messages.
        */

        /*
            name: $queue
            passive: false
            durable: true // the queue will survive server restarts
            exclusive: false // the queue can be accessed in other channels
            auto_delete: false //the queue won't be deleted once the channel is closed.
        */
        $channel->queue_declare($queue, false, true, false, false);

        /*
            name: $exchange
            type: direct
            passive: false
            durable: true // the exchange will survive server restarts
            auto_delete: false //the exchange won't be deleted once the channel is closed.
        */

        $channel->exchange_declare($exchange, AMQPExchangeType::DIRECT, false, true, false);

        $channel->queue_bind($queue, $exchange);

        $messageBody = implode(' ', ['a'=>1,'b'=>2]);
        print_r($messageBody);
        $message = new AMQPMessage($messageBody, array('content_type' => 'text/plain', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));
        $channel->basic_publish($message, $exchange);

        $channel->close();
        $connection->close();
    }

    /**
     * @return string
     */
    public function getBigContent(): string
    {
        return Co::readFile(__DIR__ . '/big.data');
    }

    /**
     * Exception
     *
     * @throws Exception
     */
    public function exception(): void
    {
        throw new RuntimeException('exception version');
    }

    /**
     * @param string $content
     *
     * @return int
     */
    public function sendBigContent(string $content): int
    {
        return strlen($content);
    }
}
