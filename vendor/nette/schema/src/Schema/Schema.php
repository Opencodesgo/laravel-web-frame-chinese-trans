<?php
/**
 * Nette，Schema，模式
 */

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Schema;

interface Schema
{
	/**
	 * Normalization.
	 * 标准化
	 * @return mixed
	 */
	function normalize($value, Context $context);

	/**
	 * Merging.
	 * 合并
	 * @return mixed
	 */
	function merge($value, $base);

	/**
	 * Validation and finalization.
	 * 验证和确定
	 * @return mixed
	 */
	function complete($value, Context $context);

	/**
	 * @return mixed
	 */
	function completeDefault(Context $context);
}
