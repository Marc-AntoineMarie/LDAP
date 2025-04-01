<?php
// Vérification des rôles
if (!isset($_SESSION['role'])) {
    header('Location: ../login/login.php');
    exit;
}
$role = $_SESSION['role'];

// Récupération des IDs de la session
$partnerId = $_SESSION['partner_id'] ?? null;
$clientId = $_SESSION['client_id'] ?? null;

// Récupérer l'ID client depuis GET si disponible
if (isset($_GET['idclients'])) {
    $_SESSION['client_id'] = intval($_GET['idclients']);
    $clientId = $_SESSION['client_id'];
}

// Détecter la page actuelle
$currentPage = basename($_SERVER['SCRIPT_NAME']);
$showClientButton = in_array($currentPage, ['clientdetail.php', 'annuaire.php']);

// Debug
error_log("[Header] Role: " . $role);
error_log("[Header] PartnerId from session: " . ($partnerId ?? 'null'));
error_log("[Header] ClientId from session: " . ($clientId ?? 'null'));
error_log("[Header] Current page: " . $currentPage);
?>


<!DOCTYPE html>
<html lang=" fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LDAP</title>
    <link rel="stylesheet" href="../partials/header_copy.css">
</head>

<body>
    <header>
        <nav class="navbar">
            <img src="../admin/logo/Logo-ldap.png" alt="Logo" class="logo-header">
            <class="nav-links">

                <?php if ($role === 'admin'): ?>
                    <a href="../admin/V1_admin.php"><i class="fas fa-home"></i> Partenaires</a>
                    <a
                        href="../clientlist/clientlist.php<?= $partnerId ? '?idpartenaires=' . htmlspecialchars($partnerId) : '' ?>">
                        <i class="fas fa-handshake"></i> Clients</a>

                    <?php if ($clientId && $showClientButton): ?>
                        <a href="../clientdetail/clientdetail.php?idclient=<?= htmlspecialchars($clientId) ?>"><i
                                class="fas fa-home"></i> Retour au client</a>
                    <?php endif; ?>

                <?php elseif ($role === 'Partenaire'): ?>
                    <a href="../clientlist/clientlist.php?idpartenaires=<?= htmlspecialchars($_SESSION['partner_id']) ?>"><i
                            class="fas fa-home"></i> Retour au client</a>

                    <?php if ($clientId && $showClientButton): ?>
                        <a href="../clientdetail/clientdetail.php?idclient=<?= htmlspecialchars($clientId) ?>"><i
                                class="fas fa-home"></i> Retour au client</a>
                    <?php endif; ?>

                <?php elseif ($role === 'Client'): ?>
                    <?php if ($showClientButton): ?>
                        <a href="../clientdetail/clientdetail.php?idclient=<?= htmlspecialchars($_SESSION['client_id']) ?>"><i
                                class="fas fa-home"></i> Retour au client</a>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- <a href="../profile/profile.php"><i class="fas fa-user-circle"></i> Profil</a> -->
                <a href="../login/logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
                </div>
        </nav>
    </header>
</body>

</html>