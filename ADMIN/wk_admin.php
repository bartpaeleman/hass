<?php
require_once __DIR__ . '/../config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['role_level']) || $_SESSION['role_level'] < 50) {
    header("HTTP/1.1 403 Forbidden");
    exit("Toegang geweigerd. Onvoldoende rechten.");
}

$requestedFile = isset($_GET['json-events']) ? $_GET['json-events'] : 'wk2026.json';
$allowedFiles = ['events.json', 'wk2026.json'];
if (!in_array($requestedFile, $allowedFiles)) {
    $requestedFile = 'wk2026.json';
}
$eventsFile = __DIR__ . '/../JSON/' . $requestedFile;
$eventsFileWeb = htmlspecialchars($requestedFile, ENT_QUOTES, 'UTF-8');


// Process form submission
$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Read current events
    $events = [];
    if (file_exists($eventsFile)) {
        $content = file_get_contents($eventsFile);
        if (substr($content, 0, 3) === "\xef\xbb\xbf") {
            $content = substr($content, 3);
        }
        $eventsData = json_decode($content, true);
        if (is_array($eventsData)) {
            $events = $eventsData;
        }
    }

    if ($action === 'delete') {
        $index = isset($_POST['index']) ? (int)$_POST['index'] : -1;
        if ($index >= 0 && $index < count($events)) {
            array_splice($events, $index, 1);
            if (file_put_contents($eventsFile, json_encode($events, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) !== false) {
                $message = "Event succesvol verwijderd.";
            } else {
                $error = "Fout bij opslaan. Controleer of de webserver schrijfrechten heeft op " . htmlspecialchars($requestedFile) . ".";
            }
        }
    } elseif ($action === 'sync_wk') {
        $html = @file_get_contents("https://pouletips.nl/wk-2026/wedstrijden/");
        if ($html === false) {
            $error = "Kon de gegevens van pouletips.nl niet ophalen.";
        } else {
            $dom = new DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new DOMXPath($dom);

            $countryFlags = [
                'Algerije' => '🇩🇿', 'Argentinië' => '🇦🇷', 'Australië' => '🇦🇺', 'België' => '🇧🇪',
                'Bosnië-Herzegovina' => '🇧🇦', 'Brazilië' => '🇧🇷', 'Canada' => '🇨🇦', 'Colombia' => '🇨🇴',
                'Curaçao' => '🇨🇼', 'DR Congo' => '🇨🇩', 'Duitsland' => '🇩🇪', 'Ecuador' => '🇪🇨',
                'Egypte' => '🇪🇬', 'Frankrijk' => '🇫🇷', 'Ghana' => '🇬🇭', 'Haïti' => '🇭🇹',
                'Irak' => '🇮🇶', 'Iran' => '🇮🇷', 'Ivoorkust' => '🇨🇮', 'Japan' => '🇯🇵',
                'Jordanië' => '🇯🇴', 'Kaapverdië' => '🇨🇻', 'Kroatië' => '🇭🇷', 'Marokko' => '🇲🇦',
                'Mexico' => '🇲🇽', 'Nederland' => '🇳🇱', 'Nieuw-Zeeland' => '🇳🇿', 'Noorwegen' => '🇳🇴',
                'Oezbekistan' => '🇺🇿', 'Oostenrijk' => '🇦🇹', 'Panama' => '🇵🇦', 'Paraguay' => '🇵🇾',
                'Portugal' => '🇵🇹', 'Qatar' => '🇶🇦', 'Saudi-Arabië' => '🇸🇦', 'Senegal' => '🇸🇳',
                'Spanje' => '🇪🇸', 'Tsjechië' => '🇨🇿', 'Tunesië' => '🇹🇳', 'Turkije' => '🇹🇷',
                'Uruguay' => '🇺🇾', 'Verenigde Staten' => '🇺🇸', 'Zuid-Afrika' => '🇿🇦',
                'Zuid-Korea' => '🇰🇷', 'Zweden' => '🇸🇪', 'Zwitserland' => '🇨🇭',
                'Schotland' => '🏴󠁧󠁢󠁳󠁣󠁴󠁿', 'Engeland' => '🏴󠁧󠁢󠁥󠁮󠁧󠁿',
            ];

            $monthMapping = [
                'jan' => '01', 'januari' => '01', 'feb' => '02', 'februari' => '02',
                'mrt' => '03', 'maart' => '03', 'apr' => '04', 'april' => '04',
                'mei' => '05', 'jun' => '06', 'juni' => '06', 'jul' => '07', 'juli' => '07',
                'aug' => '08', 'augustus' => '08', 'sep' => '09', 'september' => '09',
                'okt' => '10', 'oktober' => '10', 'nov' => '11', 'november' => '11',
                'dec' => '12', 'december' => '12'
            ];

            $stadiumMap = [
                'Los Angeles' => 'SoFi Stadium (Los Angeles)',
                'Boston' => 'Gillette Stadium (Boston)',
                'Monterrey' => 'Estadio BBVA (Monterrey)',
                'Houston' => 'NRG Stadium (Houston)',
                'New York/New Jersey' => 'MetLife Stadium (New York/New Jersey)',
                'Dallas' => 'AT&T Stadium (Dallas)',
                'Mexico City' => 'Estadio Azteca (Mexico City)',
                'Atlanta' => 'Mercedes-Benz Stadium (Atlanta)',
                'San Francisco' => 'Levi\'s Stadium (San Francisco)',
                'Seattle' => 'Lumen Field (Seattle)',
                'Toronto' => 'BMO Field (Toronto)',
                'Vancouver' => 'BC Place (Vancouver)',
                'Miami' => 'Hard Rock Stadium (Miami)',
                'Kansas City' => 'Arrowhead Stadium (Kansas City)',
                'Philadelphia' => 'Lincoln Financial Field (Philadelphia)',
                'Guadalajara' => 'Estadio Akron (Guadalajara)'
            ];

            $getFlag = function($countryName) use ($countryFlags) {
                return isset($countryFlags[$countryName]) ? $countryFlags[$countryName] : '🏳️';
            };

            $newEvents = [];

            // Group Matches
            $groupH3s = $xpath->query('//h3[starts-with(text(), "Groep")]');
            foreach($groupH3s as $h3) {
                $groupName = trim($h3->textContent);
                $greatgrandparent = $h3->parentNode->parentNode->parentNode;
                $matchLinks = $xpath->query('.//a[contains(@href, "/wk-2026/wedstrijd/")]', $greatgrandparent);

                foreach($matchLinks as $idx => $link) {
                    $container = $link;
                    $meta = $xpath->query('.//div[contains(@class, "text-xs") and contains(text(), "uur")]', $container);
                    $dateStadionStr = $meta->length > 0 ? trim($meta->item(0)->textContent) : "";

                    $parts = explode('·', $dateStadionStr);
                    $dateStr = trim($parts[0] ?? '');
                    $stadiumRaw = trim($parts[1] ?? '');

                    $time = '';
                    $dateFormatted = '';
                    if (preg_match('/(\d{4}-\d{2}-\d{2})\s+(\d{1,2}:\d{2})/', $dateStr, $dateMatch)) {
                        $dateFormatted = $dateMatch[1];
                        $time = str_replace(':', 'u', $dateMatch[2]);
                    }

                    $teamSpans = $xpath->query('.//span[contains(@class, "font-semibold")]', $container);
                    $team1 = $teamSpans->length > 0 ? trim($teamSpans->item(0)->textContent) : "";
                    $team2 = $teamSpans->length > 1 ? trim($teamSpans->item(1)->textContent) : "";

                    if ($team1 && $team2 && $dateFormatted) {
                        $flag1 = $getFlag($team1);
                        $flag2 = $getFlag($team2);
                        $name1 = ($flag1 !== '🏳️' ? $flag1 . ' ' : '') . $team1;
                        $name2 = $team2 . ($flag2 !== '🏳️' ? ' ' . $flag2 : '');
                        $finalName = "$name1 - $name2";

                        $stadiumMapped = isset($stadiumMap[$stadiumRaw]) ? $stadiumMap[$stadiumRaw] : $stadiumRaw;

                        $newEvents[] = [
                            'ronde' => "Groepsfase: Speelronde " . floor($idx / 2 + 1),
                            'name' => $finalName,
                            'date' => $dateFormatted,
                            'stadion' => "🏟️ " . $stadiumMapped,
                            'starttime' => "⏱️ Aftrap {$time}"
                        ];
                    }
                }
            }

            // Knockout Matches
            $knockoutH3s = $xpath->query('//h3[contains(text(), "Ronde van 32") or contains(text(), "Achtste finale") or contains(text(), "Kwartfinale") or contains(text(), "Halve finale") or contains(text(), "Derde plaats") or contains(text(), "Finale")]');
            foreach($knockoutH3s as $h3) {
                $roundName = trim($h3->textContent);
                $greatgrandparent = $h3->parentNode->parentNode->parentNode;
                $matches = $xpath->query('.//div[@data-live-match]', $greatgrandparent);
                foreach($matches as $idx => $match) {
                    $meta = $xpath->query('.//div[contains(@class, "text-xs")]', $match);
                    $dateStadionStr = $meta->length > 0 ? trim($meta->item(0)->textContent) : "";

                    $teamContainers = $xpath->query('.//div[contains(@class, "flex items-center justify-between gap-2") or contains(@class, "flex items-center justify-between mb-1 gap-2")]', $match);
                    $teamsStr = "";
                    if ($teamContainers->length > 0) {
                        $teamsStr = trim(preg_replace('/\s+/', ' ', $teamContainers->item(0)->textContent));
                    }

                    $parts = explode('·', $dateStadionStr);
                    $dateStr = trim($parts[0] ?? '');
                    $stadiumRaw = trim($parts[1] ?? '');

                    $time = '';
                    $dateFormatted = '';
                    if (preg_match('/(\d{1,2})\s+([a-zA-Z]+)\s+(\d{1,2}:\d{2})/', $dateStr, $dateMatch)) {
                        $day = str_pad($dateMatch[1], 2, '0', STR_PAD_LEFT);
                        $month = strtolower($dateMatch[2]);
                        $monthNum = $monthMapping[$month] ?? '06';
                        $time = str_replace(':', 'u', $dateMatch[3]);
                        $dateFormatted = "2026-$monthNum-$day";
                    }

                    $teamParts = explode(' vs ', $teamsStr);
                    $team1 = trim($teamParts[0] ?? '');
                    $team2 = trim($teamParts[1] ?? '');

                    if ($team1 && $team2 && $dateFormatted) {
                        $flag1 = $getFlag($team1);
                        $flag2 = $getFlag($team2);
                        $name1 = ($flag1 !== '🏳️' ? $flag1 . ' ' : '') . $team1;
                        $name2 = $team2 . ($flag2 !== '🏳️' ? ' ' . $flag2 : '');
                        $finalName = "$name1 - $name2";

                        $stadiumMapped = isset($stadiumMap[$stadiumRaw]) ? $stadiumMap[$stadiumRaw] : $stadiumRaw;
                        // Some knockouts have "SoFi Stadium, Los Angeles", extract mapping if possible
                        if (strpos($stadiumRaw, ',') !== false) {
                             $split = array_map('trim', explode(',', $stadiumRaw));
                             $city = $split[count($split) - 1];
                             if (isset($stadiumMap[$city])) {
                                  $stadiumMapped = $stadiumMap[$city];
                             }
                        }

                        $newEvents[] = [
                            'ronde' => "Knock-out: $roundName",
                            'name' => $finalName,
                            'date' => $dateFormatted,
                            'stadion' => "🏟️ " . $stadiumMapped,
                            'starttime' => "⏱️ Aftrap {$time}"
                        ];
                    }
                }
            }

            if (!empty($newEvents)) {
                if (file_put_contents($eventsFile, json_encode($newEvents, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) !== false) {
                    $message = "Schema succesvol gesynchroniseerd met pouletips.nl (" . count($newEvents) . " wedstrijden opgeslagen).";
                    $events = $newEvents;
                } else {
                    $error = "Fout bij opslaan. Controleer of de webserver schrijfrechten heeft op " . htmlspecialchars($requestedFile) . ".";
                }
            } else {
                $error = "Geen wedstrijden gevonden bij het parseren. Mogelijks is de website layout veranderd.";
            }
        }
    } elseif ($action === 'save') {
        $index = isset($_POST['index']) && $_POST['index'] !== '' ? (int)$_POST['index'] : -1;

        $newEvent = [
            'ronde' => $_POST['ronde'] ?? '',
            'name' => $_POST['name'] ?? '',
            'date' => $_POST['date'] ?? '',
            'stadion' => $_POST['stadion'] ?? '',
            'starttime' => $_POST['starttime'] ?? '',
        ];

        // Validate mandatory fields
        if (empty($newEvent['name'])) {
            $error = "Fout: Naam is verplicht.";
        } elseif (empty($newEvent['date'])) {
            $error = "Fout: Datum is verplicht.";
        } else {
            if ($index >= 0 && $index < count($events)) {
                $events[$index] = $newEvent;
                $successMsg = "Event succesvol gewijzigd.";
            } else {
                $events[] = $newEvent;
                $successMsg = "Nieuw event succesvol toegevoegd.";
            }
            if (file_put_contents($eventsFile, json_encode($events, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) !== false) {
                $message = $successMsg;
            } else {
                $error = "Fout bij opslaan. Controleer of de webserver schrijfrechten heeft op " . htmlspecialchars($requestedFile) . ".";
            }
        }
    }
}

// Read events for display
$events = [];
if (file_exists($eventsFile)) {
    $content = file_get_contents($eventsFile);
    if (substr($content, 0, 3) === "\xef\xbb\xbf") {
        $content = substr($content, 3);
    }
    $eventsData = json_decode($content, true);
    if (is_array($eventsData)) {
        $events = $eventsData;
    }
}

// Preserve original index for editing/deleting by creating a mapped array
$mappedEvents = [];
foreach ($events as $index => $event) {
    $mappedEvents[] = ['index' => $index, 'event' => $event];
}

// Filtering
$search = $_GET['search'] ?? '';
if ($search !== '') {
    $mappedEvents = array_filter($mappedEvents, function($item) use ($search) {
        return stripos($item['event']['name'] ?? '', $search) !== false;
    });
}

// Sorting
$sort = $_GET['sort'] ?? '';
$dir = $_GET['dir'] ?? 'asc';

if ($sort !== '') {
    usort($mappedEvents, function($a, $b) use ($sort, $dir) {
        $valA = strtolower($a['event'][$sort] ?? '');
        $valB = strtolower($b['event'][$sort] ?? '');

        if ($valA == $valB) return 0;

        if ($dir === 'desc') {
            return ($valA < $valB) ? 1 : -1;
        } else {
            return ($valA < $valB) ? -1 : 1;
        }
    });
}

// Pagination logic
$itemsPerPage = 10;
$totalEvents = count($mappedEvents);
$totalPages = max(1, ceil($totalEvents / $itemsPerPage));
$currentPage = isset($_GET['page']) ? max(1, min($totalPages, (int)$_GET['page'])) : 1;
$startIndex = ($currentPage - 1) * $itemsPerPage;

$pagedEvents = array_slice($mappedEvents, $startIndex, $itemsPerPage);

// Query builders for links
function getSortLink($col, $currentSort, $currentDir) {
    global $requestedFile;
    $newDir = ($currentSort === $col && $currentDir === 'asc') ? 'desc' : 'asc';
    $params = $_GET;
    $params['sort'] = $col;
    $params['dir'] = $newDir;
    $params['json-events'] = $requestedFile;
    return '?' . http_build_query($params);
}

$queryParams = $_GET;
unset($queryParams['page']);
$baseQuery = http_build_query($queryParams);
$pagePrefix = $baseQuery ? "?{$baseQuery}&page=" : "?page=";
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event-admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Barlow+Condensed:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../CSS/common.css">
    <link rel="stylesheet" href="../CSS/wk_admin.css">
</head>
<body>

<div class="admin-container">
    <h1>Event-admin</h1>

    <?php if ($message): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="message" style="background: rgba(255, 61, 61, 0.1); border-color: var(--alert); color: var(--alert);">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
        <div class="top-buttons-container" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <?php if ($requestedFile === 'wk2026.json'): ?>
            <a href="../wk2026.php" class="btn" style="text-decoration: none; background: rgba(255,255,255,0.1); color: var(--text-bright); padding: 10px 20px; font-size: 16px; display: inline-block;">⬅️ Dashboard</a>
        <?php else: ?>
            <a href="../kalender.php" class="btn" style="text-decoration: none; background: rgba(255,255,255,0.1); color: var(--text-bright); padding: 10px 20px; font-size: 16px; display: inline-block;">⬅️ Dashboard</a>
        <?php endif; ?>
            <button class="btn btn-add" style="margin-bottom: 0;" onclick="openForm()"><span class="btn-add-text-desktop">+ Nieuw Event Toevoegen</span><span class="btn-add-text-mobile">+ Nieuw</span></button>
            <?php if ($requestedFile === 'wk2026.json'): ?>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" style="margin: 0; display: inline;" onsubmit="return confirm('Dit zal het hele schema overschrijven met data van pouletips.nl. Doorgaan?');">
                <input type="hidden" name="action" value="sync_wk">
                <button type="submit" class="btn" style="background: var(--ok); color: var(--bg); margin-bottom: 0;">🔄 Sync Schema</button>
            </form>
            <?php endif; ?>

            <form method="GET" style="display: flex; gap: 5px; margin: 0; align-items: center;">
                <input type="hidden" name="json-events" value="<?php echo htmlspecialchars($requestedFile); ?>">
                <?php if (isset($_GET['sort'])): ?>
                    <input type="hidden" name="sort" value="<?php echo htmlspecialchars($_GET['sort']); ?>">
                <?php endif; ?>
                <?php if (isset($_GET['dir'])): ?>
                    <input type="hidden" name="dir" value="<?php echo htmlspecialchars($_GET['dir']); ?>">
                <?php endif; ?>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Zoek op land..." style="padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--surface); color: var(--text-bright); font-family: 'Share Tech Mono', monospace; width: 150px;">
                <button type="submit" class="btn" style="background: var(--accent); padding: 10px 15px;">🔍</button>
                <?php if ($search !== ''): ?>
                    <button type="button" class="btn btn-delete" style="padding: 10px 15px;" onclick="clearSearch()">Wis filter</button>
                <?php endif; ?>
            </form>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="pagination" style="display: flex; gap: 5px;">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="<?php echo $pagePrefix . $i; ?>" class="btn" style="text-decoration: none; background: <?php echo $i === $currentPage ? 'var(--accent)' : 'rgba(255,255,255,0.1)'; ?>; color: var(--text-bright);"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>

    <div style="overflow-x: auto;">
    <table>
        <thead>
            <tr>
                <th><a href="<?php echo getSortLink('name', $sort, $dir); ?>">Naam <?php echo $sort==='name' ? ($dir==='asc'?'▲':'▼') : ''; ?></a></th>
                <th class="hide-mobile"><a href="<?php echo getSortLink('ronde', $sort, $dir); ?>">Ronde <?php echo $sort==='ronde' ? ($dir==='asc'?'▲':'▼') : ''; ?></a></th>
                <th class="hide-mobile"><a href="<?php echo getSortLink('starttime', $sort, $dir); ?>">Starttijd <?php echo $sort==='starttime' ? ($dir==='asc'?'▲':'▼') : ''; ?></a></th>
                <th class="hide-mobile"><a href="<?php echo getSortLink('date', $sort, $dir); ?>">Datum <?php echo $sort==='date' ? ($dir==='asc'?'▲':'▼') : ''; ?></a></th>
                <th class="hide-mobile"><a href="<?php echo getSortLink('stadion', $sort, $dir); ?>">Stadion <?php echo $sort==='stadion' ? ($dir==='asc'?'▲':'▼') : ''; ?></a></th>
                <th>Acties</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pagedEvents as $mappedEvent):
                $index = $mappedEvent['index'];
                $event = $mappedEvent['event'];
                $icon = '⚽';
            ?>
                <tr>
                    <td class="event-name"><?php echo $icon . ' ' . htmlspecialchars($event['name'] ?? ''); ?></td>
                    <td class="hide-mobile"><?php echo htmlspecialchars($event['ronde'] ?? ''); ?></td>
                    <td class="hide-mobile"><?php echo htmlspecialchars($event['starttime'] ?? ''); ?></td>
                    <td class="hide-mobile"><?php echo htmlspecialchars($event['date'] ?? ''); ?></td>
                    <td class="hide-mobile"><?php echo htmlspecialchars($event['stadion'] ?? ''); ?></td>
                    <td class="actions-cell">
                        <button class="btn btn-edit" onclick="editForm(<?php echo $index; ?>, <?php echo htmlspecialchars(json_encode($event), ENT_QUOTES, 'UTF-8'); ?>)">Bewerk</button>
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" style="display:inline;" onsubmit="return confirm('Zeker dat je dit event wil wissen?');">
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
    </div>

    <div id="eventFormContainer" class="form-container">
        <h2 id="formTitle">Nieuwe Wedstrijd</h2>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="index" id="formIndex" value="">

            <div class="form-group">
                <label for="formName">Naam *</label>
                <input type="text" id="formName" name="name" required placeholder="Bijv. 🇲🇽 Mexico - Zuid-Afrika 🇿🇦">
            </div>

            <div class="form-group">
                <label for="formRonde">Ronde *</label>
                <input type="text" id="formRonde" name="ronde" required placeholder="Bijv. Speelronde 1">
            </div>

            <div class="form-group">
                <label for="formStarttime">Starttijd *</label>
                <input type="text" id="formStarttime" name="starttime" required placeholder="Bijv. ⏱️ Aftrap 21:00">
            </div>

            <div class="form-group" id="dateGroup">
                <label for="formDate">Datum *</label>
                <input type="date" id="formDate" name="date" required>
            </div>

            <div class="form-group">
                <label for="formStadion">Stadion *</label>
                <input type="text" id="formStadion" name="stadion" required placeholder="Bijv. 🏟️ ()">
            </div>

            <button type="submit" class="btn btn-add">Opslaan</button>
            <button type="button" class="btn" style="background:var(--text-muted); color:var(--surface);" onclick="closeForm()">Annuleren</button>
        </form>
    </div>
</div>

<script>
    function openForm() {
        document.getElementById('eventFormContainer').classList.add('active');
        document.getElementById('formTitle').innerText = 'Nieuwe Wedstrijd';
        document.getElementById('formIndex').value = '';
        document.getElementById('formName').value = '';
        document.getElementById('formRonde').value = '';
        document.getElementById('formStarttime').value = '';
        document.getElementById('formDate').value = '';
        document.getElementById('formStadion').value = '';
        document.getElementById('eventFormContainer').scrollIntoView({ behavior: 'smooth' });
    }

    function editForm(index, event) {
        document.getElementById('eventFormContainer').classList.add('active');
        document.getElementById('formTitle').innerText = 'Wedstrijd Bewerken';
        document.getElementById('formIndex').value = index;

        document.getElementById('formName').value = event.name || '';
        document.getElementById('formRonde').value = event.ronde || '';
        document.getElementById('formStarttime').value = event.starttime || '';
        document.getElementById('formDate').value = event.date || '';
        document.getElementById('formStadion').value = event.stadion || '';

        document.getElementById('eventFormContainer').scrollIntoView({ behavior: 'smooth' });
    }

    function closeForm() {
        document.getElementById('eventFormContainer').classList.remove('active');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const stateKey = 'events_admin_state_<?php echo $eventsFileWeb; ?>';
        const isPost = <?php echo $_SERVER['REQUEST_METHOD'] === 'POST' ? 'true' : 'false'; ?>;

        // If clear is set, clear localStorage and redirect
        if (urlParams.has('clear')) {
            localStorage.removeItem(stateKey);
            window.location.href = window.location.pathname + '?json-events=<?php echo $eventsFileWeb; ?>';
            return;
        }

        if (!isPost) {
            // If no search/sort/page but we have saved state, restore it
            if (!urlParams.has('search') && !urlParams.has('sort') && !urlParams.has('page') && localStorage.getItem(stateKey)) {
                window.location.href = window.location.pathname + localStorage.getItem(stateKey);
                return;
            }

            // If there is a search/sort/page query string, save it
            if (urlParams.has('search') || urlParams.has('sort') || urlParams.has('page')) {
                localStorage.setItem(stateKey, window.location.search);
            }
        }
    });

    function clearState() {
        window.location.href = '?json-events=<?php echo $eventsFileWeb; ?>&clear=1';
    }

    function clearSearch() {
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.delete('search');
        urlParams.delete('page');
        window.location.search = urlParams.toString();
    }
</script>

</body>
</html>