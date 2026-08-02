<?php
/**
 * Faker，ORM，Mandango，普及者
 */

namespace Faker\ORM\Mandango;

use Mandango\Mandango;

/**
 * Service class for populating a database using Mandango.
 * A Populator can populate several tables using ActiveRecord classes.
 * 使用Mandango填充数据库的服务类。
 */
class Populator
{
    protected $generator;
    protected $mandango;
    protected $entities = [];
    protected $quantities = [];

    public function __construct(\Faker\Generator $generator, Mandango $mandango)
    {
        $this->generator = $generator;
        $this->mandango = $mandango;
    }

    /**
     * Add an order for the generation of $number records for $entity.
	 * 为$实体的生成值的生成添加一个订单
     *
     * @param mixed $entity A Propel ActiveRecord classname, or a \Faker\ORM\Propel\EntityPopulator instance
     * @param int   $number The number of entities to populate
     */
    public function addEntity($entity, $number, $customColumnFormatters = [])
    {
        if (!$entity instanceof \Faker\ORM\Mandango\EntityPopulator) {
            $entity = new \Faker\ORM\Mandango\EntityPopulator($entity);
        }
        $entity->setColumnFormatters($entity->guessColumnFormatters($this->generator, $this->mandango));

        if ($customColumnFormatters) {
            $entity->mergeColumnFormattersWith($customColumnFormatters);
        }
        $class = $entity->getClass();
        $this->entities[$class] = $entity;
        $this->quantities[$class] = $number;
    }

    /**
     * Populate the database using all the Entity classes previously added.
	 * 使用之前添加的所有实体类填充数据库
     *
     * @return array A list of the inserted entities.
     */
    public function execute()
    {
        $insertedEntities = [];

        foreach ($this->quantities as $class => $number) {
            for ($i = 0; $i < $number; ++$i) {
                $insertedEntities[$class][] = $this->entities[$class]->execute($this->mandango, $insertedEntities);
            }
        }
        $this->mandango->flush();

        return $insertedEntities;
    }
}
