<?php

session_start(); // Démarrer la session

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: Connexion.php"); // Redirige vers la page de connexion si non connecté
    exit;
}

// Déconnexion
if (isset($_GET['deconnexion'])) {
    session_start();
    session_destroy();
    header("Location: Connexion.php");
    exit;
}

require_once __DIR__ . '/librairie/BD.php'; // Inclure la librairie pour la base de données
require_once __DIR__ . '/CSS/header.php'; // Inclure le header

// Récupération de la liste des joueurs
$joueurs = getTousLesJoueurs();

if (isset($_GET['supprimer'])) {
    $id = (int) $_GET['supprimer'];
    if (supprimerJoueur($id)) {
        $message = "Le joueur a été supprimé avec succès.";
    } else {
        $message = "Erreur lors de la suppression du joueur.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des joueurs</title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>
    <!-- Le menu de navigation est inclus ici -->
    <?php use CSS\header; ?>

    <h1>Liste des joueurs</h1>

    <!-- Bouton pour ajouter un joueur -->
    <div>
        <a href="CreationJoueur.php" class="btn btn-primary">Ajouter un joueur</a>
    </div>

    <?php if (empty($joueurs)): ?>
        <p>Aucun joueur trouvé dans la base de données.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>N° Licence</th>
                    <th>Date de naissance</th>
                    <th>Taille (cm)</th>
                    <th>Poids (kg)</th>
                    <th>Statut</th>
                    <th>Évaluation</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($joueurs as $joueur): ?>
                    <tr>
                        <td><?= htmlspecialchars($joueur['nom']) ?></td>
                        <td><?= htmlspecialchars($joueur['prenom']) ?></td>
                        <td><?= htmlspecialchars($joueur['numero_licence']) ?></td>
                        <td><?= htmlspecialchars($joueur['date_naissance']) ?></td>
                        <td><?= htmlspecialchars($joueur['taille']) ?></td>
                        <td><?= htmlspecialchars($joueur['poids']) ?></td>
                        <td><?= htmlspecialchars($joueur['statut']) ?></td>
                        <td><?= htmlspecialchars($joueur['evaluation'] ?: 'Non noté') ?></td>
                        <td>
                        <!-- Lien pour modifier et supprimer le joueur -->
                        <a href="ModifierJoueur.php?id=<?= urlencode($joueur['id']) ?>">Modifier</a>
                        <a href="?supprimer=<?= $joueur['id'] ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce joueur ?')">Supprimer</a>
                        <!-- Lien pour afficher les commentaires -->
                        <a href="Commentaire.php?id=<?= urlencode($joueur['id']) ?>" class="btn btn-secondary">Commentaires</a>
                    </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>
