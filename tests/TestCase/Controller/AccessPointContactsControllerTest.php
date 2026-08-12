<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\AccessPointContactsController;
use App\Test\Traits\ConfigureTestTrait;
use App\Test\Traits\ControllerTestTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Override;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Controller\AccessPointContactsController Test Case
 *
 * Smoke tests: every action is requested once and has to answer. They are deliberately shallow -
 * their job is to notice an action that stopped answering at all, which is where the query building
 * bugs turn up.
 */
#[UsesClass(AccessPointContactsController::class)]
class AccessPointContactsControllerTest extends TestCase
{
    use ConfigureTestTrait;
    use ControllerTestTrait;
    use IntegrationTestTrait;

    /**
     * tearDown method
     *
     * @return void
     */
    #[Override]
    public function tearDown(): void
    {
        $this->restoreConfigure();

        parent::tearDown();
    }

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
        'app.AccessPointContacts',
    ];

    /**
     * The listing renders.
     *
     * @return void
     * @link \App\Controller\AccessPointContactsController::index()
     */
    public function testIndex(): void
    {
        $this->login();
        $this->get('/access-point-contacts');

        $this->assertResponseOk();
    }

    /**
     * The listing renders with the search filled in, which builds a different query than the plain
     * listing does and is therefore worth requesting on its own.
     *
     * @return void
     * @link \App\Controller\AccessPointContactsController::index()
     */
    public function testIndexWithSearch(): void
    {
        $this->login();
        $this->get('/access-point-contacts?search=Lorem');

        $this->assertResponseOk();
    }

    /**
     * The detail of a record renders.
     *
     * @return void
     * @link \App\Controller\AccessPointContactsController::view()
     */
    public function testView(): void
    {
        $this->login();
        $this->get('/access-point-contacts/view/' . $this->firstId('AccessPointContacts'));

        $this->assertResponseOk();
    }

    /**
     * The form for a new record renders.
     *
     * @return void
     * @link \App\Controller\AccessPointContactsController::add()
     */
    public function testAdd(): void
    {
        $this->login();
        $this->get('/access-point-contacts/add');

        $this->assertResponseOk();
    }

    /**
     * The form of an existing record renders.
     *
     * @return void
     * @link \App\Controller\AccessPointContactsController::edit()
     */
    public function testEdit(): void
    {
        $this->login();
        $this->get('/access-point-contacts/edit/' . $this->firstId('AccessPointContacts'));

        $this->assertResponseOk();
    }

    /**
     * The delete action runs and redirects. Whether the record really goes depends on what else
     * still references it, which is the application rules' business rather than this test's.
     *
     * @return void
     * @link \App\Controller\AccessPointContactsController::delete()
     */
    public function testDelete(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/access-point-contacts/delete/' . $this->firstId('AccessPointContacts'));

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
     * @link \App\Controller\AccessPointContactsController::add()
     */
    public function testAddUnderTheRouteFilesItUnderTheRoute(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $before = $this->idsIn('AccessPointContacts');
        $this->post('/access-points/' . self::ACCESS_POINT_ID . '/access-point-contacts/add', [
            'name' => 'Nested contact',
        ]);

        $this->assertRedirect();
        $added = $this->addedRecord('AccessPointContacts', $before);
        $this->assertSame(self::ACCESS_POINT_ID, $added->get('access_point_id'));
    }

    /**
     * A change made on the form reaches the record.
     *
     * @return void
     * @link \App\Controller\AccessPointContactsController::edit()
     */
    public function testEditStoresTheChange(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $accessPointContactId = $this->firstId('AccessPointContacts');
        $this->post('/access-point-contacts/edit/' . $accessPointContactId, ['name' => 'Renamed contact']);

        $this->assertRedirect();
        $this->assertSame(
            'Renamed contact',
            $this->getTableLocator()->get('AccessPointContacts')->get($accessPointContactId)->name,
        );
    }

    /**
     * Numbers that were stored before they were formatted on save are brought into one format by
     * the run from the settings page.
     *
     * @return void
     * @link \App\Controller\AccessPointContactsController::formatAll()
     */
    public function testFormatAllPutsTheStoredNumbersIntoOneFormat(): void
    {
        $this->withConfigure(['Phones.defaultRegion' => 'CZ']);
        $contactId = $this->storeContactWithPhone('601234567');

        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/access-point-contacts/format-all');

        $this->assertRedirect();
        $this->assertSame(
            '+420 601 234 567',
            $this->getTableLocator()->get('AccessPointContacts')->get($contactId)->phone,
        );
    }

    /**
     * A value that cannot be read as a number is left as it stands - the run reports it rather
     * than making something up for it.
     *
     * @return void
     * @link \App\Controller\AccessPointContactsController::formatAll()
     */
    public function testFormatAllLeavesANumberItCannotReadAlone(): void
    {
        $this->withConfigure(['Phones.defaultRegion' => 'CZ']);
        $contactId = $this->storeContactWithPhone('reception desk');

        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/access-point-contacts/format-all');

        $this->assertRedirect();
        $this->assertSame(
            'reception desk',
            $this->getTableLocator()->get('AccessPointContacts')->get($contactId)->phone,
        );
    }

    /**
     * A contact reached by mail alone carries no phone, and the run has to leave it standing
     * rather than trip over the empty field.
     *
     * @return void
     * @link \App\Controller\AccessPointContactsController::formatAll()
     */
    public function testFormatAllLeavesAContactWithoutAPhoneStanding(): void
    {
        $this->withConfigure(['Phones.defaultRegion' => 'CZ']);
        $contactId = $this->storeContactWithPhone(null);

        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/access-point-contacts/format-all');

        $this->assertRedirect();
        $this->assertNull(
            $this->getTableLocator()->get('AccessPointContacts')->get($contactId)->phone,
        );
    }

    /**
     * Stores a number the way one could already be sitting in the table - past the marshalling
     * that would format it and past the rule that would refuse it.
     *
     * @param string|null $phone Number to store as it stands.
     * @return string Id of the stored record.
     */
    private function storeContactWithPhone(?string $phone): string
    {
        $contacts = $this->getTableLocator()->get('AccessPointContacts');

        $entity = $contacts->newEmptyEntity();
        $entity->set('access_point_id', self::ACCESS_POINT_ID);
        $entity->set('name', 'Reception');
        $entity->set('phone', $phone);

        $contacts->saveOrFail($entity, ['checkRules' => false]);

        return (string)$entity->get('id');
    }
}
