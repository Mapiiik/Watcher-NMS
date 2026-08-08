<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\IpAddressRangesController;
use App\Test\Traits\ControllerTestTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Controller\IpAddressRangesController Test Case
 *
 * Smoke tests: every action is requested once and has to answer. They are deliberately shallow -
 * their job is to notice an action that stopped answering at all, which is where the query building
 * bugs turn up.
 */
#[UsesClass(IpAddressRangesController::class)]
class IpAddressRangesControllerTest extends TestCase
{
    use ControllerTestTrait;
    use IntegrationTestTrait;

    /**
     * Access point the nested routes hang off.
     *
     * @var string
     */
    private const ACCESS_POINT_ID = '1bd5e754-e102-46ad-8488-11b1b44bf026';

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
     * The listing renders.
     *
     * @return void
     * @link \App\Controller\IpAddressRangesController::index()
     */
    public function testIndex(): void
    {
        $this->login();
        $this->get('/ip-address-ranges');

        $this->assertResponseOk();
    }

    /**
     * The listing renders with the search filled in, which builds a different query than the plain
     * listing does and is therefore worth requesting on its own.
     *
     * @return void
     * @link \App\Controller\IpAddressRangesController::index()
     */
    public function testIndexWithSearch(): void
    {
        $this->login();
        $this->get('/ip-address-ranges?search=Lorem');

        $this->assertResponseOk();
    }

    /**
     * The detail of a record renders.
     *
     * @return void
     * @link \App\Controller\IpAddressRangesController::view()
     */
    public function testView(): void
    {
        $this->login();
        $this->get('/ip-address-ranges/view/' . $this->firstId('IpAddressRanges'));

        $this->assertResponseOk();
    }

    /**
     * The form for a new record renders.
     *
     * @return void
     * @link \App\Controller\IpAddressRangesController::add()
     */
    public function testAdd(): void
    {
        $this->login();
        $this->get('/ip-address-ranges/add');

        $this->assertResponseOk();
    }

    /**
     * The form of an existing record renders.
     *
     * @return void
     * @link \App\Controller\IpAddressRangesController::edit()
     */
    public function testEdit(): void
    {
        $this->login();
        $this->get('/ip-address-ranges/edit/' . $this->firstId('IpAddressRanges'));

        $this->assertResponseOk();
    }

    /**
     * The delete action runs and redirects. Whether the record really goes depends on what else
     * still references it, which is the application rules' business rather than this test's.
     *
     * @return void
     * @link \App\Controller\IpAddressRangesController::delete()
     */
    public function testDelete(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/ip-address-ranges/delete/' . $this->firstId('IpAddressRanges'));

        $this->assertRedirect();
    }

    /**
     * Added under its access point, the record is filed under it without the form saying so.
     *
     * The form under an access point leaves that field out - the route already says which one it
     * is, and the controller fills it in. Posting it in the body instead, as a test reaching the
     * flat route does, asks a different question and leaves this one unasked.
     *
     * @return void
     * @link \App\Controller\IpAddressRangesController::add()
     */
    public function testAddUnderTheRouteFilesItUnderTheRoute(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $before = $this->idsIn('IpAddressRanges');
        $this->post('/access-points/' . self::ACCESS_POINT_ID . '/ip-address-ranges/add', [
            'ip_network' => '10.99.0.0/24',
        ]);

        $this->assertRedirect();
        $added = $this->addedRecord('IpAddressRanges', $before);
        $this->assertSame(self::ACCESS_POINT_ID, $added->get('access_point_id'));
    }

    /**
     * A change made on the form reaches the record.
     *
     * @return void
     * @link \App\Controller\IpAddressRangesController::edit()
     */
    public function testEditStoresTheChange(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $ipAddressRangeId = $this->firstId('IpAddressRanges');
        $this->post('/ip-address-ranges/edit/' . $ipAddressRangeId, ['name' => 'Renamed range']);

        $this->assertRedirect();
        $this->assertSame(
            'Renamed range',
            $this->getTableLocator()->get('IpAddressRanges')->get($ipAddressRangeId)->name,
        );
    }
}
