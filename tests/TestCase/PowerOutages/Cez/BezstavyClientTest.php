<?php
declare(strict_types=1);

namespace App\Test\TestCase\PowerOutages\Cez;

use App\PowerOutages\Cez\BezstavyClient;
use Cake\Http\Client\Response;
use Cake\Http\TestSuite\HttpClientTrait;
use Cake\TestSuite\TestCase;
use Override;

/**
 * App\PowerOutages\Cez\BezstavyClient Test Case
 *
 * The waiting is asked about as much as the reading is. What is being read here is an endpoint
 * nobody promised us, behind a widget somebody else publishes, and how gently it is asked is the
 * whole of our side of that bargain - so the spacing and the backing off are worth a test each,
 * and the sleeping is handed in from outside so that asking about it costs no time.
 */
class BezstavyClientTest extends TestCase
{
    use HttpClientTrait;

    /**
     * How long each wait was, in the order they happened.
     *
     * @var list<float>
     */
    private array $waits = [];

    /**
     * setUp method
     *
     * @return void
     */
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->waits = [];
    }

    /**
     * A burst of questions goes straight out, and what follows is spaced.
     *
     * @return void
     * @link \App\PowerOutages\Cez\BezstavyClient::outagesInTown()
     */
    public function testTheFirstFewQuestionsGoStraightOut(): void
    {
        $client = $this->client();

        for ($town = 1; $town <= 6; $town++) {
            $this->mockClientGet($this->url($town), $this->jsonResponse(['outages' => null]));
            $client->outagesInTown($town);
        }

        // Four went out at once; the two after them each waited.
        $this->assertCount(2, $this->waits);
        $this->assertGreaterThan(1.0, $this->waits[0]);
    }

    /**
     * Being told exactly how long to wait is taken at its word.
     *
     * @return void
     * @link \App\PowerOutages\Cez\BezstavyClient::outagesInTown()
     */
    public function testBeingToldHowLongToWaitIsObeyed(): void
    {
        $this->mockClientGet($this->url(533165), $this->newClientResponse(429, ['Retry-After: 5']));
        $this->mockClientGet($this->url(533165), $this->jsonResponse(['outages' => null]));

        $this->assertSame(['outages' => null], $this->client()->outagesInTown(533165)->data);
        $this->assertContains(5.0, $this->waits);
    }

    /**
     * Being told off without being told how long backs off further each time.
     *
     * @return void
     * @link \App\PowerOutages\Cez\BezstavyClient::outagesInTown()
     */
    public function testBeingToldOffWithoutATimeBacksOffFurtherEachTime(): void
    {
        $this->mockClientGet($this->url(533165), $this->newClientResponse(429, []));
        $this->mockClientGet($this->url(533165), $this->newClientResponse(429, []));
        $this->mockClientGet($this->url(533165), $this->jsonResponse(['outages' => null]));

        $this->assertSame(['outages' => null], $this->client()->outagesInTown(533165)->data);
        $this->assertSame([2.0, 4.0], $this->waits);
    }

    /**
     * A municipality that will not stop saying no comes back as unanswered rather than throwing.
     *
     * The one thing that must not happen: one municipality refusing has to leave the other
     * thirty-nine answered, and an outage of that municipality standing rather than swept.
     *
     * @return void
     * @link \App\PowerOutages\Cez\BezstavyClient::outagesInTown()
     */
    public function testAMunicipalityThatKeepsRefusingIsUnanswered(): void
    {
        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->mockClientGet($this->url(533165), $this->newClientResponse(429, []));
        }

        $this->assertTrue($this->client()->outagesInTown(533165)->unanswered());
    }

    /**
     * Anything else going wrong is unanswered too.
     *
     * @return void
     * @link \App\PowerOutages\Cez\BezstavyClient::outagesInTown()
     */
    public function testSomethingGoingWrongAtTheOtherEndIsUnanswered(): void
    {
        $this->mockClientGet($this->url(533165), $this->newClientResponse(500, []));

        $this->assertTrue($this->client()->outagesInTown(533165)->unanswered());
    }

    /**
     * An answer that is not an object at all is unanswered.
     *
     * @return void
     * @link \App\PowerOutages\Cez\BezstavyClient::outagesInTown()
     */
    public function testAnAnswerThatIsNotAnObjectIsUnanswered(): void
    {
        $this->mockClientGet(
            $this->url(533165),
            $this->newClientResponse(200, ['Content-Type: application/json'], '"nonsense"'),
        );

        $this->assertTrue($this->client()->outagesInTown(533165)->unanswered());
    }

    /**
     * The ordinary answer comes back as it arrived, for whoever reads it next.
     *
     * @return void
     * @link \App\PowerOutages\Cez\BezstavyClient::outagesInTown()
     */
    public function testTheAnswerComesBackAsItArrived(): void
    {
        $this->mockClientGet(
            $this->url(533165),
            $this->jsonResponse(['outages' => null, 'outages_in_town' => [['id' => '110061112294']]]),
        );

        $this->assertSame(
            ['outages' => null, 'outages_in_town' => [['id' => '110061112294']]],
            $this->client()->outagesInTown(533165)->data,
        );
    }

    /**
     * A client that records its waiting instead of doing any.
     *
     * @return \App\PowerOutages\Cez\BezstavyClient
     */
    private function client(): BezstavyClient
    {
        return new BezstavyClient(
            'https://outages.example.com',
            'Watcher NMS (tests)',
            function (float $seconds): void {
                $this->waits[] = $seconds;
            },
        );
    }

    /**
     * @param int $townCode The municipality being asked about.
     * @return string
     */
    private function url(int $townCode): string
    {
        return 'https://outages.example.com/cezd/api/inspecttown/' . $townCode;
    }

    /**
     * @param array<string, mixed> $body What the distributor answers with.
     * @param int $status What it answers.
     * @return \Cake\Http\Client\Response
     */
    private function jsonResponse(array $body, int $status = 200): Response
    {
        return $this->newClientResponse($status, ['Content-Type: application/json'], (string)json_encode($body));
    }
}
