<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Controller\Api\IpAddressRangesController;
use App\Test\Traits\ControllerTestTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Controller\Api\IpAddressRangesController Test Case
 *
 * Smoke tests: each endpoint is called once and has to answer with the JSON it promises. The write
 * endpoints report their outcome in a `message` rather than in the status code, so what is
 * asserted is that they answered at all - whether the record itself went through is the model's
 * business.
 */
#[UsesClass(IpAddressRangesController::class)]
class IpAddressRangesControllerTest extends TestCase
{
    use ControllerTestTrait;
    use IntegrationTestTrait;

    /**
     * The access point both ranges the fixtures carry hang off.
     *
     * @var string
     */
    private const ACCESS_POINT_ID = '1ec58677-1213-4950-80c4-bc1de41ea133';

    /**
     * The wider of the two ranges the fixtures carry.
     *
     * @var string
     */
    private const PARENT_NETWORK = '192.168.1.0/24';

    /**
     * The half of it that is carved out separately.
     *
     * @var string
     */
    private const CHILD_NETWORK = '192.168.1.0/25';

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.AppUsers',
        'app.AccessPointTypes',
        'app.AccessPoints',
        'app.IpAddressRanges',
    ];

    /**
     * The collection serializes.
     *
     * @return void
     * @link \App\Controller\Api\IpAddressRangesController::index()
     */
    public function testIndex(): void
    {
        $this->login();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->get('/api/ip-address-ranges.json');

        $this->assertResponseOk();
        $this->assertResponseContains('"ipAddressRanges"');
    }

    /**
     * The search endpoint answers with the same collection, narrowed down.
     *
     * @return void
     * @link \App\Controller\Api\IpAddressRangesController::search()
     */
    public function testSearch(): void
    {
        $this->login();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->get('/api/ip-address-ranges/search.json');

        $this->assertResponseOk();
        $this->assertResponseContains('"ipAddressRanges"');
    }

    /**
     * A single record serializes.
     *
     * @return void
     * @link \App\Controller\Api\IpAddressRangesController::view()
     */
    public function testView(): void
    {
        $this->login();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->get('/api/ip-address-ranges/' . $this->firstId('IpAddressRanges') . '.json');

        $this->assertResponseOk();
        $this->assertResponseContains('"ipAddressRange"');
    }

    /**
     * The endpoint takes a new record and reports the outcome.
     *
     * @return void
     * @link \App\Controller\Api\IpAddressRangesController::add()
     */
    public function testAdd(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->post('/api/ip-address-ranges.json', ['name' => 'Smoke test']);

        $this->assertResponseOk();
        $this->assertResponseContains('"message"');
    }

    /**
     * The endpoint takes a change to an existing record and reports the outcome.
     *
     * @return void
     * @link \App\Controller\Api\IpAddressRangesController::edit()
     */
    public function testEdit(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->patch('/api/ip-address-ranges/' . $this->firstId('IpAddressRanges') . '.json', ['name' => 'Smoke test']);

        $this->assertResponseOk();
        $this->assertResponseContains('"message"');
    }

    /**
     * The endpoint runs the delete and reports the outcome. Whether the record really goes depends
     * on what else still references it.
     *
     * @return void
     * @link \App\Controller\Api\IpAddressRangesController::delete()
     */
    public function testDelete(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->delete('/api/ip-address-ranges/' . $this->firstId('IpAddressRanges') . '.json');

        $this->assertResponseOk();
        $this->assertResponseContains('"message"');
    }

    /**
     * Every filter the endpoint offers can be given at once, and a range that answers all of them is
     * still found. The provisioning asks with whatever it knows, so the filters have to combine
     * rather than shut each other out.
     *
     * @return void
     * @link \App\Controller\Api\IpAddressRangesController::search()
     */
    public function testSearchTakesEveryFilterAtOnce(): void
    {
        $this->login();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->get('/api/ip-address-ranges/search.json?' . http_build_query([
            'access_point_id' => self::ACCESS_POINT_ID,
            'for_subnets' => 1,
            'for_customer_addresses_set_via_radius' => 1,
            'for_customer_addresses_set_manually' => 1,
            'for_technology_addresses_set_manually' => 1,
            'for_customer_networks_set_via_radius' => 1,
            'for_customer_networks_set_manually' => 1,
            'for_technology_networks_set_manually' => 1,
            'ip_address' => '192.168.1.10',
        ]));

        $this->assertResponseOk();
        $this->assertSame(
            [self::CHILD_NETWORK, self::PARENT_NETWORK],
            $this->networksFound(),
        );
    }

    /**
     * The ranges come back with the smallest first, which is what makes the answer usable: the range
     * an address is to be handed out of is the most specific one holding it.
     *
     * @return void
     * @link \App\Controller\Api\IpAddressRangesController::search()
     */
    public function testSearchAnswersWithTheMostSpecificRangeFirst(): void
    {
        $this->login();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->get('/api/ip-address-ranges/search.json?ip_address=192.168.1.10');

        $this->assertResponseOk();
        $this->assertSame([self::CHILD_NETWORK, self::PARENT_NETWORK], $this->networksFound());
    }

    /**
     * An address is looked for inside the ranges rather than against them, so a range that does not
     * reach that far down is left out.
     *
     * @return void
     * @link \App\Controller\Api\IpAddressRangesController::search()
     */
    public function testSearchLeavesOutARangeThatDoesNotHoldTheAddress(): void
    {
        $this->login();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->get('/api/ip-address-ranges/search.json?ip_address=192.168.1.200');

        $this->assertResponseOk();
        $this->assertSame([self::PARENT_NETWORK], $this->networksFound());
    }

    /**
     * Asking for one access point also answers with the ranges that belong to no access point at
     * all. Those are the ones held centrally, and leaving them out would have the provisioning
     * believe there is nothing left to hand out.
     *
     * @return void
     * @link \App\Controller\Api\IpAddressRangesController::search()
     */
    public function testSearchByAccessPointAlsoAnswersWithWhatBelongsToNoAccessPoint(): void
    {
        $ipAddressRanges = $this->getTableLocator()->get('IpAddressRanges');
        $ipAddressRanges->saveOrFail($ipAddressRanges->newEntity([
            'name' => 'Held centrally',
            'ip_network' => '10.99.0.0/24',
        ]));

        $this->login();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->get('/api/ip-address-ranges/search.json?access_point_id=' . self::ACCESS_POINT_ID);

        $this->assertResponseOk();
        $this->assertContains('10.99.0.0/24', $this->networksFound());
        $this->assertContains(self::PARENT_NETWORK, $this->networksFound());
    }

    /**
     * A range that is not meant for what is being asked about is left out.
     *
     * @return void
     * @link \App\Controller\Api\IpAddressRangesController::search()
     */
    public function testSearchLeavesOutARangeThatIsNotMeantForWhatIsAskedAbout(): void
    {
        $ipAddressRanges = $this->getTableLocator()->get('IpAddressRanges');
        $range = $ipAddressRanges->find()->where(['ip_network' => self::CHILD_NETWORK])->firstOrFail();
        $range->set('for_subnets', false);
        $ipAddressRanges->saveOrFail($range);

        $this->login();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->get('/api/ip-address-ranges/search.json?for_subnets=1');

        $this->assertResponseOk();
        $this->assertSame([self::PARENT_NETWORK], $this->networksFound());
    }

    /**
     * The networks the endpoint answered with, in the order it put them in.
     *
     * @return array<string>
     */
    private function networksFound(): array
    {
        /** @var iterable<\App\Model\Entity\IpAddressRange> $ipAddressRanges */
        $ipAddressRanges = $this->viewVariable('ipAddressRanges');

        $networks = [];
        foreach ($ipAddressRanges as $ipAddressRange) {
            $networks[] = (string)$ipAddressRange->get('ip_network');
        }

        return $networks;
    }
}
