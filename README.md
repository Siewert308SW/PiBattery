![Banner](https://github.com/user-attachments/assets/143c1ac7-f58b-4016-88dd-2aac3e8cd6f2)
# PiBattery – Eenvoudige Zelfbouw Thuisbatterij

**PiBattery** Er niets zo veranderlijk als onze energie-markt en Den Haag is nog wispelturiger dan het Nederlandse weer.<br/>
In de aanloop naar terugleverkosten en einde saldering zocht ik naar een goedkope oplossing om kosten te drukken.<br/>
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
En omdat dit stekker-omvormers zijn is de injectie net zoals de kant en klare oplossingen beperkt tot 800w.<br/>

---

## Basis Werking
**Het laden** van deze setup is simpel.<br/>
De php scripts berekenen aan de hand van de instellingen en configuratie of er geladen mag worden.<br/>
Hij start de laders op basis van het pv-overschot en kan de batterijen in mijn setup laden met:<br/>
- **350w**<br/>
- **700w**<br/>
- **1000w** of **1300w**<br/>
Dit hangt dus af van het beschikbare overschot.<br/>
</br>
Op basis van verschillende calculaties kan hij de laders individueel in- en uitschakelen.<br/>
Dit schakelen word gedaan door de Homewizard lokale API aan te sturen.<br/>
En heeft een schakel pauze functie om onnodig in- uitschakelen te voorkomen.<br/>
Tevens berekend hij het laadverlies om in de debug outut de correcte batterij SOC en laad- ontlaad tijden weer te geven.<br/>
<br/>

**Het ontladen** is nog simpeler.<br/>
Hier word op basis van het P1 verbruik uitgerekend of er teveel verbruik van het NET is.<br/>
En hierop word de benodigde wattage wat nodig is via de API van EcoFlow de omvormers aangestuurd.<br/>
Op deze manier word er dus aangestuurd op NOM (Nul Op De Meter).<br/>
Tevens word er rekening gehouden met zomer- en winterijd.<br/>
Dat wil zeggen dat er bepaalde zaken anders worden geregeld als het zomer- of wintertijd is.<br/>
Denk dat bijvoorbeeld dat de batterij minder diep word ontladen om langdurige slechte pv-productie dagen te overbruggen.<br/>
Ook word er rekening gehouden met korte hoge stroom pieken.<br/>
Het kan zijn dat bepaalde apparaten in huis heel even stroom verbruiken.<br/>
Het script zal hier niet direct op reageren om zo onnodig schakelen te voorkomen.<br/>
  
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
- 1x Raspberry Pi4 met behuizing, USB 64gb opslag en adapter
- 1x HomeWizard p1-meter
- 1x HomeWizard 3fase kWh-meter (realtime pv opwek uitlezen)
- 6x HomeWizard Energy-sockets
- 2x EcoFlow Powerstream 800w omvormers
- 2x EcoFlow Coolingdecks
- 2x 12cm USB powered pc fans
- 2x Victron IP22 12a laders
- 1x Powerqueen 20a LifePo4 lader
- 3x Powerqueen 25,6v 100ah LFP batterijen in parallel
- Klein materiaal zoals zekeringen, bekabeling en batterij schakelaar ect 
Totale kosten voor deze 7,5kWh thuisbatterij waren door slim inkopen €2100<br/>

---

## Installatie

** De installatie** bespreek ik hier niet want ga er vanuit dat diegene die hier mee wil spelen enige kennis van zaken heeft.<br/>
Maar in het kort komt het er hier op neer.<br/>
Je draait deze scripts op:</br>
1. **Raspberry Pi** (of andere Linux SBC/VM)
2. **PHP 8.x** geïnstalleerd  
3. **piBattery.php** word elke x seconden via cron aangeroepen
4. **HomeWizard** API aansturing voor P1-meter, kWh-meter & energy-sockets is ingeschakeld    
5. **EcoFlow API** is open gesteld via EcoFlow IoT Developer Platform voor API-sturing   
6. (Optioneel) **Domoticz** voor het door sturen van alle data om te loggen
7. **Elektrische** gedeelte vereist kennis, vertrouw je het niet dan laat je iemand met kennis dit uitvoeren.
8. De batterijen zijn parallel aangesloten.
Deze zijn op hun beurt weer aangesloten op de PV ingang op de omvormers.<br/>
De omvormers zijn op hun beurt weer met een stekker in een vrije WCD gestoken.<br/>
In mijn geval twee vrije groepen.<br/>
De laders zijn parallel aangesloten op de batterijen.<br/>
Hier een handig linkje met een wat uitgebreide uitleg:<br/>
https://ehoco.nl/eenvoudige-thuisbatterij-zelf-maken/

---

## Bestands- en Mappenstructuur

```
pibattery/
│
├─ pibattery.php                # Hoofdscript en word via cron elke 20sec aangeroepen
├─ bootstrap/
│   └─ bootstrap.php            # 1e initialisatie
├─ config/
│   └─ config.php               # Instellingen (hardware, batterijen, API’s, e.d.)
├─ data/
│   ├─ timeStamp.json           # Tijdsregistratie laatste runs
│   └─ variables.json           # Alle variabelen en tijdelijke data
├─ includes/
│   ├─ ecoflow_api_class.php    # API-integratie voor EcoFlow apparaten
│   ├─ functions.php            # Algemene functies
│   ├─ helpers.php              # Diverse hulpjes en utilities
│   └─ variables.php            # Variabelen en dynamische waarden
├─ lang/
│   ├─ langNL.php               # Nederlandse taal
│   └─ langEN.php               # Engelse taal
└─ scripts/
    ├─ baseload.php             # Baseload (basisvermogen) sturing
    ├─ charge.php               # Laadlogica beheer
    └─ domoticz.php             # Koppeling met Domoticz
```

---

## Configuratie

Alle belangrijke instellingen vind je in `config/config.php`. Hier stel je onder meer in:
- IP-adressen en API-keys voor HomeWizard/EcoFlow
- Batterijcapaciteit, type en spanning
- Laad-/ontlaadregimes (dag/nacht)
- Pauzetijden, hysterese en meetdrempels
- Optionele Domoticz-koppelingen (met IDX-nummers)

---

## Domoticz-integratie (optioneel)

Wil je actuele waarden in Domoticz zien?<br/> 
Vul dan je Domoticz-IP en de juiste IDX-nummers in voor de gewenste dummy devices in de configuratie.<br/>  
Het script `scripts/domoticz.php` regelt automatische updates van je batterij- en energiestatus in Domoticz.<br/>

---

## Bijdragen & Licentie

Dit project is open source en bedoeld om samen te verbeteren.<br/> 
Pull requests, feedback en suggesties zijn welkom.<br/>

---

## Grote dank

Mijn dank voor dit project gaat uit naar:
- Thijsmans voor het beschikbaar stellen van de EcoFlow API aansturing
- ehoco.nl voor de inspiratie van dit project
- salipander voor starten van "Eenvoudige thuisaccu samenstellen" topic op Tweakers
- En allen die ik vergeten ben ;-)

---

## Handige linkjes

Om je opweg te helpen en inspiratie op te doen hier wat handige linkjes waar ik mijn project op gebasseerd hebt.<br/>
- Tweaker: Eenvoudige thuisaccu samenstellen [Eenvoudige thuisaccu samenstellen](https://gathering.tweakers.net/forum/list_messages/2253584/0)
---

**Veel plezier met PiBattery en een slimme, duurzame thuisbatterij!**
