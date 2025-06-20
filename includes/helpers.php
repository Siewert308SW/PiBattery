<?php
//															     //
// **************************************************************//
//           		 PiBattery Solar Storage                     //
//                           Helpers                             //
// **************************************************************//
//                                                               //

// -------------------------------------------------
// Schedule
// -------------------------------------------------
	if ($runBaseload || $isManualRun){
		if ($runInfinity == 'yes') {
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
	
// = -------------------------------------------------	
// = EcoFlow Fan On/Off 
// = -------------------------------------------------
	if ($runCharger){
		if ($hwInvFanStatus == 'Off' && $invTemp >= 35){
			switchHwSocket('fan','On');
		} elseif ($hwInvFanStatus == 'On' && $invTemp < 30 && $hwInvReturn == 0){
			switchHwSocket('fan','Off');
		}
	}

// = -------------------------------------------------	
// = Batt% calibration
// = -------------------------------------------------
	if ($runCharger){
		if ($pvAvInputVoltage > ($batteryVolt + 1.3)
			&& $hwChargerOneStatus == 'Off' && $hwChargerTwoStatus == 'Off' && $hwChargerThreeStatus == 'Off'
			&& (!isset($vars['battery_calibrated']) || $vars['battery_calibrated'] !== true)
			) {

			$chargeStart  		= round($hwChargersTotalInput, 5);
			$chargeCalibrated	= round($hwChargersTotalInput - $batteryCapacitykWh, 5);
			$dischargeStart 	= round($hwInvTotal, 5);

// = Start Charge Loss Calculation
		if (!isset($vars['charge_loss_calculation']) || $vars['charge_loss_calculation'] !== true){
			
			$vars['charging_loss'] = [
				'chargeStart' => $chargeStart,
				'dischargeStart' => $dischargeStart
			];
			
			$vars['charge_loss_calculation'] = true;
			
			return;
			
		} elseif ($vars['charge_loss_calculation'] === true) {
		
			$chargedkWh    = $brutoCharged;
			$dischargedkWh = $brutoDischarged;
					
			if ($chargedkWh > 0 && $dischargedkWh > 0 && $dischargedkWh <= $chargedkWh) {
				$sessionLoss = 1 - ($dischargedkWh / $chargedkWh);

// === Log session only if new
			$sessionFile = $piBatteryPath . 'data/charge_sessions.json';
			$newSession = [
			'charged'     		 => round($chargedkWh, 5),
			'discharged'     	 => round($dischargedkWh, 5),
			'loss'        		 => round($sessionLoss, 5)
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
			if (count($sessions) > 10) {
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

			if (count($losses) >= 10) {
				$chargerLoss = array_sum($losses) / count($losses);
				$vars['charger_loss_dynamic'] = $chargerLoss;
				writeJsonLocked($varsFile, $vars);
			}
			
				if (isset($vars['charge_loss_calculation'])) {
					unset($vars['charge_loss_calculation']);
				}
			}
		}

// = End Charge Loss calculation
			$vars['charge_session'] = [
				'chargeStart'     => $chargeStart,
				'chargeCalibrated'=> $chargeCalibrated,
				'dischargeStart'  => $dischargeStart
			];
				
			$vars['battery_calibrated'] = true;	
			writeJsonLocked($varsFile, $vars);
		}

		if (isset($vars['battery_calibrated']) && $batteryPct < $chargerPausePct) {
			unset($vars['battery_calibrated']);
			writeJsonLocked($varsFile, $vars);
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
		if (!$pauseCharging && $pvAvInputVoltage > ($batteryVolt + 1.2) && $hwChargerUsage == 0) {
			$pauseCharging = true;
		}

// === End pause when battery in under the defined % value
		if ($pauseCharging && $batteryPct < $chargerPausePct) {
			$pauseCharging = false;
		}

		if (($vars['pauseCharging'] ?? null) !== $pauseCharging) {
			$vars['pauseCharging'] = $pauseCharging;
			writeJsonLocked($varsFile, $vars);
		}

		$varsState = [];
		$varsState['pauseCharging'] = $vars['pauseCharging'] ?? false;
	}

// = -------------------------------------------------	
// = Keep BMS Awake
// = -------------------------------------------------
	//if ($runCharger){
	//	if (($keepBMSalive == 'yes' && $pvAvInputVoltage <= ($batteryVolt - 3.6) && $hwChargerUsage == 0 && $hwInvReturn == 0) && (!isset($vars['keepBMSalive']) || $vars['keepBMSalive'] !== true)) {
	//		$vars['keepBMSalive'] = true;
	//		writeJsonLocked($varsFile, $vars);
			
	//	} elseif (($keepBMSalive == 'yes' && $pvAvInputVoltage >= ($batteryVolt - 2.2)) && (!isset($vars['keepBMSalive']) || $vars['keepBMSalive'] == true)) {
	//		$vars['keepBMSalive'] = false;
	//		writeJsonLocked($varsFile, $vars);	
	//	}
	//}
	
?>