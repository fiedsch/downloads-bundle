<?php

declare(strict_types=1);

namespace Fiedsch\DownloadsBundle\Model;

use Contao\Model;
use Contao\System;

/**
 * @property integer $id
 * @property integer $tstamp
 * @property string $token
 * @property string $headline
 * @property string|array|null $multiSRC
 * @property integer $start
 * @property integer $stop
 * @property boolean $published
 *
 * @method static DownloadsTokensModel|null findPublishedByToken(string $token, array $opt=array())
 */

class DownloadsTokensModel extends Model
{
    protected static $strTable = 'tl_downloads_tokens';

    public static function findByToken(string $token, array $opt=[]): ?DownloadsTokensModel
    {
        return self::findOneBy(['token=?'], [$token], $opt);
    }

    public function isHidden(): bool
    {
        // dd(['published' => $this->published, 'start' => $this->start, 'stop' => $this->stop]);
        return $this->isNotPublished() || $this->isNoLongerVisible() || $this->isNotYetVisible();
    }

    public function isNotPublished(): bool
    {
        return !$this->published;
    }

    public function isNoLongerVisible(): bool
    {
        return $this->stop && $this->stop <= time();
    }

    public function isNotYetVisible(): bool
    {
        return $this->start && $this->start > time();
    }


}