<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: Connexion.php");
    exit;
}

// Déconnexion
if (isset($_GET['deconnexion'])) {
    session_start();
    session_destroy();
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
    <link rel="stylesheet" href="./CSS/Statistique.css"> <!-- Lien vers le CSS -->
</head>
<body>
    <!-- Bandeau de navigation -->
    <header class="header">
        <nav class="navbar">
            <a href="ListeJoueur.php" class="btn btn-primary">Liste des joueurs</a>
            <a href="ListeMatch.php" class="btn btn-primary">Liste des matchs</a>
            <a href="Statistiques.php" class="btn btn-primary">Statistiques</a>
            <a href="?deconnexion=1" class="btn btn-secondary">Se déconnecter</a>
        </nav>
    </header>

    <!-- Conteneur principal -->
    <div class="container">
        <h1>Statistiques</h1>

        <!-- Statistiques générales sur les matchs -->
        <section class="stats-section">
            <h2>Statistiques des matchs</h2>
            <?php if ($statistiques['matchs']['total_matchs'] > 0): ?>
                <p>Total des matchs : <?= $statistiques['matchs']['total_matchs'] ?></p>
                <p>Matchs gagnés : <?= $statistiques['matchs']['matchs_gagnés'] ?> (<?= number_format($statistiques['matchs']['matchs_gagnés'] / $statistiques['matchs']['total_matchs'] * 100, 2) ?>%)</p>
                <p>Matchs perdus : <?= $statistiques['matchs']['matchs_perdus'] ?> (<?= number_format($statistiques['matchs']['matchs_perdus'] / $statistiques['matchs']['total_matchs'] * 100, 2) ?>%)</p>
                <p>Matchs nuls : <?= $statistiques['matchs']['matchs_nuls'] ?> (<?= number_format($statistiques['matchs']['matchs_nuls'] / $statistiques['matchs']['total_matchs'] * 100, 2) ?>%)</p>
            <?php else: ?>
                <p>Aucune donnée disponible pour les matchs.</p>
            <?php endif; ?>
        </section>

        <!-- Tableau des statistiques des joueurs -->
        <section class="stats-section">
            <h2>Statistiques des joueurs</h2>
            <?php if (!empty($statistiques['joueurs'])): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Statut</th>
                            <th>Poste Préféré</th>
                            <th>Titulaire</th>
                            <th>Remplaçant</th>
                            <th>Moyenne évaluation</th>
                            <th>% matchs gagnés</th>
                            <th>Sélections consécutives</th>
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
                                <td><?= $joueur['moyenne_evaluation'] !== null ? number_format($joueur['moyenne_evaluation'], 2) : 'Aucune donnée' ?></td>
                                <td><?= $joueur['pourcentage_victoires'] !== null ? number_format($joueur['pourcentage_victoires'], 2) . "%" : 'Aucune donnée' ?></td>
                                <td><?= $joueur['selections_consecutives'] !== null ? $joueur['selections_consecutives'] : 'Aucune donnée' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Aucune donnée disponible pour les joueurs.</p>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>
