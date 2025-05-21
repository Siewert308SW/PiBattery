![Banner](https://github.com/user-attachments/assets/143c1ac7-f58b-4016-88dd-2aac3e8cd6f2)
# PiBattery – Open Source Thuisbatterij & Zonnestroom Automatisering

**PiBattery** is een open source project voor het slim aansturen van een thuisbatterij in combinatie met zonnepanelen. Het script is gericht op de Raspberry Pi (of vergelijkbare systemen) en maakt het mogelijk om je (EcoFlow) thuisbatterij, laders en micro-omvormers automatisch te beheren op basis van actuele energiegegevens, verbruik, opbrengst én slimme regels.  
Geschikt voor de Nederlandse thuissituatie – maar makkelijk aanpasbaar.

---

## Functies & Mogelijkheden

- **Volautomatisch laden/ontladen** van je EcoFlow-batterij op basis van P1-verbruik, zonne-opbrengst en stroomprijs.
- **Peak shaving**: piekverbruik uitvlakken en meer eigen zonnestroom benutten.
- **Slimme schakeling** van laders en omvormers via HomeWizard P1/Solar, slimme stekkers en directe API-aansturing.
- **Laadverlies-meting**: automatisch verlies bijladen en corrigeren op basis van echte metingen.
- **Ondersteuning voor Domoticz**: actuele batterijstatus en energiegegevens doorgeven aan je Domoticz smart home.
- **Pauzefunctie en tijdschema’s**: voorkom onnodig laden bij wolkendips of slechte zonneprognose.
- **Meertaligheid**: zowel Nederlands als Engels.
- **Uitgebreide logging en debug-output** voor probleemoplossing en finetuning.

---

## Bestands- en Mappenstructuur

```
pibatteryTest/
│
├─ pibattery.php                # Hoofdscript voor automatisering en logica
├─ bootstrap/
│   └─ bootstrap.php            # Opstartlogica en initialisatie
├─ config/
│   └─ config.php               # Instellingen (hardware, batterijen, API’s, e.d.)
├─ data/
│   ├─ timeStamp.json           # Tijdsregistratie laatste runs
│   └─ variables.json           # Alle variabelen en tijdelijke data
├─ includes/
│   ├─ ecoflow_api_class.php    # API-integratie voor EcoFlow apparaten
│   ├─ functions.php            # Algemene functies (helpers, berekeningen)
│   ├─ helpers.php              # Diverse hulpjes en utilities
│   └─ variables.php            # Variabelen en dynamische waarden
├─ lang/
│   ├─ langNL.php               # Nederlandse taal
│   └─ langEN.php               # Engelse taal
└─ scripts/
    ├─ baseload.php             # Baseload (basisvermogen) sturing
    ├─ charge.php               # Laadlogica en batterijbeheer
    └─ domoticz.php             # Koppeling met Domoticz
```

---

## Installatie & Benodigdheden

1. **Raspberry Pi** (of andere Linux SBC/VM)
2. **PHP 8.x** geïnstalleerd  
3. **HomeWizard P1 Meter** & (optioneel) HomeWizard Solar en slimme stekkers  
4. **EcoFlow batterij en omvormers** (voor API-sturing)  
5. (Optioneel) **Domoticz** smart home platform

**Installeren:**
- Download deze repository naar je Pi:  
  `git clone https://github.com/<jouw-gebruikersnaam>/pibattery.git`
- Controleer en pas de instellingen aan in `config/config.php` (zie verderop).
- Zorg dat PHP toegang heeft tot de benodigde apparaten/APIs.
- Zet een cronjob aan voor het gewenste script, bijvoorbeeld elke 15 seconden:
  ```
  */1 * * * * php /pad/naar/pibattery.php
  ```
- Zie ook eventuele vereisten in de code (zoals `curl` voor API-aanroepen).

---

## Configuratie

Alle belangrijke instellingen vind je in `config/config.php`. Hier stel je onder meer in:
- IP-adressen en API-keys voor HomeWizard/EcoFlow
- Batterijcapaciteit, type en spanning
- Laad-/ontlaadregimes (dag/nacht)
- Pauzetijden, hysterese en meetdrempels
- Optionele Domoticz-koppelingen (met IDX-nummers)

De map `data/` bevat tijdelijke waarden zoals het laatste laad-/ontlaadmoment, variabelen, en logbestanden. Dit wordt automatisch beheerd door het script.

---

## Taalinstellingen

De interface en console-output kunnen naar wens in het Nederlands of Engels. Pas dit aan in je configuratie (`$debugLang` in `config.php` of elders in de code):

```php
$debugLang = 'NL'; // Of 'EN'
```

---

## Domoticz-integratie (optioneel)

Wil je actuele waarden in Domoticz zien?  
Vul dan je Domoticz-IP en de juiste IDX-nummers in voor de gewenste dummy devices in de configuratie.  
Het script `scripts/domoticz.php` regelt automatische updates van je batterij- en energiestatus in Domoticz.

---

## Gebruik en tips

- Start het script met `php pibattery.php` of via een cronjob.
- Raadpleeg de console-output/logs voor actuele status, foutmeldingen en optimalisaties.
- Pas waar nodig de thresholds aan voor jouw installatie.
- Meerdere scripts (zoals `charge.php` en `baseload.php`) zijn los inzetbaar voor geavanceerd gebruik.

---

## Bijdragen & Licentie

Dit project is open source en bedoeld om samen te verbeteren! Zie LICENSE voor voorwaarden.  
Pull requests, feedback en suggesties zijn welkom.

---

**Veel plezier met PiBattery en een slimme, duurzame thuisbatterij!**
