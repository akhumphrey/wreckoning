<?php

function plural($number)
{
    return $number == 1 ? '' : 's';
}

function pretty_print_time($time)
{
    $whole    = floor($time);
    $decimals = $time - $whole;
    $output = "{$whole} hour" . plural($whole);
    if (!$decimals) {
        return $output;
    }

    $minutes = ceil($decimals * 60);
    return "{$output} and {$minutes} minute" . plural($minutes);
}
