<?php
require_once '../database/db.php';
header('Content-Type: application/json');

if (!isset($_GET['partner_id'])) {
    echo json_encode(['error' => 'Aucun partenaire spécifié']);
    exit;
}

$partnerId = htmlspecialchars($_GET['partner_id']);

try {
    $stmt = $pdo->prepare("SELECT idclients, Nom FROM Clients WHERE partenaires_idpartenaires = :partnerId");
    $stmt->bindParam(':partnerId', $partnerId, PDO::PARAM_INT);
    $stmt->execute();
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Nettoyez les tampons de sortie
    if (ob_get_length()) {
        ob_clean();
    }

    // Retournez uniquement les données JSON
    echo json_encode($clients);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
