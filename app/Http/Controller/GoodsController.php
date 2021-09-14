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

//use app\Request;
use App\Rpc\Lib\GoodsInterface;
use Exception;
use Swoft\Co;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
use Swoft\Rpc\Client\Annotation\Mapping\Reference;
use Swoft\Http\Message\Request;

/**
 * Class RpcController
 *
 * @since 2.0
 *
 * @Controller()
 */
class GoodsController
{
    /**
     * @Reference(pool="goods.pool")
     *
     * @var GoodsInterface
     */
    private $goodsService;

    /**
     * @RequestMapping("addGoods")
     *
     * @return array
     */
    public function addGoods(Request $request): array
    {
        $data = $request->input();

        $result  = $this->goodsService->addGoods($data);

        return [$result];
    }

    /**
     * @RequestMapping("searchEsGoods")
     *
     * @return array
     */
    public function searchEsGoods(Request $request): array
    {
        $data = $request->input();

        $result  = $this->goodsService->searchEsGoods($data);

        return [$result];
    }

    /**
     * @RequestMapping("getDetail")
     *
     * @return array
     */
    public function getDetail(Request $request): array
    {
        $data = $request->input();
        print_r($data);
        $result  = $this->goodsService->getDetail(intval($data['sku_id']));

        return [$result];
    }

    /**
     * @RequestMapping("getList")
     *
     * @return array
     */
    public function getList(): array
    {
        $result  = $this->goodsService->getList(12, 'type');

        return [$result];
    }

    /**
     * @RequestMapping("returnBool")
     *
     * @return array
     */
    public function returnBool(): array
    {
        $result = $this->goodsService->delete(12);

        if (is_bool($result)) {
            return ['bool'];
        }

        return ['notBool'];
    }

    /**
     * @RequestMapping()
     *
     * @return array
     */
    public function bigString(): array
    {
        $string = $this->goodsService->getBigContent();

        return ['string', strlen($string)];
    }

    /**
     * @RequestMapping()
     *
     * @return array
     */
    public function sendBigString(): array
    {
        $content = Co::readFile(__DIR__ . '/../../Rpc/Service/big.data');

        $len    = strlen($content);
        $result = $this->goodsService->sendBigContent($content);
        return [$len, $result];
    }

    /**
     * @RequestMapping()
     *
     * @return array
     */
    public function returnNull(): array
    {
        $this->goodsService->returnNull();
        return [null];
    }

    /**
     * @RequestMapping()
     *
     * @return array
     *
     * @throws Exception
     */
    public function exception(): array
    {
        $this->goodsService->exception();

        return ['exception'];
    }
}
