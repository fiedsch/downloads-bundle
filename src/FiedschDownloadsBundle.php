<?php

declare(strict_types=1);

namespace Fiedsch\DownloadsBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use function dirname;

class FiedschDownloadsBundle extends Bundle
{
    public function getPath(): string
    {
        return dirname(__DIR__);
    }

}