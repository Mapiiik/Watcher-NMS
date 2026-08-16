<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\SettingsController;
use App\Test\Traits\ControllerTestTrait;
use Cake\Datasource\FactoryLocator;
use Cake\ORM\Locator\TableLocator;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Controller\SettingsController Test Case
 *
 * The actions come from the plugin's trait and are tested there. What is asked here is what only
 * this application decides: it refuses the fallback table class in the web, so a controller that
 * has no table of its own has to be all right with that.
 */
#[UsesClass(SettingsController::class)]
class SettingsControllerTest extends TestCase
{
    use ControllerTestTrait;
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.AppUsers',
        'plugin.Settings.Settings',
    ];

    /**
     * setUp method
     *
     * Console runs let a table be built for an alias no class answers to, the web does not - see
     * `App\Application::bootstrap()`. The tests are a console run, so the refusal has to be asked
     * for here or the question this case exists for is never put.
     *
     * @return void
     */
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        FactoryLocator::add('Table', (new TableLocator())->allowFallbackClass(false));
    }

    /**
     * tearDown method
     *
     * @return void
     */
    #[Override]
    protected function tearDown(): void
    {
        FactoryLocator::add('Table', new TableLocator());

        parent::tearDown();
    }

    /**
     * Editing a settings block renders, though `Settings` names no table of the application's.
     *
     * @return void
     * @link \Settings\Controller\Trait\SettingsControllerTrait::edit()
     */
    public function testEditRendersWithoutATableOfItsOwn(): void
    {
        $this->login();
        $this->get('/settings/edit/core.devices');

        $this->assertResponseOk();
    }

    /**
     * Every block the listing offers opens. A block is reached by the path it is declared under,
     * so a link naming a path nothing declares answers with a not-found rather than a page - and
     * nothing but opening it says whether the two agree.
     *
     * @param string $path Path of the block, as the listing links to it.
     * @return void
     * @link \Settings\Controller\Trait\SettingsControllerTrait::edit()
     */
    #[DataProvider('blocksProvider')]
    public function testEveryBlockTheListingOffersOpens(string $path): void
    {
        $this->login();
        $this->get('/settings/edit/' . $path);

        $this->assertResponseOk();
    }

    /**
     * @return array<string, array{string}>
     */
    public static function blocksProvider(): array
    {
        return [
            'dashboard' => ['core.dashboard'],
            'the tasks within the dashboard' => ['core.dashboard.tasks'],
            'devices' => ['core.devices'],
            'radio units' => ['core.radio_units'],
            // A block within a block opens on its own too, which is what a listing would link to
            // if one of them ever grew big enough to be worth a page of its own.
            'the register within the radio units' => ['core.radio_units.rlan'],
        ];
    }
}
