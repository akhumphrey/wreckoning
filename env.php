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

if (!empty($timezone)) {
    date_default_timezone_set($timezone);
}

use AJT\Toggl\TogglClient;
use AJT\Toggl\ReportsClient;

$toggl_client = TogglClient::factory([
    'api_key'    => $api_key,
    'apiVersion' => $api_version,
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
