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
		global $hwChargerOneIP, $hwChargerTwoIP, $hwChargerThreeIP;
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
		global $chargerhyst, $hwP1Usage, $hwSolarReturn, $vars, $varsFile, $hwChargerUsage, $currentTime, $chargerPause, $piBatteryPath, $debugMode, $debug, $debugLang;

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

			for ($j = 0; $j < $n; $j++) {
				if ($i & (1 << $j)) {
					$name = $names[$j];
					$totalChargerUsage += $chargers[$name]['power'];
					$combi[] = $name;
					if ($name == $masterName) {
						$masterInCombination = true;
					}
				}
			}
// === Skip combinations without master
			if (!$masterInCombination) {
				continue;
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

// === Smart charger shutdown
		foreach ($allCombinations as $combi) {
			if ($combi['total'] >= $hwChargerUsage) continue;

			$importSaving = $hwChargerUsage - $combi['total'];
			$importAfterSaving = $P1ChargerUsage - $importSaving;

			if (
				$importAfterSaving > 0 &&
				$importAfterSaving < $chargerhyst &&
				$P1ChargerUsage < $chargerhyst
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
	$pauseUntil = $vars['charger_pause_until'] ?? 0;
	$pendingSwitch = $vars['charger_pending_switch'] ?? false;
	$currentTimestamp = time();

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
		debugMsg("Pauze verlopen, schakeling uitvoeren");
		} else {
		debugMsg("Pause expired, charger(s) toggled ");	
		}
		
		$vars['charger_pending_switch'] = false;
		writeJsonLocked($varsFile, $vars);
		
	} elseif ($schakelingNodig) {
		$newPauseUntil = time() + $chargerPause;
		if (
			($vars['charger_pause_until'] ?? 0) !== $newPauseUntil ||
			($vars['charger_pending_switch'] ?? false) !== true
		) {
			$vars['charger_pause_until'] = $newPauseUntil;
			$vars['charger_pending_switch'] = true;
			writeJsonLocked($varsFile, $vars);
		}

			if ($debugLang == 'NL'){
			debugMsg("Schakeling vereist, pauze gestart tot " . date("H:i:s", $vars['charger_pause_until']));
			} else {
			debugMsg("Charger toggle required, pause started till " . date("H:i:s", $vars['charger_pause_until']));	
			}

		return;
	}

// === Toggle chargers ON/OFF		
		foreach ($chargers as $name => $data) {
			$shouldBeOn = in_array($name, $bestCombi);
			$isOn = ($data['status'] === 'On');

			if ($shouldBeOn && !$isOn) {
				if (!$first) {
					if (!$isManualRun){
					sleep(20);
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