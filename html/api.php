<?php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Connection: close');
$piBatteryPath = '/path/2/pibattery/';
require($piBatteryPath . 'bootstrap/bootstrap.php');
header('Content-Type: application/json');
$data = [
    'batteryPct'         => $batteryPct ?? null,
    'batteryAvailable'   => $batteryAvailable ?? null,
    'batteryVoltage'     => $pvAvInputVoltage ?? null,
    'chargerLoss'        => round($chargerLoss * 100, 2) ?? null,
    'chargeTime'         => $realChargeTime ?? null,
    'dischargeTime'      => $realDischargeTime ?? null,
    'chargerUsage'       => $hwChargerUsage ?? null,
    'lader1'             => $hwChargerOneStatus ?? null,
    'lader2'             => $hwChargerTwoStatus ?? null,
    'lader3'             => $hwChargerThreeStatus ?? null,
    'hwP1Usage'          => $hwP1Usage ?? null,
    'solar'              => $hwSolarReturn ?? null,
    'invReturn'          => $hwInvReturn ?? null,
    'invOneTemp'         => $invOneTemp ?? null,
    'invTwoTemp'         => $invTwoTemp ?? null,
    'invFan'             => $hwInvFanStatus ?? null,
    'pauseCharging'      => $pauseCharging ?? null,
    'faseProtect'        => $faseProtect ?? null,
    'chargerPausePct'    => $chargerPausePct ?? null,
    'realUsage'          => $realUsage ?? null,
    'bmsProtection'      => $vars['keepBMSalive'] ?? false,
];

echo json_encode($data);
?>