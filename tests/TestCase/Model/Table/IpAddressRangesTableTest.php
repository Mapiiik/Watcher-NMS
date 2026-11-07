<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\IpAddressRangesTable;
use App\Test\Traits\TableTestTrait;
use Cake\TestSuite\TestCase;
use Override;

/**
 * App\Model\Table\IpAddressRangesTable Test Case
 */
class IpAddressRangesTableTest extends TestCase
{
    use TableTestTrait;

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
        'app.Users',
        'app.AccessPointTypes',
        'app.AccessPoints',
        'app.IpAddressRanges',
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
        $config = $this->getTableLocator()->exists('IpAddressRanges') ? [] : ['className' => IpAddressRangesTable::class];
        $this->IpAddressRanges = $this->getTableLocator()->get('IpAddressRanges', $config);
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
        unset($this->IpAddressRanges);

        parent::tearDown();
    }

    /**
     * A new record with nothing filled in is refused - see the trait for why that is the question
     * worth asking here.
     *
     * @return void
     * @link \App\Model\Table\IpAddressRangesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->assertEmptyRecordIsRefused($this->IpAddressRanges);
    }

    /**
     * The rules refuse a record whose references point nowhere - see the trait for why that is
     * the question worth asking here.
     *
     * @return void
     * @link \App\Model\Table\IpAddressRangesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->assertDanglingReferencesAreRefused($this->IpAddressRanges);
    }

    /**
     * A range with another one under it stays and says why. The key would refuse it too, by an
     * exception the operator sees as an error page. Once nothing is under it, it goes.
     *
     * @return void
     * @link \App\Model\Table\IpAddressRangesTable::buildRules()
     */
    public function testARangeWithAnotherOneUnderItIsNotDeleted(): void
    {
        $parent = $this->IpAddressRanges->get('fa060f68-b28c-40fe-b734-1abb6a78c179');

        $this->assertFalse($this->IpAddressRanges->delete($parent));
        $this->assertNotSame([], $parent->getError('child_ip_address_ranges'));

        $this->IpAddressRanges->deleteOrFail($this->IpAddressRanges->get('c22cef67-ab70-4363-af2d-a3e7456c1ea5'));

        $this->assertTrue($this->IpAddressRanges->delete($this->IpAddressRanges->get($parent->get('id'))));
    }
}
