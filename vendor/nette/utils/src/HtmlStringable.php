<?php
/**
 * Nette，Html Stringable
 */

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette;


interface HtmlStringable
{
	/**
	 * Returns string in HTML format
	 * 返回HTML格式的字符串
	 */
	function __toString(): string;
}


interface_exists(Utils\IHtmlString::class);
