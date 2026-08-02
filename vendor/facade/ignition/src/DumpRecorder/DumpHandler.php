<?php
/**
 * Facade，Ignition，转储记录器，转储记录器
 */

namespace Facade\Ignition\DumpRecorder;

use Symfony\Component\VarDumper\Cloner\VarCloner;

class DumpHandler
{
    /** @var \Facade\Ignition\DumpRecorder\DumpRecorder */
    protected $dumpRecorder;

    public function __construct(DumpRecorder $dumpRecorder)
    {
        $this->dumpRecorder = $dumpRecorder;
    }

    public function dump($value)
    {
        $data = (new VarCloner())->cloneVar($value);

        $this->dumpRecorder->record($data);
    }
}
