<?php
//															     //
// **************************************************************//
//           		 PiBattery Solar Storage                     //
//                           Baseload                            //
// **************************************************************//
//																 //

// = -------------------------------------------------	
// = Calculate new baseload (Peakshaving not active)
// = -------------------------------------------------
	if ($hwP1Usage < $ecoflowMaxOutput
	){
		$newBaseload = round(min($ecoflowMaxOutput, max(0, ($hwP1Usage + $currentBaseload - $ecoflowOutputOffSet))) * 10);
	
	} elseif ($hwP1Usage >= $ecoflowMaxOutput
	){
		$newBaseload = ($ecoflowMaxOutput) * 10;
	}
	
// = -------------------------------------------------	
// = Idle injectie during dayTime  && $realUsage > $baseloadSplitter
// = -------------------------------------------------
	$idleInjectionVar = false;
	
	if ($idleInjection == 'yes' && $realUsage > $idleInjectionThreshold && $newBaseload < ($ecoflowMinOutput * 10) && $hwChargerUsage == 0 && $isDaytime && !$isWinter && $batteryPct > $batteryMinimum && $batteryPct < 99.99) {
		$newBaseload = abs($idleInjectionWatts * 10);
		$idleInjectionVar = true;
	}

// = -------------------------------------------------	
// = Check if baseload needs to be updated
// = ------------------------------------------------- 
	$delta = abs($newBaseload - abs($hwInvReturn * 10));
	
	$updateNeeded = false;
	$updateNeeded = ($delta > ($baseloadDelta * 10)) || ($delta > 500);
	
// = -------------------------------------------------	
// = Baseload failsaves
// = -------------------------------------------------
	$forceBaseloadOff = false;
	
// === Set baseload to null when charging
	if ($hwChargerOneStatus == 'On' || $hwChargerTwoStatus == 'On' || $hwChargerThreeStatus == 'On' || $hwChargerFourStatus == 'On') {
		$forceBaseloadOff = true;
		$newBaseload = 0;
		debugMsg('$forceBaseloadOff (Battery Charging)');
	}

// === Set baseload to null when schedule == 0
	if ($schedule == 0) {
		$forceBaseloadOff = true;
		$newBaseload = 0;
		debugMsg('$forceBaseloadOff (Not Scheduled)');
	}
	
// === Set baseload to null if inverters have to inject lower then then can handle
	if ($newBaseload > 0 && $newBaseload < ($ecoflowMinOutput * 10) && $idleInjectionVar == false) {
		$forceBaseloadOff = true;
		$newBaseload = 0;
		debugMsg('$forceBaseloadOff (Inverter Minimum)');
	}
	
// ==== Set baseload to null if inverters are getting hot	
	if ($invOneTemp >= $ecoflowMaxInvTemp) {
		$forceBaseloadOff = true;
		$newBaseload = 0;
		debugMsg('$forceBaseloadOff (Inv One HOT)');
	}

	if ($invTwoTemp >= $ecoflowMaxInvTemp) {
		$forceBaseloadOff = true;
		$newBaseload = 0;
		debugMsg('$forceBaseloadOff (Inv Two HOT)');
	}
	
// === Set baseload to null when battery is empty #failsave if SOC is calculate wrong
	if ($pvAvInputVoltage < ($batteryVolt - 3.9) && $hwInvReturn != 0) {
		$forceBaseloadOff = true;
		$newBaseload = 0;
		debugMsg('$forceBaseloadOff battery voltage low while injecting');
		
	} elseif ($pvAvInputVoltage > 0 && $pvAvInputVoltage < ($batteryVolt - 2.9) && $hwInvReturn == 0) {
		$forceBaseloadOff = true;
		$newBaseload = 0;
		debugMsg('$forceBaseloadOff battery voltage low');
	}

	if ($batteryPct <= $batteryMinimum) {
		$forceBaseloadOff = true;
		$newBaseload = 0;
		debugMsg('$forceBaseloadOff battery pct low');
	}

// === Set baseload to null when battery calibration is still running
	if (isset($vars['charge_loss_calculation'])) {
		$forceBaseloadOff = true;
		$newBaseload = 0;
		debugMsg('$forceBaseloadOff (Battery calibration)');
	}
	
// = -------------------------------------------------	
// = Set baseload to null if forceBaseloadOff
// = -------------------------------------------------

// ==== If forceBaseloadOff then set baseload to NULL
	if ($forceBaseloadOff == true && $currentBaseload > 0 && !$isManualRun) {	

		$ecoflow->setDeviceFunction($ecoflowOneSerialNumber, 'WN511_SET_PERMANENT_WATTS_PACK', ['permanent_watts' => 0]);
		sleep(5);
		$ecoflow->setDeviceFunction($ecoflowTwoSerialNumber, 'WN511_SET_PERMANENT_WATTS_PACK', ['permanent_watts' => 0]);
			
		if (($newBaseload / 10) != $oldBaseload) {
			$varsChanged = true;
			$vars['oldBaseload'] = ($newBaseload / 10);
		}
		
	}

// = -------------------------------------------------	
// = Update baseload
// = -------------------------------------------------

// === If updateNeeded then set new baseload  && !$isManualRun	
	if ($updateNeeded && $forceBaseloadOff == false) {
	//if (!$isManualRun && !$chargeLossCalculation && $forceBaseloadOff == false) {
//if ($isManualRun) {		
		$actualBaseloadDelta = abs(($newBaseload / 10) - $oldBaseload);

// === Determine is baseload update needs to be delayed
		$pendingBaseloadOverride = false;
		
	if ($pendingBaseload == true) {
		$varsChanged = true;
		$vars['baseload_pending_switch'] = false;
		$pendingBaseloadOverride = true;
		debugMsg('Op/Afschalen baseload was gepauzeerd en nu toegestaan, delta: '.$actualBaseloadDelta.' Watt');
		
	} elseif ($pendingBaseload == false) {
		
		if (($newBaseload / 10) <= $oldBaseload) {
			$pendingBaseloadOverride = true;
			$varsChanged = true;
			$vars['baseload_pending_switch'] = false;
			debugMsg('Afschalen baseload toegestaan, delta: '.$actualBaseloadDelta.' Watt');
			
		} elseif (($newBaseload / 10) > $oldBaseload) {
			
			if ($actualBaseloadDelta > $baseloadSplitter && $hwInvReturn == 0) {
				$varsChanged = true;
				$vars['baseload_pending_switch'] = true;
				debugMsg('Opschalen baseload voor 1 run gepauzeerd, delta: '.$actualBaseloadDelta.' Watt');
				return;
				
			} elseif ($actualBaseloadDelta > $baseloadSplitter && $hwInvReturn != 0) {
				$pendingBaseloadOverride = true;
				$varsChanged = true;
				$vars['baseload_pending_switch'] = false;
				debugMsg('Opschalen baseload toegestaan, delta: '.$actualBaseloadDelta.' Watt & $invInjection '.$invInjection.'');			
			
				
			} elseif ($actualBaseloadDelta <= $baseloadSplitter) {
				$pendingBaseloadOverride = true;
				$varsChanged = true;
				$vars['baseload_pending_switch'] = false;
				debugMsg('Afschalen baseload toegestaan, delta: '.$actualBaseloadDelta.' Watt & $invInjection '.$invInjection.'');			
			}
		}
	}
}


	if ($updateNeeded && !$isManualRun && $forceBaseloadOff == false && $pendingBaseloadOverride == true) {			
// === Determine target injection baseload  && !$isManualRun
		$invBaseload = ($idleInjectionVar || $newBaseload >= ($baseloadSplitter * 10) || $realUsage >= $baseloadSplitter)
			? ($newBaseload / 2)
			: $newBaseload;

// === Determine target inverter
		$useBoth = $idleInjectionVar || !($newBaseload < ($baseloadSplitter * 10) && $realUsage < $baseloadSplitter);

// === Only inverter ONE
		$ecoflow->setDeviceFunction(
			$ecoflowOneSerialNumber,
			'WN511_SET_PERMANENT_WATTS_PACK',
			['permanent_watts' => $invBaseload]
		);

// === Second inverter if needed
		sleep(5);
		if ($useBoth) {
			$ecoflow->setDeviceFunction(
				$ecoflowTwoSerialNumber,
				'WN511_SET_PERMANENT_WATTS_PACK',
				['permanent_watts' => $invBaseload]
			);
		} elseif ($hwInvTwoReturn != 0) {
			$ecoflow->setDeviceFunction(
				$ecoflowTwoSerialNumber,
				'WN511_SET_PERMANENT_WATTS_PACK',
				['permanent_watts' => 0]
			);
		}

		if (($newBaseload / 10) != $oldBaseload) {
			$varsChanged = true;
			$vars['oldBaseload'] = ($newBaseload / 10);
		}
	}
	
?>