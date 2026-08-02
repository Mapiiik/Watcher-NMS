<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Controller\Api\IpAddressRangesController;
use App\Test\Traits\ControllerTestTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Controller\Api\IpAddressRangesController Test Case
 *
 * Smoke tests: each endpoint is called once and has to answer with the JSON it promises. The write
 * endpoints report their outcome in a `message` rather than in the status code, so what is
 * asserted is that they answered at all - whether the record itself went through is the model's
 * business.
 */
#[UsesClass(IpAddressRangesController::class)]
class IpAddressRangesControllerTest extends TestCase
{
    use ControllerTestTrait;
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.AppUsers',
        'app.AccessPointTypes',
        'app.AccessPoints',
        'app.IpAddressRanges',
    ];

    /**
     * The collection serializes.
     *
     * @return void
     * @link \App\Controller\Api\IpAddressRangesController::index()
     */
    public function testIndex(): void
    {
        $this->login();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->get('/api/ip-address-ranges.json');

        $this->assertResponseOk();
        $this->assertResponseContains('"ipAddressRanges"');
    }

    /**
     * The search endpoint answers with the same collection, narrowed down.
     *
     * @return void
     * @link \App\Controller\Api\IpAddressRangesController::search()
     */
    public function testSearch(): void
    {
        $this->login();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->get('/api/ip-address-ranges/search.json');

        $this->assertResponseOk();
        $this->assertResponseContains('"ipAddressRanges"');
    }

    /**
     * A single record serializes.
     *
     * @return void
     * @link \App\Controller\Api\IpAddressRangesController::view()
     */
    public function testView(): void
    {
        $this->login();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->get('/api/ip-address-ranges/' . $this->firstId('IpAddressRanges') . '.json');

        $this->assertResponseOk();
        $this->assertResponseContains('"ipAddressRange"');
    }

    /**
     * The endpoint takes a new record and reports the outcome.
     *
     * @return void
     * @link \App\Controller\Api\IpAddressRangesController::add()
     */
    public function testAdd(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->post('/api/ip-address-ranges.json', ['name' => 'Smoke test']);

        $this->assertResponseOk();
        $this->assertResponseContains('"message"');
    }

    /**
     * The endpoint takes a change to an existing record and reports the outcome.
     *
     * @return void
     * @link \App\Controller\Api\IpAddressRangesController::edit()
     */
    public function testEdit(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->patch('/api/ip-address-ranges/' . $this->firstId('IpAddressRanges') . '.json', ['name' => 'Smoke test']);

        $this->assertResponseOk();
        $this->assertResponseContains('"message"');
    }

    /**
     * The endpoint runs the delete and reports the outcome. Whether the record really goes depends
     * on what else still references it.
     *
     * @return void
     * @link \App\Controller\Api\IpAddressRangesController::delete()
     */
    public function testDelete(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->delete('/api/ip-address-ranges/' . $this->firstId('IpAddressRanges') . '.json');

        $this->assertResponseOk();
        $this->assertResponseContains('"message"');
    }
}
