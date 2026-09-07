<?php

declare(strict_types=1);

namespace OrHardening;

final class AppPasswordGuard
{
    /**
     * @param bool $coreAvailable Valor que WordPress iba a devolver.
     * @param bool $allowViaFilter true si or_hardening_allow_application_passwords lo autoriza.
     */
    public function available(bool $coreAvailable, bool $allowViaFilter): bool
    {
        if ($allowViaFilter) {
            return $coreAvailable;
        }

        return false;
    }
}
