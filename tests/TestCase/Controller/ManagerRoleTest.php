<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Test\Traits\ControllerTestTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * What a manager is let into beyond the other roles.
 *
 * A manager may do whatever any of the other roles may, and on top of that only what
 * `config/permissions.php` gives it on its own. Each of those is asked about next to a role that
 * has to be refused it - otherwise the test would pass just as well on a page open to everybody.
 */
class ManagerRoleTest extends TestCase
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
        'plugin.Files.DocumentationTypes',
        'app.TaskStates',
        'app.TaskTypes',
    ];

    /**
     * The pages given to a manager on their own.
     *
     * @return array<string, array{string}>
     */
    public static function pagesOfTheManager(): array
    {
        return [
            'documentation types' => ['/documentation-types/add'],
            'task states' => ['/task-states/add'],
            'task types' => ['/task-types/add'],
        ];
    }

    /**
     * @param string $page Address of the page.
     * @return void
     */
    #[DataProvider('pagesOfTheManager')]
    public function testAManagerIsLetInWhereTheOtherRolesAreNot(string $page): void
    {
        $this->login('manager');
        $this->get($page);
        $this->assertResponseOk();

        // the widest of the other roles, which still may not
        $this->login('sales-manager');
        $this->get($page);
        $this->assertRedirect('/');
    }
}
