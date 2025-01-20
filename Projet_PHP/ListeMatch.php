<?php

session_start(); // Démarrer la session

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: Connexion.php"); // Redirige vers la page de connexion si non connecté
    exit;
}

// Déconnexion
if (isset($_GET['deconnexion'])) {
    session_destroy();
    header("Location: Connexion.php");
    exit;
}

require_once __DIR__ . '/librairie/BD.php'; // Inclure le fichier BD.php

// Récupérer tous les matchs via la fonction getAllMatchs() de BD.php
$matchs = getAllMatchs();

if (isset($_GET['supprimer'])) {
    $id = (int) $_GET['supprimer'];
    if (supprimerMatch($id)) {
        // Redirection immédiate pour rafraîchir la page après suppression
        header("Location: ListeMatch.php");
        exit;
    } else {
        $message = "Erreur : Vous ne pouvez pas supprimer un match qui a déjà eu lieu.";
        // Redirection après 3 secondes si le match ne peut pas être supprimé
        echo "<script>
                setTimeout(function(){
                    window.location.href = 'ListeMatch.php';
                }, 3000);
              </script>";
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des matchs</title>
    <link rel="stylesheet" href="./css/ListeMatch.css"> <!-- Lien vers le CSS -->
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
        <h1>Liste des matchs</h1>

        <!-- Bouton pour ajouter un match -->
        <div class="action-buttons">
            <a href="CreationMatch.php" class="btn btn-primary">Ajouter un match</a>
        </div>

        <!-- Affichage du message -->
        <?php if (isset($message)): ?>
            <p class="error-message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <!-- Table des matchs -->
        <?php if (empty($matchs)): ?>
            <p>Aucun match trouvé.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Date et Heure</th>
                        <th>Équipe adverse</th>
                        <th>Lieu</th>
                        <th>Résultat (Équipe)</th>
                        <th>Résultat (Adverse)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($matchs as $match): ?>
                        <tr>
                            <?php
                            // Formater la date et l'heure
                            $date_match = new DateTime($match['date_match']);
                            $heure_match = new DateTime($match['heure_match']);
                            $date_heure_formatee = $date_match->format('d/m/Y') . ' à ' . $heure_match->format('H:i');
                            ?>
                            <td><?= htmlspecialchars($date_heure_formatee) ?></td>
                            <td><?= htmlspecialchars($match['equipe_adverse']) ?></td>
                            <td><?= htmlspecialchars($match['lieu']) ?></td>
                            <td><?= htmlspecialchars($match['resultat_equipe']) ?></td>
                            <td><?= htmlspecialchars($match['resultat_adverse']) ?></td>
                            <td>
                                <a href="ModifierMatch.php?id=<?= urlencode($match['id']) ?>">Modifier</a>
                                <a href="ModifierFeuilleMatch.php?match_id=<?= urlencode($match['id']) ?>">Voir/Modifier la sélection</a>
                                <a href="?supprimer=<?= $match['id'] ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce match ?')">Supprimer</a>
                                <a href="Resultat.php?id=<?= urlencode($match['id']) ?>">Modifier le résultat</a>
                                <a href="Evaluations.php?id=<?= urlencode($match['id']) ?>">Évaluer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
