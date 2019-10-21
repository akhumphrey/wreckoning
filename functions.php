<?php

function plural($number)
{
    return $number == 1 ? '' : 's';
}

function pretty_print_time($time)
{
    $hours    = floor($time);
    $decimals = $time - $hours;
    $minutes  = ceil($decimals * 60);
    if ($minutes == 60) {
        $hours++;
        $minutes = 0;
    }

    $output = [];
    if ($hours) {
        $output['hours'] = "{$hours} hour" . plural($hours);
    }

    if ($minutes) {
        $output['minutes'] = "{$minutes} minute" . plural($minutes);
    }

    return '<strong>' . implode(' and ', $output) . '</strong>';
}
