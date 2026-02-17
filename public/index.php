<?php
require_once '../src/Database.php';
require_once '../src/FileLogger.php';

$db = new Database('localhost', 'hosting_dashboard', 'root', '');
$logger = new FileLogger('../activity.log');

// Form beküldés kezelése
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $domain = $_POST['domain'];
    $email = $_POST['email'];

    if ($db->addDomain($domain, $email)) {
        $logger->log("Új domain regisztrálva: $domain ($email)");
    }
}

$domains = $db->getDomains();
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Hosting Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light container py-5">
    <h1 class="mb-4">Rackhost Dashboard Demo</h1>
    
    <div class="card p-4 mb-5 shadow-sm">
        <h3>Domain Hozzáadása</h3>
        <form method="POST" class="row g-3">
            <div class="col-md-5">
                <input type="text" name="domain" class="form-control" placeholder="pelda.hu" required>
            </div>
            <div class="col-md-5">
                <input type="email" name="email" class="form-control" placeholder="tulajdonos@email.com" required>
            </div>
            <div class="col-md-2">
                <button type="submit" name="save" class="btn btn-primary w-100">Mentés</button>
            </div>
        </form>
    </div>

    <h3>Regisztrált Domainek</h3>
    <table class="table table-hover bg-white shadow-sm">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Domain</th>
                <th>E-mail</th>
                <th>Dátum</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($domains as $d): ?>
            <tr>
                <td><?= htmlspecialchars($d['id']) ?></td>
                <td><?= htmlspecialchars($d['domain_name']) ?></td>
                <td><?= htmlspecialchars($d['owner_email']) ?></td>
                <td><?= $d['created_at'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>