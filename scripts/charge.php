<?php
//															     //
// **************************************************************//
//           		 PiBattery Solar Storage                     //
//                    Start/Stop Charging P1                     //
// **************************************************************//
//                                                               //

// = Charging (ON)
	if ($faseProtect == 0 && !$invInjection && $hwSolarReturn != 0){
		
		if (!$varsState['pauseCharging']) {
			chargerSet($chargers, $P1ChargerUsage);
		}
		
	}

// = Charging mode (OFF)
	if (!$isManualRun) {
		if (($hwChargerUsage <= $chargerWattsIdle || $invInjection || $hwSolarReturn == 0 || $faseProtect == 1) && ($hwChargerOneStatus == 'On' || $hwChargerTwoStatus == 'On' || $hwChargerThreeStatus == 'On' || $hwChargerFourStatus == 'On')){			
			switchHwSocket('four','Off'); sleep(1);
			switchHwSocket('two','Off'); sleep(1);
			switchHwSocket('three','Off'); sleep(1);
			switchHwSocket('one','Off');
		}

// = Reset forceChargeMode
		if ($hwChargerUsage == 0 && !$isManualRun && $vars['forceChargeMode'] == true){
				$vars['forceChargeMode'] = false;
				$varsChanged = true;
		}
	
// = Unset Charger pending switch	
		if ($invInjection && !$isManualRun && isset($vars['charger_pause_until'])){
			$varsChanged = true;		
			$vars['charger_pending_switch'] = false;
			unset($vars['charger_pause_until']);
		}
	}	
?>