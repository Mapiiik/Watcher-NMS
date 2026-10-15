<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api\Agent;

use App\Controller\Api\Agent\ProvisionController;
use Cake\Core\Configure;
use Cake\Log\Engine\ArrayLog;
use Cake\Log\Log;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Override;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * Who a Watcher Agent has to be to push what it found.
 */
#[UsesClass(ProvisionController::class)]
class ProvisionControllerTest extends TestCase
{
    use IntegrationTestTrait;

    private const URL = '/api/agent/provision/routeros.json';

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Users',
    ];

    /**
     * @inheritDoc
     */
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        Log::drop('auth');
        Log::setConfig('auth', ['className' => ArrayLog::class, 'scopes' => ['auth']]);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    protected function tearDown(): void
    {
        Log::drop('auth');

        parent::tearDown();
    }

    /**
     * An agent signed in as an account with the role `agent` is let in. What it then sends is up
     * to the action, which turns an empty push down as a bad request rather than as a stranger.
     *
     * @return void
     */
    public function testAnAgentWithAnAccountOfItsOwnIsLetIn(): void
    {
        $this->addAccount('agent', 'agent-key');

        $this->push('agent-key');

        $this->assertResponseCode(400);
    }

    /**
     * Anybody else is turned away: a wrong token, an account of another role, and the token the
     * environment used to hold, which lets nobody in any more.
     *
     * @return void
     */
    public function testAnybodyElseIsTurnedAway(): void
    {
        Configure::write('Agent.provisionToken', 'environment-token');
        $this->addAccount('api', 'api-key');

        $this->push('wrong-token');
        $this->assertResponseCode(401);

        $this->push('api-key');
        $this->assertResponseCode(401);

        $this->push('environment-token');
        $this->assertResponseCode(401);
    }

    /**
     * Adds an account that signs in with an API key.
     *
     * @param string $role Its role.
     * @param string $key Its key.
     * @return void
     */
    private function addAccount(string $role, string $key): void
    {
        $users = $this->getTableLocator()->get('Users');
        $users->deleteAll(['1 = 1']);
        $user = $users->newEmptyEntity();
        $user->patch([
            'username' => $role . '-client',
            'password' => 'Kopr na zahrade 7x',
            'role' => $role,
            'active' => true,
            'api_token' => $key,
        ], ['guard' => false]);
        $users->saveOrFail($user);
    }

    /**
     * Pushes nothing with a Bearer token.
     *
     * @param string $token The token.
     * @return void
     */
    private function push(string $token): void
    {
        $this->configRequest(['headers' => [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ]]);
        $this->post(self::URL, '{}');
    }
}
