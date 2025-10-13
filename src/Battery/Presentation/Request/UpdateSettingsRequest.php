<?php

declare(strict_types=1);

namespace App\Battery\Presentation\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class UpdateSettingsRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Range(min: 1, max: 24)]
        public readonly int $capacityHours,
    ) {
    }
}
