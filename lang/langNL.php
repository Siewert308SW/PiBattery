<?php
//															     //
// **************************************************************//
//           		 PiBattery Solar Storage                     //
//                             Print                             //
// **************************************************************//
//                                                               //
if ($debugLang == 'NL'){
	
// === Print Charger Status	
	echo ' -/- Laders                          -\-'.PHP_EOL;
	printRow('Lader 1', $hwChargerOneStatus);
	printRow('Lader 2', $hwChargerTwoStatus);
	printRow('Lader 3', $hwChargerThreeStatus);
	printRow('Lader 4', $hwChargerFourStatus);
	printRow('Laders verbruik', $hwChargerUsage, 'Watt');
	echo ' '.PHP_EOL;

// === Print Schakeltijd
	echo ' -/- Schakeltijd                     -\-'.PHP_EOL;
	if ($runInfinity == 'no') {
		printRow('Start Tijd', $invStartTime);
		printRow('Eind Tijd', $invEndTime);
	}
	printRow('Schakeltijd', ($schedule != 0 ? 'Actief' : 'Niet actief'));
	if ($isWinter){
	printRow('Wintertijd programma', 'Actief', '');	
	} else {
	printRow('Zomertijd programma', 'Actief', '');		
	}	
	printRow('Dag programma', ($isDaytime ? 'Actief' : 'Niet actief'));	
	echo ' '.PHP_EOL;
	
// === Print Battery Status		
	echo ' -/- Batterij                        -\-'.PHP_EOL;
	printRow('Batterij voltage', $pvAvInputVoltage, 'Volt');
	printRow('Opgeslagen energie', round($batteryAvailable, 2), 'kWh');
	printRow('Batterij SOC', $batteryPct, '%');
	printRow('Laad verlies (gemiddeld)',  round($chargerLoss * 100, 3), '%');
	if ($hwChargerUsage > 100 && $batteryPct < 100) {
		printRow('Geschatte oplaadtijd '.round($batteryPct,0).'% > 100%', $realChargeTime, 'u/m');
	}
	if ($hwInvReturn != 0 && $batteryPct > $batteryMinimum) {
		printRow('Geschatte ontlaadtijd '.$batteryMinimum.'% < '.round($batteryPct,0).'%', $realDischargeTime, 'u/m');
	}
	echo ' '.PHP_EOL;

// === Print Inverter Status 
	echo ' -/- EcoFlow Omvormers               -\-'.PHP_EOL;
	//printRow('Omvormer 1 Baseload', $currentOneBaseload, 'Watt');
	printRow('Omvormer 1 Output', $hwInvOneReturn, 'Watt');
	//printRow('Omvormer 2 Baseload', $currentTwoBaseload, 'Watt');
	printRow('Omvormer 2 Output', $hwInvTwoReturn, 'Watt');
	printRow('Omvormer 1 Temperatuur', $invOneTemp, '°C');
	printRow('Omvormer 2 Temperatuur', $invTwoTemp, '°C');
	printRow('Omvormer koeling', $hwInvFanStatus);
	echo ' '.PHP_EOL;

// === Print Energie Status		
	echo ' -/- Energie                         -\-'.PHP_EOL;
	printRow('Echte verbruik', $realUsage, 'Watt');
	printRow('P1-Meter', $hwP1Usage, 'Watt');
	printRow('Zonnepanelen opwek', $hwSolarReturn, 'Watt');
	printRow('Batterij opwek', $hwInvReturn, 'Watt');
	printRow('Overschot t.b.v laders', ($P1ChargerUsage < 0 ? $P1ChargerUsage : 0), 'Watt');
	echo ' '.PHP_EOL;

// === Print Baseload
	echo ' -/- Baseload                        -\-'.PHP_EOL;
	printRow('Huidige baseload', $currentBaseload, 'Watt');
	printRow('Nieuwe baseload', ($newBaseload / 10), 'Watt');
	printRow('Delta', $delta, 'Watt');
	printRow('Baseload update', ($updateNeeded ? 'true' : 'false'));
	echo ' '.PHP_EOL;
	
// === Print Various
	echo ' -/- Various                         -\-'.PHP_EOL;
	printRow('BMS bescherming', ($bmsProtect ? 'Bijladen' : 'Niet actief'));	
	printRow('L'.$fase.' bescherming', ($faseProtect ? 'Actief' : 'Niet actief'));
	printRow('Laad pauze '.$chargerPausePct.'% <-> 100%', ($pauseCharging ? 'Actief' : 'Niet actief'));
	echo ' '.PHP_EOL;
		
// === Print additional debugMsg
	echo ' -/- DebugMsg'.PHP_EOL;
		echo '  ~~ Script gestart via '.($isManualRun ? 'Terminal' : ($isCronRun ? 'Cronjob' : 'Onbekend')).PHP_EOL;
	if (!empty($GLOBALS['debugBuffer'])) {
		foreach ($GLOBALS['debugBuffer'] as $line) {
		echo '  ~~ '.$line.''.PHP_EOL;
		}
	} else {
		echo '  ~~ Geen berichten'.PHP_EOL;	
	}
	
}
?>