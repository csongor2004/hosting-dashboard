<?php
class Database
{
    private $pdo;

    public function __construct($host, $db, $user, $pass)
    {
        try {
            $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
            $this->pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (PDOException $e) {
            die("Adatbázis hiba: " . $e->getMessage());
        }
    }

    public function getDomains($searchTerm = "")
    {
        if (!empty($searchTerm)) {
            $stmt = $this->pdo->prepare("SELECT * FROM domains WHERE domain_name LIKE ? OR owner_email LIKE ? ORDER BY created_at DESC");
            $stmt->execute(["%$searchTerm%", "%$searchTerm%"]);
            return $stmt->fetchAll();
        }
        return $this->pdo->query("SELECT * FROM domains ORDER BY created_at DESC")->fetchAll();
    }

    public function addDomain($name, $email)
    {
        $stmt = $this->pdo->prepare("INSERT INTO domains (domain_name, owner_email) VALUES (?, ?)");
        return $stmt->execute([$name, $email]);
    }

    public function deleteDomain($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM domains WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getDomainIP($domain)
    {
        $ip = gethostbyname($domain);
        return ($ip !== $domain) ? $ip : 'Nincs IP';
    }

    public function hasMailServer($domain)
    {
        return checkdnsrr($domain, "MX") ? 'Van' : 'Nincs';
    }
    // Port figyelő: Ellenőrzi, hogy él-e a webszerver (HTTP 80)
    public function checkService($domain)
    {
        $connection = @fsockopen($domain, 80, $errno, $errstr, 2); 
        if ($connection) {
            fclose($connection);
            return true;
        }
        return false;
    }
}