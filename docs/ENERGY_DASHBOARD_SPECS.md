# Energy Dashboard Specificaties

## Doel
Standalone live dashboard voor energieflows.

## Technische Aanpak
- **Layering:** PNG achtergrond (MyHouse.png), SVG overlay (MyHouse.svg) met `position: absolute`.
- **Animatie:** `stroke-dasharray` + `stroke-dashoffset` animatie op `<path>` elementen.
- **Transformatie:** Polygonen uit de bron-SVG moeten handmatig of via script worden omgezet naar `<path d="M...L...">` om animatie toe te laten.
- **Richting:** Negatieve eindwaarde = vooruit, Positieve eindwaarde = achteruit.

## Energie Flows (Klassen & Stijlen)
- .green (cls-5): #39b54a (Zonne-energie)
- .orange (cls-2): #f7941d (Batterij)
- .yellow (cls-4): #fee756 (Import net)
- .red (cls-3): #ed1c24 (Export net)
- .blue (cls-1): #00aeef (Gas)

## CSS Keyframes
@keyframes flow-fwd { to { stroke-dashoffset: -40; } }
@keyframes flow-rev { to { stroke-dashoffset: 40; } }

## Logica & Sensor Mapping
Alle lijnen kunnen slechts in 1 richting animeren. Welke lijnen animatie vertonen, is afhankelijk van de voor opgewekte energie, gas en electriciteit die we hier aan gaan koppelen (ook aanwezig in energy.php dashboard). 

**GAS:**
De blauwe lijn is gasconsumptie, en die gaat steeds van onder (gasleverancier) naar boven (condensatieketel) en mag ook altijd animeren.

**ELECTRICITEIT:**
De animaties voor de rode, groene en oranje lijn moeten steeds vanuit de bovenkant naar onder stromen. De gele lijn gaat over stroom die we van het net verbruiken.
De meest linke groene lijn is het eigen verbruik rechtsreeks van opgewekte stroom uit de zonnepannelen. Animatie is hier actief wanneer sensor.zonneenergie_productie_nu > 0 is en tegelijk sensor.electriciteit_netverbruik_nu 0 is en de sensor.batterij_status niet "Laden" is.
De meest rechts groene lijn moet animeren van boven links naar onder rechts, wanneer sensor.zonneenergie_productie_nu > 0 is en tegelijk de sensor.batterij_status wel "Laden" is.
De rode lijn die van boven vertrekt, is de verbinding tussen de omvormer voor onze zonnepannelen (sensor.zonneenergie_productie_nu) en de electriciteitsmeter. 
Het rode stuk verder onderaan,is voor de verbinding tussen de electriciteitsmeter en het stroom netwerk en gaat over injectie naar de leverancier toe (sensor.electriciteit_injectie_nu).
Er is pas animatie voor deze lijnen als een van deze daaraan respectievelijk gekoppelde waarden groter is dan 0.
De gele lijn animeert pas als er stroom van het net gehaald wordt (waarde sensor.electriciteit_netverbruik_nu > 0), van onder naar boven.
