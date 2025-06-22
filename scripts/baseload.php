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
		$newBaseload = round(min($ecoflowMaxOutput, max(0, ($hwP1Usage + $currentBaseload - $ecoflowOutputOffSet))) * 10);
	} elseif ($hwP1Usage >= $ecoflowMaxOutput){
		$newBaseload = ($ecoflowMaxOutput) * 10;
	}
	
// = -------------------------------------------------	
// = Check if baseload needs to be updated
// = ------------------------------------------------- 
	$delta = abs($newBaseload - ($currentBaseload * 10));
	
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
	if ($hwChargerOneStatus == 'On' || $hwChargerTwoStatus == 'On' || $hwChargerThreeStatus == 'On' ||
		$schedule == 0 || $newBaseload <= ($ecoflowMinOutput * 10) || $batteryPct <= $batteryMinimum || $pvAvInputVoltage <= ($batteryVolt - 3.6) || $invTemp >= $ecoflowMaxInvTemp
		) {
		$forceBaseloadOff = true;
		debugMsg('Forced off');
	}
	
// = -------------------------------------------------	
// = Update baseload
// = -------------------------------------------------
	if ($updateNeeded && !$isManualRun && $updateAllowed) {
		
		if ($forceBaseloadOff == true && $baseload != 0) {
			$invBaseload = 0;
		} elseif ($forceBaseloadOff == false) {
			$invBaseload = ($newBaseload / 2);	
		}
		
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