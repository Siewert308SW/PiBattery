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
	if ($hwP1Usage < $ecoflowMaxOutput){
		$newBaseload = ($hwP1Usage + $currentBaseload - $ecoflowOutputOffSet) * 10;		
	} elseif ($hwP1Usage >= $ecoflowMaxOutput){
		$newBaseload = ($ecoflowMaxOutput) * 10;
	}	

// = -------------------------------------------------	
// = Baseload failsaves
// = -------------------------------------------------

// = Set Max inverter output
	if ($newBaseload > ($ecoflowMaxOutput * 10)) {
		$newBaseload = $ecoflowMaxOutput * 10;
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
		($batteryPct > 100) ||
		($pvAvInputVoltage <= ($batteryVolt - 1.9) && $hwInvReturn != 0) ||
		($pvAvInputVoltage > 0 && $pvAvInputVoltage < ($batteryVolt - 1.6) && $hwInvReturn == 0)
	) {
		$forceBaseloadOff = true;
	}

	if ($forceBaseloadOff) {
		$newBaseload = 0;
		$newInvBaseload = 0;
	}


	if ($invTemp >= $ecoflowMaxInvTemp) {
		$newBaseload = $newBaseload / 1.5;
	}
	
// = -------------------------------------------------	
// = Update baseload via API
// = ------------------------------------------------- 
	$delta = abs($newBaseload - ($currentBaseload * 10));
	$updateNeeded = false;

		if ($hwP1Usage > 0) {
			$updateNeeded = ($delta > (10 * 10)) || ($delta > 150);
		} else {
			$updateNeeded = ($newBaseload != ($currentBaseload * 10));
		}

	if ($updateNeeded && !$isManualRun) {
		if ($currentBaseload == 0) {

			if (isset($vars['pauseBaseload']) && $vars['pauseBaseload'] === true){

				$invBaseload = ($newBaseload / 2);
				$ecoflow->setDeviceFunction($ecoflowOneSerialNumber, 'WN511_SET_PERMANENT_WATTS_PACK', ['permanent_watts' => $invBaseload]);
				sleep(1);
				$ecoflow->setDeviceFunction($ecoflowTwoSerialNumber, 'WN511_SET_PERMANENT_WATTS_PACK', ['permanent_watts' => $invBaseload]);
				unset($vars['pauseBaseload']);
				writeJsonLocked($varsFile, $vars);
				return;
			
			} else {

				$vars['pauseBaseload'] = true;
				writeJsonLocked($varsFile, $vars);
			}

		} else {

			$invBaseload = ($newBaseload / 2);
			$ecoflow->setDeviceFunction($ecoflowOneSerialNumber, 'WN511_SET_PERMANENT_WATTS_PACK', ['permanent_watts' => $invBaseload]);
			sleep(1);
			$ecoflow->setDeviceFunction($ecoflowTwoSerialNumber, 'WN511_SET_PERMANENT_WATTS_PACK', ['permanent_watts' => $invBaseload]);

			if (isset($vars['pauseBaseload'])) {
				unset($vars['pauseBaseload']);
				writeJsonLocked($varsFile, $vars);
			}

		}

	} elseif (isset($vars['pauseBaseload'])) {

		unset($vars['pauseBaseload']);
		writeJsonLocked($varsFile, $vars);
	}
?>