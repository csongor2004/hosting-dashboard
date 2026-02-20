<?php
require_once '../src/Database.php';
require_once '../src/FileLogger.php';
require_once '../src/config.php';

$db = new Database(Config::$db['host'], Config::$db['name'], Config::$db['user'], Config::$db['pass']);
$logger = new FileLogger('../activity.log');

// Műveletek kezelése (Delete / Status Update)
if (isset($_GET['action'])) {
    $id = (int) $_GET['id'];
    if ($_GET['action'] === 'delete') {
        if ($db->deleteDomain($id))
            $logger->log("Törölve: ID $id");
    } elseif ($_GET['action'] === 'toggle') {
        $newStatus = ($_GET['current'] === 'aktív') ? 'lejárt' : 'aktív';
        if ($db->updateStatus($id, $newStatus))
            $logger->log("Státuszváltás: ID $id -> $newStatus");
    }
    header("Location: index.php");
    exit;
}

// CSV Export kezelése
if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=domainek.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Domain', 'Email', 'Statusz', 'Datum']);
    $rows = $db->getDomains();
    foreach ($rows as $row) {
        fputcsv($output, [$row['id'], $row['domain_name'], $row['owner_email'], $row['status'], $row['created_at']]);
    }
    fclose($output);
    exit;
}

// Új domain mentése
if (isset($_POST['save'])) {
    $name = trim($_POST['domain']);
    $email = trim($_POST['email']);
    if (!empty($name) && !empty($email)) {
        if ($db->addDomain($name, $email)) {
            $logger->log("Hozzáadva: $name ($email)");
            header("Location: index.php?success=1");
            exit;
        }
    }
}

$search = $_GET['search'] ?? "";
$domains = $db->getDomains($search);

// Statisztikák kiszámítása
$totalDomains = count($domains);
$activeDomains = count(array_filter($domains, fn($d) => $d['status'] === 'aktív'));
$expiredDomains = count(array_filter($domains, fn($d) => $d['status'] === 'lejárt'));
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rackhost - Mini Hosting Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light p-4">

<div class="container">
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm rounded mb-4 px-3">
        <a class="navbar-brand text-primary fw-bold" href="#">RACKHOST DASHBOARD</a>
        <div class="ms-auto">
            <a href="?export=1" class="btn btn-outline-secondary btn-sm"><i class="bi bi-download"></i> CSV Export</a>
        </div>
    </nav>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 bg-primary text-white">
                <h6 class="text-uppercase small">Összes Domain</h6>
                <h2 class="mb-0 fw-bold"><?= $totalDomains ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 bg-success text-white">
                <h6 class="text-uppercase small">Aktív</h6>
                <h2 class="mb-0 fw-bold"><?= $activeDomains ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 bg-danger text-white">
                <h6 class="text-uppercase small">Lejárt</h6>
                <h2 class="mb-0 fw-bold"><?= $expiredDomains ?></h2>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm p-4 mb-4">
        <form method="GET" class="row g-3">
            <div class="col-md-10">
                <input type="text" name="search" class="form-control" placeholder="Keresés domain vagy email alapján..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Keresés</button>
            </div>
        </form>
    </div>

    <div class="card border-0 shadow-sm p-4 mb-4 bg-white">
        <h5 class="mb-3">Új Domain Hozzáadása</h5>
        <form method="POST" class="row g-3">
            <div class="col-md-5"><input type="text" name="domain" class="form-control" placeholder="domain.hu" required></div>
            <div class="col-md-5"><input type="email" name="email" class="form-control" placeholder="tulaj@email.hu" required></div>
            <div class="col-md-2"><button type="submit" name="save" class="btn btn-success w-100">Hozzáadás</button></div>
        </form>
    </div>

    <table class="table table-hover bg-white shadow-sm rounded">
        <thead class="table-dark">
            <tr>
                <th>Domain</th>
                <th>Csomag</th>
                <th>Lejárat</th>
                <th>IP / Szolgáltatás</th>
                <th>Mail (MX)</th>
                <th>Státusz</th>
                <th>Műveletek</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($domains as $d): ?>
            <tr>
                <td><strong><?= htmlspecialchars($d['domain_name']) ?></strong><br><small class="text-muted"><?= htmlspecialchars($d['owner_email']) ?></small></td>
                <td><span class="badge bg-secondary text-uppercase"><?= $d['plan_type'] ?></span></td>
                <td>
                    <?php
                    $daysLeft = (strtotime($d['expiry_date']) - time()) / 86400;
                    $color = $daysLeft < 30 ? 'text-danger fw-bold' : 'text-muted';
                    ?>
                    <span class="<?= $color ?>"><?= $d['expiry_date'] ?></span>
                </td>
                <td>
                    <span class="badge <?= $db->checkService($d['domain_name']) ? 'bg-success' : 'bg-danger' ?>" title="HTTP Port 80">●</span>
                    <small><?= $db->getDomainIP($d['domain_name']) ?></small>
                </td>
                <td>
                    <span class="badge <?= $db->hasMailServer($d['domain_name']) === 'Van' ? 'bg-success' : 'bg-secondary' ?>"><?= $db->hasMailServer($d['domain_name']) ?></span>
                </td>
                <td>
                    <a href="?action=toggle&id=<?= $d['id'] ?>&current=<?= $d['status'] ?>" class="text-decoration-none">
                        <span class="badge <?= $d['status'] === 'aktív' ? 'bg-success' : 'bg-danger' ?>"><?= $d['status'] ?></span>
                    </a>
                </td>
                <td>
                    <a href="?action=delete&id=<?= $d['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Biztosan törlöd?')"><i class="bi bi-trash"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>