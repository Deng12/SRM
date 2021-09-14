<?php
namespace App\Rpc\Client;

use Swoft\Rpc\Client\Client;
use App\Components\LoadBalance\RandLoadBalance;
use Swoft\Rpc\Client\Contract\ProviderInterface;

class Provider implements ProviderInterface
{
    /*
    protected $serviceName;

    public function __construct($serviceName)
    {
        $this->serviceName=$serviceName;
    }

    public function getList(Client $client): array
    {
        // TODO: Implement getList() method.
        return ['192.168.232.101:18307','192.168.232.101:18307'];
    }*/


    protected $serviceName;

    public function __construct($serviceName)    {
        $this->serviceName = $serviceName;
    }

    public function getList(Client $client): array
    {
        $config = bean('config')->get('provider.consul.order');
        $address = bean('consulProvider')->getServerList($this->serviceName, $config);
        //负载均衡(加权随机)
        $address = RandLoadBalance::select(array_values($address));
        if($address){
            $address = $address['address'];
        }
        if(!$address){
            die('no service!');
        }
        //根据服务名称consul当中获取动态地址
        print_r([$address]);
        return [$address];
    }
}
