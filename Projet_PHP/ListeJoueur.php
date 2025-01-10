<?php

session_start(); // Démarre la session

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
    <h1>Liste des joueurs</h1>

    <?php if (empty($joueurs)): ?>
        <p>Aucun joueur trouvé dans la base de données.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>N° Licence</th>
                    <th>Date de naissance</th>
                    <th>Taille (cm)</th>
                    <th>Poids (kg)</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($joueurs as $joueur): ?>
                    <tr>
                        <td><?= htmlspecialchars($joueur['id']) ?></td>
                        <td><?= htmlspecialchars($joueur['nom']) ?></td>
                        <td><?= htmlspecialchars($joueur['prenom']) ?></td>
                        <td><?= htmlspecialchars($joueur['numero_licence']) ?></td>
                        <td><?= htmlspecialchars($joueur['date_naissance']) ?></td>
                        <td><?= htmlspecialchars($joueur['taille']) ?></td>
                        <td><?= htmlspecialchars($joueur['poids']) ?></td>
                        <td><?= htmlspecialchars($joueur['statut']) ?></td>
                        <td>
                        <!-- Lien pour modifier le joueur -->
                        <a href="ModifierJoueur.php?id=<?= urlencode($joueur['id']) ?>">Modifier</a>
                        <a href="?supprimer=<?= $joueur['id'] ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce joueur ?')">Supprimer</a>
                    </td>
                    </tr>
                    <a href="?deconnexion=true">Se déconnecter</a>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>
