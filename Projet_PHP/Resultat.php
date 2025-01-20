<?php

session_start(); // Démarre la session

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

require_once __DIR__ . '/librairie/BD.php';

$message = '';
$match = null;

// Vérifie si un ID est passé en paramètre
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = (int) $_GET['id'];
    $match = getMatchParId($id); // Fonction pour récupérer un match par ID

    if (!$match) {
        $message = "Match introuvable.";
    }
} else {
    $message = "ID manquant.";
}

// Traite le formulaire de modification du résultat
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) $_POST['id'];
    $resultat_equipe = ($_POST['resultat_equipe'] !== '') ? (int)$_POST['resultat_equipe'] : null;
    $resultat_adverse = ($_POST['resultat_adverse'] !== '') ? (int)$_POST['resultat_adverse'] : null;

    // Modifier uniquement les résultats
    if (modifierResultat($id, $resultat_equipe, $resultat_adverse)) {
        header("Location: ListeMatch.php");
        exit();
    } else {
        $message = "Erreur lors de la modification du résultat.";
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le résultat du match</title>
    <link rel="stylesheet" href="./CSS/Resultat.css"> <!-- Lien vers le CSS -->
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
        <h1>Modifier le résultat du match</h1>

        <!-- Affichage du message -->
        <?php if (!empty($message)) : ?>
            <p class="error-message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <!-- Formulaire -->
        <?php if ($match) : ?>
            <form method="POST" action="Resultat.php" class="form-container">
                <input type="hidden" name="id" value="<?= htmlspecialchars($match['id']) ?>">

                <div class="field">
                    <label>Date :</label>
                    <p><?= htmlspecialchars($match['date_match']) ?></p>
                </div>

                <div class="field">
                    <label>Heure :</label>
                    <p><?= htmlspecialchars($match['heure_match']) ?></p>
                </div>

                <div class="field">
                    <label>Équipe adverse :</label>
                    <p><?= htmlspecialchars($match['equipe_adverse']) ?></p>
                </div>

                <div class="field">
                    <label>Lieu :</label>
                    <p><?= htmlspecialchars($match['lieu']) ?></p>
                </div>

                <div class="field">
                    <label for="resultat_equipe">Résultat de l'équipe :</label>
                    <input type="number" id="resultat_equipe" name="resultat_equipe" value="<?= htmlspecialchars($match['resultat_equipe']) ?>">
                </div>

                <div class="field">
                    <label for="resultat_adverse">Résultat de l'adversaire :</label>
                    <input type="number" id="resultat_adverse" name="resultat_adverse" value="<?= htmlspecialchars($match['resultat_adverse']) ?>">
                </div>

                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
