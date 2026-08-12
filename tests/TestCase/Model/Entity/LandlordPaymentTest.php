<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\AccessPoint;
use App\Model\Entity\LandlordPayment;
use App\Model\Entity\PaymentPurpose;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Model\Entity\LandlordPayment Test Case
 */
#[UsesClass(LandlordPayment::class)]
class LandlordPaymentTest extends TestCase
{
    /**
     * A payment is told apart by the access point it is paid for and what it is paid for.
     *
     * @return void
     * @link \App\Model\Entity\LandlordPayment::_getName()
     */
    public function testAPaymentIsNamedByItsAccessPointAndPurpose(): void
    {
        $payment = new LandlordPayment([
            'access_point' => new AccessPoint([
                'name' => 'Jablonec - water tower',
            ]),
            'payment_purpose' => new PaymentPurpose([
                'name' => 'Rent',
            ]),
        ]);

        $this->assertSame('Jablonec - water tower - Rent', $payment->name);
    }

    /**
     * A payment whose purpose has not been filled in leaves no separator hanging behind the access
     * point.
     *
     * @return void
     * @link \App\Model\Entity\LandlordPayment::_getName()
     */
    public function testAPaymentWithoutAPurposeHasNoTrailingSeparator(): void
    {
        $payment = new LandlordPayment([
            'access_point' => new AccessPoint([
                'name' => 'Jablonec - water tower',
            ]),
        ]);

        $this->assertSame('Jablonec - water tower', $payment->name);
    }

    /**
     * A payment not tied to an access point is not preceded by a separator either.
     *
     * @return void
     * @link \App\Model\Entity\LandlordPayment::_getName()
     */
    public function testAPaymentWithoutAnAccessPointHasNoLeadingSeparator(): void
    {
        $payment = new LandlordPayment([
            'payment_purpose' => new PaymentPurpose([
                'name' => 'Rent',
            ]),
        ]);

        $this->assertSame('Rent', $payment->name);
    }
}
