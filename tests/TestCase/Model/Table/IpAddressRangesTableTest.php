<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\IpAddressRangesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\IpAddressRangesTable Test Case
 */
class IpAddressRangesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\IpAddressRangesTable
     */
    protected $IpAddressRanges;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.AccessPointTypes',
        'app.AccessPoints',
        'app.IpAddressRanges',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('IpAddressRanges') ? [] : ['className' => IpAddressRangesTable::class];
        $this->IpAddressRanges = $this->getTableLocator()->get('IpAddressRanges', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->IpAddressRanges);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\IpAddressRangesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \App\Model\Table\IpAddressRangesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
