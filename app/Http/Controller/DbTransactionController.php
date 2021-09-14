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

use App\Components\Database;
use App\Components\Order;

use App\Model\Entity\Count;
use App\Model\Entity\Test;
use App\Model\Entity\Usertest;
//use App\Model\Entity\User3;
use Swoft\Db\DB;
use Swoft\Db\Exception\DbException;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
use Throwable;
use function sgo;
use Swoft\Db\Query;

/**
 * Class DbTransactionController
 *
 * @since 2.0
 *
 * @Controller("dbTransaction")
 */
class DbTransactionController
{

    /**
     * @RequestMapping(route="testExec")
     *
     * @return false|string
     * @throws Throwable
     */
    public function testExec()
    {

        /*
        $a = new \App\Components\Order\DiffStrutsDb();
        $b = $a->insert(['data'=>['id'=>time()]]);
        return $b;*/


        /*往数据库中插入test表*/
        $bool = DB::unprepared(
            'CREATE TABLE `test` (
                  `id` bigint(20) NOT NULL AUTO_INCREMENT,
                  `goods_name` varchar(25) DEFAULT NULL,
                  `goods_price` int(11) DEFAULT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8'
        );

        return json_encode($bool);
    }

    /**
     * @RequestMapping(route="testInsert")
     *
     * @return false|string
     * @throws Throwable
     */
    public function testInsert(){

        $test = new Test();

        $test->setName('六星教育');

        $test->save();

        //保存获取 ID
        $userId = $test->getId();
        return json_encode($userId);
    }

    /**
     * @RequestMapping(route="testSelectMySql")
     *
     * @return false|string
     * @throws Throwable
     */
    public function testSelectMySql(){
        //D:\phpstudy_pro\Extensions\Apache2.4.39\bin\ab -n 50000 -c 1000 http://192.168.232.100:18306/dbTransaction/testSelectMySql
        $result = Test::where('id', 5)->first(['id', 'name']);

        return json_encode($result);
    }

    /**
     * @RequestMapping(route="testSelectMongodb")
     *
     * @return false|string
     * @throws Throwable
     */
    public function testSelectMongodb(){

        $manager = new \MongoDB\Driver\Manager("mongodb://localhost:27017");

        $filter = ['x' => ['$eq' => 3]];
        $options = [
            'projection' => ['_id' => 0],
            'sort' => ['x' => -1],
        ];

        // 查询数据
        $query = new \MongoDB\Driver\Query($filter, $options);
        $cursor = $manager->executeQuery('test.sites', $query);

        $return = [];
        foreach ($cursor as $document) {
            $return = $document;
        }

        return json_encode((array)$return);
    }


    /**
     * @RequestMapping(route="testSelect")
     *
     * @return false|string
     * @throws Throwable
     */
    public function testSelect(){
        $data = DB::table('test')
            ->orderBy('id', 'desc')
            ->get();
        return json_encode($data);
    }

    /**
     * @RequestMapping(route="insertTest")
     *
     * @return false|string
     * @throws Throwable
     */
    function insertTest(){
        /** @var bool */
        $ret = DB::table('users')->insert([
            ['email' => 'taylor@example.com', 'votes' => 0],
            ['email' => 'dayle@example.com', 'votes' => 0]
        ]);
        return json_encode($ret);
    }

    /**
     * @RequestMapping(route="ts")
     *
     * @return false|string
     * @throws Throwable
     */
    public function ts()
    {
        $id = $this->getId();

        DB::beginTransaction();
        $user = User::find($id);

        sgo(function () use ($id) {
            DB::beginTransaction();
            User::find($id);
        });

        return json_encode($user->toArray());
    }

    /**
     * @RequestMapping(route="cm")
     *
     * @return false|string
     * @throws Throwable
     */
    public function cm()
    {
        $id = $this->getId();

        DB::beginTransaction();
        $user = User::find($id);
        DB::commit();

        sgo(function () use ($id) {
            DB::beginTransaction();
            User::find($id);
            DB::commit();
        });

        return json_encode($user->toArray());
    }

    /**
     * @RequestMapping(route="rl")
     *
     * @return false|string
     * @throws Throwable
     */
    public function rl()
    {
        $id = $this->getId();

        DB::beginTransaction();
        $user = User::find($id);
        DB::rollBack();

        sgo(function () use ($id) {
            DB::beginTransaction();
            User::find($id);
            DB::rollBack();
        });

        return json_encode($user->toArray());
    }

    /**
     * @RequestMapping(route="ts2")
     *
     * @return false|string
     * @throws Throwable
     */
    public function ts2()
    {
        $id = $this->getId();

        DB::connection()->beginTransaction();
        $user = User::find($id);

        sgo(function () use ($id) {
            DB::connection()->beginTransaction();
            User::find($id);
        });

        return json_encode($user->toArray());
    }

    /**
     * @RequestMapping(route="cm2")
     *
     * @return false|string
     * @throws Throwable
     */
    public function cm2()
    {
        $id = $this->getId();

        DB::connection()->beginTransaction();
        $user = User::find($id);
        DB::connection()->commit();

        sgo(function () use ($id) {
            DB::connection()->beginTransaction();
            User::find($id);
            DB::connection()->commit();
        });

        return json_encode($user->toArray());
    }

    /**
     * @RequestMapping(route="rl2")
     *
     * @return false|string
     * @throws Throwable
     */
    public function rl2()
    {
        $id = $this->getId();

        DB::connection()->beginTransaction();
        $user = User::find($id);
        DB::connection()->rollBack();

        sgo(function () use ($id) {
            DB::connection()->beginTransaction();
            User::find($id);
            DB::connection()->rollBack();
        });

        return json_encode($user->toArray());
    }

    /**
     * @RequestMapping()
     * @return array
     * @throws DbException
     * @throws Throwable
     */
    public function multiPool()
    {
        DB::beginTransaction();

        // db3.pool
        $user = new User3();
        $user->setAge(random_int(1, 100));
        $user->setUserDesc('desc');

        $user->save();
        $uid3 = $user->getId();


        //db.pool
        $uid = $this->getId();

        $count = new Count();
        $count->setUserId(random_int(1, 100));
        $count->setAttributes('attr');
        $count->setCreateTime(time());

        $count->save();
        $cid = $count->getId();

        DB::rollBack();

        $u3 = User3::find($uid3)->toArray();
        $u  = User::find($uid);
        $c  = Count::find($cid);

        return [$u3, $u, $c];
    }

    /**
     * @return int
     * @throws Throwable
     */
    public function getId(): int
    {
        $user = new User();
        $user->setAge(random_int(1, 100));
        $user->setUserDesc('desc');

        $user->save();

        return $user->getId();
    }
}
