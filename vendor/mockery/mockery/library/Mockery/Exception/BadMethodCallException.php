<?php
/**
 * Mockery，异常，错误方法调用异常
 */

namespace Mockery\Exception;

class BadMethodCallException extends \BadMethodCallException
{
    private $dismissed = false;

    public function dismiss()
    {
        $this->dismissed = true;

        // we sometimes stack them
        if ($this->getPrevious() && $this->getPrevious() instanceof BadMethodCallException) {
            $this->getPrevious()->dismiss();
        }
    }

    public function dismissed()
    {
        return $this->dismissed;
    }
}
