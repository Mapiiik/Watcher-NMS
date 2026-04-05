<?php
declare(strict_types=1);

namespace App\Provisioning\RouterOS;

use App\Model\Entity\DeviceType;
use App\Model\Entity\RouterosDevice;

/**
 * Class RouterosProvisionScriptBuilder
 *
 * This class is responsible for building a provisioning script for RouterOS devices based on the device information
 * and device type settings. The script includes commands to set a unique password for the admin user if the
 * device type is configured to do so.
 */
final class ProvisionScriptBuilder
{
    /**
     * Builds a RouterOS provisioning script based on the provided device and device type.
     *
     * The script includes logging statements for each step of the provisioning process.
     * If the device type is configured to automatically set a unique password, the script
     * will include commands to create or update the admin user with the generated credentials.
     *
     * @param \App\Model\Entity\RouterosDevice $device The RouterOS device entity containing device information.
     * @param \App\Model\Entity\DeviceType $deviceType The device type entity containing provisioning settings.
     * @return string The generated RouterOS provisioning script.
     */
    public function build(
        RouterosDevice $device,
        DeviceType $deviceType,
    ): string {
        $script = [];

        if ($deviceType->automatically_set_a_unique_password) {
            $script[] = ':log warning "Watcher NMS: The unique password should be set automatically.'
                . ' Sending configuration."';
            $script[] = '';
            $script[] = $this->userBlock($device);
        }

        return implode("\n", array_filter($script));
    }

    /**
     * Generates the user provisioning block for the RouterOS script.
     *
     * This block checks if the admin user exists and either creates it with the generated password
     * or updates the existing user's password. If credentials cannot be generated, it logs an error
     * and skips user provisioning.
     *
     * @param \App\Model\Entity\RouterosDevice $device The RouterOS device entity for which to generate credentials.
     * @return string The user provisioning block of the script.
     */
    private function userBlock(RouterosDevice $device): string
    {
        $user = CredentialsGenerator::getUsername($device);
        $pass = CredentialsGenerator::getPassword($device);

        if (!$user || !$pass) {
            return ':log error "Watcher NMS: Unable to generate credentials. Skipping user provisioning."';
        }

        return implode("\n", [
            '/user',
            ':if ([:len [find name="' . $user . '"]] = 0) do={',
            '    :log warning "Watcher NMS: Adding ' . $user . ' user"',
            '    add name="' . $user . '" group=full password="' . $pass . '"',
            '} else={',
            '    :log warning "Watcher NMS: Updating ' . $user . ' user"',
            '    set [find name="' . $user . '"] group=full password="' . $pass . '"',
            '}',
        ]);
    }
}
