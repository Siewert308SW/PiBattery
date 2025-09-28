<?php
//															     //
// **************************************************************//
//           		 PiBattery Solar Storage                     //
//                           Helpers                             //
// **************************************************************//
//
	
// -------------------------------------------------
// Determine winter/summertime schedule
// -------------------------------------------------
	if ($runBaseload || $isManualRun){
		$scheduleAllowed = false;
		if ($winterPause == 'yes' && $isWinter && !$isDaytime && !isset($vars['battery_empty'])) {
		$scheduleAllowed = true;
		
		} elseif ($winterPause == 'yes' && $isWinter && $isDaytime && !isset($vars['battery_empty']) && $realUsage >= $idleInjectionThreshold) {
		$scheduleAllowed = true;
		
		} elseif ($winterPause == 'yes' && !$isWinter) {
		$scheduleAllowed = true;
		
		} elseif ($winterPause == 'no') {
		$scheduleAllowed = true;
		}
	}
	
// -------------------------------------------------
// Schedule
// -------------------------------------------------
	if ($runBaseload || $isManualRun){
		if ($runInfinity == 'yes' && $scheduleAllowed == true) {
			$schedule = 1;
			
		} elseif ($runInfinity == 'no') {
			
			if ($invStartTime < $invEndTime) {
				if ($currentTime >= $invStartTime && $currentTime <= $invEndTime) {
					$schedule = 1;
				} else {
					$schedule = 0;
				}
				
			} else {
				
				if ($currentTime >= $invStartTime || $currentTime <= $invEndTime) {
					$schedule = 1;
				} else {
					$schedule = 0;
				}
			}

		} else {
			$schedule = 0;
		}
	}
	
// = -------------------------------------------------	
// = Fase powerusage protection
// = -------------------------------------------------
	if ($runCharger || $isManualRun){
		if ($hwP1Fase >= $maxFaseWatts){
			$faseProtect = 1;
		} else {
			if ($hwP1Fase <= $maxFaseWatts){
			$faseProtect = 0;
			} else {
			$faseProtect = 0;
			}
		}
	}

// -------------------------------------------------
// Reset $vars['battery_empty']
// -------------------------------------------------
	if ($runBaseload){
		if ($winterPause == 'yes' && $isNightTime && $batteryPct >= 60 && isset($vars['battery_empty'])) {
			
			if ($hwInvOneStatus == 'Off' || $hwInvTwoStatus == 'Off'){
				switchHwSocket('invOne','On');
				switchHwSocket('invTwo','On');
			}
						
			unset($vars['battery_empty']);
			$varsChanged = true;
		}
	}
	
// = -------------------------------------------------	
// = EcoFlow Fan On/Off 
// = -------------------------------------------------
	if ($runCharger){
		if ($hwInvFanStatus == 'Off' && $invTemp >= 35){
			switchHwSocket('fan','On');
		} elseif ($hwInvFanStatus == 'On' && $invTemp < 30){
			switchHwSocket('fan','Off');
		}
	}

// = -------------------------------------------------	
// = Adjusted $chargerhyst
// = -------------------------------------------------
	if ($runCharger){
		if ($hwChargerUsage != 0 && $hwSolarReturn > -600) {
			$chargerhyst = $chargerhyst / 2;
		}
	}
	
// = -------------------------------------------------	
// = Batt% calibration
// = -------------------------------------------------
	if ($runCharger){
		if ($pvAvInputVoltage > ($batteryVolt + 1.7)
			&& $hwChargerOneStatus == 'Off' && $hwChargerTwoStatus == 'Off' && $hwChargerThreeStatus == 'Off' && $hwChargerFourStatus == 'Off'
			&& (!isset($vars['battery_calibrated']) || $vars['battery_calibrated'] !== true)
			) {

			$chargeStart  		= round($hwChargersTotalInput, 7);
			$chargeCalibrated	= round($hwChargersTotalInput - $batteryCapacitykWh, 7);
			$dischargeStart 	= round($hwInvTotal, 7);

// = Start Charge Loss Calculation
		if (!isset($vars['charge_loss_calculation']) || $vars['charge_loss_calculation'] !== true){
			$vars['charging_loss'] = [
				'chargeStart' => $chargeStart,
				'dischargeStart' => $dischargeStart
			];
			
			$vars['charge_loss_calculation'] = true;
			$varsChanged = true;			
			return;
			
		} elseif ($vars['charge_loss_calculation'] === true) {
		
			$chargedkWh    = $brutoCharged;
			$dischargedkWh = $brutoDischarged;
					
			if ($chargedkWh > 0 && $dischargedkWh > 0 && $dischargedkWh <= $chargedkWh) {
				$sessionLoss = 1 - ($dischargedkWh / $chargedkWh);

// === Log session only if new
			$sessionFile = $piBatteryPath . 'data/charge_sessions.json';
			$newSession = [
			'charged'     		 => round($chargedkWh, 7),
			'discharged'     	 => round($dischargedkWh, 7),
			'loss'        		 => round($sessionLoss, 7)
			];

			$sessions = [];
			$skipSession = false;

			if (file_exists($sessionFile)) {
				$sessions = json_decode(file_get_contents($sessionFile), true);
				if (!is_array($sessions)) $sessions = [];

// === Check id session is identical to the latest session
				$lastSession = end($sessions);
				if (
					isset($lastSession['charged'], $lastSession['discharged']) &&
					$newSession['charged'] === $lastSession['charged'] &&
					$newSession['discharged'] === $lastSession['discharged']
					) {
					$skipSession = true;
					}
				}

				if (!$skipSession) {
					$sessions[] = $newSession;

// === Remove oldest session				
			if (count($sessions) > $chargeSessions) {
			array_shift($sessions);
			}	
			writeJsonLocked($sessionFile, $sessions);
			}

// === Calculate session average
			$losses = [];
			foreach ($sessions as $s) {
				if (isset($s['charged'], $s['discharged']) &&
					$s['charged'] > 0 &&
					$s['discharged'] > 0 &&
					$s['charged'] >= $s['discharged']
					) {
					$loss = 1 - ($s['discharged'] / $s['charged']);
					$losses[] = $loss;
				}
			}

			if (count($losses) >= $chargeSessions) {
				$chargerLoss = array_sum($losses) / count($losses);
				if ($chargerLoss != $vars['charger_loss_dynamic']) {
				$varsChanged = true;
				$vars['charger_loss_dynamic'] = $chargerLoss;
				}
			}
			
				if (isset($vars['charge_loss_calculation'])) {
					$varsChanged = true;
					unset($vars['charge_loss_calculation']);
					
					if (isset($vars['battery_empty'])) {
						
						if ($hwInvOneStatus == 'Off' || $hwInvTwoStatus == 'Off'){
							switchHwSocket('invOne','On');
							switchHwSocket('invTwo','On');
						}
					
						unset($vars['battery_empty']);
						$varsChanged = true;
					}

				}
		
			}
		}

// = End Charge Loss calculation
			$varsChanged = true;
			$vars['charge_session'] = [
				'chargeStart'     => $chargeStart,
				'chargeCalibrated'=> $chargeCalibrated,
				'dischargeStart'  => $dischargeStart
			];
				
			$vars['battery_calibrated'] = true;
		}

		if (isset($vars['battery_calibrated']) && $batteryPct < $chargerPausePct) {
			unset($vars['battery_calibrated']);
			$varsChanged = true;
		}
	}
	
// = -------------------------------------------------	
// = Estimated charge/discharge time
// = -------------------------------------------------

// === ChargeTime till 100%
		if ($hwChargerUsage > $chargerWattsIdle && $batteryPct < 100) {
			$currentWh = ($batteryPct / 100) * $batteryCapacityWh;
			
			$neededWh = $batteryCapacityWh - $currentWh;
			$neededWhAdjusted = $neededWh / (1 - $chargerLoss);
			$chargeTime = $neededWhAdjusted / $hwChargerUsage;
			$realChargeTime = convertTime($chargeTime);
		}

// === DischargeTime till minimum-SOC
		if ($hwInvReturn != 0 && $batteryPct > $batteryMinimum) {
			$currentWh = ($batteryPct / 100) * $batteryCapacityWh;
			
			$minWh = ($batteryMinimum / 100) * $batteryCapacityWh;
			$availableWh = $currentWh - $minWh;
			$dischargeTime = $availableWh / abs($hwInvReturn);
			$realDischargeTime = convertTime($dischargeTime);
		}
	
// = -------------------------------------------------	
// = Pause charging until desired Batt%
// = -------------------------------------------------
	if ($runCharger){
// === Activate pause when battery is (almost) full > 26,85V
		if (!$pauseCharging && $vars['pauseCharging'] !== true && $pvAvInputVoltage > ($batteryVolt + 1.8) && $hwChargerUsage <= $chargerWattsIdle) {
			$pauseCharging = true;
		}

// === End pause when battery in under the defined % value
		if ($pauseCharging && $vars['pauseCharging'] !== false && $batteryPct < $chargerPausePct) {
			$pauseCharging = false;
		}

		if (($vars['pauseCharging'] ?? null) !== $pauseCharging) {
			$vars['pauseCharging'] = $pauseCharging;
			$varsChanged = true;
		}

		$varsState = [];
		$varsState['pauseCharging'] = $vars['pauseCharging'] ?? false;
	}
	
// = -------------------------------------------------	
// = Determine injection
// = -------------------------------------------------
	if ($runBaseload){
		if ($hwInvReturn != 0 && $invInjection === false && $idleInjection == 'no') {
			$varsChanged = true;		
			$vars['invInjection'] = true;
			
		} elseif ($hwInvReturn >= $idleInjectionWatts && $hwInvReturn <= 0 && $invInjection === true && $idleInjection == 'yes') {
			$varsChanged = true;		
			$vars['invInjection'] = false;
			
		} elseif ($hwInvReturn < $idleInjectionWatts && $invInjection === false && $idleInjection == 'yes') {
			$varsChanged = true;		
			$vars['invInjection'] = true;
			
		} elseif ($hwInvReturn == 0 && $invInjection === true) {
			$varsChanged = true;		
			$vars['invInjection'] = false;
		}
	}

?>