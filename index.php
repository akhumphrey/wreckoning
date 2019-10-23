<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/env.php';
require_once __DIR__ . '/functions.php';

use AJT\Toggl\TogglClient;
use AJT\Toggl\ReportsClient;

$total_working_hours_per_month = 150;

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

$report_params = [
    'user_agent'   => 'PHP API Client',
    'workspace_id' => $workspace_id,
];

$report = $report_client->details($report_params + [
    'since' => date('Y-m-01'),
    'until' => date('Y-m-t'),
]);
$hours_worked_this_month = round((($report['total_grand'] / 1000) / 60) / 60, 2);

if ($hours_worked_this_month > $total_working_hours_per_month) {
    $over_total = pretty_print_time($hours_worked_this_month - $total_working_hours_per_month);
    echo "You have worked {$over_total} over and above the contracted total of {$total_working_hours_per_month} for the month.";
    exit;
}

$hours_remaining_this_month = $total_working_hours_per_month - $hours_worked_this_month;

$last_day_of_the_month = date('t');
$today = date('j');
if ($today == $last_day_of_the_month) {
    $total_hours_minutes_for_rendering = pretty_print_time($hours_remaining_this_month);
    echo "You have {$total_hours_minutes_for_rendering} to complete today.";
    exit;
}

$remaining_days_this_month = $last_day_of_the_month - $today;
$non_contact_weekdays      = ['Tue', 'Wed'];
$additional_skipped_days   = [];
$additionally_working_days = [];
$days_remaining            = 0;

$running_day = time();
for ($i = 0; $i < $remaining_days_this_month; $i++) {
    $running_day = strtotime('+1 day', $running_day);
    $non_contact = in_array(date('D', $running_day), $non_contact_weekdays);
    $skipped     = in_array(date('d', $running_day), $additional_skipped_days);
    $additional  = in_array(date('d', $running_day), $additionally_working_days);
    if (($non_contact || $skipped) && !$additional) {
        continue;
    }

    $days_remaining++;
}

if ($days_remaining === 0) {
    $total_hours_minutes_for_rendering = pretty_print_time($hours_remaining_this_month);
    echo "You have {$total_hours_minutes_for_rendering} to complete today.";
    exit;
}

$working_today = 'today and ';
if (in_array(date('D'), $non_contact_weekdays) || in_array(date('d'), $additional_skipped_days)) {
    $working_today = null;
}

$report = $report_client->details($report_params + [
    'since' => date('Y-m-d'),
    'until' => date('Y-m-d'),
]);
$hours_worked_today = round((($report['total_grand'] / 1000) / 60) / 60, 2);

if ($working_today) {
    $average = round($hours_remaining_this_month / ($days_remaining + 1), 2);
} else {
    $average = round($hours_remaining_this_month / $days_remaining, 2);
}

$total_hours_minutes_for_rendering   = pretty_print_time($hours_remaining_this_month);
$average_hours_minutes_for_rendering = pretty_print_time($average);

$day_plural = plural($days_remaining);

$days = "{$days_remaining} more day{$day_plural}";

?>
<p>
    You have <strong><?= $working_today . $days; ?></strong> to complete
    <strong><?= $total_hours_minutes_for_rendering; ?></strong>, approximately
    <strong><?= $average_hours_minutes_for_rendering; ?></strong> per day.
</p>
<?php
if ($hours_worked_today) {
?>
<p>
    You have worked <strong><?= pretty_print_time($hours_worked_today); ?></strong> today.
<?php
    if ($hours_worked_today >= $average) {
        $over = pretty_print_time($hours_worked_today - $average);
        echo "This is {$over} more than the expected average.";
    } elseif ($working_today) {
        $remaining = pretty_print_time($average - $hours_worked_today);
        echo "You have another {$remaining} left to work today.";
    }
?>
</p>
<?php
}
