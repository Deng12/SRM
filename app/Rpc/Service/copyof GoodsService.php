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
require_once __DIR__ . '/../../../vendor/rabbitmq/config/config.php';
require_once __DIR__ . '/../../../vendor/rabbitmq/vendor/autoload.php';

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
     * @param int   $id
     * @param mixed $type
     * @param int   $count
     *
     * @return array
     */
    public function getList(int $id, $type, int $count = 10): array
    {
        $connection = new AMQPStreamConnection(HOST, PORT, USER, PASS, VHOST);
        $channel = $connection->channel();

        print_r($connection);
        print_r($channel);

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
