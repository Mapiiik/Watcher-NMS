<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\AccessPointTypesTable;
use App\Test\Traits\TableTestTrait;
use Cake\TestSuite\TestCase;
use Override;

/**
 * App\Model\Table\AccessPointTypesTable Test Case
 */
class AccessPointTypesTableTest extends TestCase
{
    use TableTestTrait;

    /**
     * Test subject
     *
     * @var \App\Model\Table\AccessPointTypesTable
     */
    protected $AccessPointTypes;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.AccessPointTypes',
        'app.AccessPoints',
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
        $config = $this->getTableLocator()->exists('AccessPointTypes') ? [] : ['className' => AccessPointTypesTable::class];
        $this->AccessPointTypes = $this->getTableLocator()->get('AccessPointTypes', $config);
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
        unset($this->AccessPointTypes);

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
        $this->assertValidatorIsNotEmpty($this->AccessPointTypes);
    }

    /**
     * The colour of a type ends up in a style attribute, so only a colour gets in.
     *
     * @return void
     * @link \App\Model\Table\AccessPointTypesTable::validationDefault()
     */
    public function testOnlyAColorIsStored(): void
    {
        $this->assertSame([], $this->AccessPointTypes->newEntity(['color' => '#ffcdd2'])->getError('color'));
        $this->assertNotSame([], $this->AccessPointTypes->newEntity(['color' => '"><img'])->getError('color'));
    }
}
