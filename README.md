# Handleiding `events.json` (Speciale Dagen Dashboard)

Het "Speciale Dagen" dashboard (`speciale_dagen.php`) laadt zijn gegevens in vanuit het configuratiebestand `events.json`. Dit bestand gebruikt het JSON (JavaScript Object Notation) formaat.

Dit document beschrijft in detail hoe je nieuwe gelegenheden (events) kunt toevoegen, wijzigen en welke flexibele formules er allemaal ondersteund worden. Ook beschrijft het hoe de data in de UI automatisch gekoppeld wordt aan de dynamische filters (ALLE, GEZIN, FAMILIE, FEESTDAGEN, ANDERE).

## Basisstructuur

Het bestand `events.json` bevat altijd een lijst (Array) van objecten. Elke set accolades `{ }` stelt één gelegenheid voor. De velden worden gescheiden door een komma.

```json
[
  {
    "type": "vast",
    "category": "verjaardag",
    "name": "Linda",
    "date": "1969-10-05"
  },
  {
    "type": "flexibel",
    "category": "feestdag",
    "name": "Pasen",
    "formula": "easter 0"
  }
]
```

## Beschikbare Parameters (Sleutels)

Elk event object kan de volgende parameters bevatten:

### 1. `type` (Verplicht)
Bepaalt hoe de datum berekend moet worden.
- **`vast`**: Gebruik dit voor datums die altijd op dezelfde dag en maand vallen (zoals verjaardagen, huwelijksverjaardagen of vaste feestdagen zoals Kerstmis). Vereist de parameter `date`.
- **`flexibel`**: Gebruik dit voor datums die elk jaar veranderen op basis van een berekening (zoals Pasen, Moederdag, of Black Friday). Vereist de parameter `formula`.

### 2. `category` (Verplicht)
Bepaalt het icoontje, de kleurcodering in de UI en de automatische tekstgeneratie.
- **`verjaardag`**: (Kleur: Groen / `var(--ok)`) Berekent automatisch de leeftijd (bijv. "wordt 36 jaar").
- **`huwelijk`**: (Kleur: Oranje / `var(--warn)`) Berekent automatisch de jubileumjaren (bijv. "10 jaar getrouwd").
- **`feestdag`**: (Kleur: Rood / `var(--alert)`) Gebruikt standaard "Feestdag" als label.
- **`sport`**: (Kleur: Blauw / `var(--accent)`) Voor sportevenementen (zoals WK2026).
- **`andere`** (of **`interessant`**): (Kleur: Blauw / `var(--accent)`) Een algemene categorie. Ideaal in combinatie met een eigen `boodschap`.

### 3. `name` (Verplicht)
De weergavenaam van de gelegenheid (bijv. "Matthijs & Steffie", "Oudjaar", "Vakantie").

### 4. `date` (Verplicht als `type` = `vast`)
De vaste datum. Kan in twee formaten geschreven worden:
- **`YYYY-MM-DD`** (Jaar-Maand-Dag): Bijvoorbeeld `"1969-10-05"`. Gebruik dit altijd voor verjaardagen en huwelijken. Het dashboard heeft het jaartal nodig om de leeftijd/jubileumjaren te berekenen en toont dit jaartal ook in het dashboard.
- **`MM-DD`** (Maand-Dag): Bijvoorbeeld `"12-31"`. Gebruik dit voor vaste feestdagen (zoals Kerstmis of Oudjaar) waarvan het originele jaartal niet relevant is.

### 5. `formula` (Verplicht als `type` = `flexibel`)
Een dynamische formule waarmee het dashboard de datum voor het huidige of volgende jaar berekent. Er zijn 3 syntax-smaken:

#### A. Paas-gebaseerd (`easter [offset]`)
Berekent de datum ten opzichte van Paaszondag.
- `"easter 0"` = Paaszondag.
- `"easter -49"` = Carnaval (49 dagen voor Pasen).
- `"easter +50"` = Pinkstermaandag (50 dagen na Pasen).
- `"easter +39"` = Hemelvaartsdag.

#### B. Weekdag-gebaseerd (`weekday [occurrence] [day_of_week] [month]`)
Zoekt naar een specifieke dag in een specifieke maand.
- **`[occurrence]`**: `1` (eerste), `2` (tweede), `3` (derde), `4` (vierde), `5` (vijfde), of `last` (laatste).
- **`[day_of_week]`**: `0`=Zondag, `1`=Maandag, `2`=Dinsdag, `3`=Woensdag, `4`=Donderdag, `5`=Vrijdag, `6`=Zaterdag.
- **`[month]`**: `1` (Januari) t/m `12` (December).
- *Voorbeelden:*
  - `"weekday 2 0 5"` = Moederdag in België (2e Zondag (0) van Mei (5)).
  - `"weekday 2 0 6"` = Vaderdag in België (2e Zondag (0) van Juni (6)).
  - `"weekday last 0 3"` = Begin Zomeruur (Laatste Zondag (0) van Maart (3)).
  - `"weekday last 0 10"` = Begin Winteruur (Laatste Zondag (0) van Oktober (10)).

#### C. Thanksgiving-gebaseerd (`thanksgiving [offset]`)
Berekent een datum ten opzichte van de Amerikaanse Thanksgiving (de 4e donderdag van november).
- `"thanksgiving 0"` = Thanksgiving.
- `"thanksgiving +1"` = Black Friday (1 dag na Thanksgiving).

### 6. `boodschap` (Optioneel)
Een aangepaste tekst die getoond wordt onder de naam (in de grote/gekleurde `msg-highlight` weergave). Dit is zeer handig voor de categorie `andere`.
- Voorbeeld: `"boodschap": "Eindelijk vakantie!"`

### 7. `info` (Optioneel)
Een multifunctioneel veld dat gebruikt wordt voor zowel weergave als voor filtering via de UI-knoppen bovenaan het dashboard.
- Voor categorie **`interessant`**: De waarde wordt na de naam en datum getoond als een informatief bericht (bijv. `"info": "Klok gaat 1 uur achteruit"`).
- Voor categorieën **`verjaardag`** en **`huwelijk`**: De tekst wordt verborgen op het scherm, maar gebruikt om te bepalen of het event onder de UI-filter **GEZIN** of **FAMILIE** valt. (bijv. `"info": "Gezin"` of `"info": "Familie"`).

---

## Complete Voorbeelden

Hier is een combinatie van verschillende correcte formats:

```json
[
  { 
    "type": "vast", 
    "category": "verjaardag", 
    "name": "Linda", 
    "date": "1969-10-05",
    "info": "Gezin"
  },
  { 
    "type": "vast", 
    "category": "huwelijk", 
    "name": "Matthijs & Steffie", 
    "date": "2018-08-09",
    "info": "Familie"
  },  
  { 
    "type": "vast", 
    "category": "feestdag", 
    "name": "Oudjaar", 
    "date": "12-31" 
  },
  { 
    "type": "vast", 
    "category": "andere", 
    "name": "Reis naar Spanje", 
    "date": "07-15",
    "boodschap": "Vertrek om 08:00!"
  },
  { 
    "type": "flexibel", 
    "category": "feestdag", 
    "name": "Pasen", 
    "formula": "easter 0" 
  },
  { 
    "type": "flexibel", 
    "category": "interessant", 
    "name": "Zomeruur", 
    "formula": "weekday last 0 3",
    "boodschap": "Klok uur vooruit!"
  }
]
```

## Belangrijke Tips
1. Let er altijd op dat de laatste accolades in de lijst **géén komma** hebben. Een extra komma aan het einde van een JSON bestand zorgt voor een crash.
2. Zorg dat datumreeksen (zoals de `date` of het getal voor `month` in de formule) numeriek kloppen. "02-30" is ongeldig en wordt genegeerd.
