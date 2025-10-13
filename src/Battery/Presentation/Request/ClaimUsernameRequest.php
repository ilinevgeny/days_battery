<?php

declare(strict_types=1);

namespace App\Battery\Presentation\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class ClaimUsernameRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 3, max: 32)]
        #[Assert\Regex(pattern: '/^[a-zA-Z0-9_-]+$/', message: 'Username can only contain letters, numbers, underscores and hyphens')]
        public readonly string $username,
    ) {
    }
}
