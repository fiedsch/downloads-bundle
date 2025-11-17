<?php

declare(strict_types=1);

namespace Fiedsch\DownloadsBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\StringUtil;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCallback(table: 'tl_downloads_tokens', target: 'list.label.label')]
class DownloadTokensLabelCallbackListener
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

     public function __invoke(array $row, string $label, DataContainer $dc, array $labels): array
    {

        $headline = StringUtil::deserialize($row['headline'])['value'];
        $token = sprintf('<span class="label-info">[%s]</span>', $row['token']);

        return [$headline, $token];
    }

}