<?php
try {
    // Établir la connexion avec la base de données
    $host = 'localhost';
    $db   = 'LDAP';
    $user = 'gcadmin';
    $pass = 'Y7x49spyK5MF';
    $charset = 'utf8mb4';
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO('mysql:host='.$host.'; dbname='.$db,$user,$pass);
    //echo "db.php-> tout fonctionne batard | ";
} catch (PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
    exit;
}



?>