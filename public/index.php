<?php
require_once '../src/Config.php';
require_once '../src/Database.php';

$db = new Database(Config::$db['host'], Config::$db['name'], Config::$db['user'], Config::$db['pass']);
$search = $_GET['search'] ?? "";
$domains = $db->getDomains($search);

// Egyszerű statisztika
$total = count($domains);
$active = count(array_filter($domains, fn($d) => $d['status'] === 'aktív'));
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Hosting Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light p-5">
    <div class="container card p-4 shadow-sm border-0">
        <h1 class="text-primary mb-4">Domain Manager</h1>
        
        <div class="row mb-4">
            <div class="col-md-6"><div class="card p-3 bg-primary text-white"><h6>Összesen</h6><h2><?= $total ?></h2></div></div>
            <div class="col-md-6"><div class="card p-3 bg-success text-white"><h6>Aktív</h6><h2><?= $active ?></h2></div></div>
        </div>

        <form class="d-flex mb-4">
            <input type="text" name="search" class="form-control me-2" placeholder="Domain keresése..." value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-outline-primary">Keresés</button>
        </form>

        <table class="table table-hover">
            <thead class="table-dark">
                <tr><th>Domain</th><th>Lejárat</th><th>Állapot</th></tr>
            </thead>
            <tbody>
                <?php foreach ($domains as $d): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($d['domain_name']) ?></strong></td>
                    <td><?= $d['expiry_date'] ?></td>
                    <td><span class="badge badge-<?= $d['status'] ?>"><?= $d['status'] ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>