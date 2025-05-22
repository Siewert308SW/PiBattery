<?php
//															     //
// **************************************************************//
//           		 PiBattery Solar Storage                     //
//                    Start/Stop Charging P1                     //
// **************************************************************//
//                                                               //
	if ($faseProtect == 0 && $hwInvReturn == 0 && $hwSolarReturn != 0){
		
		if (!$varsState['pauseCharging']) {
			chargerSet($chargers, $P1ChargerUsage);
		}
	}
		
	if (($hwChargerUsage <= $chargerWattsIdle || $hwP1Usage > 1300 || $hwInvReturn != 0 || $hwSolarReturn == 0 || $faseProtect == 1) && ($hwChargerOneStatus == 'On' || $hwChargerTwoStatus == 'On' || $hwChargerThreeStatus == 'On')){			
		if (!$isManualRun){
		switchHwSocket('two','Off'); sleep(1);
		switchHwSocket('three','Off'); sleep(1);
		switchHwSocket('one','Off');
		}		
	}
?>