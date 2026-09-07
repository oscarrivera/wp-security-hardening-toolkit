<?php

declare(strict_types=1);

namespace OrHardening;

final class XmlRpcGuard
{
    public function apply(bool $currentlyEnabled, bool $shouldDisable): bool
    {
        return $shouldDisable ? false : $currentlyEnabled;
    }
}
