<?php
declare(strict_types=1);

namespace App\CRM\Provider;

use App\CRM\ApiClient;
use Override;

/**
 * The customer points as the CRM lists them.
 */
final readonly class CustomerPointProviderApi implements CustomerPointProviderInterface
{
    /**
     * @inheritDoc
     */
    #[Override]
    public function read(): array
    {
        return CustomerPointPayloadNormalizer::customerPoints(ApiClient::customerPoints()->orFail());
    }
}
