<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Audit;

use App\Model\Audit\AuditTrail;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\ORM\Table;
use Cake\TestSuite\LogTestTrait;
use Cake\TestSuite\TestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use SplObjectStorage;

/**
 * App\Model\Audit\AuditTrail Test Case
 */
#[CoversClass(AuditTrail::class)]
class AuditTrailTest extends TestCase
{
    use LocatorAwareTrait;
    use LogTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.AppUsers',
        'app.Manufacturers',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    #[Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->setupLog(['warning']);
        $this->getTableLocator()->get('Manufacturers')->getConnection()
            ->execute('DELETE FROM audit_logs');
    }

    /**
     * What goes into the options of every save the transaction makes.
     *
     * @return void
     */
    public function testTheOptionsCarryTheQueueAndTheTransaction(): void
    {
        $options = (new AuditTrail())->options();

        $this->assertInstanceOf(SplObjectStorage::class, $options['_auditQueue']);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            (string)$options['_auditTransaction'],
        );
    }

    /**
     * The same trail asked twice gives the same transaction, which is what ties the writes of
     * one act together.
     *
     * @return void
     */
    public function testTheTransactionStandsForTheLifeOfTheTrail(): void
    {
        $trail = new AuditTrail();

        $this->assertSame(
            $trail->options()['_auditTransaction'],
            $trail->options()['_auditTransaction'],
        );
    }

    /**
     * Writing the queue out is what gets a transaction into the log at all.
     *
     * @return void
     */
    public function testTheQueueReachesTheLogOnlyWhenItIsWrittenOut(): void
    {
        $makers = $this->getTableLocator()->get('Manufacturers');
        $trail = new AuditTrail();
        $maker = $makers->find()->firstOrFail();

        $makers->getConnection()->transactional(function () use ($makers, $maker, $trail): bool {
            $maker->set('name', 'Renamed inside a transaction');
            $makers->saveOrFail($maker, $trail->options());

            return true;
        });

        $this->assertSame(0, $this->logged(), 'nothing inside a transaction reaches the log by itself');

        $trail->flush($makers, $maker);

        $this->assertSame(1, $this->logged());
    }

    /**
     * Written out twice, the same act is in the log once. The behaviour empties the copy of the
     * options it is handed rather than the trail, so the trail has to forget the queue itself.
     *
     * @return void
     */
    public function testWritingItOutTwiceDoesNotLogItTwice(): void
    {
        $makers = $this->getTableLocator()->get('Manufacturers');
        $trail = new AuditTrail();
        $maker = $makers->find()->firstOrFail();

        $makers->getConnection()->transactional(function () use ($makers, $maker, $trail): bool {
            $maker->set('name', 'Renamed once');
            $makers->saveOrFail($maker, $trail->options());

            return true;
        });

        $trail->flush($makers, $maker);
        $trail->flush($makers, $maker);

        $this->assertSame(1, $this->logged());
    }

    /**
     * A trail written out through a table that keeps no log says so, because every table of ours
     * keeps one - but it does not bring the request down over a transaction that has committed.
     *
     * @return void
     */
    public function testATableWithNoAuditLogIsComplainedAboutRatherThanThrownOver(): void
    {
        $plain = $this->getTableLocator()->get('PlainManufacturers', [
            'className' => Table::class,
            'table' => 'manufacturers',
        ]);

        (new AuditTrail())->flush($plain, $plain->newEmptyEntity());

        $this->assertLogMessageContains('warning', 'keeps no audit log');
        $this->assertSame(0, $this->logged());
    }

    /**
     * @return int How many rows the audit log holds.
     */
    private function logged(): int
    {
        $rows = $this->getTableLocator()->get('Manufacturers')->getConnection()
            ->selectQuery(['c' => 'count(*)'], 'audit_logs')
            ->execute()
            ->fetchAll('assoc');

        return (int)$rows[0]['c'];
    }
}
