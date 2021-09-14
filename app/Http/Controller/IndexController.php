<?php declare(strict_types=1);
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://swoft.org/docs
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Http\Controller;

use App\Model\Data\GoodsData;
use Swoft;
use Swoft\Db\DB;
use Swoft\Http\Message\ContentType;
use Swoft\Http\Message\Response;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
use Swoft\Redis\Redis;
use Swoft\View\Renderer;
use Throwable;
use function bean;
use function context;
use App\Model\Logic\ConsulLogic;

use ReflectionException;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Bean\Exception\ContainerException;
use Swoft\Consul\Agent;
use Swoft\Consul\Catalog;
use Swoft\Consul\Exception\ClientException;
use Swoft\Consul\Exception\ServerException;
use Swoft\Consul\Health;
use Swoft\Consul\KV;
use Swoft\Consul\Session;

/**
 * Class IndexController
 * @package App\Http\Controller
 * @Controller(prefix="/index")
 */
class IndexController{

    /**
     * @RequestMapping("test")
     * @throws \Swoft\Exception\SwoftException
     */
    public function test(){
        $res = Context()->getResponse();

        $data = ['name'=>'helloword'];
        return $res->withData($data);
    }

    /**
     * @RequestMapping("index")
     * @throws \Swoft\Exception\SwoftException
     */
    public function index(){
        $res = Context()->getResponse();

        $data = ['name'=>'Swoft2.0.2222'];
        return $res->withData($data);
    }


    /**
     * @RequestMapping("testMysql")
     * @throws \Swoft\Exception\SwoftException
     */
    public function testMysql(){

        $result = DB::table('test')->limit(2)->offset(3)->get()->all();
        return $result;
    }


    /**
     * @RequestMapping("testRedis")
     * @throws \Swoft\Exception\SwoftException
     */
    public function testRedis(){

        $result = Redis::set("test_key","test_value");

        $keyVal = Redis::get("test_key");

        return [
            $result,
            $keyVal
        ];
    }

    /**
     * @RequestMapping("testMongo")
     * @throws \Swoft\Exception\SwoftException
     */
    public function testMongo(){
        $bulk = new \MongoDB\Driver\BulkWrite;
        $document = ['_id' => new \MongoDB\BSON\ObjectID, 'name' => 'hello'];

        $_id= $bulk->insert($document);

        var_dump($_id);

        $manager = new \MongoDB\Driver\Manager("mongodb://192.168.232.100:27017");
        $writeConcern = new \MongoDB\Driver\WriteConcern(\MongoDB\Driver\WriteConcern::MAJORITY, 1000);
        $result = $manager->executeBulkWrite('test.runoob', $bulk, $writeConcern);
    }

    /**
     * @RequestMapping("testConsul")
     * @throws \Swoft\Exception\SwoftException
     */
    public function testConsul(){
        $ConsulLogic = new ConsulLogic();
        $ConsulLogic->kv();
    }
    public function getService(){

    }


    /**
     * @RequestMapping("kv")
     * @throws \Swoft\Exception\SwoftException
     */
    public function kv(): void
    {
        $a = new KV();
        $value = 'value content';
        $a->put('/test/my/key', $value);

        $response = $a->get('/test/my/key');
        var_dump($response->getBody(), $response->getResult());
    }
}
