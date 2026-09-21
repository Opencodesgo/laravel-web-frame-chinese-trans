<?php
/**
 * Hamcrest，数组，是否数组大小
 */

namespace Hamcrest\Arrays;

/*
 Copyright (c) 2009 hamcrest.org
 */
use Hamcrest\Core\DescribedAs;
use Hamcrest\Core\IsNot;
use Hamcrest\FeatureMatcher;
use Hamcrest\Matcher;
use Hamcrest\Util;

/**
 * Matches if array size satisfies a nested matcher.
 * 匹配如果数组大小满足一个嵌套matcher。
 */
class IsArrayWithSize extends FeatureMatcher
{

    public function __construct(Matcher $sizeMatcher)
    {
        parent::__construct(
            self::TYPE_ARRAY,
            null,
            $sizeMatcher,
            'an array with size',
            'array size'
        );
    }

    protected function featureValueOf($array)
    {
        return count($array);
    }

    /**
     * Does array size satisfy a given matcher?
	 * 数组大小是否满足给定的matcher ?
     *
     * @param \Hamcrest\Matcher|int $size as a {@link Hamcrest\Matcher} or a value.
     *
     * @return \Hamcrest\Arrays\IsArrayWithSize
     * @factory
     */
    public static function arrayWithSize($size)
    {
        return new self(Util::wrapValueWithIsEqual($size));
    }

    /**
     * Matches an empty array.
	 * 匹配一个空数组
     *
     * @factory
     */
    public static function emptyArray()
    {
        return DescribedAs::describedAs(
            'an empty array',
            self::arrayWithSize(0)
        );
    }

    /**
     * Matches an empty array.
	 * 匹配一个空数组
     *
     * @factory
     */
    public static function nonEmptyArray()
    {
        return DescribedAs::describedAs(
            'a non-empty array',
            self::arrayWithSize(IsNot::not(0))
        );
    }
}
