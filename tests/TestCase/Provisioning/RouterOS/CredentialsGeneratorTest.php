<?php
declare(strict_types=1);

namespace App\Test\TestCase\Provisioning\RouterOS;

use App\Model\Entity\RouterosDevice;
use App\Provisioning\RouterOS\CredentialsGenerator;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Provisioning\RouterOS\CredentialsGenerator Test Case
 */
#[UsesClass(CredentialsGenerator::class)]
class CredentialsGeneratorTest extends TestCase
{
    /**
     * A device that has been seen gets the fixed name the routers are provisioned under, and a
     * password of its own.
     *
     * @return void
     * @link \App\Provisioning\RouterOS\CredentialsGenerator::getUsername()
     * @link \App\Provisioning\RouterOS\CredentialsGenerator::getPassword()
     */
    public function testADeviceWithASerialNumberIsGivenCredentials(): void
    {
        $device = new RouterosDevice(['serial_number' => 'HFT08KQ4T6C']);

        $this->assertSame('admin', CredentialsGenerator::getUsername($device));
        $this->assertNotEmpty(CredentialsGenerator::getPassword($device));
    }

    /**
     * The password is derived from the serial number rather than drawn at random, so the same device
     * can be provisioned again and reached with what was set the first time.
     *
     * @return void
     * @link \App\Provisioning\RouterOS\CredentialsGenerator::getPassword()
     */
    public function testTheSameDeviceIsAlwaysGivenTheSamePassword(): void
    {
        $device = new RouterosDevice(['serial_number' => 'HFT08KQ4T6C']);

        $this->assertSame(
            CredentialsGenerator::getPassword($device),
            CredentialsGenerator::getPassword(new RouterosDevice(['serial_number' => 'HFT08KQ4T6C'])),
        );
    }

    /**
     * Two devices are not given the same password, which is the whole point of deriving it from
     * something that tells them apart.
     *
     * @return void
     * @link \App\Provisioning\RouterOS\CredentialsGenerator::getPassword()
     */
    public function testTwoDevicesAreNotGivenTheSamePassword(): void
    {
        $this->assertNotSame(
            CredentialsGenerator::getPassword(new RouterosDevice(['serial_number' => 'HFT08KQ4T6C'])),
            CredentialsGenerator::getPassword(new RouterosDevice(['serial_number' => 'HFT08KQ4T6D'])),
        );
    }

    /**
     * A device that has never reported its serial number has nothing to derive credentials from, and
     * is answered with none rather than with credentials everything without a serial number shares.
     *
     * @return void
     * @link \App\Provisioning\RouterOS\CredentialsGenerator::getUsername()
     * @link \App\Provisioning\RouterOS\CredentialsGenerator::getPassword()
     */
    public function testADeviceWithoutASerialNumberIsGivenNoCredentials(): void
    {
        $device = new RouterosDevice([]);

        $this->assertNull(CredentialsGenerator::getUsername($device));
        $this->assertNull(CredentialsGenerator::getPassword($device));
    }
}
