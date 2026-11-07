<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\CustomerPointsTable;
use App\Test\Traits\TableTestTrait;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Override;

/**
 * App\Model\Table\CustomerPointsTable Test Case
 */
class CustomerPointsTableTest extends TestCase
{
    use TableTestTrait;

    /**
     * Test subject
     *
     * @var \App\Model\Table\CustomerPointsTable
     */
    protected $CustomerPoints;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.CustomerPoints',
        'app.CustomerConnections',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('CustomerPoints') ? [] : ['className' => CustomerPointsTable::class];
        $this->CustomerPoints = TableRegistry::getTableLocator()->get('CustomerPoints', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    #[Override]
    protected function tearDown(): void
    {
        /** @phpstan-ignore unset.possiblyHookedProperty */
        unset($this->CustomerPoints);

        parent::tearDown();
    }

    /**
     * Every column may stay empty here, so what can go wrong is the validator itself - see the
     * trait.
     *
     * @return void
     */
    public function testValidationDefault(): void
    {
        $this->assertValidatorIsNotEmpty($this->CustomerPoints);
    }
}
