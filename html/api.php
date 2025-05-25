<?php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Connection: close');
$piBatteryPath = '/path/2/pibattery/';
require($piBatteryPath . 'bootstrap/bootstrap.php');
header('Content-Type: application/json');
$data = [
    'batteryPct'         => $batteryPct,
    'batteryAvailable'   => $batteryAvailable,
    'batteryVoltage'     => $pvAvInputVoltage,
    'chargerLoss'        => round($chargerLoss * 100, 2),
    'chargeTime'         => $realChargeTime ?? null,
    'dischargeTime'      => $realDischargeTime ?? null,
    'chargerUsage'       => $hwChargerUsage,
    'lader1'             => $hwChargerOneStatus,
    'lader2'             => $hwChargerTwoStatus,
    'lader3'             => $hwChargerThreeStatus,
    'hwP1Usage'          => $hwP1Usage,
    'solar'              => $hwSolarReturn,
    'invReturn'          => $hwInvReturn,
    'invOneTemp'         => $invOneTemp,
    'invTwoTemp'         => $invTwoTemp,
    'invFan'             => $hwInvFanStatus,
    'pauseCharging'      => $pauseCharging,
    'faseProtect'        => $faseProtect,
    'chargerPausePct'    => $chargerPausePct,
    'realUsage'          => $realUsage,
];

echo json_encode($data);
?>