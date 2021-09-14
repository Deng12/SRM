<?php declare(strict_types=1);

namespace App\Model\Entity;

use Swoft\Db\Annotation\Mapping\Column;
use Swoft\Db\Annotation\Mapping\Entity;
use Swoft\Db\Annotation\Mapping\Id;
use Swoft\Db\Eloquent\Model;

/**
 *
 * Class Demo
 *
 * @since 2.0
 *
 * @Entity(table="demo",pool="db2.pool")  //定义Model,参数是对应的表和连接池(选填)
 */
class Demo extends Model
{
    /**
     *默认自动添加 created_at 和 updated_at,不需要时设置为false
     * @var bool
     */
    public $modelTimestamps = false;

    /**
     *
     * @Id(incrementing=false)
     * @Column(name="id")    //定义列
     *
     * @var int
     */
    private $id;

    /**
     * @Column(name="name")
     *
     * @var string|null
     */
    private $name;

    /**
     * @param int $id
     *
     * @return void
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @param string|null $name
     *
     * @return void
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }
}
