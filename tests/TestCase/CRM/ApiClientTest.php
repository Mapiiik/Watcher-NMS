<?php
declare(strict_types=1);

namespace App\Test\TestCase\CRM;

use App\CRM\ApiClient;
use App\Test\Traits\ConfigureTestTrait;
use Cake\Http\TestSuite\HttpClientTrait;
use Cake\TestSuite\TestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Http\Message\RequestInterface;

/**
 * App\CRM\ApiClient Test Case
 */
#[CoversClass(ApiClient::class)]
class ApiClientTest extends TestCase
{
    use ConfigureTestTrait;
    use HttpClientTrait;

    /**
     * setUp method
     *
     * @return void
     */
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->withConfigure([
            'Crm.url' => 'https://crm.example.com',
            'Crm.key' => 'secret',
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
        $this->restoreConfigure();

        parent::tearDown();
    }

    /**
     * The certificate is checked unless the installation says otherwise, which only a development
     * Watcher CRM with a certificate of its own has any business doing.
     *
     * @return void
     * @link \App\CRM\ApiClient::getAccessPointReferences()
     */
    public function testTheCertificateIsCheckedUnlessTurnedOff(): void
    {
        $checked = [];
        $this->mockClientGet(
            'https://crm.example.com/api/access-points/1/references.json',
            $this->newClientResponse(
                200,
                ['Content-Type: application/json'],
                (string)json_encode(['references' => ['contracts' => 2]]),
            ),
            ['match' => function (RequestInterface $request, array $options) use (&$checked): bool {
                $this->assertSame('Bearer secret', $request->getHeaderLine('Authorization'));
                $checked[] = [
                    $options['ssl_verify_peer'],
                    $options['ssl_verify_peer_name'],
                    $options['ssl_verify_host'],
                ];

                return true;
            }],
        );

        $this->withConfigure(['Crm.verifyTls' => true]);
        $this->assertTrue(ApiClient::getAccessPointReferences('1')->ok());

        $this->withConfigure(['Crm.verifyTls' => false]);
        $this->assertTrue(ApiClient::getAccessPointReferences('1')->ok());

        $this->assertSame([[true, true, true], [false, false, false]], $checked);
    }
}
