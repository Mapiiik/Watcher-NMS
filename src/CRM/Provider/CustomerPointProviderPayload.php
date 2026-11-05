<?php
declare(strict_types=1);

namespace App\CRM\Provider;

use Override;

/**
 * The customer points of a listing somebody already has.
 */
final readonly class CustomerPointProviderPayload implements CustomerPointProviderInterface
{
    /**
     * @param array<mixed> $payload The listing, as the CRM answers with it.
     */
    public function __construct(
        private array $payload,
    ) {
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function read(): array
    {
        return CustomerPointPayloadNormalizer::customerPoints($this->payload);
    }
}
