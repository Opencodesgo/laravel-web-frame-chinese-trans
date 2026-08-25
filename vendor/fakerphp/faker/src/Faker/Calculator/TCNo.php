<?php
/**
 * Faker，计算器，TC No
 */

namespace Faker\Calculator;

/**
 * @deprecated moved to tr_TR\Person, use {@link \Faker\Provider\tr_TR\Person}.
 * @see \Faker\Provider\tr_TR\Person
 */
class TCNo
{
    /**
     * Generates Turkish Identity Number Checksum
     * Gets first 9 digit as prefix and calculates checksum
	 * 生成土耳其身份证号码校验码，取前9位数字作为前缀并计算校验码。
     *
     * https://en.wikipedia.org/wiki/Turkish_Identification_Number
     *
     * @param string $identityPrefix
     *
     * @return string Checksum (two digit)
     *
     * @deprecated use {@link \Faker\Provider\tr_TR\Person::tcNoChecksum()} instead
     * @see \Faker\Provider\tr_TR\Person::tcNoChecksum()
     */
    public static function checksum($identityPrefix)
    {
        return \Faker\Provider\tr_TR\Person::tcNoChecksum($identityPrefix);
    }

    /**
     * Checks whether a TCNo has a valid checksum
	 * 检查TCNo是否有有效的校验和
     *
     * @param string $tcNo
     *
     * @return bool
     *
     * @deprecated use {@link \Faker\Provider\tr_TR\Person::tcNoIsValid()} instead
     * @see \Faker\Provider\tr_TR\Person::tcNoIsValid()
     */
    public static function isValid($tcNo)
    {
        return \Faker\Provider\tr_TR\Person::tcNoIsValid($tcNo);
    }
}
