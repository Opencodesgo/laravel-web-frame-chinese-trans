<?php
/**
 * TijsVerkoyen，CssToInlineStyles，Css，属性，Property
 */

namespace TijsVerkoyen\CssToInlineStyles\Css\Property;

use Symfony\Component\CssSelector\Node\Specificity;

final class Property
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $value;

    /**
     * @var Specificity|null
     */
    private $originalSpecificity;

    /**
     * Property constructor.
     * @param string           $name
     * @param string           $value
     * @param Specificity|null $specificity
     */
    public function __construct($name, $value, ?Specificity $specificity = null)
    {
        $this->name = $name;
        $this->value = $value;
        $this->originalSpecificity = $specificity;
    }

    /**
     * Get name
	 * 获取名字
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get value
	 * 获取值
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get originalSpecificity
	 * 得到originalSpecificity
     *
     * @return Specificity|null
     */
    public function getOriginalSpecificity()
    {
        return $this->originalSpecificity;
    }

    /**
     * Is this property important?
	 * 这个属性重要吗？
     *
     * @return bool
     */
    public function isImportant()
    {
        return (stripos($this->value, '!important') !== false);
    }

    /**
     * Get the textual representation of the property
	 * 获取属性的文本表示形式
     *
     * @return string
     */
    public function toString()
    {
        return sprintf(
            '%1$s: %2$s;',
            $this->name,
            $this->value
        );
    }
}
