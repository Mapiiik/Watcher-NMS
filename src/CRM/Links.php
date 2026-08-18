<?php
declare(strict_types=1);

namespace App\CRM;

use Cake\Core\Configure;

/**
 * Where a thing of the customer relationship management's is to be found.
 *
 * The paths belong to the other application and change with it, so they are written here once
 * rather than wherever a link happens to be wanted. Nothing is offered when this installation has
 * no such application to point at - a caller that gets nothing shows the plain number instead,
 * which is the honest answer. Building the path regardless would point the link back at this
 * application, at an address that does not exist here.
 */
class Links
{
    /**
     * The application itself.
     */
    public static function home(): ?string
    {
        return self::to('');
    }

    /**
     * Whatever the other application answered with, which hands out its own paths.
     */
    public static function path(string $path): ?string
    {
        return self::to('/' . ltrim($path, '/'));
    }

    /**
     * The listing of customers, narrowed to whoever carries this number.
     *
     * A number is all this application keeps - of a customer or of a contract alike - so it asks
     * to be searched for rather than pointing at a record it cannot name.
     */
    public static function search(string $number): ?string
    {
        return self::to('/customers?search=' . rawurlencode($number));
    }

    /**
     * Puts a path behind the address of the other application.
     */
    private static function to(string $path): ?string
    {
        $url = Configure::read('Crm.url');

        return is_string($url) && $url !== '' ? rtrim($url, '/') . $path : null;
    }
}
