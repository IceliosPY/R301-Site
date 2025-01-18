<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: Connexion.php"); // Rediriger vers la page de connexion si non connecté
    exit;
}

require_once __DIR__ . '/librairie/BD.php'; // Inclure le fichier BD.php

// Récupérer les statistiques
$statistiques = getStatistiquesMatchs();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques des Matchs</title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>
    <h1>Statistiques des Matchs</h1>

    <p>Nombre total de matchs avec résultat : <?= $statistiques['total'] ?></p>
    <p>Matchs gagnés : <?= $statistiques['gagnes'] ?> (<?= number_format($statistiques['pourcentageGagnes'], 2) ?>%)</p>
    <p>Matchs perdus : <?= $statistiques['perdus'] ?> (<?= number_format($statistiques['pourcentagePerdus'], 2) ?>%)</p>
    <p>Matchs nuls : <?= $statistiques['nuls'] ?> (<?= number_format($statistiques['pourcentageNuls'], 2) ?>%)</p>

    <a href="ListeMatch.php">Retour à la liste des matchs</a>
</body>
</html>
