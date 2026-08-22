<?php
declare(strict_types=1);

namespace App\Model\Rule;

use App\CRM\ApiClient as CRMApiClient;
use Cake\Datasource\EntityInterface;

/**
 * A place of the network may only go once nothing in the customer relationship management names it.
 *
 * The records over there name a place by its number and nothing holds them to it, so letting a
 * place go here leaves them pointing at nothing - and the operator who finds out is the one who
 * opens a contract months later, not the one who pressed delete.
 *
 * The refusal is the other way round from what the same pair does when a record is saved. A save
 * that cannot be checked goes through, because an operator with a job to write down should not
 * wait for a system that is down; a delete that cannot be checked does not, because it is what
 * cannot be taken back.
 */
final class NotReferencedInCrmRule
{
    /**
     * @param \Cake\Datasource\EntityInterface $entity The place being let go.
     * @param array<string, mixed> $options Options the rules checker was given.
     * @return string|bool
     */
    public function __invoke(EntityInterface $entity, array $options): bool|string
    {
        $answer = CRMApiClient::getAccessPointReferences((string)$entity->get('id'));

        // an installation that was never given one has nothing over there to leave pointing nowhere
        if (!$answer->asked) {
            return true;
        }

        if (!$answer->ok()) {
            return __(
                'Watcher CRM could not be asked whether anything there still stands on this access'
                . ' point. Please, try again.',
            );
        }

        $standing = array_sum($answer->data);

        if ($standing === 0) {
            return true;
        }

        return __n(
            'Watcher CRM still holds {0} record naming this access point. Move it elsewhere first.',
            'Watcher CRM still holds {0} records naming this access point. Move them elsewhere first.',
            $standing,
            $standing,
        );
    }
}
