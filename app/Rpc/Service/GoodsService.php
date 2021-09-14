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

use App\Rpc\Lib\GoodsInterface;
use Exception;
use RuntimeException;
use Swoft\Co;
use Swoft\Rpc\Server\Annotation\Mapping\Service;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;
use Elasticsearch\ClientBuilder;

/**
 * Class GoodsService
 *
 * @since 2.0
 *
 * @Service()
 */
class GoodsService implements GoodsInterface
{

    /**
     * @param array   $data
     *
     * @return array
     */
    public function addGoods(array $data): array
    {

        $hosts = [
            '192.168.232.104:9200', //IP+端口
        ];
        $client = \Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();

        $params = [
            'index' => 'goods_list',
            'id'    => 124,
            'body'  => [
                'id'     => '124',
                'goods_name'      => '测试商品的名称',
                'goods_description'    => '测试商品的描述',
                'goods_info' => '...',
            ]
        ];

        $response = $client->index($params);

        return $response;
    }


    /**
     * @param array   $data
     *
     * @return array
     */
    public function searchEsGoods(array $data): array
    {

        $hosts = [
            '192.168.232.104:9200', //IP+端口
        ];
        $client = \Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();


        $params = [
            'index' => 'goods_list',
            'body'  => [
                'query' => [
                    'match' => [
                        'goods_name' => '测试'
                    ]
                ]
            ]
        ];

        $response = $client->search($params);
        print_r($response);

        return $response;
    }

    /**
     * @param int   $sku_id
     *
     * @return array
     */
    public function getDetail(int $sku_id): array
    {
        $manager = new \MongoDB\Driver\Manager("mongodb://localhost:27017");

        $filter = ['sku_id' => ['$eq' => $sku_id]];
        $options = [];

        // 查询数据
        $query = new \MongoDB\Driver\Query($filter, $options);
        $cursor = $manager->executeQuery('goods.detail', $query);

        $data = [];
        foreach ($cursor as $document) {
            print_r($document);
            $data = json_decode( json_encode( $document),true);
        }

        return $data;
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
