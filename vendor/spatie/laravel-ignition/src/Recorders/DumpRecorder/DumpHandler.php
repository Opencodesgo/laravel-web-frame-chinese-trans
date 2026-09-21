<?php
/**
 * Spatie，LaravelIgnition，记录器，Dump 记录器，转储控制器
 */

namespace Spatie\LaravelIgnition\Recorders\DumpRecorder;

use Symfony\Component\VarDumper\Cloner\VarCloner;

class DumpHandler
{
    protected DumpRecorder $dumpRecorder;

    public function __construct(DumpRecorder $dumpRecorder)
    {
        $this->dumpRecorder = $dumpRecorder;
    }

    public function dump(mixed $value): void
    {
        $data = (new VarCloner)->cloneVar($value);

        $this->dumpRecorder->record($data);
    }
}
