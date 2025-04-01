<?php
require_once __DIR__ . '/../core/TeloraLDAPSync.php';

try {
    $sync = new TeloraLDAPSync();
    
    // Récupère tous les clients
    $query = "SELECT id, name FROM clients WHERE active = 1";
    $stmt = $GLOBALS['db']->prepare($query);
    $stmt->execute();
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $totalClients = count($clients);
    $successClients = 0;
    
    echo "Début de la synchronisation initiale...\n";
    echo "Nombre total de clients : $totalClients\n\n";
    
    foreach ($clients as $client) {
        try {
            echo "Synchronisation du client {$client['name']} (ID: {$client['id']})...\n";
            
            if ($sync->syncClientContacts($client['id'])) {
                $successClients++;
                echo "✓ Client synchronisé avec succès\n";
            } else {
                echo "⚠ Synchronisation partielle (certains contacts en erreur)\n";
            }
        } catch (Exception $e) {
            echo "✗ ERREUR : " . $e->getMessage() . "\n";
        }
        echo "\n";
    }
    
    echo "Synchronisation terminée !\n";
    echo "Clients traités avec succès : $successClients / $totalClients\n";
    
} catch (Exception $e) {
    echo "ERREUR FATALE : " . $e->getMessage() . "\n";
    exit(1);
}
