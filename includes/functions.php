<?php
//															     //
// **************************************************************//
//           		 PiBattery Solar Storage                     //
//                           Functions                           //
// **************************************************************//
//                                                               //

// = -------------------------------------------------
// = Function debugMsg
// = -------------------------------------------------
	if (!isset($GLOBALS['debugBuffer'])) {
		$GLOBALS['debugBuffer'] = [];
	}

	function debugMsg(string $message): void {
		global $debug;
		$formatted = "$message";
		$GLOBALS['debugBuffer'][] = $formatted;
	}
	
// = -------------------------------------------------	
// = Function column alignment
// = -------------------------------------------------
	function printRow($label, $value, $unit = '', $widthLabel = 33, $widthTotal = 13) {
		$label = str_pad($label, $widthLabel, ' ', STR_PAD_RIGHT);
		$rightPart = rtrim($value) . ($unit ? ' ' . ltrim($unit) : '');
		$rightPart = str_pad($rightPart, $widthTotal, ' ', STR_PAD_LEFT);
		echo '  -- ' . $label . ': ' . $rightPart . PHP_EOL;
	}

// = -------------------------------------------------	
// = Function GET HomeWizard data
// = -------------------------------------------------
	function getHwData($ip) {
		global $debug, $debugLang;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://".$ip."/api/v1/data");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);

		if (curl_errno($ch)) {
			if ($debugLang == 'NL'){
			debugMsg('Kan geen gegevens ophalen van Homewizard: '.$ip.'!');
			} else {
			debugMsg('Can not get data Homewizard: '.$ip.'!');	
			}
			curl_close($ch);
			return false;
		} else {
			$decoded = json_decode($result);
			$hwDataValue = round($decoded->active_power_w);
			curl_close($ch);
			return $hwDataValue;
		}
	}

// = -------------------------------------------------
// = Function GET HomeWizard (energy-socket) status
// = -------------------------------------------------
	function getHwStatus($ip) {
		global $debug, $debugLang;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://".$ip."/api/v1/state");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);

		if (curl_errno($ch)) { 
			if ($debugLang == 'NL'){
			debugMsg('Kan geen gegevens ophalen van Homewizard: '.$ip.'!');
			} else {
			debugMsg('Can not get data Homewizard: '.$ip.'!');	
			}
			curl_close($ch);
			return false;
		} else {
			$decoded = json_decode($result);
			$statusBool = $decoded->power_on;
			$hwStatus = $statusBool == 1 ? 'On' : 'Off';
			curl_close($ch);
			return $hwStatus;
		}
	}

// = -------------------------------------------------
// = Function Switch HomeWizard (energy-socket) status
// = -------------------------------------------------
	function switchHwSocket($energySocket, $cmd) {
		global $debug, $debugLang;
		global $hwChargerOneIP, $hwChargerTwoIP, $hwChargerThreeIP, $hwChargerFourIP;
		global $hwEcoFlowOneIP, $hwEcoFlowTwoIP, $hwEcoFlowFanIP;

		// Bepaal IP-adres
		switch ($energySocket) {
			case 'one':
				$ip = $hwChargerOneIP;
				break;
			case 'two':
				$ip = $hwChargerTwoIP;
				break;
			case 'three':
				$ip = $hwChargerThreeIP;
				break;
			case 'four':
				$ip = $hwChargerFourIP;
				break;
			case 'invOne':
				$ip = $hwEcoFlowOneIP;
				break;
			case 'invTwo':
				$ip = $hwEcoFlowTwoIP;
				break;
			case 'fan':
				$ip = $hwEcoFlowFanIP;
				break;
			default:
			
			if ($debugLang == 'NL'){
			debugMsg('Kan geen gegevens ophalen van Homewizard: '.$ip.'!');
			debugMsg('Onbekend energySocket: '.$energySocket.'!');
			} else {
			debugMsg('Can not get data Homewizard: '.$ip.'!');
			debugMsg('Unknown energySocket: '.$energySocket.'!');
			}
				return;
		}

		$currentStatus = getHwStatus($ip);

		if (
			($cmd === 'On' && $currentStatus === 'On') ||
			($cmd === 'Off' && $currentStatus === 'Off')
		) {
			return;
		}

		$url = "http://$ip/api/v1/state";
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Content-Type: application/x-www-form-urlencoded',
		]);

		$cmdJson = ($cmd === 'On') ? 'true' : 'false';
		curl_setopt($ch, CURLOPT_POSTFIELDS, '{"power_on": '.$cmdJson.'}');

		curl_exec($ch);
		curl_close($ch);
	}

// = -------------------------------------------------
// = Function GET HomeWizard Total Output Data
// = -------------------------------------------------
	function getHwTotalOutputData($ip) {
		global $debug, $debugLang;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://".$ip."/api/v1/data");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);

		if (curl_errno($ch)) { 
			if ($debugLang == 'NL'){
			debugMsg('Kan geen gegevens ophalen van Homewizard: '.$ip.'!');
			} else {
			debugMsg('Can not get data Homewizard: '.$ip.'!');
			}
			curl_close($ch);
			return false;
		} else {
			$decoded = json_decode($result);
			$HwTotalOutputValue = round($decoded->total_power_export_kwh, 3);
			curl_close($ch);
			return $HwTotalOutputValue;
		}
	}
	
// = -------------------------------------------------
// = Function GET HomeWizard Total Input Data
// = -------------------------------------------------
	function getHwTotalInputData($ip) {
		global $debug, $debugLang;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://".$ip."/api/v1/data");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);

		if (curl_errno($ch)) { 
			if ($debugLang == 'NL'){
			debugMsg('Kan geen gegevens ophalen van Homewizard: '.$ip.'!');
			} else {
			debugMsg('Can not get data Homewizard: '.$ip.'!');
			}
			curl_close($ch);
			return false;
		} else {
			$decoded = json_decode($result);
			$value = round($decoded->total_power_import_kwh, 3);
			curl_close($ch);
			return $value;
		}
	}
	
// = -------------------------------------------------	
// = Function GET HomeWizard P1 fase data
// = -------------------------------------------------
	function getHwP1FaseData($ip, $fase) {
		global $debug, $debugLang;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://".$ip."/api/v1/data");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);

		if (curl_errno($ch)) { 
			if ($debugLang == 'NL'){
			debugMsg('Kan geen gegevens ophalen van Homewizard: '.$ip.'!');
			} else {
			debugMsg('Can not get data Homewizard: '.$ip.'!');
			}
			curl_close($ch);
			return false;
		} else {
			$decoded = json_decode($result);
			switch ($fase) {
				case 1:
					$HwP1FaseValue = round($decoded->active_power_l1_w, 3);
					break;
				case 2:
					$HwP1FaseValue = round($decoded->active_power_l2_w, 3);
					break;
				case 3:
					$HwP1FaseValue = round($decoded->active_power_l3_w, 3);
					break;
				default:
					$HwP1FaseValue = false;
					break;
			}
			curl_close($ch);
			return $HwP1FaseValue;
		}
	}

// = -------------------------------------------------	
// = Function to convert time in decimals to realTime
// = -------------------------------------------------
	function convertTime($dec)
	{
		$seconds = ($dec * 3600);
		$hours = floor($dec);
		$seconds -= $hours * 3600;
		$minutes = floor($seconds / 60);
		$seconds -= $minutes * 60;
		return lz($hours).":".lz($minutes)."";
	}

// = -------------------------------------------------
// = lz = leading zero
// = -------------------------------------------------
	function lz($num)
	{
		return (strlen($num) < 2) ? "0{$num}" : $num;
	}
			
// = -------------------------------------------------
// = Function to calculate if chargers may be switched
// = -------------------------------------------------
	function chargerSet(array $chargers, float $P1ChargerUsage): void {
		global $invInjection, $chargerhyst, $realUsage, $hwP1Usage, $hwInvReturn, $hwSolarReturn, $vars, $varsFile, $hwChargerUsage, $chargerWattsIdle, $chargeLossCalculation, $pvAvInputVoltage, $batteryVolt, $currentTime, $chargerPause, $pauseCharging, $piBatteryPath, $debugMode, $debug, $debugLang, $isManualRun, $varsChanged, $ecoflowMaxOutput;

		$currentTotal = 0;

		foreach ($chargers as $name => $data) {
			if ($data['status'] === 'On') {
				$currentTotal += $data['power'];
			}
		}

// === Determine chargers behaviour
		if ($P1ChargerUsage < 0) {
			$availableSolarPower = abs($P1ChargerUsage); 						 // Heavy Solar Power surplus
		} elseif ($P1ChargerUsage >= 0 && $P1ChargerUsage < ($chargerhyst / 2)) {
			$availableSolarPower = $chargerhyst; 								 // Some grid import, toggle chargers within hysteresis allowed
		} else {
			$availableSolarPower = 0;   										 // Heavy grid import, all chargers have to toggle OFF
		}

// === Find master charger
		$masterName = null;
		foreach ($chargers as $name => $data) {
			if (!empty($data['master'])) {
				$masterName = $name;
				break;
			}
		}

		if (is_null($masterName)) {
			
			if ($debugLang == 'NL'){
			debugMsg("Geen master lader gedefinieerd!");
			} else {
			debugMsg("No master charger defined!");
			}
			
			return;
		}

		$names = array_keys($chargers);
		$n = count($names);
		$combinations = [];
		$allCombinations = [];

// === Seek best charger combination which fits in the solar power surplus
		for ($i = 1; $i < (1 << $n); $i++) {
			$combi = [];
			$totalChargerUsage = 0;
			$masterInCombination = false;
			$containsRestricted = false;
			$restrictedName = null;
	
			for ($j = 0; $j < $n; $j++) {
				if ($i & (1 << $j)) {
					$name = $names[$j];
					$combi[] = $name;
					$totalChargerUsage += $chargers[$name]['power'];
	
					if ($name === $masterName) {
						$masterInCombination = true;
					}
	
					if (!empty($chargers[$name]['spare_charger'])) {
						$containsRestricted = true;
						$restrictedName = $name;
					}
				}
			}
	
// === Skip combinations without master
			if (!$masterInCombination) {
				continue;
			}

// === Skip toggling ON a restricted charger is found within a combintion
			if ($containsRestricted) {
				$otherChargers = array_diff(array_keys($chargers), [$restrictedName]);
				if (count(array_intersect($combi, $otherChargers)) !== count($otherChargers)) {
					continue;
				}
			}
	
			$allCombinations[] = ['names' => $combi, 'total' => $totalChargerUsage];

			if ($totalChargerUsage <= $availableSolarPower) {
				$combinations[] = ['names' => $combi, 'total' => $totalChargerUsage];
			}
		}

// === Choose best combination
		usort($combinations, function($a, $b) {
			return $b['total'] <=> $a['total'];
		});

		$bestCombi = $combinations[0]['names'] ?? [];
		$bestTotal = $combinations[0]['total'] ?? 0;

/*
// === Battery almost fully charged, force a charger to top the battery of
	if ($pvAvInputVoltage >= ($batteryVolt + 1.5)) {
		$forceCharger = 'charger2';

		if (!in_array($forceCharger, $bestCombi) || count($bestCombi) > 1) {
			$bestCombi = [$forceCharger];
			$combinations = [['names' => $bestCombi, 'total' => $chargers[$forceCharger]['power']]];
			$allCombinations = $combinations;

			$vars['forceChargeMode'] = true;
			$varsChanged = true;

			if ($debugLang == 'NL') {
				debugMsg("Batterij bijna vol - alleen $forceCharger actief houden tot idle bereikt is");
			} else {
				debugMsg("Battery nearly full - keep only $forceCharger active until idle reached");
			}
		}
	} elseif ($pvAvInputVoltage < ($batteryVolt + 1.5)) {
			$vars['forceChargeMode'] = false;
			$varsChanged = true;
	}
*/

// === Battery almost fully charged, force chargers to top the battery off
	if ($pvAvInputVoltage > ($batteryVolt + 1.5) && $hwChargerUsage > $chargerWattsIdle) {
		$forceCharger = ['charger1', 'charger2', 'charger3'];

		if (array_diff($forceCharger, $bestCombi) || count($bestCombi) > count($forceCharger)) {
			$bestCombi = $forceCharger;
			$totalPower = array_sum(array_map(function($name) use ($chargers) {
				return $chargers[$name]['power'];
			}, $forceCharger));
			$combinations = [['names' => $bestCombi, 'total' => $totalPower]];
			$allCombinations = $combinations;

			$vars['forceChargeMode'] = true;
			$varsChanged = true;

			if ($debugLang == 'NL') {
				debugMsg("Batterij bijna vol - alleen " . implode(', ', $forceCharger) . " actief houden tot idle bereikt is");
			} else {
				debugMsg("Battery nearly full - keep only " . implode(', ', $forceCharger) . " active until idle reached");
			}
		}
	}
		
// === Smart charger shutdown
		foreach ($allCombinations as $combi) {
			if ($combi['total'] >= $hwChargerUsage) continue;

			$importSaving = $hwChargerUsage - $combi['total'];
			$importAfterSaving = $P1ChargerUsage - $importSaving;

			if (
				$importAfterSaving > 0 &&
				$importAfterSaving < $chargerhyst &&
				$P1ChargerUsage < 10
			) {
				
			if ($debugLang == 'NL'){
			debugMsg("Afschaling geblokkeerd: met combinatie ".implode(', ', $combi['names'])." zakt P1 naar {$importAfterSaving}W < hysterese = {$chargerhyst}W");	
			} else {
			debugMsg("Downscaling blocked: with combination ".implode(', ', $combi['names'])." sinks p1 to {$importAfterSaving}W < hysteresis = {$chargerhyst}W");	
			}
				
				return;
			}
		}
	
		if ($bestTotal < $currentTotal && $hwP1Usage < $chargerhyst) {
			
			if ($debugLang == 'NL'){
			debugMsg("Afschalen geblokkeerd: P1 import = {$hwP1Usage}W < hysterese = {$chargerhyst}W");	
			} else {
			debugMsg("Downscaling blocked: P1 import = {$hwP1Usage}W < hysteresis = {$chargerhyst}W");		
			}
			
			return;
		}

		if ($bestTotal < $hwChargerUsage && $P1ChargerUsage > 0) {
			$importSaving = $hwChargerUsage - $bestTotal;
			$importAfterSaving = $P1ChargerUsage - $importSaving;

			if ($importAfterSaving > 0 && $importAfterSaving < $chargerhyst && $P1ChargerUsage < $chargerhyst){
				if ($debugLang == 'NL'){
				debugMsg("Afschalen geblokkeerd: P1 import na besparing = {$importAfterSaving}W < hysterese = {$chargerhyst}W");
				} else {
				debugMsg("Downscaling blocked: P1 import after savings = {$importAfterSaving}W < hysteresis = {$chargerhyst}W");		
				}
			
				return;
			} else {
				if ($debugLang == 'NL'){
				debugMsg("Alternatieve afschaling geaccepteerd: besparing van {$importSaving}W");
				} else {
				debugMsg("Alternative downscaling accepted: saving of {$importSaving}W");	
				}
			}
		}
	
		if ($debug == 'yes') {
			if (!empty($bestCombi)) {
				if ($debugLang == 'NL'){
				debugMsg("Beste lader combinatie - ".implode(', ', $bestCombi)."");
				} else {
				debugMsg("Best charger combination - ".implode(', ', $bestCombi)."");	
				}				
			} else {
				if ($debugLang == 'NL'){
				debugMsg("Geen lader combinatie gevonden");
				} else {
				debugMsg("Did not find any charger combination");	
				}
			}
		}

// === Check if toggling chargers is needed
		$schakelingNodig = false;
		$first = true;

		foreach ($chargers as $name => $data) {
			$shouldBeOn = in_array($name, $bestCombi);
			$isOn = ($data['status'] === 'On');

			if ($shouldBeOn && !$isOn) {
				$schakelingNodig = true;
				break;
			} elseif (!$shouldBeOn && $isOn) {
				$schakelingNodig = true;
				break;
			}
		}

// === Check if charging is paused
	$pauseUntil       = $vars['charger_pause_until'] ?? 0;
	$pendingSwitch 	  = $vars['charger_pending_switch'] ?? false;
	$currentTimestamp = time();

	if (($vars['forceChargeMode'] ?? false) !== true) {
	if ($pauseUntil >= $currentTimestamp) {
		if ($debugLang == 'NL'){
		debugMsg("Pauze actief tot " . date("H:i:s", $pauseUntil) . ", geen actie");
		} else {
		debugMsg("Pause active till " . date("H:i:s", $pauseUntil) . ", no action required");	
		}

		return;
	}


	if ($pendingSwitch) {
		if ($debugLang == 'NL'){
		debugMsg("Pauze verlopen, volgende run schakeling uitvoeren");
		} else {
		debugMsg("Pause expired, next run charger(s) toggled ");	
		}

		if ($vars['charger_pending_switch'] == true) {
		$varsChanged = true;		
		$vars['charger_pending_switch'] = false;
		unset($vars['charger_pause_until']);
		}
		
	} elseif ($schakelingNodig) {
		$newPauseUntil = time() + $chargerPause;
		if (
			($vars['charger_pause_until'] ?? 0) !== $newPauseUntil ||
			($vars['charger_pending_switch'] ?? false) !== true
		) {
			if ($vars['charger_pending_switch'] == false) {
			$varsChanged = true;	
			$vars['charger_pause_until'] = $newPauseUntil;
			$vars['charger_pending_switch'] = true;
			}
		}

			if ($debugLang == 'NL'){
			debugMsg("Schakeling vereist, pauze gestart tot " . date("H:i:s", $vars['charger_pause_until']));
			} else {
			debugMsg("Charger toggle required, pause started till " . date("H:i:s", $vars['charger_pause_until']));	
			}

		return;
	}
	}

// === Toggle chargers ON/OFF		
		foreach ($chargers as $name => $data) {
			$shouldBeOn = in_array($name, $bestCombi);
			$isOn = ($data['status'] === 'On');

		if (($shouldBeOn && !$isOn && $realUsage <= 900 && $hwChargerUsage == 0 && !$chargeLossCalculation && !$pauseCharging) || ($shouldBeOn && !$isOn && $realUsage <= 3300 && $hwChargerUsage != 0 && !$chargeLossCalculation && !$pauseCharging)) {
				
			if (!$first) {
					if (!$isManualRun){
					sleep(10);
					}
				}
				$first = false;
				if (!$isManualRun){
				switchHwSocket($data['label'], 'On');
				}
				if ($debugLang == 'NL'){
				debugMsg("Inschakelen: $name");
				} else {
				debugMsg("Switched On: $name");
				}

			} elseif (!$shouldBeOn && $isOn) {
				if (!$isManualRun){				
				switchHwSocket($data['label'], 'Off');
				}
				if ($debugLang == 'NL'){
				debugMsg("Uitschakelen: $name");
				} else {
				debugMsg("Switched Off: $name");
				}
			}
		}
	}
	
// = -------------------------------------------------
// = Function writeJsonLocked
// = -------------------------------------------------
	function writeJsonLocked(string $filename, array $data): void {
		$fp = @fopen($filename, 'c+');
		if (!$fp) return;

		if (flock($fp, LOCK_EX)) {
			ftruncate($fp, 0);
			rewind($fp);
			fwrite($fp, json_encode($data, JSON_PRETTY_PRINT));
			fflush($fp);
			flock($fp, LOCK_UN);
		}
		fclose($fp);
	}
?>