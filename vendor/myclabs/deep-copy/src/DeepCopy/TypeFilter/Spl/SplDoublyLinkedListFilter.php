<?php
/**
 * DeepCopy，类型过滤器，Spl，Spl 双链列表滤波器
 */

namespace DeepCopy\TypeFilter\Spl;

use Closure;
use DeepCopy\DeepCopy;
use DeepCopy\TypeFilter\TypeFilter;
use SplDoublyLinkedList;

/**
 * @final
 */
class SplDoublyLinkedListFilter implements TypeFilter
{
    private $copier;

    public function __construct(DeepCopy $copier)
    {
        $this->copier = $copier;
    }

    /**
     * {@inheritdoc}
     */
    public function apply($element)
    {
        $newElement = clone $element;

        $copy = $this->createCopyClosure();

        return $copy($newElement);
    }

    private function createCopyClosure()
    {
        $copier = $this->copier;

        $copy = function (SplDoublyLinkedList $list) use ($copier) {
            // Replace each element in the list with a deep copy of itself
			// 将列表中的每个元素替换为一个完整的副本
            for ($i = 1; $i <= $list->count(); $i++) {
                $copy = $copier->recursiveCopy($list->shift());

                $list->push($copy);
            }

            return $list;
        };

        return Closure::bind($copy, null, DeepCopy::class);
    }
}
