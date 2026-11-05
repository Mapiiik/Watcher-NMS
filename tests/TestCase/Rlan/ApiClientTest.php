<?php
declare(strict_types=1);

namespace App\Test\TestCase\Rlan;

use App\Rlan\ApiClient;
use Cake\Cache\Cache;
use Cake\Http\Client\Response;
use Cake\Http\TestSuite\HttpClientTrait;
use Cake\TestSuite\TestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Http\Message\RequestInterface;

/**
 * App\Rlan\ApiClient Test Case
 */
#[CoversClass(ApiClient::class)]
class ApiClientTest extends TestCase
{
    use HttpClientTrait;

    /**
     * Where the register answers in these tests.
     */
    private const URL = 'https://rlan.example.com';

    /**
     * setUp method
     *
     * @return void
     */
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        Cache::clear();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    #[Override]
    protected function tearDown(): void
    {
        Cache::clear();

        parent::tearDown();
    }

    /**
     * A token that is refused is thrown out for good. Kept between runs, it would come straight
     * back from where it was kept, be refused again, and the reading would fail until it aged out.
     *
     * @return void
     * @link \App\Rlan\ApiClient::myStations()
     */
    public function testARefusedTokenIsNotTakenBackFromTheCache(): void
    {
        $this->mockClientPost(self::URL . '/user/login', $this->signedIn('first'));

        $presented = [];
        $this->mockClientGet(
            self::URL . '/station/all-my-stations',
            $this->answer(200, ['data' => []]),
            ['match' => function (RequestInterface $request) use (&$presented): bool {
                $presented[] = $request->getHeaderLine('access-token');

                return true;
            }],
        );

        $this->assertTrue($this->client()->myStations()->ok());

        // The next run holds the token in the cache, and the register no longer takes it.
        $this->cleanupMockResponses();
        $this->mockClientGet(
            self::URL . '/station/all-my-stations',
            $this->answer(401, []),
            ['match' => fn(RequestInterface $request): bool => $request->getHeaderLine('access-token') === 'first'],
        );
        $this->mockClientPost(self::URL . '/user/login', $this->signedIn('second'));
        $this->mockClientGet(
            self::URL . '/station/all-my-stations',
            $this->answer(200, ['data' => []]),
            ['match' => fn(RequestInterface $request): bool => $request->getHeaderLine('access-token') === 'second'],
        );

        $this->assertTrue($this->client()->myStations()->ok(), 'The refused token came back.');
    }

    /**
     * The register turning the sign-in away is an answer like any other, and the caller says what
     * it is worth.
     *
     * @return void
     * @link \App\Rlan\ApiClient::myStations()
     */
    public function testARefusedSignInIsAnAnswer(): void
    {
        $this->mockClientPost(self::URL . '/user/login', $this->answer(403, []));

        $answer = $this->client()->myStations();

        $this->assertFalse($answer->ok());
        $this->assertTrue($answer->unanswered());
    }

    /**
     * An installation without the register was not asked anything, which is not a failure.
     *
     * @return void
     * @link \App\Rlan\ApiClient::myStations()
     */
    public function testWithoutAConfigurationNothingIsAsked(): void
    {
        $answer = (new ApiClient('', '', ''))->myStations();

        $this->assertFalse($answer->ok());
        $this->assertFalse($answer->unanswered());
    }

    /**
     * @return \App\Rlan\ApiClient
     */
    private function client(): ApiClient
    {
        return new ApiClient(self::URL, 'stations@example.com', 'secret');
    }

    /**
     * What the register answers to signing in.
     *
     * @param string $token The token it hands over.
     * @return \Cake\Http\Client\Response
     */
    private function signedIn(string $token): Response
    {
        return $this->answer(200, ['data' => [
            'id' => 1,
            'access_token' => ['token' => $token, 'expiration' => time() + 86400],
        ]]);
    }

    /**
     * @param int $status The status.
     * @param array<string, mixed> $body What it says.
     * @return \Cake\Http\Client\Response
     */
    private function answer(int $status, array $body): Response
    {
        return $this->newClientResponse($status, ['Content-Type: application/json'], (string)json_encode($body));
    }
}
