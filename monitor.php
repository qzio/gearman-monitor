#!/usr/bin/env php
<?php
require_once __DIR__ . '/gearmanmonitor.php';
$monitor = new GearmanMonitor();


// if command was passed, just do it once and then exit.
if (!empty($argv[1]))
{
	$cmd = strtolower($argv[1]);
	if (in_array($cmd, GearmanMonitor::$commands))
	{
		echo "GearmanMonitor::".$cmd." --\n";
		$response = $monitor->cmd($cmd);
	}
	else
	{
		$cmd = 'help';
		$response = $monitor->cmd($cmd);
	}
  GearmanMonitor::showResponse($response, $cmd);
	exit;
}


$cmd = 'help';
$response = $monitor->cmd($cmd);
GearmanMonitor::showResponse($response, $cmd);
while($cmd = trim(fgets(STDIN))) {
	if (strpos($cmd,'exit') === 0 || strpos($cmd,'quit') === 0 ) {
		echo "found exit, stopping\n";
		break;
	} else {
		$response = $monitor->cmd($cmd);
    GearmanMonitor::showResponse($response,$cmd);
		echo "\n-------------------------\n";
	}
}

echo "\n";
exit;
