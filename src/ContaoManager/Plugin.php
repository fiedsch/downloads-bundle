<?php

declare(strict_types=1);

namespace Fiedsch\DownloadsBundle\ContaoManager;

use Fiedsch\DownloadsBundle\FiedschDownloadsBundle;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;

class Plugin implements BundlePluginInterface
{
    /**
     * {@inheritdoc}
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            (new BundleConfig(FiedschDownloadsBundle::class))
                ->setLoadAfter([ContaoCoreBundle::class]),
        ];
    }
}