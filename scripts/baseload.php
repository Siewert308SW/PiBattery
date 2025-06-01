<?php
//															     //
// **************************************************************//
//           		 PiBattery Solar Storage                     //
//                           Baseload                            //
// **************************************************************//
//																 //

// = -------------------------------------------------	
// = Calculate new baseload
// = -------------------------------------------------

	if ($hwP1Usage > 0 && $hwP1Usage < $ecoflowMaxOutput){
		if ($currentBaseload == 0) {
			$newBaseload = round((($hwP1Usage - $ecoflowOutputOffSet) * 10) / 2);
		} else {
			$newBaseload = round(($hwP1Usage + $currentBaseload - $ecoflowOutputOffSet) * 10);
		}
	
	} elseif ($hwP1Usage < 0){
		$newBaseload = round(($hwP1Usage + $currentBaseload - $ecoflowOutputOffSet) * 10);
		if ($newBaseload <= 0){
			$newBaseload = 0;
		}
	} elseif ($hwP1Usage >= $ecoflowMaxOutput){
		$newBaseload = round(($ecoflowMaxOutput) * 10);

	}	

// = -------------------------------------------------	
// = Baseload failsaves
// = -------------------------------------------------

// = Set Max inverter output
	if ($newBaseload > ($ecoflowMaxOutput * 10)) {
		$newBaseload = round($ecoflowMaxOutput * 10);
	}

// = Set baseload to zero for the following conditions #failsaves	
	$forceBaseloadOff = false;

	if (
		$hwChargerOneStatus == 'On' ||
		$hwChargerTwoStatus == 'On' ||
		$hwChargerThreeStatus == 'On' ||
		$schedule == 0 ||
		($newBaseload <= ($ecoflowMinOutput * 10) && $hwSolarReturn != 0) ||
		($batteryPct < $batteryMinimum) ||
		($chargeLossCalculation == true) ||
		($pvAvInputVoltage <= ($batteryVolt - 1.9) && $hwInvReturn != 0) ||
		($pvAvInputVoltage > 0 && $pvAvInputVoltage < ($batteryVolt - 1.6) && $hwInvReturn == 0)
	) {
		$forceBaseloadOff = true;
	}

	if ($forceBaseloadOff) {
		$newBaseload = 0;
	}


	if ($invTemp >= $ecoflowMaxInvTemp) {
		$newBaseload = round($newBaseload / 1.5);
	}
	
// = -------------------------------------------------	
// = Check if baseload needs to be updated
// = ------------------------------------------------- 
	$delta = abs($newBaseload - ($currentBaseload * 10));
	$updateNeeded = false;

		if ($hwP1Usage > 0) {
			$updateNeeded = ($delta > (10 * 10)) || ($delta > 150);
		} else {
			$updateNeeded = ($newBaseload != ($currentBaseload * 10));
		}
		
// = -------------------------------------------------	
// = Check if inverters recieved previous update
// = -------------------------------------------------

	$updateAllowed 		= false;
	$baseload           = $currentBaseload * 10;
	$baseloadComp       = $baseload - $oldBaseload;
	
// = Inverters not ready		
		if (($baseload == $oldBaseload || $varsTimer['lastBaseloadRun'] >= 60) || ($baseloadComp >= 0 && $baseloadComp <= 15)) {
			$updateAllowed = true;
			
// = Inverters ready
		} else {
			$totalFailedUpdates += 1;
			$vars['totalFailedUpdates'] = $totalFailedUpdates;
			writeJsonLocked($varsFile, $vars);
		}
	
// = -------------------------------------------------	
// = Update baseload
// = -------------------------------------------------
	
	if ($updateNeeded && !$isManualRun && $updateAllowed == true) {	
			
		$invBaseload = ($newBaseload / 2);
		$ecoflow->setDeviceFunction($ecoflowOneSerialNumber, 'WN511_SET_PERMANENT_WATTS_PACK', ['permanent_watts' => $invBaseload]);
		sleep(1);
		$ecoflow->setDeviceFunction($ecoflowTwoSerialNumber, 'WN511_SET_PERMANENT_WATTS_PACK', ['permanent_watts' => $invBaseload]);
		sleep(1);
		
		$totalSuccesUpdates += 1;
		$vars['totalSuccesUpdates'] = $totalSuccesUpdates;
		$vars['oldBaseload'] = $newBaseload;
		writeJsonLocked($varsFile, $vars);
		
	}
?>