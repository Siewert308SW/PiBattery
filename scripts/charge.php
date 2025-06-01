<?php
//															     //
// **************************************************************//
//           		 PiBattery Solar Storage                     //
//                    Start/Stop Charging P1                     //
// **************************************************************//
//                                                               //

// = Normal charging mode (ON)
	if ($faseProtect == 0 && $hwInvReturn == 0 && $hwSolarReturn != 0 && $vars['keepBMSalive'] !== true){
		
		if (!$varsState['pauseCharging']) {
			chargerSet($chargers, $P1ChargerUsage);
		}
	}

// = BMS protection mode
	if ($vars['keepBMSalive'] === true && $hwChargerUsage == 0){
	switchHwSocket('two','On');
	}

// = Normal charging mode (OFF)	
	if (($hwChargerUsage <= $chargerWattsIdle || $hwInvReturn != 0 || $hwSolarReturn == 0 || $faseProtect == 1) && ($hwChargerOneStatus == 'On' || $hwChargerTwoStatus == 'On' || $hwChargerThreeStatus == 'On')){			
		if (!$isManualRun && $vars['keepBMSalive'] !== true){
		switchHwSocket('two','Off'); sleep(1);
		switchHwSocket('three','Off'); sleep(1);
		switchHwSocket('one','Off');
		}		
	}
?>