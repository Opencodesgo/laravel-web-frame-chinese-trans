<?php
/**
 * Faker，核心，扩展，版本扩展
 */

namespace Faker\Extension;

/**
 * @experimental This interface is experimental and does not fall under our BC promise
 * 这个接口是实验性的,不会在我们的BC承诺下下降。
 */
interface VersionExtension extends Extension
{
    /**
     * Get a version number in semantic versioning syntax 2.0.0. (https://semver.org/spec/v2.0.0.html)
	 * 在语义版本化语法2.0.0中获取一个版本号。
     *
     * @param bool $preRelease Pre release parts may be randomly included
     * @param bool $build      Build parts may be randomly included
     *
     * @example 1.0.0
     * @example 1.0.0-alpha.1
     * @example 1.0.0-alpha.1+b71f04d
     */
    public function semver(bool $preRelease = false, bool $build = false): string;
}
