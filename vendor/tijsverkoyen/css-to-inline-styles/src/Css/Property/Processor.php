<?php
/**
 * TijsVerkoyen，CssToInlineStyles，Css，属性，处理器
 */

namespace TijsVerkoyen\CssToInlineStyles\Css\Property;

use Symfony\Component\CssSelector\Node\Specificity;

class Processor
{
    /**
     * Split a string into separate properties
	 * 将字符串分割成单独的属性
     *
     * @param string $propertiesString
     *
     * @return string[]
     */
    public function splitIntoSeparateProperties($propertiesString)
    {
        $propertiesString = $this->cleanup($propertiesString);

        $properties = (array) explode(';', $propertiesString);
        $keysToRemove = array();
        $numberOfProperties = count($properties);

        for ($i = 0; $i < $numberOfProperties; $i++) {
            $properties[$i] = trim($properties[$i]);

            // if the new property begins with base64 it is part of the current property
			// 如果新属性以base64开头，则它是当前属性的一部分。
            if (isset($properties[$i + 1]) && strpos(trim($properties[$i + 1]), 'base64,') === 0) {
                $properties[$i] .= ';' . trim($properties[$i + 1]);
                $keysToRemove[] = $i + 1;
            }
        }

        if (!empty($keysToRemove)) {
            foreach ($keysToRemove as $key) {
                unset($properties[$key]);
            }
        }

        return array_values($properties);
    }

    /**
     * @param string $string
     *
     * @return string
     */
    private function cleanup($string)
    {
        $string = str_replace(array("\r", "\n"), '', $string);
        $string = str_replace(array("\t"), ' ', $string);
        $string = str_replace('"', '\'', $string);
        $string = preg_replace('|/\*.*?\*/|', '', $string) ?? $string;
        $string = preg_replace('/\s\s+/', ' ', $string) ?? $string;

        $string = trim($string);
        $string = rtrim($string, ';');

        return $string;
    }

    /**
     * Converts a property-string into an object
	 * 将属性字符串转换为对象
     *
     * @param string $property
     *
     * @return Property|null
     */
    public function convertToObject($property, ?Specificity $specificity = null)
    {
        if (strpos($property, ':') === false) {
            return null;
        }

        list($name, $value) = explode(':', $property, 2);

        $name = trim($name);
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        return new Property($name, $value, $specificity);
    }

    /**
     * Converts an array of property-strings into objects
	 * 将属性字符串的数组转换为对象
     *
     * @param string[] $properties
     *
     * @return Property[]
     */
    public function convertArrayToObjects(array $properties, ?Specificity $specificity = null)
    {
        $objects = array();

        foreach ($properties as $property) {
            $object = $this->convertToObject($property, $specificity);
            if ($object === null) {
                continue;
            }

            $objects[] = $object;
        }

        return $objects;
    }

    /**
     * Build the property-string for multiple properties
	 * 为多个属性构建属性字符串
     *
     * @param Property[] $properties
     *
     * @return string
     */
    public function buildPropertiesString(array $properties)
    {
        $chunks = array();

        foreach ($properties as $property) {
            $chunks[] = $property->toString();
        }

        return implode(' ', $chunks);
    }
}
