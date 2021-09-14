<?php

namespace App\Listener;

use Swoft\Event\Annotation\Mapping\Listener;
use Swoft\Event\EventHandlerInterface;
use Swoft\Event\EventInterface;
use Swoft\Server\ServerEvent;

/**
 * Class RegisterServer
 * @package App\Listener
 * @Listener(ServerEvent::BEFORE_START)
 */
class RegisterServer implements EventHandlerInterface
{
    public function handle(EventInterface $event): void
    {

        var_dump(bean('config')->get('provider.consul.order'));

        $config = bean('config')->get('provider.consul.order');
        bean('consulProvider')->registerServer($config);
    }
}
