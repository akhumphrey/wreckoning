<?php

$environment = getenv('environment') ?: 'development';

if ($environment == 'development') {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

$timezone                      = getenv('timezone');
$total_working_hours_per_month = getenv('total_working_hours_per_month');
$api_key                       = getenv('api_key');
$api_version                   = getenv('api_version');
$api_reporting_version         = getenv('api_reporting_version');
$non_contact_days              = getenv('non_contact_days') ?: [];
$additionally_skipped_days     = getenv('additionally_skipped_days') ?: [];
$additional_working_days       = getenv('additional_working_days') ?: [];
