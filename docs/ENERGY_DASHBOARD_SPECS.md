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
