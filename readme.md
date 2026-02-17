# Mini Hosting Dashboard – PHP Gyakorlati Projekt

Ez egy egyszerű domain-kezelő rendszer, amelyet a **Rackhost** gyakornoki jelentkezésemhez készítettem el felkészülésként. A projekt célja a PHP, az OOP alapok, a fájlkezelés és a biztonságos adatbázis-kezelés bemutatása.

##  Funkciók
* **Domain regisztráció**: Új domainek felvétele és mentése MySQL adatbázisba.
* **OOP felépítés**: Különálló osztályok az adatbázis-kezeléshez és a naplózáshoz.
* **Eseménynaplózás**: Minden tevékenység elmentésre kerül egy szerveroldali `.log` fájlba.
* **Biztonság**: PDO prepared statement-ek használata az SQL injection ellen és XSS védelem.
* **Modern UI**: Bootstrap 5 keretrendszerrel készült reszponzív felület.

##  Technológiai stack
* **Backend**: PHP 8.x
* **Adatbázis**: MariaDB / MySQL
* **Frontend**: HTML5, CSS3, Bootstrap 5
* **Környezet**: XAMPP / Apache

##  Telepítés és használat
1. Klónozd le a tárolót a XAMPP `htdocs` mappájába.
2. Importáld a `sql/setup.sql` fájlt a phpMyAdmin felületén.
3. Nyisd meg a böngésződben a `http://localhost/[mappa_neve]/public/index.php` címet.

##  Tervezett fejlesztések
* DNS rekordok (A, MX) lekérdezése PHP-val.
* Domain törlése és státuszának módosítása.