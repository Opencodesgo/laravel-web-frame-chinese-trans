<?php
/**
 * Cron，字段工厂接口
 */

namespace Cron;

interface FieldFactoryInterface
{
    public function getField(int $position): FieldInterface;
}
