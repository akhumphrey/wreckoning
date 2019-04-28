<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/env.php';
require_once __DIR__ . '/functions.php';

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

$working_hours_per_month = 150;

if ($hours_worked_this_month > $working_hours_per_month) {
    $over_total = pretty_print_time($hours_worked_this_month - $working_hours_per_month);
    echo "You have worked <strong>{$over_total}</strong> over and above the contracted total of {$working_hours_per_month} for the month.";
    exit;
}

$hours_remaining = $working_hours_per_month - $hours_worked_this_month;

$last_day_of_the_month = date('t');
$today = date('j');
if ($today == $last_day_of_the_month) {
    $total_hours_minutes = pretty_print_time($hours_remaining);
    echo "You have <strong>{$total_hours_minutes}</strong> to complete today.";
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

$average = round($hours_remaining / ($days_remaining + 1), 2);

$total_hours_minutes   = pretty_print_time($hours_remaining);
$average_hours_minutes = pretty_print_time($average);

$day_plural = plural($days_remaining);
$days = "{$days_remaining} more day{$day_plural}";
echo "You have <strong>today and {$days}</strong> to complete <strong>{$total_hours_minutes}</strong>, approximately <strong>{$average_hours_minutes}</strong> per day.";
