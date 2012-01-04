<?php
require __DIR__ . '/../gearmanmonitor.php';

$monitor = new GearmanMonitor();

$status = $monitor->cmd('status');
$workers = $monitor->cmd('workers');
echo '<pre>';
print_r(GearmanMonitor::showResponse($status,'status'));
echo '</pre>';
echo "\n<hr/>\n";
echo '<pre>';
print_r(GearmanMonitor::showResponse($workers,'workers'));
echo '</pre>';
