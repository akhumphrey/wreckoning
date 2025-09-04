<?php

$environment = getenv('environment') ?: 'development';

if ($environment == 'development') {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

$timezone                      = $_ENV['timezone'];
$total_working_hours_per_month = $_ENV['total_working_hours_per_month'];
$api_key                       = $_ENV['api_key'];
$api_version                   = $_ENV['api_version'];
$api_reporting_version         = $_ENV['api_reporting_version'];
$non_contact_days              = $_ENV['non_contact_days'] ?: [];
$additionally_skipped_days     = $_ENV['additionally_skipped_days'] ?: [];
$additional_working_days       = $_ENV['additional_working_days'] ?: [];
