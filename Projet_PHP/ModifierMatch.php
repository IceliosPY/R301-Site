<?php

session_start(); // Démarrer la session

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: Connexion.php"); // Rediriger vers la page de connexion si non connecté
    exit;
}

require_once __DIR__ . '/librairie/BD.php'; // Inclure les fonctions liées à la base de données

$message = '';
$match = null; // Initialiser la variable

// Vérifier si un ID est passé dans l'URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = (int)$_GET['id'];
    $match = getMatchParId($id); // Récupérer les détails du match

    if (!$match) {
        $message = "Match introuvable.";
    }
} else {
    $message = "ID du match manquant.";
}

// Traiter le formulaire si une soumission a été effectuée
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    $date_match = $_POST['date_match'];
    $heure_match = $_POST['heure_match'];
    $equipe_adverse = $_POST['equipe_adverse'];
    $lieu = $_POST['lieu'];

    // Validation de la date et de l'heure
    $datetime_saisie = new DateTime($date_match . ' ' . $heure_match);
    $datetime_actuelle = new DateTime();

    if ($datetime_saisie < $datetime_actuelle) {
        $message = "La date et l'heure du match doivent être au moins égales à la date et l'heure actuelles.";
    } else {
        // Mise à jour des détails du match
        if (modifierMatch($id, $date_match, $heure_match, $equipe_adverse, $lieu)) {
            header("Location: ListeMatch.php?success=1"); // Redirection après modification réussie
            exit();
        } else {
            $message = "Erreur lors de la modification du match.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un match</title>
    <link rel="stylesheet" href="./CSS/ModifierMatch.css"> <!-- Lien vers le CSS -->
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
        <h1>Modifier un match</h1>

        <!-- Affichage du message -->
        <?php if (!empty($message)) : ?>
            <p class="error-message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <!-- Formulaire -->
        <?php if ($match) : ?>
            <form method="POST" action="ModifierMatch.php?id=<?= htmlspecialchars($match['id']) ?>" class="form-container">
                <input type="hidden" name="id" value="<?= htmlspecialchars($match['id']) ?>">

                <label for="date_match">Date :</label>
                <input type="date" id="date_match" name="date_match" value="<?= htmlspecialchars($match['date_match']) ?>" required>

                <label for="heure_match">Heure :</label>
                <input type="time" id="heure_match" name="heure_match" value="<?= htmlspecialchars($match['heure_match']) ?>" required>

                <label for="equipe_adverse">Équipe adverse :</label>
                <input type="text" id="equipe_adverse" name="equipe_adverse" value="<?= htmlspecialchars($match['equipe_adverse']) ?>" required>

                <label for="lieu">Lieu :</label>
                <input type="text" id="lieu" name="lieu" value="<?= htmlspecialchars($match['lieu']) ?>" required>

                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </form>
        <?php else : ?>
            <p>Aucun match à modifier.</p>
        <?php endif; ?>
    </div>
</body>
</html>
