<?php
$eventsFile = '../events.json';

// Process form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Read current events
    $events = [];
    if (file_exists($eventsFile)) {
        $eventsData = json_decode(file_get_contents($eventsFile), true);
        if (is_array($eventsData)) {
            $events = $eventsData;
        }
    }

    if ($action === 'delete') {
        $index = isset($_POST['index']) ? (int)$_POST['index'] : -1;
        if ($index >= 0 && $index < count($events)) {
            array_splice($events, $index, 1);
            file_put_contents($eventsFile, json_encode($events, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $message = "Event succesvol verwijderd.";
        }
    } elseif ($action === 'save') {
        $index = isset($_POST['index']) && $_POST['index'] !== '' ? (int)$_POST['index'] : -1;

        $newEvent = [
            'type' => $_POST['type'] ?? 'vast',
            'category' => $_POST['category'] ?? 'andere',
            'name' => $_POST['name'] ?? '',
        ];

        if ($newEvent['type'] === 'vast') {
            $date = $_POST['date'] ?? '';
            // If the user selected 'jaarlijks' (no year), strip the year from YYYY-MM-DD
            if (isset($_POST['yearly_no_year']) && $_POST['yearly_no_year'] === '1' && strlen($date) === 10) {
                $date = substr($date, 5); // Take MM-DD
            }
            $newEvent['date'] = $date;
        } else {
            $newEvent['formula'] = $_POST['formula'] ?? '';
        }

        if (!empty($_POST['info'])) {
            $newEvent['info'] = $_POST['info'];
        }

        if (!empty($_POST['boodschap'])) {
            $newEvent['boodschap'] = $_POST['boodschap'];
        }

        // Validate mandatory fields
        if (empty($newEvent['name'])) {
            $message = "Fout: Naam is verplicht.";
        } elseif ($newEvent['type'] === 'vast' && empty($newEvent['date'])) {
            $message = "Fout: Datum is verplicht voor een vast event.";
        } elseif ($newEvent['type'] === 'flexibel' && empty($newEvent['formula'])) {
            $message = "Fout: Formule is verplicht voor een flexibel event.";
        } else {
            if ($index >= 0 && $index < count($events)) {
                $events[$index] = $newEvent;
                $message = "Event succesvol gewijzigd.";
            } else {
                $events[] = $newEvent;
                $message = "Nieuw event succesvol toegevoegd.";
            }
            file_put_contents($eventsFile, json_encode($events, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
    }
}

// Read events for display
$events = [];
if (file_exists($eventsFile)) {
    $eventsData = json_decode(file_get_contents($eventsFile), true);
    if (is_array($eventsData)) {
        $events = $eventsData;
    }
}

// Pagination logic
$itemsPerPage = 10;
$totalEvents = count($events);
$totalPages = max(1, ceil($totalEvents / $itemsPerPage));
$currentPage = isset($_GET['page']) ? max(1, min($totalPages, (int)$_GET['page'])) : 1;
$startIndex = ($currentPage - 1) * $itemsPerPage;

// Preserve original index for editing/deleting by creating a mapped array
$mappedEvents = [];
foreach ($events as $index => $event) {
    $mappedEvents[] = ['index' => $index, 'event' => $event];
}
$pagedEvents = array_slice($mappedEvents, $startIndex, $itemsPerPage);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event-admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Barlow+Condensed:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../CSS/common.css">
    <style>
        body { margin: 0; padding: 20px; font-family: 'Share Tech Mono', monospace; background: var(--bg); color: var(--text-bright); }
        .admin-container { max-width: 95%; margin: 0 auto; background: var(--surface); padding: 20px; border-radius: 8px; }
        h1, h2 { font-family: 'Barlow Condensed', sans-serif; color: var(--text-bright); }
        .message { padding: 10px; margin-bottom: 20px; border-radius: 4px; background: rgba(0, 230, 118, 0.1); border: 1px solid var(--ok); color: var(--ok); }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th, td { text-align: left; padding: 10px; border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
        th { background: rgba(0, 0, 0, 0.2); }
        .btn { padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-family: inherit; font-size: 14px; }
        .btn-edit { background: var(--accent); color: var(--bg); }
        .btn-delete { background: var(--alert); color: var(--text-bright); }
        .btn-add { background: var(--ok); color: var(--bg); padding: 10px 20px; font-size: 16px; margin-bottom: 20px; }

        /* Form styling */
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: var(--text-muted); }
        input[type="text"], select { width: 100%; padding: 8px; box-sizing: border-box; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.2); color: var(--text-bright); border-radius: 4px; font-family: inherit; }
        .form-container { background: rgba(0,0,0,0.2); padding: 20px; border-radius: 8px; margin-top: 20px; border: 1px solid rgba(255,255,255,0.1); display: none; }
        .form-container.active { display: block; }
        .help-text { font-size: 12px; color: var(--text-muted); margin-top: 4px; display: block; }
        .event-name { font-weight: bold; color: var(--accent); font-size: 1.1em; }
        .actions-cell { display: flex; gap: 10px; align-items: center; }
    </style>
</head>
<body>

<div class="admin-container">
    <h1>Event-admin</h1>

    <?php if ($message): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <button class="btn btn-add" style="margin-bottom: 0;" onclick="openForm()">+ Nieuw Event Toevoegen</button>

        <?php if ($totalPages > 1): ?>
        <div class="pagination" style="display: flex; gap: 5px;">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" class="btn" style="text-decoration: none; background: <?php echo $i === $currentPage ? 'var(--accent)' : 'rgba(255,255,255,0.1)'; ?>; color: var(--text-bright);"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>Naam</th>
                <th>Type</th>
                <th>Categorie</th>
                <th>Datum / Formule</th>
                <th>Info / Boodschap</th>
                <th>Acties</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pagedEvents as $mappedEvent):
                $index = $mappedEvent['index'];
                $event = $mappedEvent['event'];
                $cat = strtolower($event['category'] ?? '');
                $icon = '📅';
                if ($cat === 'verjaardag') $icon = '🎂';
                elseif ($cat === 'huwelijk') $icon = '💍';
                elseif ($cat === 'feestdag') $icon = '🎉';
            ?>
                <tr>
                    <td class="event-name"><?php echo $icon . ' ' . htmlspecialchars($event['name'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($event['type'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($event['category'] ?? ''); ?></td>
                    <td>
                        <?php
                        if (($event['type'] ?? '') === 'vast') echo htmlspecialchars($event['date'] ?? '');
                        else echo htmlspecialchars($event['formula'] ?? '');
                        ?>
                    </td>
                    <td>
                        <?php
                        $extras = [];
                        if (!empty($event['info'])) $extras[] = "Info: " . htmlspecialchars($event['info']);
                        if (!empty($event['boodschap'])) $extras[] = "Boodschap: " . htmlspecialchars($event['boodschap']);
                        echo implode('<br>', $extras);
                        ?>
                    </td>
                    <td class="actions-cell">
                        <button class="btn btn-edit" onclick="editForm(<?php echo $index; ?>, <?php echo htmlspecialchars(json_encode($event), ENT_QUOTES, 'UTF-8'); ?>)">Bewerk</button>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Zeker dat je dit event wil wissen?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="index" value="<?php echo $index; ?>">
                            <button type="submit" class="btn btn-delete">Wis</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($pagedEvents)): ?>
                <tr><td colspan="6" style="text-align: center;">Geen events gevonden.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div id="eventFormContainer" class="form-container">
        <h2 id="formTitle">Nieuw Event</h2>
        <form method="POST">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="index" id="formIndex" value="">

            <div class="form-group">
                <label for="formName">Naam *</label>
                <input type="text" id="formName" name="name" required placeholder="Bijv. Alice, Pasen, Vakantie">
            </div>

            <div class="form-group">
                <label for="formCategory">Categorie *</label>
                <select id="formCategory" name="category" required>
                    <option value="verjaardag">Verjaardag</option>
                    <option value="huwelijk">Huwelijk</option>
                    <option value="feestdag">Feestdag</option>
                    <option value="interessant">Interessant</option>
                    <option value="andere">Andere</option>
                </select>
            </div>

            <div class="form-group">
                <label for="formType">Type *</label>
                <select id="formType" name="type" required onchange="toggleTypeFields()">
                    <option value="vast">Vast</option>
                    <option value="flexibel">Flexibel</option>
                </select>
            </div>

            <div class="form-group" id="dateGroup">
                <label for="formDate">Datum *</label>
                <input type="date" id="formDate" name="date">
                <div style="margin-top: 5px;">
                    <input type="checkbox" id="formYearly" name="yearly_no_year" value="1" style="width: auto;">
                    <label for="formYearly" style="display: inline; font-size: 14px; color: var(--text-bright);">Jaarlijks event (geen jaartal opslaan, enkel MM-DD)</label>
                </div>
                <span class="help-text">Gebruik het jaartal voor leeftijd/jaren berekening, of vink aan voor jaarlijkse vaste dagen (zoals Nieuwjaar).</span>
            </div>

            <div class="form-group" id="formulaGroup" style="display:none;">
                <label for="formFormula">Formule *</label>
                <input type="text" id="formFormula" name="formula" list="formulaList" placeholder="Kies of typ een formule">
                <datalist id="formulaList">
                    <option value="easter 0">Pasen</option>
                    <option value="easter +39">Hemelvaart</option>
                    <option value="easter +50">Pinksteren</option>
                    <option value="weekday 2 0 5">Moederdag (BE)</option>
                    <option value="weekday 2 0 6">Vaderdag (BE)</option>
                    <option value="weekday last 0 3">Zomeruur</option>
                    <option value="weekday last 0 10">Winteruur</option>
                    <option value="thanksgiving 0">Thanksgiving</option>
                    <option value="thanksgiving +1">Black Friday</option>
                </datalist>
                <span class="help-text">Syntax: 'easter [offset]', 'weekday [occurrence 1-5,last] [day 0-6] [month 1-12]', of 'thanksgiving [offset]'.</span>
            </div>

            <div class="form-group">
                <label for="formInfo">Info (Optioneel)</label>
                <input type="text" id="formInfo" name="info" list="infoList" placeholder="Kies of typ info (bijv. Gezin, Familie)">
                <datalist id="infoList">
                    <option value="Gezin"></option>
                    <option value="Familie"></option>
                </datalist>
                <span class="help-text">Bepaalt de filter voor verjaardag/huwelijk, toont tekst voor 'interessant'.</span>
            </div>

            <div class="form-group">
                <label for="formBoodschap">Boodschap (Optioneel)</label>
                <input type="text" id="formBoodschap" name="boodschap" placeholder="Bijv. Eindelijk vakantie!">
                <span class="help-text">Wordt prominent getoond onder de naam (vooral handig voor 'andere').</span>
            </div>

            <button type="submit" class="btn btn-add">Opslaan</button>
            <button type="button" class="btn" style="background:var(--text-muted); color:var(--surface);" onclick="closeForm()">Annuleren</button>
        </form>
    </div>
</div>

<script>
    function toggleTypeFields() {
        const type = document.getElementById('formType').value;
        const dateGroup = document.getElementById('dateGroup');
        const formulaGroup = document.getElementById('formulaGroup');
        const dateInput = document.getElementById('formDate');
        const formulaInput = document.getElementById('formFormula');

        if (type === 'vast') {
            dateGroup.style.display = 'block';
            formulaGroup.style.display = 'none';
            dateInput.required = true;
            formulaInput.required = false;
        } else {
            dateGroup.style.display = 'none';
            formulaGroup.style.display = 'block';
            dateInput.required = false;
            formulaInput.required = true;
        }
    }

    function openForm() {
        document.getElementById('eventFormContainer').classList.add('active');
        document.getElementById('formTitle').innerText = 'Nieuw Event';
        document.getElementById('formIndex').value = '';
        document.getElementById('formName').value = '';
        document.getElementById('formCategory').value = 'andere';
        document.getElementById('formType').value = 'vast';
        document.getElementById('formDate').value = '';
        document.getElementById('formFormula').value = '';
        document.getElementById('formInfo').value = '';
        document.getElementById('formBoodschap').value = '';
        toggleTypeFields();
        document.getElementById('eventFormContainer').scrollIntoView({ behavior: 'smooth' });
    }

    function editForm(index, event) {
        document.getElementById('eventFormContainer').classList.add('active');
        document.getElementById('formTitle').innerText = 'Event Bewerken';
        document.getElementById('formIndex').value = index;

        document.getElementById('formName').value = event.name || '';
        document.getElementById('formCategory').value = event.category || 'andere';
        document.getElementById('formType').value = event.type || 'vast';

        let d = event.date || '';
        document.getElementById('formYearly').checked = false;
        if (d.length === 5) { // MM-DD format
            // set a dummy year to make the datepicker show the date
            document.getElementById('formDate').value = '2000-' + d;
            document.getElementById('formYearly').checked = true;
        } else {
            document.getElementById('formDate').value = d;
        }

        document.getElementById('formFormula').value = event.formula || '';
        document.getElementById('formInfo').value = event.info || '';
        document.getElementById('formBoodschap').value = event.boodschap || '';

        toggleTypeFields();
        document.getElementById('eventFormContainer').scrollIntoView({ behavior: 'smooth' });
    }

    function closeForm() {
        document.getElementById('eventFormContainer').classList.remove('active');
    }
</script>

</body>
</html>