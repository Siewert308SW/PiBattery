<?php
//															     //
// **************************************************************//
//           		 PiBattery Solar Storage                     //
//                        Config variables                       //
// **************************************************************//
//                                                               //

// = Debug?
	$debug                  = 'yes';        					 // Value 'yes' or 'no'
	$debugLang				= 'NL';								 // Debug output language, EN for English - NL for Dutch

// = Schedule variables
	$invStartTime           = '00:00';      					 // Inverter start time (used when $runInfinity == 'no')
	$invEndTime             = '13:00';      					 // Inverter end time (used when $runInfinity == 'no')
	$runInfinity            = 'yes';        					 // Value 'yes' or 'no'. If 'yes', the inverter will continue to generate power if possible, depending on settings

// = Location variables
	$latitude               = '00.00000';   					 // Latitude
	$longitude              = '-0.00000';   					 // Longitude
	$zenitLat               = '89.5';       					 // Zenith latitude: the highest point of the sky as seen from the observer’s location
	$zenitLong              = '91.7';       					 // Zenith longitude: the highest point of the sky as seen from the observer’s location
	$timezone               = 'Europe/Amsterdam'; 				 // My php.ini doesn't apply the timezone, so it’s set manually here

// = Battery variables
	$batteryVolt            = 25.6;         					 // Battery Voltage
	$batteryAh              = 300;          					 // Total Ah of all batteries
	$batteryMinimum         = 10;           					 // Minimum percentage to keep in the battery, wintertime will be automaticly set to 25%
	$keepBMSalive		    = 'yes';                             // Value 'yes' or 'no' if 'yes' After a long inactivity the battery will charge a bit to keep BMS awake
	
// = Inverter variables
	$ecoflowMaxOutput       = 1150;         					 // Maximum output (Watts) the inverter is allowed to deliver
	$ecoflowMinOutput       = 50;          					     // Minimum output (Watts); the inverter is allowed to deliver
	$ecoflowOutputOffSet    = 5;           					     // Subtract this value (Watts) from the new baseload: this part is always imported from the grid to prevent injection
	$ecoflowMaxInvTemp      = 65;           					 // Maximum internal temperature (°C); inverter stops feeding above this temperature
	
// = Charger variables
	$chargerWattsIdle       = 200;          					 // Standby Watts of all chargers when the batteries are full
	$chargerPausePct        = 85;           					 // When battery has been charged 100% till what % has it to drop before charging is allowed again
	$chargerhyst            = 150;          					 // Only turn off chargers if import exceeds this many Watts (prevents flip-flopping)
	$chargerPause           = 60;          					     // Delay in seconds before toggling chargers (prevents flip-flops)
	
// = Phase protection
	$faseProtection         = 'yes';        				     // Value 'yes' or 'no'
	$maxFaseWatts           = 4500;         				     // If 'yes' whats the max Watts to guard, all chargers are turned off to prevent overloading
	$fase                   = 1;            				     // Which phase to protect

// = HomeWizard variables
	$hwP1IP                 = '0.0.0.0';     			         // HomeWizard P1-meter IP address
	$hwKwhIP                = '0.0.0.0';     			         // HomeWizard Solar kWh meter IP address
	$hwEcoFlowOneIP         = '0.0.0.0';     			         // HomeWizard EcoFlow One socket IP address
	$hwEcoFlowTwoIP         = '0.0.0.0';     			         // HomeWizard EcoFlow Two socket IP address
	$hwChargerOneIP         = '0.0.0.0';     			         // HomeWizard Charger ONE (300W socket) IP address
	$hwChargerTwoIP         = '0.0.0.0';     			         // HomeWizard Charger TWO (600W socket) IP address
	$hwChargerThreeIP       = '0.0.0.0';     			         // HomeWizard Charger THREE (300W socket) IP address
	$hwEcoFlowFanIP         = '0.0.0.0';     			         // HomeWizard FAN socket IP address

// = Chargers
	$chargers = [
		'charger1' => ['ip' => ''.$hwChargerOneIP.'', 'power' => 300, 'label' => 'one', 'master' => true],
		'charger2' => ['ip' => ''.$hwChargerTwoIP.'', 'power' => 600, 'label' => 'two', 'master' => false],
		'charger3' => ['ip' => ''.$hwChargerThreeIP.'', 'power' => 300, 'label' => 'three', 'master' => false],
	];

// = Ecoflow Powerstream API variables
	$ecoflowAccessKey	    = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';// Powerstream API access key
	$ecoflowSecretKey	    = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';// Powerstream API secret key
	$ecoflowOneSerialNumber = 'HWXXXXXXXXXXXXXX';		         // Powerstream One serialnummer
	$ecoflowTwoSerialNumber = 'HWXXXXXXXXXXXXXX';		         // Powerstream Two serialnummer
?>