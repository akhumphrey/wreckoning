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

    $output = "{$hours} hour" . plural($hours);
    if (!$minutes) {
        return $output;
    }

    return "{$output} and {$minutes} minute" . plural($minutes);
}
