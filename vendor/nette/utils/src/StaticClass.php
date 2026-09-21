<?php
/**
 * Nette，静态类
 */

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette;


/**
 * Static class.
 * 静态类
 */
trait StaticClass
{
	/**
	 * Class is static and cannot be instantiated.
	 * 类是静态的，不能实例化。
	 */
	private function __construct()
	{
	}


	/**
	 * Call to undefined static method.
	 * 调用未定义的静态方法
	 * @throws MemberAccessException
	 */
	public static function __callStatic(string $name, array $args): mixed
	{
		Utils\ObjectHelpers::strictStaticCall(static::class, $name);
	}
}
