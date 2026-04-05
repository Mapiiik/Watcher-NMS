<?php
declare(strict_types=1);

namespace App\Provisioning\RouterOS;

use App\Model\Entity\RouterosDevice;
use Cake\Utility\Security;

/**
 * Class RouterosCredentialsGenerator
 *
 * This class generates credentials for RouterOS devices based on their serial numbers.
 * The username is fixed as 'admin', while the password is derived from the serial number
 * using a SHA-256 hash and a custom character set encoding.
 */
final class CredentialsGenerator
{
    /**
     * get RouterOS device username
     *
     * @param \App\Model\Entity\RouterosDevice $routerosDevice Entity
     * @return string|null
     * @psalm-suppress UnusedParam
     */
    public static function getUsername(RouterosDevice $routerosDevice): ?string
    {
        if (empty($routerosDevice->serial_number)) {
            return null;
        }

        return 'admin';
    }

    /**
     * get RouterOS device password
     *
     * @param \App\Model\Entity\RouterosDevice $routerosDevice Entity
     * @return string|null
     */
    public static function getPassword(RouterosDevice $routerosDevice): ?string
    {
        if (empty($routerosDevice->serial_number)) {
            return null;
        }

        $hash = Security::hash($routerosDevice->serial_number, 'sha256', true);

        return self::hexToSetString(substr($hash, 0, 20));
    }

    /**
     * Converts a hexadecimal string to a custom character set string.
     *
     * @param string $hex Input string in hexadecimal format (e.g., "1a2b3c").
     * @return string Encoded string in the custom character set.
     */
    private static function hexToSetString(string $hex): string
    {
        $chars = 'abcdefghijklmnopqrstuwvxyzABCDEFGHIJKLMNOPQRSTUWVXYZ0123456789';
        $setbase = strlen($chars);

        $answer = '';

        // Iterate until the hex string is empty
        while ($hex !== '' && $hex !== '0') {
            $hex_result = ''; // Result of division in hex
            $dec_remain = 0; // Decimal remainder

            // Divide hex by base (custom charset length)
            foreach (str_split($hex) as $char) {
                // Combine remainder with the current hex digit
                $dec_remain = $dec_remain * 16 + hexdec($char);

                // Perform integer division
                $hex_digit = (int)($dec_remain / $setbase);
                $dec_remain %= $setbase;

                // Build the new hex string (excluding leading zeros)
                $hex_result .= $hex_digit > 0 || $hex_result !== '' ? dechex($hex_digit) : '';
            }

            // Prepend the corresponding character to the answer
            $answer = $chars[$dec_remain] . $answer;

            // Update hex for the next iteration
            $hex = $hex_result;
        }

        return $answer ?: $chars[0]; // Return first char if input is zero
    }
}
