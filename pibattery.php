<?php
//															     //
// **************************************************************//
//           		 PiBattery Solar Storage                     //
//                                                               //
// **************************************************************//
//																 //

// = Variables
	$timeStamp 				= time();
	$piBatteryPath 			= __DIR__ . '/';
	$timeStampFile 			= $piBatteryPath . 'data/timeStamp.json';
	
	$scriptTimer 			= [];
	$scriptTimer 			= file_exists($timeStampFile) ? json_decode(file_get_contents($timeStampFile), true) : [];
	$runCharger 			= false;
	$runBaseload 			= false;
	$runDomoticz 			= false;
	
	require $piBatteryPath . 'bootstrap/bootstrap.php';

// = Determine is script called by terminal
	$isCliInteractive 		= function_exists('posix_isatty') && posix_isatty(STDOUT);
	$isManualRun 			= php_sapi_name() === 'cli' && $isCliInteractive;
	$isCronRun 				= php_sapi_name() === 'cli' && !$isCliInteractive;
	
// = Determine if Charger script may execute
	if (!isset($scriptTimer['lastChargerRun']) || ($timeStamp - $scriptTimer['lastChargerRun']) >= $chargeTimer) {
		$runCharger = true;
		$scriptTimer['lastChargerRun'] = $timeStamp;

		if ($runCharger && $hwInvReturn == 0) {
			writeJsonLocked($timeStampFile, $scriptTimer);
			require_once $piBatteryPath . 'scripts/charge.php';
		}
	
	}

// = Determine if Baseload script may be executed	
	if (!isset($scriptTimer['lastBaseloadRun']) || ($timeStamp - $scriptTimer['lastBaseloadRun']) >= $baseloadTimer) {
		$runBaseload = true;
		$scriptTimer['lastBaseloadRun'] = $timeStamp;
		
		if ($runBaseload) {
			writeJsonLocked($timeStampFile, $scriptTimer);
			require_once $piBatteryPath . 'scripts/baseload.php';
		}
	}

// = Determine if Domoticz script may be executed	
	if (!isset($scriptTimer['lastDomoticzRun']) || ($timeStamp - $scriptTimer['lastDomoticzRun']) >= $domoticzTimer) {
		$runDomoticz = true;
		$scriptTimer['lastDomoticzRun'] = $timeStamp;
		
		if ($runDomoticz) {
			sleep(1);
			writeJsonLocked($timeStampFile, $scriptTimer);
			require_once $piBatteryPath . 'scripts/domoticz.php';
		}
	}
	
// = Include Debug Output	
	if ($debug == 'yes' && $isManualRun){	
		echo ' '.PHP_EOL;
		echo '  ---------------------------------------------------'.PHP_EOL;
	    echo '  --                   PiBattery                   --'.PHP_EOL;
		echo '  --            '.$batteryCapacitykWh.' kWh Solar Storage             --'.PHP_EOL;
		echo '  ---------------------------------------------------'.PHP_EOL;
		echo ' '.PHP_EOL;
	
		$files = glob(__DIR__ . '/lang/*.php');
		foreach ($files as $file) {
			if ($file != __FILE__) {
				require_once($file);
			}
		}
		
		echo ' '.PHP_EOL;
		echo '  ---------------------------------------------------'.PHP_EOL;
		echo '  --                     The End                   --'.PHP_EOL;
		echo '  ---------------------------------------------------'.PHP_EOL;
		echo ' '.PHP_EOL;
			
	}
?>