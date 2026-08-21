<?php
declare(strict_types=1);

namespace App\PowerOutages\Cez;

use App\Model\Entity\PowerOutageScope;
use App\PowerOutages\Dto\PowerOutageQuery;
use App\PowerOutages\Dto\PowerOutageReading;
use App\PowerOutages\Provider\PowerOutageProviderInterface;
use Cake\Cache\Cache;
use Override;
use Settings\Utility\Settings;

/**
 * The outages this distributor publishes, however they have to be asked for.
 *
 * Two endpoints, and above this line neither of them exists: what comes back is one reading per
 * question, answered or not. An outage that both of them answered about arrives twice, once in
 * each reading, because which readings saw it is exactly what has to be written down - putting the
 * two together is the business of whoever writes the mirror.
 */
final class CezOutageProvider implements PowerOutageProviderInterface
{
    /**
     * @param \App\PowerOutages\Cez\BezstavyClient $bezstavy Where to ask about a municipality.
     * @param \App\PowerOutages\Cez\DipClient $dip Where to ask about a supply point.
     */
    public function __construct(
        private readonly BezstavyClient $bezstavy,
        private readonly DipClient $dip,
    ) {
    }

    /**
     * The provider this installation is configured for.
     *
     * @param callable|null $sleeper How to wait, so that a test does not have to.
     * @return self
     */
    public static function fromConfiguration(?callable $sleeper = null): self
    {
        return new self(
            BezstavyClient::fromConfiguration($sleeper),
            DipClient::fromConfiguration($sleeper),
        );
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function read(PowerOutageQuery $query): array
    {
        $readings = [];

        // The supply points first: they are the answers worth having, they are not metered, and a
        // run cut short by something going wrong will at least have got them.
        foreach ($query->eans as $ean) {
            $payload = $this->dip->outagesAtSupplyPoint($ean);

            $readings[] = $payload === null
                ? PowerOutageReading::unanswered(PowerOutageScope::forEan($ean))
                : PowerOutageReading::ofEan($ean, CezPayloadNormalizer::fromEan($payload, $ean));
        }

        foreach ($query->townCodes as $townCode) {
            $payload = $this->townPayload($townCode);

            $readings[] = $payload === null
                ? PowerOutageReading::unanswered(PowerOutageScope::forTown($townCode))
                : PowerOutageReading::ofTown($townCode, CezPayloadNormalizer::fromTown($payload, $townCode));
        }

        return $readings;
    }

    /**
     * What one municipality answered, kept for a while so that a run repeated after something went
     * wrong does not ask the same thing again.
     *
     * Kept as it arrived rather than as it was read, so that a change to how it is read does not
     * have to wait for a cache to turn over. Written with a lifetime of its own rather than
     * through remember(): the pool it lives in keeps what it is given for as long as the
     * deployment says, which for this is either far too long or, in development, no time at all.
     *
     * @param int $townCode The registry number of the municipality.
     * @return array<string, mixed>|null
     */
    private function townPayload(int $townCode): ?array
    {
        $key = 'power_outages_town_' . $townCode;
        $seconds = (int)Settings::get('core.access_points.power_outages.town_cache_seconds', 3600);

        if ($seconds > 0) {
            $cached = Cache::read($key);

            if (is_array($cached)) {
                /** @var array<string, mixed> $cached */
                return $cached;
            }
        }

        $payload = $this->bezstavy->outagesInTown($townCode);

        if ($payload !== null && $seconds > 0) {
            Cache::pool('default')->set($key, $payload, $seconds);
        }

        return $payload;
    }
}
