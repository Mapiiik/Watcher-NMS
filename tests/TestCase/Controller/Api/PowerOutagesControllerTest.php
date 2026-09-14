<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Controller\Api\PowerOutagesController;
use App\Test\Traits\ControllerTestTrait;
use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Controller\Api\PowerOutagesController Test Case
 *
 * More than the smoke test its siblings get, because this answer is read by another application
 * that cannot see what it was built from. What is asserted is the wire format itself: the fields
 * are the contract, and a field quietly renamed here is a column quietly emptied over there.
 */
#[UsesClass(PowerOutagesController::class)]
class PowerOutagesControllerTest extends TestCase
{
    use ControllerTestTrait;
    use IntegrationTestTrait;

    /**
     * The outage of the fixture that was not called off.
     *
     * @var string
     */
    private const LIVE_OUTAGE_ID = 'b1b2c3d4-0001-4a5b-9a4a-2c0f4d5e6a71';

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.AppUsers',
        'app.AccessPointTypes',
        'app.AccessPoints',
        'app.CustomerPoints',
        'app.CustomerConnections',
        'app.AccessPointSupplyAddresses',
        'app.PowerOutages',
        'app.PowerOutageScopes',
        'app.AccessPointPowerOutages',
    ];

    /**
     * The listing answers with what it promises.
     *
     * @return void
     * @link \App\Controller\Api\PowerOutagesController::index()
     */
    public function testIndex(): void
    {
        $this->moveLiveOutage(DateTime::now()->addDays(3));

        $this->login();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->get('/api/power-outages.json');

        $this->assertResponseOk();
        $this->assertResponseContains('"powerOutages"');
    }

    /**
     * Every field the other application reads is in the answer, under the name it reads it by.
     *
     * @return void
     * @link \App\Controller\Api\PowerOutagesController::index()
     */
    public function testTheAnswerCarriesWhatTheOtherApplicationReads(): void
    {
        $this->moveLiveOutage(DateTime::now()->addDays(3));

        $this->login();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->get('/api/power-outages.json');

        $this->assertResponseOk();

        /** @var array{powerOutages: array<int, array<string, mixed>>} $body */
        $body = json_decode((string)$this->_response?->getBody(), true);

        $this->assertCount(1, $body['powerOutages'], 'The cancelled outage is not news.');

        $row = $body['powerOutages'][0];

        $this->assertSame(
            [
                'access_point_id',
                'access_point_name',
                'connections',
                'begins_at',
                'ends_at',
                'certainty',
                'matched_by',
                'match_note',
                'summary',
                'announcement_url',
            ],
            array_keys($row),
        );
        $this->assertSame('Kolin water tower', $row['access_point_name']);
        $this->assertSame('probable', $row['certainty']);
        $this->assertSame('address', $row['matched_by']);
        $this->assertIsInt($row['connections']);
    }

    /**
     * Asked to look less far ahead, it does.
     *
     * @return void
     * @link \App\Controller\Api\PowerOutagesController::index()
     */
    public function testHowFarAheadToLookCanBeAsked(): void
    {
        $this->moveLiveOutage(DateTime::now()->addDays(10));

        $this->login();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->get('/api/power-outages.json?within_days=3');

        $this->assertResponseOk();
        $this->assertResponseNotContains('Kolin water tower');

        $this->get('/api/power-outages.json?within_days=30');

        $this->assertResponseOk();
        $this->assertResponseContains('Kolin water tower');
    }

    /**
     * Nonsense in the address is answered with the usual horizon rather than with an error.
     *
     * @return void
     * @link \App\Controller\Api\PowerOutagesController::index()
     */
    public function testNonsenseAboutTheHorizonIsNotAnError(): void
    {
        $this->login();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->get('/api/power-outages.json?within_days=whenever');

        $this->assertResponseOk();
    }

    /**
     * The account the other application signs in as is let in here.
     *
     * @return void
     * @link \App\Controller\Api\PowerOutagesController::index()
     */
    public function testTheApiRoleIsAllowed(): void
    {
        $this->login('api');
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->get('/api/power-outages.json');

        $this->assertResponseOk();
    }

    /**
     * Put the outage of the fixture that was not called off on a given day, lasting an hour.
     *
     * The fixture is written against fixed moments, so the outage is moved onto the days these
     * ask about rather than the question being moved onto it.
     *
     * @param \Cake\I18n\DateTime $begins When it is to begin.
     * @return void
     */
    private function moveLiveOutage(DateTime $begins): void
    {
        $outages = TableRegistry::getTableLocator()->get('PowerOutages');

        $outage = $outages->get(self::LIVE_OUTAGE_ID);
        $outage->set('begins_at', $begins);
        $outage->set('ends_at', $begins->addHours(1));
        $outages->saveOrFail($outage);
    }
}
