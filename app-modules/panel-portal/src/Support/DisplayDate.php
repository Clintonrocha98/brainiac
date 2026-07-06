<?php

declare(strict_types=1);

namespace He4rt\Portal\Support;

use Carbon\CarbonInterface;

/**
 * Formatação de datas para exibição no portal (timezone de exibição).
 */
final class DisplayDate
{
    public static function short(?CarbonInterface $date): ?string
    {
        return $date?->timezone(config('app.display_timezone'))->translatedFormat('d M Y');
    }
}
