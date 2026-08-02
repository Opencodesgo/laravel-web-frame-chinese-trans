<?php
/**
 * Symfony，Component，Translation，异常，逻辑异常
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Translation\Exception;

/**
 * Base LogicException for Translation component.
 * 翻译组件的基本LogicException。
 *
 * @author Abdellatif Ait boudad <a.aitboudad@gmail.com>
 */
class LogicException extends \LogicException implements ExceptionInterface
{
}
