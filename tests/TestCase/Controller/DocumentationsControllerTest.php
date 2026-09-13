<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\DocumentationsController;
use App\Model\Table\DocumentationsTable;
use App\Test\Traits\ControllerTestTrait;
use Cake\Core\Configure;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Files\Service\Documentations;
use Files\Service\FileStorage;
use Override;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Controller\DocumentationsController Test Case
 *
 * The agenda reads what the address it was opened at says: under a connection it is that
 * connection's documentation, under a customer it is everything about them, and with no nesting
 * at all it is the lot. Filing works the same way round, which is what keeps documentation from
 * being hung on somebody else's record.
 */
#[UsesClass(DocumentationsController::class)]
class DocumentationsControllerTest extends TestCase
{
    use ControllerTestTrait;
    use IntegrationTestTrait;
    use LocatorAwareTrait;

    /**
     * The record the documentation hangs on.
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
        'plugin.Files.DocumentationTypes',
        'plugin.Files.Documentations',
        'plugin.Files.Files',
        'plugin.Files.FileLinks',
    ];

    /**
     * Where the bytes go while this runs.
     *
     * @var string
     */
    private string $root;

    /**
     * setUp method
     *
     * @return void
     */
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->root = TMP . 'documentations-' . uniqid();
        Configure::write('Files.root', $this->root);

        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    #[Override]
    protected function tearDown(): void
    {
        Configure::delete('Files.root');
        $this->removeDirectory($this->root);

        parent::tearDown();
    }

    /**
     * @link \App\Controller\DocumentationsController::add()
     * @return void
     */
    public function testWhatItHangsOnComesFromTheAddressAndNotFromTheForm(): void
    {
        $kind = $this->kind();

        $this->post(
            '/access-points/' . self::ACCESS_POINT_ID . '/documentations/add',
            ['documentation_type_id' => $kind, 'name' => 'The installation'],
        );

        $this->assertResponseSuccess();

        /** @var \App\Model\Entity\Documentation $filed */
        $filed = $this->documentations()->find()->firstOrFail();

        $this->assertSame(self::ACCESS_POINT_ID, $filed->access_point_id);
    }

    /**
     * @link \App\Controller\DocumentationsController::index()
     * @return void
     */
    public function testTheListingFollowsTheAddressItWasOpenedAt(): void
    {
        $kind = $this->kind();
        $this->documentations()->saveOrFail($this->documentations()->newEntity([
            'documentation_type_id' => $kind,
            'access_point_id' => self::ACCESS_POINT_ID,
            'name' => 'The installation',
        ]));
        $this->documentations()->saveOrFail($this->documentations()->newEntity([
            'documentation_type_id' => $kind,
            'name' => 'Something filed against nobody',
        ]));

        $this->get('/documentations');
        $this->assertResponseContains('The installation');
        $this->assertResponseContains('Something filed against nobody');

        $this->get('/access-points/' . self::ACCESS_POINT_ID . '/documentations');
        $this->assertResponseContains('The installation');
        $this->assertResponseNotContains('Something filed against nobody');
    }

    /**
     * @link \App\Controller\DocumentationsController::add()
     * @return void
     */
    public function testATypeThatAsksForAnAccessPointIsNotFiledWithoutOne(): void
    {
        $kind = $this->kind(['access_point_required' => true]);

        $this->post('/documentations/add', ['documentation_type_id' => $kind, 'name' => 'Nowhere in particular']);

        $this->assertResponseSuccess();
        $this->assertSame(0, $this->documentations()->find()->count());
    }

    /**
     * @link \App\Controller\DocumentationsController::delete()
     * @return void
     */
    public function testDeletingOneTakesWhatItHeldWithIt(): void
    {
        $documentation = $this->documentations()->saveOrFail($this->documentations()->newEntity([
            'documentation_type_id' => $this->kind(),
            'access_point_id' => self::ACCESS_POINT_ID,
            'name' => 'The installation',
        ]));

        $storage = new FileStorage();
        $file = $storage->store('a photograph of the mast', 'text/plain');
        $storage->link(
            $file,
            Documentations::MODEL,
            (string)$documentation->id,
            Documentations::ATTACHMENT,
            Documentations::UPLOADED,
        );

        $this->post('/documentations/delete/' . $documentation->id);

        $this->assertRedirect();
        $this->assertSame(0, $this->documentations()->find()->count());
        $this->assertFalse($storage->has($file), 'The bytes should have gone with the last link to them.');
    }

    /**
     * Every page of the agenda answers. Little is asked of them beyond that, but a page that has
     * stopped rendering at all is worth hearing about from the suite rather than from somebody
     * clicking.
     *
     * @link \App\Controller\DocumentationsController::view()
     * @return void
     */
    public function testEveryPageOfTheAgendaAnswers(): void
    {
        $documentation = $this->documentations()->saveOrFail($this->documentations()->newEntity([
            'documentation_type_id' => $this->kind(),
            'access_point_id' => self::ACCESS_POINT_ID,
            'name' => 'The installation',
        ]));

        $under = '/access-points/' . self::ACCESS_POINT_ID . '/documentations/';

        $pages = [
            'add',
            'view/' . $documentation->id,
            'edit/' . $documentation->id,
            'add-files/' . $documentation->id,
        ];

        foreach ($pages as $page) {
            $this->get($under . $page);
            $this->assertResponseOk('The page at ' . $page . ' did not answer.');
        }
    }

    /**
     * A kind of documentation, saved, by its id.
     *
     * @param array<string, mixed> $said What to say about it.
     * @return string
     */
    private function kind(array $said = []): string
    {
        $types = $this->fetchTable('DocumentationTypes');

        return (string)$types->saveOrFail($types->newEntity($said + [
            'name' => 'Installation',
            'position' => 0,
            'currently_offered' => true,
            'date_required' => false,
            'access_point_required' => false,
        ]))->id;
    }

    /**
     * @return \App\Model\Table\DocumentationsTable
     */
    private function documentations(): DocumentationsTable
    {
        /** @var \App\Model\Table\DocumentationsTable $table */
        $table = $this->fetchTable('Documentations');

        return $table;
    }

    /**
     * Removes a directory and everything under it.
     *
     * @param string $directory The directory.
     * @return void
     */
    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        foreach (array_diff((array)scandir($directory), ['.', '..']) as $entry) {
            $path = $directory . DS . $entry;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }

        rmdir($directory);
    }
}
