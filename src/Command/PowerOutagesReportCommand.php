<?php
declare(strict_types=1);

namespace App\Command;

use App\Model\Table\AccessPointPowerOutagesTable;
use App\Service\OperatorReport;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\I18n\DateTime;
use Cake\Log\Log;
use Cake\Mailer\Mailer;
use Exception;
use Override;
use Settings\Utility\Settings;
use Throwable;

/**
 * Tells the operators which of our masts are about to lose power.
 *
 * Meant to be run unattended on the mornings somebody is there to act on it. When there is nothing
 * coming it says so and sends nothing: a daily report that arrives every day whether or not it has
 * anything to say is a daily report people set a rule to file away unread.
 */
class PowerOutagesReportCommand extends Command
{
    /**
     * Set available arguments
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to update
     * @return \Cake\Console\ConsoleOptionParser
     */
    #[Override]
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser
            ->setDescription('Report the planned outages coming up over our access points.')
            ->addArgument('emails', [
                'help' => 'list of emails for sending the report',
                'required' => false,
            ]);

        return $parser;
    }

    /**
     * Start the Command
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int|null|void The exit code or null for success
     */
    #[Override]
    public function execute(Arguments $args, ConsoleIo $io)
    {
        try {
            // named on the command line for a one-off, otherwise whoever is configured to be told
            $emails = $args->getArgument('emails');
            $recipients = $emails === null
                ? OperatorReport::recipients()
                : (preg_split('/\s+/', trim($emails), -1, PREG_SPLIT_NO_EMPTY) ?: []);

            $withinDays = (int)Settings::get('core.access_points.power_outages.report_within_days', 14);
            $links = $this->upcoming($withinDays);

            if ($links === []) {
                Log::write('debug', 'No planned outage is coming up over any of our access points.');
                $io->success(__('No planned outage is coming up over any of our access points.'));

                return static::CODE_SUCCESS;
            }

            $io->helper('Table')->output($this->asTable($links));

            if ($recipients === []) {
                $io->warning(__('Nobody is configured to send the report to, so it stays here.'));

                return static::CODE_SUCCESS;
            }

            $this->send($recipients, $links, $withinDays, $io);

            return static::CODE_SUCCESS;
        } catch (Throwable $e) {
            Log::error('Error during the planned outages report: ' . PHP_EOL . $e->getMessage());

            $io->error(__('Error during the planned outages report: {0}', $e->getMessage()));

            OperatorReport::send(
                __('Planned outages report failed'),
                __(
                    'Reporting the planned outages failed.' . PHP_EOL . PHP_EOL . 'Error: {0}',
                    [$e->getMessage()],
                ),
            );

            return static::CODE_ERROR;
        }
    }

    /**
     * The outages coming up over our masts, what is known before what is guessed.
     *
     * @param int $withinDays How soon an outage has to begin to be worth reporting.
     * @return array<int, \App\Model\Entity\AccessPointPowerOutage>
     */
    private function upcoming(int $withinDays): array
    {
        $now = DateTime::now();

        /** @var array<int, \App\Model\Entity\AccessPointPowerOutage> $links */
        $links = $this->fetchTable(AccessPointPowerOutagesTable::class)
            ->find()
            ->contain(['AccessPoints', 'PowerOutages'])
            ->where([
                'PowerOutages.cancelled' => false,
                'PowerOutages.begins_at IS NOT' => null,
                'PowerOutages.begins_at <=' => $now->addDays(max(0, $withinDays)),
                'OR' => [
                    'PowerOutages.ends_at IS' => null,
                    'PowerOutages.ends_at >=' => $now,
                ],
                'AccessPoints.archived IS' => null,
            ])
            // What is known before what is guessed, the same way the dashboard card orders it and
            // leaning on the same accident of `certain` sorting before `probable`.
            ->orderBy([
                'AccessPointPowerOutages.certainty' => 'ASC',
                'PowerOutages.begins_at' => 'ASC',
            ])
            ->all()
            ->toList();

        return $links;
    }

    /**
     * The same, as something the console can print.
     *
     * @param array<int, \App\Model\Entity\AccessPointPowerOutage> $links What is coming up.
     * @return array<int, array<int, string>>
     */
    private function asTable(array $links): array
    {
        $table = [[
            __('Access Point'),
            __('Begins'),
            __('Ends'),
            __('Certainty'),
            __('Where'),
        ]];

        foreach ($links as $link) {
            $table[] = [
                (string)$link->access_point?->name_for_lists,
                (string)$link->power_outage?->begins_at,
                (string)$link->power_outage?->ends_at,
                $link->certainty->label(),
                (string)$link->power_outage?->summary,
            ];
        }

        return $table;
    }

    /**
     * Put the report in the post.
     *
     * @param array<int, string> $recipients Who to tell.
     * @param array<int, \App\Model\Entity\AccessPointPowerOutage> $links What is coming up.
     * @param int $withinDays How far ahead this looked.
     * @param \Cake\Console\ConsoleIo $io The console io.
     * @return void
     */
    private function send(array $recipients, array $links, int $withinDays, ConsoleIo $io): void
    {
        $mailer = new Mailer('default');

        foreach ($recipients as $recipient) {
            $mailer->addTo($recipient);
        }

        // The same words every time, whatever the report holds. A subject that counts what is
        // inside it cannot be filed on by a rule in somebody's mail, which is the one thing a
        // report arriving on a schedule has to allow.
        $mailer->setSubject(__('Planned power outages over our access points'));
        $mailer->setEmailFormat('html');

        $mailer->viewBuilder()
            ->setLayout('default')
            ->setTemplate('power-outages-report');

        $mailer->setViewVars([
            'title' => __n(
                'The distributor has published an outage over one of our access points in the next {0} days.',
                'The distributor has published outages over our access points in the next {0} days.',
                count($links),
                $withinDays,
            ),
            'links' => $links,
        ]);

        try {
            $mailer->deliver();
            Log::write('debug', 'The planned outages have been reported.');
            $io->info(__('The planned outages have been reported.'));
        } catch (Exception $e) {
            Log::write('warning', 'The planned outages cannot be reported. (' . $e->getMessage() . ')');
            $io->abort(__('The planned outages cannot be reported.'));
        }
    }
}
