<?php

use Contao\ArrayUtil;
use Fiedsch\DownloadsBundle\Model\DownloadsTokensModel;

ArrayUtil::arrayInsert($GLOBALS['BE_MOD'], 1, [
    'tokens' => [
        'downloads_tokens' => [
            'tables' => ['tl_downloads_tokens'],
        ]
    ]
]);


$GLOBALS['TL_MODELS']['tl_downloads_tokens'] = DownloadsTokensModel::class;
