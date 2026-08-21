<?php
declare(strict_types=1);

namespace App\PowerOutages\Provider;

use App\Model\Entity\PowerOutageScope;
use App\PowerOutages\Cez\CezPayloadNormalizer;
use App\PowerOutages\Dto\PowerOutageQuery;
use App\PowerOutages\Dto\PowerOutageReading;
use Override;
use RuntimeException;

/**
 * The same readings, out of a file somebody kept rather than off the network.
 *
 * What an operator sends when the mirror goes wrong, and what a change to how the answers are read
 * is tried against first. The file is a map of the question to the answer, using the same words
 * the mirror records a reading under:
 *
 *     {"town:533165": { ... }, "ean:859182400000001231": { ... }}
 *
 * A question the file says nothing about is unanswered, exactly as it would be if the distributor
 * had not answered it - so a file holding one municipality can be replayed against a run that
 * would have asked about forty without the other thirty-nine being swept away.
 */
final class PowerOutageProviderPayload implements PowerOutageProviderInterface
{
    /**
     * @param array<string, mixed> $payloads The answers, keyed by the question each answers.
     */
    public function __construct(private readonly array $payloads)
    {
    }

    /**
     * The readings kept in a file.
     *
     * @param string $path Where the file is.
     * @return self
     */
    public static function fromFile(string $path): self
    {
        if (!is_readable($path)) {
            throw new RuntimeException(__('There is nothing to read at {0}.', $path));
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException(__('The kept answers at {0} could not be read.', $path));
        }

        $payloads = json_decode($contents, true);

        if (!is_array($payloads)) {
            throw new RuntimeException(__('The kept answers at {0} are not what was expected.', $path));
        }

        /** @var array<string, mixed> $payloads */
        return new self($payloads);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function read(PowerOutageQuery $query): array
    {
        $readings = [];

        foreach ($query->eans as $ean) {
            $payload = $this->payloads[PowerOutageScope::forEan($ean)] ?? null;

            $readings[] = is_array($payload)
                ? PowerOutageReading::ofEan($ean, CezPayloadNormalizer::fromEan($payload, $ean))
                : PowerOutageReading::unanswered(PowerOutageScope::forEan($ean));
        }

        foreach ($query->townCodes as $townCode) {
            $payload = $this->payloads[PowerOutageScope::forTown($townCode)] ?? null;

            $readings[] = is_array($payload)
                ? PowerOutageReading::ofTown($townCode, CezPayloadNormalizer::fromTown($payload, $townCode))
                : PowerOutageReading::unanswered(PowerOutageScope::forTown($townCode));
        }

        return $readings;
    }
}
