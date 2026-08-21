<?php
declare(strict_types=1);

namespace App\Test\TestCase\PowerOutages\Service;

use App\Model\Entity\AccessPoint;
use App\Model\Entity\AccessPointSupplyAddress;
use App\Model\Entity\PowerOutage;
use App\Model\Entity\PowerOutageScope;
use App\Model\Enum\OutageCertainty;
use App\Model\Enum\OutageMatch;
use App\PowerOutages\Service\PowerOutageMatcher;
use Cake\TestSuite\TestCase;
use Override;

/**
 * App\PowerOutages\Service\PowerOutageMatcher Test Case
 *
 * Nothing here touches the database or a network: everything the matcher reads has already been
 * written down, which is the whole reason the links can be worked out again from the mirror alone.
 */
class PowerOutageMatcherTest extends TestCase
{
    /**
     * Test subject
     */
    private PowerOutageMatcher $matcher;

    /**
     * setUp method
     *
     * @return void
     */
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->matcher = new PowerOutageMatcher();
    }

    /**
     * An outage that came back from asking about our own supply point is about this mast.
     *
     * @return void
     * @link \App\PowerOutages\Service\PowerOutageMatcher::matchOne()
     */
    public function testAnOutageSeenBySupplyPointIsCertain(): void
    {
        $accessPoint = $this->accessPoint(ean: '859182400000001231');
        $outage = $this->outage(scopes: ['ean:859182400000001231']);

        $match = $this->matcher->matchOne($accessPoint, $outage);

        $this->assertSame(OutageCertainty::Certain, $match?->certainty);
        $this->assertSame(OutageMatch::Ean, $match->matchedBy);
    }

    /**
     * The supply point of somebody else says nothing about this mast.
     *
     * @return void
     * @link \App\PowerOutages\Service\PowerOutageMatcher::matchOne()
     */
    public function testAnOutageSeenByAnotherSupplyPointIsNotOurs(): void
    {
        $accessPoint = $this->accessPoint(ean: '859182400000001231');
        $outage = $this->outage(scopes: ['ean:859182400000009999']);

        $this->assertNull($this->matcher->matchOne($accessPoint, $outage));
    }

    /**
     * A house number inside a span the distributor wrote out is covered.
     *
     * @return void
     * @link \App\PowerOutages\Service\PowerOutageMatcher::matchOne()
     */
    public function testAHouseNumberInsideASpanMatches(): void
    {
        $accessPoint = $this->accessPoint(addresses: [$this->address(houseNumber: 129)]);
        $outage = $this->outage(streets: [$this->street(houseNums: '106, 107, 126-131, 139')]);

        $match = $this->matcher->matchOne($accessPoint, $outage);

        $this->assertSame(OutageCertainty::Probable, $match?->certainty);
        $this->assertSame(OutageMatch::Address, $match->matchedBy);
        $this->assertSame(42, $match->distanceMetres);
    }

    /**
     * The distributor wrote down which buildings go dark, and ours is not one of them.
     *
     * The one thing the matcher says that rules a mast out rather than in, and worth its own test:
     * getting it wrong the other way would have every mast on a street reported for an outage that
     * touches three houses on it.
     *
     * @return void
     * @link \App\PowerOutages\Service\PowerOutageMatcher::matchOne()
     */
    public function testAHouseNumberTheOutageDoesNotNameIsRuledOut(): void
    {
        $accessPoint = $this->accessPoint(addresses: [$this->address(houseNumber: 108)]);
        $outage = $this->outage(streets: [$this->street(houseNums: '106, 107, 109')]);

        $this->assertNull($this->matcher->matchOne($accessPoint, $outage));
    }

    /**
     * A building numbered the way holiday buildings are is not one of the houses.
     *
     * @return void
     * @link \App\PowerOutages\Service\PowerOutageMatcher::matchOne()
     */
    public function testARegistrationNumberIsNotReadAgainstTheHouseNumbers(): void
    {
        $accessPoint = $this->accessPoint(addresses: [
            $this->address(houseNumber: 5, numberType: AccessPointSupplyAddress::NUMBER_TYPE_REGISTRATION),
        ]);
        $outage = $this->outage(streets: [$this->street(houseNums: '5')]);

        $this->assertNull($this->matcher->matchOne($accessPoint, $outage));
    }

    /**
     * The same building, once the outage names the registration numbers instead.
     *
     * @return void
     * @link \App\PowerOutages\Service\PowerOutageMatcher::matchOne()
     */
    public function testARegistrationNumberMatchesTheRegistrationNumbers(): void
    {
        $accessPoint = $this->accessPoint(addresses: [
            $this->address(houseNumber: 5, numberType: AccessPointSupplyAddress::NUMBER_TYPE_REGISTRATION),
        ]);
        $outage = $this->outage(streets: [$this->street(houseNums: '', evNums: '3, 5, 8')]);

        $this->assertSame(OutageMatch::Address, $this->matcher->matchOne($accessPoint, $outage)?->matchedBy);
    }

    /**
     * An orientation number carries its letter, and matches only written out in full.
     *
     * @return void
     * @link \App\PowerOutages\Service\PowerOutageMatcher::matchOne()
     */
    public function testAnOrientationNumberMatchesWithItsLetter(): void
    {
        $accessPoint = $this->accessPoint(addresses: [
            $this->address(houseNumber: 2186, orientationNumber: 12, orientationLetter: 'b'),
        ]);
        $outage = $this->outage(streets: [$this->street(houseNums: '', streetNums: '10, 12b, 14')]);

        $this->assertSame(OutageMatch::Address, $this->matcher->matchOne($accessPoint, $outage)?->matchedBy);
    }

    /**
     * A street named without any numbers takes in everything on it.
     *
     * @return void
     * @link \App\PowerOutages\Service\PowerOutageMatcher::matchOne()
     */
    public function testAStreetNamedWithoutNumbersRestsOnTheStreetAlone(): void
    {
        $accessPoint = $this->accessPoint(addresses: [$this->address(houseNumber: 108)]);
        $outage = $this->outage(streets: [$this->street(houseNums: '')]);

        $match = $this->matcher->matchOne($accessPoint, $outage);

        $this->assertSame(OutageCertainty::Probable, $match?->certainty);
        $this->assertSame(OutageMatch::Street, $match->matchedBy);
        $this->assertStringContainsString('street only', strval($match->note));
    }

    /**
     * Numbers written in a way nothing here can read weaken the link rather than ruling it out.
     *
     * @return void
     * @link \App\PowerOutages\Service\PowerOutageMatcher::matchOne()
     */
    public function testUnreadableNumbersLeaveTheLinkStandingOnTheStreet(): void
    {
        $accessPoint = $this->accessPoint(addresses: [$this->address(houseNumber: 108)]);
        $outage = $this->outage(streets: [$this->street(houseNums: 'vsechna cisla')]);

        $this->assertSame(OutageMatch::Street, $this->matcher->matchOne($accessPoint, $outage)?->matchedBy);
    }

    /**
     * The right street in the wrong municipality is the wrong street.
     *
     * @return void
     * @link \App\PowerOutages\Service\PowerOutageMatcher::matchOne()
     */
    public function testTheSameStreetInAnotherMunicipalityIsNotOurs(): void
    {
        $accessPoint = $this->accessPoint(addresses: [$this->address(houseNumber: 129)]);
        $outage = $this->outage(streets: [
            $this->street(townCode: 582786, houseNums: '126-131'),
        ]);

        $this->assertNull($this->matcher->matchOne($accessPoint, $outage));
    }

    /**
     * A name that lost its marks on the way is still the same name.
     *
     * @return void
     * @link \App\PowerOutages\Service\PowerOutageMatcher::matchOne()
     */
    public function testAStreetStrippedOfItsMarksStillMatches(): void
    {
        $accessPoint = $this->accessPoint(addresses: [
            $this->address(houseNumber: 129, streetName: 'Hlubočská'),
        ]);
        $outage = $this->outage(streets: [
            $this->street(street: 'Hlubocska', houseNums: '126-131'),
        ]);

        $this->assertSame(OutageMatch::Address, $this->matcher->matchOne($accessPoint, $outage)?->matchedBy);
    }

    /**
     * Where two of the addresses around the mast are on the street, the nearer one is the reason.
     *
     * @return void
     * @link \App\PowerOutages\Service\PowerOutageMatcher::matchOne()
     */
    public function testTheNearerOfTwoMatchingAddressesIsTheOneRecorded(): void
    {
        $accessPoint = $this->accessPoint(addresses: [
            $this->address(id: 'far', houseNumber: 130, distanceMetres: 300),
            $this->address(id: 'near', houseNumber: 129, distanceMetres: 40),
        ]);
        $outage = $this->outage(streets: [$this->street(houseNums: '126-131')]);

        $this->assertSame('near', $this->matcher->matchOne($accessPoint, $outage)?->supplyAddressId);
    }

    /**
     * A match on an address beats one resting on the street alone, however near the street is.
     *
     * @return void
     * @link \App\PowerOutages\Service\PowerOutageMatcher::matchOne()
     */
    public function testAnAddressBeatsAStreet(): void
    {
        $accessPoint = $this->accessPoint(addresses: [
            $this->address(id: 'near', houseNumber: 1, distanceMetres: 10, streetName: 'Kutnohorska'),
            $this->address(id: 'far', houseNumber: 129, distanceMetres: 300),
        ]);
        $outage = $this->outage(streets: [
            $this->street(street: 'Kutnohorska', houseNums: ''),
            $this->street(houseNums: '126-131'),
        ]);

        $match = $this->matcher->matchOne($accessPoint, $outage);

        $this->assertSame(OutageMatch::Address, $match?->matchedBy);
        $this->assertSame('far', $match->supplyAddressId);
    }

    /**
     * A house with no street is matched by its number, the way the village is addressed.
     *
     * Most of the countryside carries a house number and nothing else, and the distributor lists
     * those under a street entry with no street in it. Reading that as a broken row rather than as
     * the houses without a street was what made two thirds of the addresses invisible.
     *
     * @return void
     * @link \App\PowerOutages\Service\PowerOutageMatcher::matchOne()
     */
    public function testAHouseWithNoStreetIsMatchedByItsNumber(): void
    {
        $accessPoint = $this->accessPoint(addresses: [
            $this->address(houseNumber: 222, streetName: ''),
        ]);
        $outage = $this->outage(streets: [
            $this->street(street: '', houseNums: '69, 150, 183, 211, 222, 265'),
        ]);

        $match = $this->matcher->matchOne($accessPoint, $outage);

        $this->assertSame(OutageCertainty::Probable, $match?->certainty);
        $this->assertSame(OutageMatch::Address, $match->matchedBy);
    }

    /**
     * The same number in another part of the municipality is another house.
     *
     * A house number is unique within a part of a municipality, not within the whole of it - one
     * municipality here carries number 95 in three of its parts - so without comparing the part,
     * an outage in one village would be reported over a mast in the next.
     *
     * @return void
     * @link \App\PowerOutages\Service\PowerOutageMatcher::matchOne()
     */
    public function testTheSameNumberInAnotherPartOfTheMunicipalityIsNotOurs(): void
    {
        $accessPoint = $this->accessPoint(addresses: [
            $this->address(houseNumber: 95, streetName: '', townPart: 'Svetla'),
        ]);
        $outage = $this->outage(streets: [
            $this->street(street: '', townPart: 'Borkov', houseNums: '95'),
        ]);

        $this->assertNull($this->matcher->matchOne($accessPoint, $outage));
    }

    /**
     * A street of the same name in another part of the municipality is another street.
     *
     * @return void
     * @link \App\PowerOutages\Service\PowerOutageMatcher::matchOne()
     */
    public function testTheSameStreetInAnotherPartOfTheMunicipalityIsNotOurs(): void
    {
        $accessPoint = $this->accessPoint(addresses: [
            $this->address(houseNumber: 129, townPart: 'Kolin VI'),
        ]);
        $outage = $this->outage(streets: [
            $this->street(townPart: 'Kolin IV', houseNums: '126-131'),
        ]);

        $this->assertNull($this->matcher->matchOne($accessPoint, $outage));
    }

    /**
     * A house with no street is not matched against a street that has one.
     *
     * @return void
     * @link \App\PowerOutages\Service\PowerOutageMatcher::matchOne()
     */
    public function testAHouseWithNoStreetIsNotMatchedAgainstANamedStreet(): void
    {
        $accessPoint = $this->accessPoint(addresses: [
            $this->address(houseNumber: 217, streetName: ''),
        ]);
        $outage = $this->outage(streets: [$this->street(street: 'Bozkovska', houseNums: '217')]);

        $this->assertNull($this->matcher->matchOne($accessPoint, $outage));
    }

    /**
     * A mast with no address around it and no supply point cannot be matched at all.
     *
     * Which is exactly the mast standing away from any village, and the reason the page has to say
     * so out loud rather than showing an empty list.
     *
     * @return void
     * @link \App\PowerOutages\Service\PowerOutageMatcher::matchOne()
     */
    public function testAMastWithNothingToGoOnMatchesNothing(): void
    {
        $accessPoint = $this->accessPoint(addresses: []);
        $outage = $this->outage(streets: [$this->street(houseNums: '126-131')]);

        $this->assertNull($this->matcher->matchOne($accessPoint, $outage));
    }

    /**
     * Several outages at once come back keyed by the outage they are about.
     *
     * @return void
     * @link \App\PowerOutages\Service\PowerOutageMatcher::match()
     */
    public function testSeveralOutagesComeBackKeyedByOutage(): void
    {
        $accessPoint = $this->accessPoint(addresses: [$this->address(houseNumber: 129)]);

        $matches = $this->matcher->match($accessPoint, [
            $this->outage(id: 'ours', streets: [$this->street(houseNums: '126-131')]),
            $this->outage(id: 'theirs', streets: [$this->street(houseNums: '1, 2, 3')]),
        ]);

        $this->assertSame(['ours'], array_keys($matches));
    }

    /**
     * A mast, with whatever is known about where it stands.
     *
     * @param string|null $ean The supply point, where it is written down.
     * @param array<int, \App\Model\Entity\AccessPointSupplyAddress>|null $addresses The addresses around it.
     * @return \App\Model\Entity\AccessPoint
     */
    private function accessPoint(?string $ean = null, ?array $addresses = null): AccessPoint
    {
        $accessPoint = new AccessPoint([
            'id' => 'access-point',
            'name' => 'Kolin water tower',
            'electricity_ean' => $ean,
        ]);
        $accessPoint->set('access_point_supply_addresses', $addresses ?? [$this->address()]);

        return $accessPoint;
    }

    /**
     * One of the addresses around the mast.
     *
     * @param string $id What to call it, so that a test can say which one matched.
     * @param int|null $houseNumber The number of the building.
     * @param string $numberType Which of the two kinds of number that is.
     * @param int|null $orientationNumber The number the building carries along the street.
     * @param string|null $orientationLetter The letter on it, where it has one.
     * @param int|null $distanceMetres How far it stands from the mast.
     * @param string $streetName The street it is on.
     * @return \App\Model\Entity\AccessPointSupplyAddress
     */
    private function address(
        string $id = 'address',
        ?int $houseNumber = 106,
        string $numberType = AccessPointSupplyAddress::NUMBER_TYPE_HOUSE,
        ?int $orientationNumber = null,
        ?string $orientationLetter = null,
        ?int $distanceMetres = 42,
        string $streetName = 'Hlubocska',
        string $townPart = 'Kolin VI',
    ): AccessPointSupplyAddress {
        return new AccessPointSupplyAddress([
            'id' => $id,
            'rank' => 1,
            'distance_metres' => $distanceMetres,
            'town_code' => 533165,
            'town_name' => 'Kolin',
            'town_part_name' => $townPart,
            'street_name' => $streetName,
            'house_number' => $houseNumber,
            'orientation_number' => $orientationNumber,
            'orientation_letter' => $orientationLetter,
            'number_type' => $numberType,
            'formatted_address' => $streetName . ' ' . strval($houseNumber) . ', 28002 Kolin',
        ]);
    }

    /**
     * One street an outage reaches, in the shape the mirror keeps it.
     *
     * @param string $street The name of the street.
     * @param int|null $townCode The municipality it is in.
     * @param string $houseNums The house numbers the outage names.
     * @param string $evNums The registration numbers the outage names.
     * @param string $streetNums The orientation numbers the outage names.
     * @return array<string, mixed>
     */
    private function street(
        string $street = 'Hlubocska',
        ?int $townCode = 533165,
        string $houseNums = '',
        string $evNums = '',
        string $streetNums = '',
        string $townPart = 'Kolin VI',
    ): array {
        return [
            'town_code' => $townCode,
            'town' => 'Kolin',
            'town_part' => $townPart,
            'street' => $street,
            'house_nums' => $houseNums,
            'ev_nums' => $evNums,
            'street_nums' => $streetNums,
        ];
    }

    /**
     * One outage, with the readings that saw it and the places it reaches.
     *
     * @param string $id What to call it, so that a test can say which one matched.
     * @param array<int, string> $scopes The readings that saw it.
     * @param array<int, array<string, mixed>> $streets The streets it reaches.
     * @return \App\Model\Entity\PowerOutage
     */
    private function outage(string $id = 'outage', array $scopes = [], array $streets = []): PowerOutage
    {
        $outage = new PowerOutage([
            'id' => $id,
            'distributor' => 'CEZD',
            'outage_number' => '110061112294',
            'places' => ['parcels' => [], 'towns' => [], 'streets' => $streets],
        ]);
        $outage->set('power_outage_scopes', array_map(
            fn(string $scope): PowerOutageScope => new PowerOutageScope(['scope' => $scope]),
            $scopes,
        ));

        return $outage;
    }
}
