<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\PaymentPurposesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\PaymentPurposesTable Test Case
 */
class PaymentPurposesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\PaymentPurposesTable
     */
    protected $PaymentPurposes;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.AccessPoints',
        'app.PaymentPurposes',
        'app.LandlordPayments',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('PaymentPurposes') ? [] : ['className' => PaymentPurposesTable::class];
        $this->PaymentPurposes = $this->fetchTable('PaymentPurposes', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->PaymentPurposes);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\PaymentPurposesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
