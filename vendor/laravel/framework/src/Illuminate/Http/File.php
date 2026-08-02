<?php
/**
 * Illuminate，Http，文件
 */

namespace Illuminate\Http;

use Symfony\Component\HttpFoundation\File\File as SymfonyFile;

class File extends SymfonyFile
{
    use FileHelpers;
}
