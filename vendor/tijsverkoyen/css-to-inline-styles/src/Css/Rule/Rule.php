<?php
/**
 * TijsVerkoyen，CssToInlineStyles，Css，规则，规则
 */

namespace TijsVerkoyen\CssToInlineStyles\Css\Rule;

use Symfony\Component\CssSelector\Node\Specificity;
use TijsVerkoyen\CssToInlineStyles\Css\Property\Property;

final class Rule
{
    /**
     * @var string
     */
    private $selector;

    /**
     * @var Property[]
     */
    private $properties;

    /**
     * @var Specificity
     */
    private $specificity;

    /**
     * @var integer
     */
    private $order;

    /**
     * Rule constructor.
	 * 规则构造方法
     *
     * @param string      $selector
     * @param Property[]  $properties
     * @param Specificity $specificity
     * @param int         $order
     */
    public function __construct($selector, array $properties, Specificity $specificity, $order)
    {
        $this->selector = $selector;
        $this->properties = $properties;
        $this->specificity = $specificity;
        $this->order = $order;
    }

    /**
     * Get selector
	 * 获取选择器
     *
     * @return string
     */
    public function getSelector()
    {
        return $this->selector;
    }

    /**
     * Get properties
	 * 获得属性
     *
     * @return Property[]
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Get specificity
	 * 获得特异性
     *
     * @return Specificity
     */
    public function getSpecificity()
    {
        return $this->specificity;
    }

    /**
     * Get order
	 * 获取排序
     *
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }
}
