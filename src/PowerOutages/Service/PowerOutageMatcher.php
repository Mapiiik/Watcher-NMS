<?php
declare(strict_types=1);

namespace App\PowerOutages\Service;

use App\Model\Entity\AccessPoint;
use App\Model\Entity\AccessPointSupplyAddress;
use App\Model\Entity\PowerOutage;
use App\Model\Entity\PowerOutageScope;
use App\Model\Enum\OutageCertainty;
use App\Model\Enum\OutageMatch;
use App\PowerOutages\Dto\OutageMatchResult;
use Cake\Utility\Text;

/**
 * Deciding which of the published outages are about one of our masts.
 *
 * Nothing here talks to anybody. Everything it reads has already been written down - the addresses
 * around the mast, and where the outage reaches - which is what lets the whole set of links be
 * worked out again from the mirror without asking the distributor anything.
 *
 * There are two quite different grounds for a link and they are not worth the same. An outage that
 * came back from asking about our own supply point is about this mast and nothing else. An outage
 * found by looking at the addresses around the mast is a good guess and no more: a mast on a roof
 * is fed from the building it stands on, which may be on another street, and one in a field is fed
 * from a line the distributor lists by parcel, which is not something we look at. So the ground is
 * recorded beside the link, and what the operator is really being told is that filling in the
 * supply point turns the guessing off.
 */
final class PowerOutageMatcher
{
    /**
     * Which of the outages are about this access point.
     *
     * @param \App\Model\Entity\AccessPoint $accessPoint The mast, with the addresses around it.
     * @param iterable<\App\Model\Entity\PowerOutage> $outages The outages to consider.
     * @return array<string, \App\PowerOutages\Dto\OutageMatchResult> Keyed by the id of the outage.
     */
    public function match(AccessPoint $accessPoint, iterable $outages): array
    {
        $matches = [];

        foreach ($outages as $outage) {
            $match = $this->matchOne($accessPoint, $outage);

            if ($match !== null) {
                $matches[$outage->id] = $match;
            }
        }

        return $matches;
    }

    /**
     * Whether one outage is about this access point, and on what grounds.
     *
     * @param \App\Model\Entity\AccessPoint $accessPoint The mast, with the addresses around it.
     * @param \App\Model\Entity\PowerOutage $outage The outage to consider.
     * @return \App\PowerOutages\Dto\OutageMatchResult|null
     */
    public function matchOne(AccessPoint $accessPoint, PowerOutage $outage): ?OutageMatchResult
    {
        $certain = $this->matchBySupplyPoint($accessPoint, $outage);

        if ($certain !== null) {
            return $certain;
        }

        $best = null;

        foreach ($accessPoint->access_point_supply_addresses ?? [] as $address) {
            foreach ($outage->placesOf('streets') as $street) {
                $match = $this->matchAddressAgainstStreet($address, $street);

                if ($match !== null && $match->isBetterThan($best)) {
                    $best = $match;
                }
            }
        }

        return $best;
    }

    /**
     * The outage came back from asking about the supply point of this access point.
     *
     * @param \App\Model\Entity\AccessPoint $accessPoint The mast.
     * @param \App\Model\Entity\PowerOutage $outage The outage to consider.
     * @return \App\PowerOutages\Dto\OutageMatchResult|null
     */
    private function matchBySupplyPoint(AccessPoint $accessPoint, PowerOutage $outage): ?OutageMatchResult
    {
        $ean = trim(strval($accessPoint->electricity_ean));

        if ($ean === '') {
            return null;
        }

        $wanted = PowerOutageScope::forEan($ean);

        foreach ($outage->power_outage_scopes ?? [] as $scope) {
            if ($scope->scope === $wanted) {
                return new OutageMatchResult(OutageCertainty::Certain, OutageMatch::Ean);
            }
        }

        return null;
    }

    /**
     * Whether one address near the mast is on one street the outage reaches.
     *
     * @param \App\Model\Entity\AccessPointSupplyAddress $address One address near the mast.
     * @param array<string, mixed> $street One street the outage reaches.
     * @return \App\PowerOutages\Dto\OutageMatchResult|null
     */
    private function matchAddressAgainstStreet(
        AccessPointSupplyAddress $address,
        array $street,
    ): ?OutageMatchResult {
        if (!$this->isSameTown($address, $street) || !$this->isSameStreet($address, $street)) {
            return null;
        }

        $covered = $this->coversTheAddress($address, $street);

        if ($covered === false) {
            // The distributor wrote down which buildings on this street go dark, and ours is not
            // among them. That is the one thing said here that rules a mast out rather than in.
            return null;
        }

        $written = $this->writeAddress($address);

        if ($covered === null) {
            // Either the whole street goes dark, or the numbers were written in a way nothing here
            // can read. The link stands, and says out loud that it rests on the street alone.
            return new OutageMatchResult(
                OutageCertainty::Probable,
                OutageMatch::Street,
                __('{0}, street only', $written),
                $address->distance_metres,
                $address->id,
            );
        }

        return new OutageMatchResult(
            OutageCertainty::Probable,
            OutageMatch::Address,
            $written,
            $address->distance_metres,
            $address->id,
        );
    }

    /**
     * Whether the two are in one municipality.
     *
     * By the number the registry keeps it under wherever the outage carries one, because two
     * spellings of one name are far likelier than two municipalities under one number. The reading
     * made by supply point carries no number, so there the names are compared instead.
     *
     * @param \App\Model\Entity\AccessPointSupplyAddress $address One address near the mast.
     * @param array<string, mixed> $street One street the outage reaches.
     * @return bool
     */
    private function isSameTown(AccessPointSupplyAddress $address, array $street): bool
    {
        $townCode = $street['town_code'] ?? null;

        if (is_int($townCode) && $address->town_code !== null) {
            return $townCode === $address->town_code;
        }

        return $this->isSameName($address->town_name, $street['town'] ?? null);
    }

    /**
     * Whether the two are on one street.
     *
     * @param \App\Model\Entity\AccessPointSupplyAddress $address One address near the mast.
     * @param array<string, mixed> $street One street the outage reaches.
     * @return bool
     */
    private function isSameStreet(AccessPointSupplyAddress $address, array $street): bool
    {
        return $this->isSameName($address->street_name, $street['street'] ?? null);
    }

    /**
     * Whether the outage names the building this address is.
     *
     * True where it names it, false where it names the buildings on the street and ours is not one
     * of them, and null where it names the street without saying which buildings - or names them
     * in a way nothing here can read.
     *
     * The three kinds of number are asked separately, because the distributor writes them
     * separately: a street listed by its house numbers says nothing about the buildings on it that
     * carry a registration number instead.
     *
     * @param \App\Model\Entity\AccessPointSupplyAddress $address One address near the mast.
     * @param array<string, mixed> $street One street the outage reaches.
     * @return bool|null
     */
    private function coversTheAddress(AccessPointSupplyAddress $address, array $street): ?bool
    {
        $houseNumbers = trim(strval($street['house_nums'] ?? ''));
        $registrationNumbers = trim(strval($street['ev_nums'] ?? ''));
        $orientationNumbers = trim(strval($street['street_nums'] ?? ''));

        if ($houseNumbers === '' && $registrationNumbers === '' && $orientationNumbers === '') {
            // The whole street, as far as anybody here can tell.
            return null;
        }

        $ours = $address->number_type === AccessPointSupplyAddress::NUMBER_TYPE_REGISTRATION
            ? $registrationNumbers
            : $houseNumbers;

        if ($ours === '' && $orientationNumbers === '') {
            // The outage writes out which buildings go dark, but names none of the kind this
            // address carries: a street listed by its house numbers is saying nothing about the
            // building beside them that carries a registration number instead.
            return false;
        }

        $orientation = $address->orientationNumberWritten();

        $byNumber = $ours === '' || $address->house_number === null
            ? null
            : $this->covers($ours, strval($address->house_number));

        $byOrientation = $orientationNumbers === '' || $orientation === null
            ? null
            : $this->covers($orientationNumbers, $orientation);

        if ($byNumber === true || $byOrientation === true) {
            return true;
        }

        // A list that could be read and does not name us rules the mast out. One that could not be
        // read leaves the link standing on the street, weakened rather than thrown away.
        return $byNumber === false || $byOrientation === false ? false : null;
    }

    /**
     * Whether a list of building numbers takes in one of ours.
     *
     * Null where the field is empty or nothing in it could be read, so that a listing written in
     * some way nobody expected weakens the link rather than quietly ruling the mast out.
     *
     * @param string $field The numbers as the distributor wrote them.
     * @param string $ours The number of our address.
     * @return bool|null
     */
    private function covers(string $field, string $ours): ?bool
    {
        $read = false;
        $ours = mb_strtolower(trim($ours));

        foreach (explode(',', $field) as $token) {
            $token = mb_strtolower(trim($token));

            if ($token === '') {
                continue;
            }

            // A span of house numbers, which is how a run of them along one side is written.
            if (preg_match('/^(\d+)\s*-\s*(\d+)$/', $token, $span) === 1 && ctype_digit($ours)) {
                $read = true;

                if ((int)$ours >= (int)$span[1] && (int)$ours <= (int)$span[2]) {
                    return true;
                }

                continue;
            }

            if (ctype_digit($token) && ctype_digit($ours)) {
                $read = true;

                if ((int)$token === (int)$ours) {
                    return true;
                }

                continue;
            }

            // A number with a letter on it only ever matches the same thing written out.
            if (preg_match('/^\d+[a-z]?$/u', $token) === 1) {
                $read = true;

                if ($token === $ours) {
                    return true;
                }
            }
        }

        return $read ? false : null;
    }

    /**
     * The address as it goes into the note beside the link.
     *
     * @param \App\Model\Entity\AccessPointSupplyAddress $address One address near the mast.
     * @return string
     */
    private function writeAddress(AccessPointSupplyAddress $address): string
    {
        $written = trim(strval($address->formatted_address));

        if ($written === '') {
            $written = trim(implode(' ', array_filter([
                strval($address->street_name),
                strval($address->house_number),
            ])));
        }

        if ($written === '') {
            $written = __('address not given');
        }

        return $address->distance_metres === null
            ? $written
            : __('{0} ({1} m)', $written, $address->distance_metres);
    }

    /**
     * Whether two names of a place are the same name.
     *
     * Compared without regard to case, and with the marks taken off as a second try: both sides
     * come from the same national registry, but a name arriving stripped of its marks is far more
     * likely than two different streets meeting this way.
     *
     * @param string|null $ours What the registry called it.
     * @param mixed $theirs What the distributor called it.
     * @return bool
     */
    private function isSameName(?string $ours, mixed $theirs): bool
    {
        if (!is_string($theirs)) {
            return false;
        }

        $ours = mb_strtolower(trim(strval($ours)));
        $theirs = mb_strtolower(trim($theirs));

        if ($ours === '' || $theirs === '') {
            return false;
        }

        return $ours === $theirs || $this->withoutMarks($ours) === $this->withoutMarks($theirs);
    }

    /**
     * The same name with the marks taken off its letters.
     *
     * @param string $name The name to strip.
     * @return string
     */
    private function withoutMarks(string $name): string
    {
        return mb_strtolower(Text::transliterate($name));
    }
}
