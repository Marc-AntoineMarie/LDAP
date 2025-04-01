<?php
require 'db.php';

session_start();

// vérification user connecter
if (!isset($_SESSION['iduser'])) {
    header("Location: ../login/login.php");
    exit();
// } else {
//     // echo"l'utilisateur est connecter";
}

// déconnexion de la session 
if(isset($_POST['Déconnexion'])) {
    session_destroy();
    header("Location: ../login/login.php");
    exit();
}

?>