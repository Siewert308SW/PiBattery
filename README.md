![Banner](https://github.com/user-attachments/assets/143c1ac7-f58b-4016-88dd-2aac3e8cd6f2)
# PiBattery – Eenvoudige Zelfbouw Thuisbatterij

**PiBattery** Er niets zo veranderlijk als onze energie-markt en Den Haag is nog wispelturiger dan het Nederlandse weer.<br/>
In de aanloop naar terugleverkosten en einde saldering zocht ik naar een goedkope en simpele oplossing om kosten te drukken.<br/>
Mede door stijgende energiekosten en komende maatregelen die Den Haag en de energieboeren voor ogen hebben wordt energieopslag thuis steeds aantrekkelijker.<br/>
De huidige prijzen van kant&klare thuisbatterijen zijn wat mij betreft ook nog niet aantrekkelijk genoeg.<br/>
En ik verwacht met de komende populariteit van de thuisbatterij dat de prijzen op basis van vraag en aanbod alleen maar zullen stijgen.<br/>
Een zelfbouw thuisbatterij kan een betaalbare oplossing zijn om de overtollige energie die je zelf opwekt op te slaan.<br/>
In mijn zoektocht naar een oplossing kwam ik uit in een thread op Tweakers.<br/>
Waarin een goedkope oplossing word besproken en daar op voortbordurend wil ik graag mij setup en scripts met jullie delen.<br/>
Ik leg hier de simpele basis principes uit, diep in de materie ga ik niet.<br/>
Verwacht dat diegene die hiermee aan de slag gaat enige technischekennis, php en aanverwante hebben.<br/>

---
## Doel

**Doel** met deze setup is om in de nachten en avonden aan te sturen op NOM (nul op de meter).<br/>
En overdag de grootste verbruiks pieken wil afvlakken.<br/>
Handelen zoals inkoop/verkoop in combinatie met een dynamisch contract is niet mijn doel.<br/>

Ik tracht eerder de maandelijkse kosten te drukken door overshot van mijn zonnepanelen op te slaan in de batterij en dat scheelt het een paar centen terugleverkosten.<br/>
En doordat ik die opgeslagen energie 's avonds en 's nachts weer gebruik scheelt het afnamen van het net.<br/>
En daarmee probeer ik de maandelijkse energiekosten te drukken.<br/>

De setup die ik hier heb hangen is/lijkt op een kant en klare stekker batterij zoals die van HomeWizard of Marstek ect.<br/>
Alleen is het een zelfbouw en mist dus een gelikt kastje.<br/>
En in tegenstelling tot de grote jongens is het een systeem die niet offgrid kan.<br/>
Verder maak ik gebruik van twee EcoFlow Powerstream 800w omvormers.<br/>
En omdat dit stekker-omvormers zijn is de injectie net zoals de kant en klare oplossingen beperkt to 800w.<br/>
En aangezien ik met 25,6v LFP batterijen werk is de injectie beperkt tot max 600w per omvormer.<br/>
Maar in mijn thuisituatie is 1200w injectie meer dan genoeg.<br/>

---

## Functies & Mogelijkheden

- **Volautomatisch laden/ontladen** van de batterijen word gedaan op basis van P1-verbruik, zonne-opbrengst.
- **Slimme schakeling** van laders en omvormers via HomeWizard P1-meter, kWh-meter, energy-sockets en directe API-aansturing.
- **Laadverlies-meting** word automatisch berekend en gecorrigeerd op basis van laden en ontladen.
- **Ondersteuning voor Domoticz**: actuele batterijstatus en energiegegevens worden doorgeven aan Domoticz.
- **Pauzefunctie en tijdschema’s**: voorkom onnodig laden bij wolkendips of slechte zonneprognose.
- **Meertaligheid**: zowel Nederlands als Engels.
- **Uitgebreide logging en debug-output** voor probleemoplossing en finetuning.
- **Fase bescherming**: Laders word direct uitgeschakeld indien de Fase waarop de laders zijn aangesloten een te hoog verbruik heeft.
- **Extra koeling** in de vorm van 12cm pc fans op de omvormers worden automatisch aangestuurd.

---

## Mijn Setup & Kosten
- 1x HomeWizard p1-meter
- 1x HomeWizard 3fase kWh-meter (realtime pv opwek uitlezen)
- 6x HomeWizard Energy-sockets
- 2x EcoFlow Powerstream 800w omvormers
- 2x EcoFlow Coolingdecks
- 2x 12cm USb powered pc fans
- 2x Victron IP22 12a laders
- 1x Powerqueen 20a LifePo4 lader
- 3x Powerqueen 25,6v 100ah LFP batterijen
- Klein materiaal zoals zekeringen, bekabeling en batterij schakelaar ect  

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
