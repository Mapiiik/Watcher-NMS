<?php
declare(strict_types=1);

use App\Command\CustomerPointsUpdateCommand;
use App\Command\ElectricityMeterReadingsReportCommand;
use App\Command\PowerOutagesReportCommand;
use App\Command\PowerOutagesUpdateCommand;
use App\Command\RadarInterferencesReportCommand;
use App\Command\RadarInterferencesUpdateCommand;
use App\Command\RlanStationsUpdateCommand;
use App\Command\RouterosDevicesCleanupCommand;
use Files\Command\CheckFilesCommand;
use Files\Command\FilesTrashPurgeCommand;
use Files\Command\GeneratePreviewsCommand;

/*
 * What the application schedules, see plugins/Scheduler/README.md. These only ever add a row that
 * is missing, an installation changes its own in the application. A row is off unless it says
 * `'enabled' => true`, which starts it on every installation with the next release.
 */
return [
    'Scheduler' => [
        'defaults' => [
            'Update radar interferences' => [
                'command' => RadarInterferencesUpdateCommand::class,
                'frequency' => '0 */2 * * *',
            ],
            'Report radar interferences' => [
                'command' => RadarInterferencesReportCommand::class,
                'frequency' => '30 14 * * *',
            ],
            'Update customer points' => [
                'command' => CustomerPointsUpdateCommand::class,
                'frequency' => '15 2 * * *',
            ],
            'Update RLAN stations' => [
                'command' => RlanStationsUpdateCommand::class,
                'frequency' => '15 4 * * *',
            ],
            'Report electricity meter readings' => [
                'command' => ElectricityMeterReadingsReportCommand::class,
                'frequency' => '0 8 1,15 * *',
            ],
            'Update power outages' => [
                'command' => PowerOutagesUpdateCommand::class,
                'frequency' => '25 5 * * *',
            ],
            'Report power outages' => [
                'command' => PowerOutagesReportCommand::class,
                'frequency' => '0 6 * * 1',
            ],
            'Clean up RouterOS devices' => [
                'command' => RouterosDevicesCleanupCommand::class,
                'frequency' => '45 3 * * *',
            ],
            'Purge files trash' => [
                'command' => FilesTrashPurgeCommand::class,
                'frequency' => '55 3 * * *',
            ],
            'Check files' => [
                'command' => CheckFilesCommand::class,
                'frequency' => '0 5 * * 0',
            ],
            'Generate previews' => [
                'command' => GeneratePreviewsCommand::class,
                'frequency' => '30 5 * * *',
            ],
        ],
    ],
];
