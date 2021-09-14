<?php
namespace App\Rpc\Client;


use Swoft\Rpc\Client\Contract\ProviderInterface;

class Client extends \swoft\Rpc\Client\Client{

    protected  $serviceName; //服务名称

    public function getProvider(): ?ProviderInterface   {

        //切换成curl发生在服务启动之前
        //$config = bean('config')->get('provider.consul');
        //bean('consulProvider')->registerServer($config);
        //不能区分当前调用的服务是哪个
        return $this->provider=new Provider($this->getServiceName());
    }
    /*
     * 获取服务名称
     */
    public  function  getServiceName(){
        return $this->serviceName;
    }
}
