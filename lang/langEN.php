<?php
//															     //
// **************************************************************//
//           		 PiBattery Solar Storage                     //
//                             Print                             //
// **************************************************************//
//                                                               //
if ($debugLang == 'EN'){
		
// === Print Charger Status	
	echo ' -/- Chargers                        -\-'.PHP_EOL;
	printRow('Charger 1', $hwChargerOneStatus);
	printRow('Charger 2', $hwChargerTwoStatus);
	printRow('Charger 3', $hwChargerThreeStatus);
	printRow('Charger usage', $hwChargerUsage, 'Watt');
	echo ' '.PHP_EOL;

// === Print Schedule
	echo ' -/- Schedule                        -\-'.PHP_EOL;
	if ($runInfinity == 'no') {
		printRow('Start Time', $invStartTime);
		printRow('Eind Time', $invEndTime);
	}
	printRow('Schedule', ($schedule != 0 ? 'Actief' : 'Niet actief'));
	echo ' '.PHP_EOL;
	
// === Print Battery Status		
	echo ' -/- Battery                         -\-'.PHP_EOL;
	printRow('Battery voltage', $pvAvInputVoltage, 'Volt');
	printRow('Stored energy', round($batteryAvailable, 2), 'kWh');
	printRow('Battery SOC', $batteryPct, '%');
	printRow('Charge loss (average)',  round($chargerLoss * 100, 3), '%');
	if ($hwChargerUsage > 100 && $batteryPct < 100) {
		printRow('Estimated charge time >=100%', $realChargeTime, 'h/m');
	}
	if ($hwInvReturn != 0 && $batteryPct > $batteryMinimum) {
		printRow('Estimated discharge time <='.$batteryMinimum.'%', $realDischargeTime, 'h/m');
	}
	echo ' '.PHP_EOL;

// === Print Inverter Status 
	echo ' -/- EcoFlow Inverters               -\-'.PHP_EOL;
	printRow('Inverter 1 Temperature', $invOneTemp, '°C');
	printRow('Inverter 2 Temperature', $invTwoTemp, '°C');
	printRow('Inverter cooling fans', $hwInvFanStatus);
	echo ' '.PHP_EOL;

// === Print Energy Status		
	echo ' -/- Energy                          -\-'.PHP_EOL;
	printRow('Real power usage', $realUsage, 'Watt');
	printRow('P1-Meter', $hwP1Usage, 'Watt');
	printRow('Solar production', $hwSolarReturn, 'Watt');
	printRow('Battery injection', $hwInvReturn, 'Watt');
	printRow('Solar surplus', ($P1ChargerUsage < 0 ? $P1ChargerUsage : 0), 'Watt');
	echo ' '.PHP_EOL;

// === Print Baseload
	echo ' -/- Baseload                        -\-'.PHP_EOL;
	printRow('Current baseload', $currentBaseload, 'Watt');
	printRow('New baseload', ($newBaseload / 10), 'Watt');
	printRow('Baseload update', ($updateNeeded ? 'true' : 'false'));
	echo ' '.PHP_EOL;
	
// === Print Various
	echo ' -/- Various                         -\-'.PHP_EOL;
	printRow('L'.$fase.' protection', ($faseProtect ? 'Active' : 'Not active'));
	printRow('Charge pause '.$chargerPausePct.'% > 100%', ($pauseCharging ? 'Active' : 'Not active'));
	$varsPauseFile = $piBatteryPath . 'data/variables.json';
	$varsPause = file_exists($varsPauseFile) ? json_decode(file_get_contents($varsPauseFile), true) : [];

	$pauseUntil = $varsPause['charger_pause_until'] ?? 0;
	$pendingSwitch = $varsPause['charger_pending_switch'] ?? false;
	$currentTimestamp = time();

	if ($pauseUntil >= $currentTimestamp) {
		printRow('Charger toggle timeout', 'Active', '');
	} elseif ($pendingSwitch) {
		printRow('Lader toggle timeout', 'Expired', '');
	} else {
		printRow('Lader toggle timeout', 'Not active', '');
	}	
	echo ' '.PHP_EOL;
		
// === Print additional debugMsg
	echo ' -/- DebugMsg'.PHP_EOL;
	echo '  ~~ Script started via '.($isManualRun ? 'Terminal' : ($isCronRun ? 'Cronjob' : 'Unknown')).PHP_EOL;
	if (!empty($GLOBALS['debugBuffer'])) {
		foreach ($GLOBALS['debugBuffer'] as $line) {
		echo '  ~~ '.$line.''.PHP_EOL;
		}
	} else {
		echo '  ~~ No messages'.PHP_EOL;	
	}
}
?>