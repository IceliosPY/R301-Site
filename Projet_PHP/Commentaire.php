<?php
session_start(); // Démarrer la session

// Déconnexion
if (isset($_GET['deconnexion'])) {
    session_destroy(); // Détruire la session
    header("Location: Connexion.php"); // Rediriger vers la page de connexion
    exit;
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: Connexion.php"); // Redirige vers la page de connexion si non connecté
    exit;
}

require_once __DIR__ . '/librairie/BD.php'; // Inclure la librairie pour la base de données

// Récupération des informations du joueur
if (!isset($_GET['id'])) {
    header("Location: ListeJoueur.php"); // Redirige si l'ID n'est pas fourni
    exit;
}

$id = (int) $_GET['id'];
$joueur = getJoueurParId($id);

if (!$joueur) {
    $message = "Joueur introuvable.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commentaires du joueur</title>
    <link rel="stylesheet" href="./css/Commentaires.css"> <!-- Lien vers le CSS -->
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
        <h1>Commentaires du joueur</h1>

        <?php if (isset($message)): ?>
            <p class="error-message"><?= htmlspecialchars($message) ?></p>
        <?php else: ?>
            <div class="player-details">
                <p><strong>Nom :</strong> <?= htmlspecialchars($joueur['nom']) ?></p>
                <p><strong>Prénom :</strong> <?= htmlspecialchars($joueur['prenom']) ?></p>
            </div>

            <div class="player-comments">
                <p><strong>Commentaires :</strong></p>
                <p><?= htmlspecialchars($joueur['commentaires'] ?? 'Aucun commentaire.') ?></p>
            </div>
        <?php endif; ?>

        <div class="action-buttons">
            <a href="ListeJoueur.php" class="btn btn-primary">Retour à la liste</a>
        </div>
    </div>
</body>
</html>
