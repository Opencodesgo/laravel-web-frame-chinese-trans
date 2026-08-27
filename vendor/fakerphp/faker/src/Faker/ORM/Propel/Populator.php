<?php
/**
 * Faker，ORM，推进，填充器
 */

namespace Faker\ORM\Propel;

/**
 * Service class for populating a database using the Propel ORM.
 * A Populator can populate several tables using ActiveRecord classes.
 * 使用Propel ORM填充数据库的服务类。
 */
class Populator
{
    protected $generator;
    protected $entities = [];
    protected $quantities = [];

    public function __construct(\Faker\Generator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * Add an order for the generation of $number records for $entity.
	 * 为$entity添加生成$number记录的订单。
     *
     * @param mixed $entity A Propel ActiveRecord classname, or a \Faker\ORM\Propel\EntityPopulator instance
     * @param int   $number The number of entities to populate
     */
    public function addEntity($entity, $number, $customColumnFormatters = [], $customModifiers = [])
    {
        if (!$entity instanceof \Faker\ORM\Propel\EntityPopulator) {
            $entity = new \Faker\ORM\Propel\EntityPopulator($entity);
        }
        $entity->setColumnFormatters($entity->guessColumnFormatters($this->generator));

        if ($customColumnFormatters) {
            $entity->mergeColumnFormattersWith($customColumnFormatters);
        }
        $entity->setModifiers($entity->guessModifiers($this->generator));

        if ($customModifiers) {
            $entity->mergeModifiersWith($customModifiers);
        }
        $class = $entity->getClass();
        $this->entities[$class] = $entity;
        $this->quantities[$class] = $number;
    }

    /**
     * Populate the database using all the Entity classes previously added.
	 * 使用前面添加的所有Entity类填充数据库
     *
     * @param PropelPDO $con A Propel connection object
     *
     * @return array A list of the inserted PKs
     */
    public function execute($con = null)
    {
        if (null === $con) {
            $con = $this->getConnection();
        }
        $isInstancePoolingEnabled = \Propel::isInstancePoolingEnabled();
        \Propel::disableInstancePooling();
        $insertedEntities = [];
        $con->beginTransaction();

        foreach ($this->quantities as $class => $number) {
            for ($i = 0; $i < $number; ++$i) {
                $insertedEntities[$class][] = $this->entities[$class]->execute($con, $insertedEntities);
            }
        }
        $con->commit();

        if ($isInstancePoolingEnabled) {
            \Propel::enableInstancePooling();
        }

        return $insertedEntities;
    }

    protected function getConnection()
    {
        // use the first connection available
        $class = key($this->entities);

        if (!$class) {
            throw new \RuntimeException('No class found from entities. Did you add entities to the Populator ?');
        }

        $peer = $class::PEER;

        return \Propel::getConnection($peer::DATABASE_NAME, \Propel::CONNECTION_WRITE);
    }
}
