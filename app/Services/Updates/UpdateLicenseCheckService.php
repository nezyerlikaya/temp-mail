<?php

namespace App\Services\Updates;

class UpdateLicenseCheckService
{
    /** @return array{status: string, label: string, message: string} */
    public function readiness(): array
    {
        return [
            'status' => 'warning',
            'label' => 'License check readiness',
            'message' => 'License verification is prepared for the official update server and will be enforced before install actions.',
        ];
    }
}
