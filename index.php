<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/env.php';
require_once __DIR__ . '/templates/header.php';

$until = date('d');
if ((int) $until > 1) {
    $until -= 1;
}

$report = $report_client->details($report_params + [
    'since' => date('Y-m-01'),
    'until' => date("Y-m-{$until}"),
]);
$hours_worked_before_today = rounded_hours($report['total_grand']);

$report = $report_client->details($report_params + [
    'since' => date('Y-m-d'),
    'until' => date('Y-m-d'),
]);
$hours_worked_today = rounded_hours($report['total_grand']);

$hours_worked_this_month = $hours_worked_before_today + $hours_worked_today;

?>
<h3>Today</h3>
<?php

if ($hours_worked_this_month > $total_working_hours_per_month) {
    $over_total = pretty_print_time($hours_worked_this_month - $total_working_hours_per_month);
?>
<p>
    You have worked <?= $over_total; ?> over and above the contracted total of
    <?= $total_working_hours_per_month; ?> hours for the month.
</p>
<?php
    exit;
}

$hours_remaining_this_month        = $total_working_hours_per_month - $hours_worked_before_today;
$total_hours_minutes_for_rendering = pretty_print_time($hours_remaining_this_month);


$last_day_of_the_month = date('t');
$today = date('j');
if ($today == $last_day_of_the_month) {
    if ($hours_worked_today) {
?>
<p>You have worked <?= pretty_print_time($hours_worked_today); ?> today.</p>
<?php
    }
?>
<p>You have <?= $total_hours_minutes_for_rendering ;?> to complete today.</p>
<?php
    exit;
}

$remaining_days_this_month = $last_day_of_the_month - $today;
$days_remaining = 0;

if (!is_array($non_contact_days)) {
    $non_contact_days = explode(',', $non_contact_days);
}

if (!is_array($additionally_skipped_days)) {
    $additionally_skipped_days = explode(',', $additionally_skipped_days);
}

if (!is_array($additional_working_days)) {
    $additional_working_days = explode(',', $additional_working_days);
}

$running_day = time();
for ($i = 0; $i < $remaining_days_this_month; $i++) {
    $running_day = strtotime('+1 day', $running_day);
    $non_contact = in_array(date('D', $running_day), $non_contact_days);
    $skipped     = in_array(date('d', $running_day), $additionally_skipped_days);
    $additional  = in_array(date('d', $running_day), $additional_working_days);
    if (($non_contact || $skipped) && !$additional) {
        continue;
    }

    $days_remaining++;
}

$total_hours_minutes_for_rendering = pretty_print_time($hours_remaining_this_month - $hours_worked_today);
if ($days_remaining === 0) {
    echo "<p>You have {$total_hours_minutes_for_rendering} left to complete today.</p>";
    exit;
}

$working_today = 'today and ';
if (in_array(date('D'), $non_contact_days) || in_array(date('d'), $additionally_skipped_days)) {
    $working_today = null;
}

if ($working_today) {
    $average = round($hours_remaining_this_month / ($days_remaining + 1), 2);
} else {
    $average = round($hours_remaining_this_month / $days_remaining, 2);
}

$average_hours_minutes_for_rendering = pretty_print_time($average);
?>
<p>
<?php
if ($days_remaining === 1) {
    echo "You have <strong>{$working_today}1 more day</strong>";
} else {
    $day_plural = plural($days_remaining);
    $days       = "{$days_remaining} more day{$day_plural}";
    echo "You have <strong>{$working_today}{$days}</strong>";
}
?>
    to complete
    <?= $total_hours_minutes_for_rendering; ?>, approximately
    <?= $average_hours_minutes_for_rendering; ?> per day.
</p>
<?php

if ($hours_worked_today) {
?>
<p>
    You have worked <?= pretty_print_time($hours_worked_today); ?> today.
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

if ($additionally_skipped_days || $additional_working_days) {
?>
<h4>Modifiers in effect this month:</h4>
<?php
    if ($additionally_skipped_days) {
?>
<p>
    Skipping the following day<?= plural(count($additionally_skipped_days)); ?>:
    <ul>
<?php
        foreach ($additionally_skipped_days as $day) {
            $line = $day . date('S', strtotime(date('Y-m-') . $day));
?>
        <li><?= $day < date('j') ? "<s>{$line}</s>" : $line; ?></li>
<?php
        }
?>
    </ul>
</p>
<?php
    }

    if ($additional_working_days) {
?>
<p>
    Added the following day<?= plural(count($additional_working_days)); ?>:
    <ul>
<?php
        foreach ($additional_working_days as $day) {
            $line = $day . date('S', strtotime(date('Y-m-') . $day));
?>
        <li><?= $day < date('j') ? "<s>{$line}</s>" : $line; ?></li>
<?php
        }
?>
    </ul>
</p>
<?php
    }
}

require_once __DIR__ . '/templates/footer.php';
