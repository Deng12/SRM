<?php

namespace App\Components\Order;

use App\Components\Database;

class DiffStrutsDb
{
    private $mod = 3;

    public function insert($array){

        //获得数据库配置
        $config = bean('config')->get('database');

        //订单号规则介绍
        $order_no = time().'-用户id'.'-商店id'.'-6位随机数';

        //这里写死订单后5位数进行测试
        $order_no_last5 = 66666;
        $order_no_last4 = 6666;

        /*找库*/
        $db_no = $order_no_last4%$this->mod; //0,1,2
        //算出库名
        $db_name = "order_main_".$db_no;

        /*获得库的配置*/
        $db_config =  $config['order_main'][$db_name];

        /*找表*/
        $table_no = ($order_no_last5%$this->mod);
        //算出表名
        $table_name = "order_".$table_no;

        /*连接数据库*/
        $pdo = new \App\Components\Database\SimplePdo(
            $host=$db_config['host'],
            $user=$db_config['user'],
            $pass=$db_config['pass'],
            $dbname=$db_config['dbname'],
            $no_persistent=false
        );

        /*插入数据到表中*/
        $array['table'] = $table_name;
        $result = $pdo->insert($array);


        return $result;
    }
}
