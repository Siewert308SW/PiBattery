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

	$newBaseload = round(min($ecoflowMaxOutput, max(0, ($hwP1Usage + $currentBaseload - $ecoflowOutputOffSet))) * 10);	
	
// = -------------------------------------------------	
// = Check if baseload needs to be updated
// = ------------------------------------------------- 

	if ($hwChargerUsage == 0) {
		$delta = abs($newBaseload - ($currentBaseload * 10));
	} else {
		$delta = 0;
	}
	
	$updateNeeded = false;

	if ($hwP1Usage > 0 && $hwChargerUsage == 0) {
		$updateNeeded = ($delta > (10 * 10)) || ($delta > 150);
	} elseif ($hwP1Usage < 0 && $hwChargerUsage == 0) {
		$updateNeeded = ($delta > (1 * 10)) || ($delta > 10);
	}

// = -------------------------------------------------	
// = Check if inverters are ready to recieve update
// = -------------------------------------------------
	$updateAllowed 				= false;
	$writeAllowed 				= false;
	$baseload           		= $currentBaseload * 10;
	$baseloadComp       		= $baseload - $oldBaseload;
	$timeStamp 					= time();
	$totalAllowedUpdates 		= $vars['totalAllowedUpdates'] ?? 0;
	$totalFailedUpdates 		= $vars['totalFailedUpdates'] ?? 0;
	$totalAllowedFailedUpdates 	= $vars['totalAllowedFailedUpdates'] ?? 0;

// = Inverters ready
	if ($updateNeeded) {	
		if ((!isset($vars['updateAllowedTimer'])) && ($baseload == $oldBaseload || $baseloadComp >= 0 && $baseloadComp <= 10)) {
			if (!isset($vars['updateAllowedTimer'])) {
			$updateAllowed = true;
			$writeAllowed  = true;
				$totalAllowedUpdates += 1;
				$vars['totalAllowedUpdates'] = $totalAllowedUpdates;
			}

			debugMsg('Omvormers gereed om nieuwe baseload te ontvangen!');
			debugMsg('currentBaseload: '.$baseload.' \-/ oldBaseload: '.$oldBaseload.' \-/ newBaseload: '.$newBaseload.' \-/ BaseloadComp: '.$baseloadComp.'');
			
// = Inverters not ready
		} else {
			if (!isset($vars['updateAllowedTimer'])) {
			$writeAllowed  = true;
			$vars['updateAllowedTimer'] = $timeStamp;
			$totalFailedUpdates += 1;
			$vars['totalFailedUpdates'] = $totalFailedUpdates;
			}
			
			debugMsg('Omvormers nog niet gereed om nieuwe baseload te ontvangen!');
			debugMsg('currentBaseload: '.$baseload.' \-/ oldBaseload: '.$oldBaseload.' \-/ newBaseload: '.$newBaseload.' \-/ BaseloadComp: '.$baseloadComp.'');

		}

// = Inverters not ready but override allowes update			
		if (isset($vars['updateAllowedTimer']) && ($timeStamp - $vars['updateAllowedTimer']) >= 60) {
			$updateAllowed = true;
			$writeAllowed  = true;
			$totalAllowedFailedUpdates += 1;
			$vars['totalAllowedFailedUpdates'] = $totalAllowedFailedUpdates;
			unset($vars['updateAllowedTimer']);

			debugMsg('Omvormers nog niet gereed om nieuwe baseload te ontvangen maar krijgt wel toestemming!');
			debugMsg('currentBaseload: '.$baseload.' \-/ oldBaseload: '.$oldBaseload.' \-/ newBaseload: '.$newBaseload.' \-/ BaseloadComp: '.$baseloadComp.'');

		}
	}
// = -------------------------------------------------	
// = Baseload failsaves
// = -------------------------------------------------
		$forceBaseloadOff = false;
// Set baseload to null when charging
	if (
		$hwChargerOneStatus == 'On' || $hwChargerTwoStatus == 'On' || $hwChargerThreeStatus == 'On' ||
		$schedule == 0 || $newBaseload <= $ecoflowMinOutput || $batteryPct <= $batteryMinimum || $pvAvInputVoltage <= ($batteryVolt - 3.6)
		) {
		$newBaseload = 0;
		$forceBaseloadOff = true;
	}

// Limit baseload when inverter is getting to hot
	if ($invTemp >= $ecoflowMaxInvTemp) {
		$newBaseload = ($newBaseload) / 1.5;
	}
	
// = -------------------------------------------------	
// = Update baseload
// = -------------------------------------------------
	if (($forceBaseloadOff && $currentBaseload != 0) || ($updateNeeded && !$isManualRun && $updateAllowed)) {
		$invBaseload = ($newBaseload / 2);
		$ecoflow->setDeviceFunction($ecoflowOneSerialNumber, 'WN511_SET_PERMANENT_WATTS_PACK', ['permanent_watts' => $invBaseload]);
		sleep(1);
		$ecoflow->setDeviceFunction($ecoflowTwoSerialNumber, 'WN511_SET_PERMANENT_WATTS_PACK', ['permanent_watts' => $invBaseload]);
		sleep(1);
		$vars['oldBaseload'] = $newBaseload;
		
		if (isset($vars['updateAllowedTimer'])) {
		$writeAllowed  = true;
		unset($vars['updateAllowedTimer']);
	    }
	}

// = Write all variables	
	if (!$isManualRun && $writeAllowed == true) {
		writeJsonLocked($varsFile, $vars);
	}
?>