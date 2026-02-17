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

    public function addDomain($name, $email)
    {
        $stmt = $this->pdo->prepare("INSERT INTO domains (domain_name, owner_email) VALUES (?, ?)");
        return $stmt->execute([$name, $email]);
    }

    public function getDomains()
    {
        return $this->pdo->query("SELECT * FROM domains ORDER BY created_at DESC")->fetchAll();
    }
}