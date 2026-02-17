<?php
require_once '../src/Database.php';
require_once '../src/FileLogger.php';

$db = new Database('localhost', 'hosting_dashboard', 'root', '');
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
// CSV Export kezelése (az oldal tetejére a require-ok után)
if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=domainek.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Domain', 'Email', 'Statusz', 'Datum']);
    $rows = $db->getDomains();
    foreach ($rows as $row)
        fputcsv($output, [$row['id'], $row['domain_name'], $row['owner_email'], $row['status'], $row['created_at']]);
    exit;
}

// Új domain mentése
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    if ($db->addDomain($_POST['domain'], $_POST['email'])) {
        $logger->log("Új regisztráció: " . $_POST['domain']);
    }
}

// Keresés és listázás
$search = $_GET['search'] ?? "";
$domains = $db->getDomains($search);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Rackhost Pro Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="text-primary">Hosting Manager v1.0</h1>
        <form class="d-flex w-50">
            <input type="text" name="search" class="form-control me-2" placeholder="Keresés domainre vagy emailre..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-outline-primary">Keresés</button>
        </form>
    </div>

    <div class="card p-4 mb-5 shadow-sm border-0">
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
                <th>IP (A)</th>
                <th>Mail (MX)</th>
                <th>Email</th>
                <th>Státusz</th>
                <th>Műveletek</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($domains as $d): ?>
            <tr>
                <td><strong><?= htmlspecialchars($d['domain_name']) ?></strong></td>
                <td><small><?= $db->getDomainIP($d['domain_name']) ?></small></td>
                <td><span class="badge <?= $db->hasMailServer($d['domain_name']) === 'Van' ? 'bg-success' : 'bg-secondary' ?>"><?= $db->hasMailServer($d['domain_name']) ?></span></td>
                <td><?= htmlspecialchars($d['owner_email']) ?></td>
                <td><span class="badge <?= $d['status'] === 'aktív' ? 'bg-primary' : 'bg-warning' ?>"><?= $d['status'] ?></span></td>
                <td>
                    <a href="?action=toggle&id=<?= $d['id'] ?>&current=<?= $d['status'] ?>" class="btn btn-sm btn-outline-secondary">Váltás</a>
                    <a href="?action=delete&id=<?= $d['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Törlöd?')">X</a>
                </td>
                <td>
                        <strong><?= htmlspecialchars($d['domain_name']) ?></strong>
                        <a href="https://www.whois.com/whois/<?= $d['domain_name'] ?>" target="_blank" class="ms-1 text-decoration-none" title="Tulajdonos ellenőrzése">ℹ️</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>