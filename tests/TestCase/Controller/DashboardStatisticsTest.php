<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Dashboard\Card\StatisticsCard;
use App\Test\Traits\ControllerTestTrait;
use Cake\Chronos\Chronos;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * The statistics card, counted over an inventory built for it.
 *
 * Kept apart from the rest of the dashboard's tests because these add masts, devices and radios
 * dated around the stopped clock, which the counts there would then have to step around.
 */
#[CoversClass(StatisticsCard::class)]
class DashboardStatisticsTest extends TestCase
{
    use ControllerTestTrait;
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.AccessPointTypes',
        'app.AccessPoints',
        'app.CustomerPoints',
        'app.CustomerConnections',
        'app.DeviceTypes',
        'app.RouterosDevices',
        'app.Manufacturers',
        'app.RadioLinks',
        'app.RadioUnitBands',
        'app.RadioUnitTypes',
        'app.AntennaTypes',
        'app.RadioUnits',
    ];

    /**
     * The clock as it was before a test stopped it somewhere else.
     *
     * @var \Cake\Chronos\Chronos|null
     */
    private ?Chronos $was = null;

    /**
     * setUp method
     *
     * The middle of November 2026. The fixtures hold three masts from 2023, one of them
     * archived then, a device last written in 2021, and a radio unit and a link from 2021.
     *
     * @return void
     */
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->was = Chronos::getTestNow();
        Chronos::setTestNow(new Chronos('2026-11-14 12:00:00'));

        // Written straight: the rules and the stamps of a record are not what this is about.
        $locator = $this->getTableLocator();
        $locator->get('AccessPoints')->updateAll(
            ['archived' => '2026-11-03 10:00:00'],
            ['id' => '1bd5e754-e102-46ad-8488-11b1b44bf026'],
        );
        $this->insert('access_points', [
            // added this month, twice
            ['id' => 'a4000000-0000-4000-8000-000000000004', 'created' => '2026-11-05 09:00:00', 'archived' => null],
            ['id' => 'a7000000-0000-4000-8000-000000000007', 'created' => '2026-11-10 09:00:00', 'archived' => null],
            // added and archived last month
            [
                'id' => 'a5000000-0000-4000-8000-000000000005',
                'created' => '2026-10-10 09:00:00',
                'archived' => '2026-10-25 09:00:00',
            ],
            // older than the column that would say when
            ['id' => 'a6000000-0000-4000-8000-000000000006', 'created' => null, 'archived' => null],
        ]);

        $this->insert('routeros_devices', [
            [
                'id' => 'd2000000-0000-4000-8000-000000000002',
                'created' => '2026-11-02 09:00:00',
                'modified' => '2026-11-14 10:00:00',
                'software_version' => '7.23.5',
            ],
            [
                'id' => 'd3000000-0000-4000-8000-000000000003',
                'created' => '2026-10-15 09:00:00',
                'modified' => '2026-11-13 10:00:00',
                'software_version' => '7.23.5',
            ],
            ...array_map(
                fn(int $i, ?string $version): array => [
                    'id' => sprintf('d%d000000-0000-4000-8000-00000000000%d', $i, $i),
                    'created' => '2025-01-01 09:00:00',
                    'modified' => '2026-11-12 10:00:00',
                    'software_version' => $version,
                ],
                [4, 5, 6, 7, 8, 9],
                ['7.1', '7.16', '7.2', '7.3', '7.4', null],
            ),
        ]);

        $this->insert('radio_units', [['id' => 'e2000000-0000-4000-8000-000000000002', 'created' => '2026-11-03 09:00:00']]);
        $this->insert('radio_links', [['id' => 'f2000000-0000-4000-8000-000000000002', 'created' => '2026-10-03 09:00:00']]);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    #[Override]
    protected function tearDown(): void
    {
        Chronos::setTestNow($this->was);

        parent::tearDown();
    }

    /**
     * Rows written past the table, with nothing but what the case needs.
     *
     * @param string $table The table.
     * @param list<array<string, mixed>> $rows The rows, each with the columns of the first.
     * @return void
     */
    private function insert(string $table, array $rows): void
    {
        $columns = array_keys($rows[0]);
        $query = $this->getTableLocator()->get('Users')->getConnection()->insertQuery($table)->insert($columns);
        foreach ($rows as $row) {
            $query->values($row);
        }
        $query->execute();
    }

    /**
     * The card as the dashboard fetched it.
     *
     * @return array<string, mixed>
     */
    private function cardData(): array
    {
        $this->login('network-technician');
        $this->get('/dashboard/card/statistics');
        $this->assertResponseOk();

        /** @var \App\Dashboard\Card\StatisticsCard $card */
        $card = $this->viewVariable('card');

        return $card->data();
    }

    /**
     * This month and the one before, each group by its rows.
     *
     * @return void
     * @link \App\Dashboard\Card\StatisticsCard::data()
     */
    public function testThisMonthAndTheOneBeforeAreCounted(): void
    {
        $figures = [];
        foreach ($this->cardData()['groups'] as $group) {
            foreach ($group['rows'] as $row) {
                $figures[$group['title']][$row['title']] = $row['values'];
            }
        }

        $this->assertSame(
            [
                'Access Points' => [
                    'Active' => [4, 3],
                    'Added' => [2, 1],
                    'Archived' => [1, 1],
                ],
                // whether a device reported last month is not on record
                'RouterOS Devices' => [
                    'Reporting' => [8, null],
                    'Not Reporting' => [1, null],
                    'Seen for the First Time' => [1, 1],
                ],
                'Radio Units' => [
                    'On Record' => [2, 1],
                    'Added' => [1, 0],
                ],
                'Radio Links' => [
                    'On Record' => [2, 2],
                    'Added' => [0, 1],
                ],
            ],
            $figures,
        );
        $this->assertResponseContains('November 2026');
        $this->assertResponseContains('October 2026');
    }

    /**
     * The software the reporting devices run, the most common first and the rest together.
     *
     * @return void
     * @link \App\Dashboard\Card\StatisticsCard::data()
     */
    public function testTheMostCommonVersionsAreNamed(): void
    {
        $this->assertSame(
            [
                'shown' => [
                    ['version' => '7.23.5', 'devices' => 2],
                    ['version' => '7.1', 'devices' => 1],
                    ['version' => '7.16', 'devices' => 1],
                    ['version' => '7.2', 'devices' => 1],
                    ['version' => '7.3', 'devices' => 1],
                ],
                // 7.4 and the one that never said
                'others' => 2,
            ],
            $this->cardData()['versions'],
        );
        $this->assertResponseContains('Other Versions');
    }

    /**
     * The numbers of now lead to the listings that show them.
     *
     * @return void
     * @link \App\Dashboard\Card\StatisticsCard::data()
     */
    public function testTheNumbersLeadToTheListings(): void
    {
        $this->cardData();

        $this->assertResponseContains('href="/access-points"');
        $this->assertResponseContains('href="/access-points/index/archived"');
        $this->assertResponseContains('href="/routeros-devices"');
        $this->assertResponseContains('href="/routeros-devices?stale=1"');
        $this->assertResponseContains('href="/radio-units"');
        $this->assertResponseContains('href="/radio-links"');
    }
}
