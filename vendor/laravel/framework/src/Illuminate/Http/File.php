<?php
/**
 * Illuminate，Http，文件，从 Symfony 文件继承
 */

namespace Illuminate\Http;

use Symfony\Component\HttpFoundation\File\File as SymfonyFile;

class File extends SymfonyFile
{
    use FileHelpers;
}
