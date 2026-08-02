<?php
/**
 * Facade，Ignition，支持，Fake Composer
 */

namespace Facade\Ignition\Support;

class FakeComposer
{
    public function getClassMap()
    {
        return [];
    }

    public function getPrefixes()
    {
        return [];
    }

    public function getPrefixesPsr4()
    {
        return [];
    }
}
