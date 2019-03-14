<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/env.php';

use AJT\Toggl\TogglClient;
use AJT\Toggl\ReportsClient;

$toggl_client = TogglClient::factory([
    'api_key'    => $api_key,
    'apiVersion' => $api_version
]);

$workspace_id = null;
foreach ($toggl_client->getWorkspaces([]) as $workspace) {
    $workspace_id = $workspace['id'];
    break;
}

$report_client = ReportsClient::factory([
    'api_key'    => $api_key,
    'apiVersion' => $api_reporting_version,
    'debug'      => false,
]);

$report = $report_client->details([
    'since'        => date('Y-m-01'),
    'until'        => date('Y-m-t'),
    'user_agent'   => 'PHP API Client',
    'workspace_id' => $workspace_id,
]);

$hours_worked_this_month = round((($report['total_grand'] / 1000) / 60) / 60, 2)    ;

function plural($number)
{
    return $number == 1 ? '' : 's';
}

$working_hours_per_month = 150;
$hours_remaining = $working_hours_per_month - $hours_worked_this_month;

$last_day_of_the_month = date('t');
$today = date('j');
if ($today == $last_day_of_the_month) {
    echo "You have {$hours_remaining} to complete today";
    exit;
}

$remaining_days_this_month = $last_day_of_the_month - $today;
$non_contact_weekdays    = ['Tue', 'Wed'];
$additional_skipped_days = [];
$days_remaining          = 0;

$running_day = time();
for ($i = 0; $i < $remaining_days_this_month; $i++) {
    $running_day = strtotime('+1 day', $running_day);
    $non_contact = in_array(date('D', $running_day), $non_contact_weekdays);
    $skipped     = in_array(date('d', $running_day), $additional_skipped_days);
    if ($non_contact || $skipped) {
        continue;
    }

    $days_remaining++;
}

$average    = round($hours_remaining / $days_remaining, 2);

$hour_plural = plural($hours_remaining);
$day_plural  = plural($days_remaining);
$avg_plural  = plural($average);
echo "You have <strong>{$days_remaining} day{$day_plural}</strong> to complete <strong>{$hours_remaining} hour{$hour_plural}</strong>, an average of <strong>{$average} hour{$avg_plural}</strong> per day";
