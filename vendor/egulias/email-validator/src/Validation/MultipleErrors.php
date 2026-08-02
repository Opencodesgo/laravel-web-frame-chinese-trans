<?php
/**
 * Egulias，EmailValidator，验证，多重错误
 */

namespace Egulias\EmailValidator\Validation;

use Egulias\EmailValidator\Exception\InvalidEmail;

class MultipleErrors extends InvalidEmail
{
    const CODE = 999;
    const REASON = "Accumulated errors for multiple validations";	#多重验证累积误差
    /**
     * @var InvalidEmail[]
     */
    private $errors = [];

    /**
     * @param InvalidEmail[] $errors
     */
    public function __construct(array $errors)
    {
        $this->errors = $errors;
        parent::__construct();
    }

    /**
     * @return InvalidEmail[]
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
