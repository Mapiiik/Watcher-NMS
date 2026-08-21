<?php
declare(strict_types=1);

namespace App\PowerOutages\Dto;

use App\Model\Entity\PowerOutageScope;

/**
 * The answer to one question, and whether it was answered at all.
 *
 * The flag is the whole point of carrying a reading around rather than a list of outages. Most
 * questions come back with nothing and that is the ordinary case, so nothing downstream may read
 * an empty list as a failure - but a question that really did fail must not have its outages swept
 * away as though the distributor had stopped publishing them. The difference is recorded here so
 * that the service deciding what to sweep never has to learn what HTTP is.
 */
final readonly class PowerOutageReading
{
    /**
     * @param string $scope The question that was asked.
     * @param bool $answered Whether the distributor answered it.
     * @param list<\App\PowerOutages\Dto\PowerOutageData> $outages What it answered with.
     */
    public function __construct(
        public string $scope,
        public bool $answered,
        public array $outages = [],
    ) {
    }

    /**
     * A question the distributor did not answer.
     *
     * @param string $scope The question that was asked.
     * @return self
     */
    public static function unanswered(string $scope): self
    {
        return new self($scope, answered: false);
    }

    /**
     * The answer about one municipality.
     *
     * @param int $townCode The registry number of the municipality.
     * @param list<\App\PowerOutages\Dto\PowerOutageData> $outages What the distributor answered with.
     * @return self
     */
    public static function ofTown(int $townCode, array $outages): self
    {
        return new self(PowerOutageScope::forTown($townCode), answered: true, outages: $outages);
    }

    /**
     * The answer about one supply point.
     *
     * @param string $ean The EAN of the supply point.
     * @param list<\App\PowerOutages\Dto\PowerOutageData> $outages What the distributor answered with.
     * @return self
     */
    public static function ofEan(string $ean, array $outages): self
    {
        return new self(PowerOutageScope::forEan($ean), answered: true, outages: $outages);
    }
}
