<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: Connexion.php");
    exit;
}

require_once __DIR__ . '/librairie/BD.php'; // Inclure le fichier BD.php

// Récupérer les statistiques
$statistiques = getStatistiques();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques des matchs et joueurs</title>
    <link rel="stylesheet" href="/CSS/styles.css">
</head>
<body>
    <h1>Statistiques</h1>

    <!-- Statistiques générales sur les matchs -->
    <h2>Statistiques des matchs</h2>
    <p>Total des matchs : <?= $statistiques['matchs']['total_matchs'] ?></p>
    <p>Matchs gagnés : <?= $statistiques['matchs']['matchs_gagnés'] ?> (<?= number_format($statistiques['matchs']['matchs_gagnés'] / $statistiques['matchs']['total_matchs'] * 100, 2) ?>%)</p>
    <p>Matchs perdus : <?= $statistiques['matchs']['matchs_perdus'] ?> (<?= number_format($statistiques['matchs']['matchs_perdus'] / $statistiques['matchs']['total_matchs'] * 100, 2) ?>%)</p>
    <p>Matchs nuls : <?= $statistiques['matchs']['matchs_nuls'] ?> (<?= number_format($statistiques['matchs']['matchs_nuls'] / $statistiques['matchs']['total_matchs'] * 100, 2) ?>%)</p>

    <!-- Tableau des statistiques des joueurs -->
    <h2>Statistiques des joueurs</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Statut</th>
                <th>Poste Préféré</th>
                <th>Titulaire (sélections)</th>
                <th>Remplaçant (sélections)</th>
                <th>Moyenne évaluation</th>
                <th>% matchs gagnés</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($statistiques['joueurs'] as $joueur) : ?>
                <tr>
                    <td><?= htmlspecialchars($joueur['nom'] . ' ' . $joueur['prenom']) ?></td>
                    <td><?= htmlspecialchars($joueur['statut']) ?></td>
                    <td><?= htmlspecialchars($joueur['poste_prefere']) ?></td>
                    <td><?= $joueur['titulaire_count'] ?></td>
                    <td><?= $joueur['remplaçant_count'] ?></td>
                    <td><?= number_format($joueur['moyenne_evaluation'], 2) ?></td>
                    <td><?= number_format($joueur['pourcentage_victoires'], 2) ?>%</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>
</html>
