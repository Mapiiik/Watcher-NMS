<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use App\Command\RlanStationsUpdateCommand;
use App\Test\Traits\ConfigureTestTrait;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\Http\Client\Response;
use Cake\Http\TestSuite\HttpClientTrait;
use Cake\TestSuite\EmailTrait;
use Cake\TestSuite\TestCase;
use Override;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Command\RlanStationsUpdateCommand Test Case
 *
 * Unlike the other unattended commands this one cannot be handed a file to stand in for the thing
 * it reads: signing in and the shape the register wraps its answers in are the two things about
 * this reading that can go wrong, and a file exercises neither. So the register itself is answered
 * for here, and the file is a way of replaying a reading rather than a way of faking one.
 */
#[UsesClass(RlanStationsUpdateCommand::class)]
class RlanStationsUpdateCommandTest extends TestCase
{
    use ConfigureTestTrait;
    use ConsoleIntegrationTestTrait;
    use EmailTrait;
    use HttpClientTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.AppUsers',
        'app.RlanStations',
    ];

    /**
     * Readings this test wrote down, to be taken away again.
     *
     * @var array<string>
     */
    private array $written = [];

    /**
     * setUp method
     *
     * @return void
     */
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        // The run reports a failure by mail, so a test of the failing path needs somebody to
        // report it to.
        $this->withConfigure([
            'Report.errorEmails' => ['nobody@example.com'],
            'Rlan.url' => 'https://register.example.com/api/v1',
            'Rlan.email' => 'somebody@example.com',
            'Rlan.password' => 'not the real one',
        ]);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    #[Override]
    protected function tearDown(): void
    {
        foreach ($this->written as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
        $this->written = [];
        $this->restoreConfigure();

        parent::tearDown();
    }

    /**
     * The options are named where somebody looking for them would look.
     *
     * @return void
     * @link \App\Command\RlanStationsUpdateCommand::buildOptionParser()
     */
    public function testBuildOptionParser(): void
    {
        $this->exec('rlan_stations_update --help');

        $this->assertExitSuccess();
        $this->assertOutputContains('--file');
    }

    /**
     * Called with nothing at all, which is how a scheduler calls it, it signs in with what the
     * installation is configured with. This is the path that actually runs.
     *
     * @return void
     * @link \App\Command\RlanStationsUpdateCommand::execute()
     */
    public function testExecuteWithoutArgumentsReadsTheRegister(): void
    {
        $this->mockClientPost(
            'https://register.example.com/api/v1/user/login',
            $this->jsonResponse(['status' => 200, 'data' => [
                'id' => 329,
                'access_token' => ['token' => 'a token', 'expiration' => time() + 3600],
            ]]),
        );
        $this->mockClientGet(
            'https://register.example.com/api/v1/station/all-my-stations',
            $this->jsonResponse(['status' => 200, 'data' => [
                '9100' => ['id' => 9100, 'n' => 'Read from the register', 'u' => 329],
            ]]),
        );

        $this->exec('rlan_stations_update');

        $this->assertExitSuccess();
        $this->assertOutputContains('1');

        $this->assertSame(
            1,
            $this->getTableLocator()->get('RlanStations')->find()->where(['station_id' => 9100])->count(),
        );
    }

    /**
     * A reading that was kept is replayed instead of asking the register.
     *
     * @return void
     * @link \App\Command\RlanStationsUpdateCommand::execute()
     */
    public function testExecuteWithAKeptReading(): void
    {
        $file = $this->reading(['status' => 200, 'data' => [
            '9100' => ['id' => 9100, 'n' => 'Replayed'],
        ]]);

        $this->exec('rlan_stations_update --file ' . $file);

        $this->assertExitSuccess();

        $station = $this->getTableLocator()->get('RlanStations')
            ->find()->where(['station_id' => 9100])->firstOrFail();

        $this->assertSame('Replayed', $station->get('name'));
    }

    /**
     * A kept reading may carry the technical parameters that went with it.
     *
     * @return void
     * @link \App\Command\RlanStationsUpdateCommand::execute()
     */
    public function testAKeptReadingMayCarryTheParameters(): void
    {
        $file = $this->reading([
            'stations' => ['status' => 200, 'data' => [
                '9100' => ['id' => 9100, 'n' => 'Replayed with parameters'],
            ]],
            'parameters' => [
                ['status' => 200, 'data' => [
                    '9100' => ['id' => 9100, 'power' => '10.00'],
                ]],
            ],
        ]);

        $this->exec('rlan_stations_update --file ' . $file);

        $this->assertExitSuccess();

        $station = $this->getTableLocator()->get('RlanStations')
            ->find()->where(['station_id' => 9100])->firstOrFail();

        $this->assertSame('10.00', $station->get('power'));
    }

    /**
     * A register that cannot be reached leaves the mirror as it was, and says so by mail.
     *
     * @return void
     * @link \App\Command\RlanStationsUpdateCommand::execute()
     */
    public function testExecuteLeavesTheMirrorAloneWhenTheRegisterCannotBeRead(): void
    {
        $this->mockClientPost(
            'https://register.example.com/api/v1/user/login',
            $this->jsonResponse(['status' => 500, 'error' => 'something went wrong'], 500),
        );

        $this->exec('rlan_stations_update');

        $this->assertExitError();
        $this->assertMailCount(1);

        // The station the fixture holds is still there.
        $this->assertSame(
            1,
            $this->getTableLocator()->get('RlanStations')->find()->where(['station_id' => 8039])->count(),
        );
    }

    /**
     * A register naming nothing at all is a reading that went wrong, not a register that has
     * emptied - so the mirror is left alone rather than swept.
     *
     * @return void
     * @link \App\Command\RlanStationsUpdateCommand::execute()
     */
    public function testARegisterNamingNothingEmptiesNothing(): void
    {
        $file = $this->reading(['status' => 200, 'data' => []]);

        $this->exec('rlan_stations_update --file ' . $file);

        $this->assertExitError();
        $this->assertMailCount(1);
        $this->assertSame(1, $this->getTableLocator()->get('RlanStations')->find()->count());
    }

    /**
     * A file that is not a reading is refused rather than read as an empty one.
     *
     * @return void
     * @link \App\Command\RlanStationsUpdateCommand::execute()
     */
    public function testAFileThatIsNotAReadingIsRefused(): void
    {
        $file = TMP . 'rlan-stations-' . uniqid() . '.json';
        file_put_contents($file, 'not json at all');
        $this->written[] = $file;

        $this->exec('rlan_stations_update --file ' . $file);

        $this->assertExitError();
        $this->assertSame(1, $this->getTableLocator()->get('RlanStations')->find()->count());
    }

    /**
     * Write a reading the command can be pointed at.
     *
     * @param array<string, mixed> $reading What it is to hold.
     * @return string Path to it.
     */
    private function reading(array $reading): string
    {
        $file = TMP . 'rlan-stations-' . uniqid() . '.json';
        file_put_contents($file, (string)json_encode($reading));
        $this->written[] = $file;

        return $file;
    }

    /**
     * @param array<string, mixed> $body What the register answers with.
     * @param int $status What it answers.
     * @return \Cake\Http\Client\Response
     */
    private function jsonResponse(array $body, int $status = 200): Response
    {
        return $this->newClientResponse(
            $status,
            ['Content-Type: application/json'],
            (string)json_encode($body),
        );
    }
}
