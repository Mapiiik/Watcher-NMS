<?php
declare(strict_types=1);

namespace App\Test\TestCase\PowerOutages\Cez;

use App\PowerOutages\Cez\DipClient;
use Cake\Http\Client\Response;
use Cake\Http\TestSuite\HttpClientTrait;
use Cake\TestSuite\TestCase;

/**
 * App\PowerOutages\Cez\DipClient Test Case
 */
class DipClientTest extends TestCase
{
    use HttpClientTrait;

    /**
     * Where the portal answers, as far as these tests are concerned.
     */
    private const URL = 'https://portal.example.com/shutdown-search';

    /**
     * The ordinary answer comes back as it arrived, for whoever reads it next.
     *
     * @return void
     * @link \App\PowerOutages\Cez\DipClient::outagesAtSupplyPoint()
     */
    public function testTheAnswerComesBackAsItArrived(): void
    {
        $body = ['data' => [['number' => '110061107633']], 'statusCode' => 200];
        $this->mockClientPost(self::URL, $this->jsonResponse($body));

        $this->assertSame($body, $this->client()->outagesAtSupplyPoint('859182400000001231')->data);
    }

    /**
     * A supply point the portal has never heard of is answered, and answered with nothing.
     *
     * Worth telling apart from a question that failed: it means the supply point has no outage,
     * not that we failed to find out.
     *
     * @return void
     * @link \App\PowerOutages\Cez\DipClient::outagesAtSupplyPoint()
     */
    public function testASupplyPointWithNothingPlannedIsStillAnAnswer(): void
    {
        $this->mockClientPost(self::URL, $this->jsonResponse(['data' => [], 'statusCode' => 200]));

        $this->assertSame(
            ['data' => [], 'statusCode' => 200],
            $this->client()->outagesAtSupplyPoint('859182400000001231')->data,
        );
    }

    /**
     * The portal reports what it thinks of a question inside the answer rather than in the status.
     *
     * @return void
     * @link \App\PowerOutages\Cez\DipClient::outagesAtSupplyPoint()
     */
    public function testAnAnswerSayingItWentWrongIsUnanswered(): void
    {
        $this->mockClientPost(self::URL, $this->jsonResponse(['data' => [], 'statusCode' => 500]));

        $this->assertTrue($this->client()->outagesAtSupplyPoint('859182400000001231')->unanswered());
    }

    /**
     * Something going wrong at the other end is unanswered.
     *
     * @return void
     * @link \App\PowerOutages\Cez\DipClient::outagesAtSupplyPoint()
     */
    public function testSomethingGoingWrongAtTheOtherEndIsUnanswered(): void
    {
        $this->mockClientPost(self::URL, $this->newClientResponse(503, []));

        $this->assertTrue($this->client()->outagesAtSupplyPoint('859182400000001231')->unanswered());
    }

    /**
     * With nowhere configured to ask, nothing is asked.
     *
     * @return void
     * @link \App\PowerOutages\Cez\DipClient::outagesAtSupplyPoint()
     */
    public function testWithNowhereToAskNothingIsAsked(): void
    {
        $client = new DipClient('', '', function (): void {
        });

        $answer = $client->outagesAtSupplyPoint('859182400000001231');

        // nobody asked, which is not the same as having asked and got nothing back
        $this->assertFalse($answer->asked);
        $this->assertFalse($answer->unanswered());
    }

    /**
     * A client that records its waiting instead of doing any.
     *
     * @return \App\PowerOutages\Cez\DipClient
     */
    private function client(): DipClient
    {
        return new DipClient(self::URL, 'Watcher NMS (tests)', function (): void {
        });
    }

    /**
     * @param array<string, mixed> $body What the portal answers with.
     * @return \Cake\Http\Client\Response
     */
    private function jsonResponse(array $body): Response
    {
        return $this->newClientResponse(200, ['Content-Type: application/json'], (string)json_encode($body));
    }
}
