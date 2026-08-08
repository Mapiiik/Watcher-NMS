<?php
declare(strict_types=1);

namespace App\Test\TestCase\Provisioning\RouterOS;

use App\Model\Entity\DeviceType;
use App\Model\Entity\RouterosDevice;
use App\Provisioning\RouterOS\CredentialsGenerator;
use App\Provisioning\RouterOS\ProvisionScriptBuilder;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Provisioning\RouterOS\ProvisionScriptBuilder Test Case
 *
 * What the builder writes is handed to a router to run, so what is held on to here is which commands
 * end up in it - and, where the credentials cannot be worked out, that it says so rather than
 * sending a command with nothing in it.
 */
#[UsesClass(ProvisionScriptBuilder::class)]
class ProvisionScriptBuilderTest extends TestCase
{
    /**
     * Serial number the credentials are derived from.
     *
     * @var string
     */
    private const SERIAL_NUMBER = 'HFT08KQ4T6C';

    /**
     * A device type that does not ask for a password of its own is sent nothing to run.
     *
     * @return void
     * @link \App\Provisioning\RouterOS\ProvisionScriptBuilder::build()
     */
    public function testADeviceTypeThatAsksForNothingIsSentNothing(): void
    {
        $script = (new ProvisionScriptBuilder())->build(
            new RouterosDevice(['serial_number' => self::SERIAL_NUMBER]),
            new DeviceType(['automatically_set_a_unique_password' => false]),
        );

        $this->assertSame('', trim($script));
    }

    /**
     * A device type that asks for a password of its own is sent the commands that set one, under the
     * name the credentials are generated for.
     *
     * @return void
     * @link \App\Provisioning\RouterOS\ProvisionScriptBuilder::build()
     */
    public function testADeviceTypeThatAsksForAPasswordIsSentTheCommandsThatSetOne(): void
    {
        $device = new RouterosDevice(['serial_number' => self::SERIAL_NUMBER]);

        $script = (new ProvisionScriptBuilder())->build(
            $device,
            new DeviceType(['automatically_set_a_unique_password' => true]),
        );

        $user = CredentialsGenerator::getUsername($device);
        $password = CredentialsGenerator::getPassword($device);

        $this->assertStringContainsString('/user', $script);
        $this->assertStringContainsString('add name="' . $user . '" group=full password="' . $password . '"', $script);
        $this->assertStringContainsString('set [find name="' . $user . '"] group=full password="', $script);
    }

    /**
     * The script covers a router that has the user already as well as one that does not, because
     * which of the two is being provisioned is only known once it runs.
     *
     * @return void
     * @link \App\Provisioning\RouterOS\ProvisionScriptBuilder::build()
     */
    public function testTheScriptCoversARouterThatHasTheUserAlreadyAndOneThatDoesNot(): void
    {
        $script = (new ProvisionScriptBuilder())->build(
            new RouterosDevice(['serial_number' => self::SERIAL_NUMBER]),
            new DeviceType(['automatically_set_a_unique_password' => true]),
        );

        $this->assertStringContainsString('do={', $script);
        $this->assertStringContainsString('} else={', $script);
    }

    /**
     * A device whose credentials cannot be worked out is sent a line saying so rather than a command
     * that would set an empty password on it.
     *
     * @return void
     * @link \App\Provisioning\RouterOS\ProvisionScriptBuilder::build()
     */
    public function testADeviceWithoutCredentialsIsSentAComplaintRatherThanAnEmptyPassword(): void
    {
        $script = (new ProvisionScriptBuilder())->build(
            new RouterosDevice([]),
            new DeviceType(['automatically_set_a_unique_password' => true]),
        );

        $this->assertStringContainsString('Skipping user provisioning', $script);
        $this->assertStringNotContainsString('password=""', $script);
        $this->assertStringNotContainsString('/user', $script);
    }
}
