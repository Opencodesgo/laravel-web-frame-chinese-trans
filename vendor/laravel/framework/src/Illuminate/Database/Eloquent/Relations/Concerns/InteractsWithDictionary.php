<?php
/**
 * Illuminate，数据库，Eloquent，关系，问题，与字典交互
 */

namespace Illuminate\Database\Eloquent\Relations\Concerns;

use BackedEnum;
use Doctrine\Instantiator\Exception\InvalidArgumentException;
use UnitEnum;

trait InteractsWithDictionary
{
    /**
     * Get a dictionary key attribute - casting it to a string if necessary.
	 * 获取一个字典键属性——必要时将其转换为字符串
     *
     * @param  mixed  $attribute
     * @return mixed
     *
     * @throws \Doctrine\Instantiator\Exception\InvalidArgumentException
     */
    protected function getDictionaryKey($attribute)
    {
        if (is_object($attribute)) {
            if (method_exists($attribute, '__toString')) {
                return $attribute->__toString();
            }

            if (function_exists('enum_exists') &&
                $attribute instanceof UnitEnum) {
                return $attribute instanceof BackedEnum ? $attribute->value : $attribute->name;
            }

            throw new InvalidArgumentException('Model attribute value is an object but does not have a __toString method.');
        }

        return $attribute;
    }
}
