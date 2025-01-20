<?php

session_start(); // Démarre la session

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

$message = '';

// Traitement du formulaire d'ajout d'un match
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_match = $_POST['date_match'];
    $heure_match = $_POST['heure_match'];
    $equipe_adverse = $_POST['equipe_adverse'];
    $lieu = $_POST['lieu'];
    $resultat_equipe = $_POST['resultat_equipe']; // Nouveau champ
    $resultat_adverse = $_POST['resultat_adverse']; // Nouveau champ

    // Combiner date et heure pour la comparaison
    $datetime_saisie = new DateTime($date_match . ' ' . $heure_match);
    $datetime_actuelle = new DateTime(); // Date et heure actuelles

    if ($datetime_saisie < $datetime_actuelle) {
        $message = "La date et l'heure du match doivent être au moins égales à la date et l'heure actuelles.";
    } else {
        $match_id = ajouterMatch($date_match, $heure_match, $equipe_adverse, $lieu, $resultat_equipe, $resultat_adverse);

        if ($match_id) {
            header("Location: ListeMatch.php?match_id=" . $match_id);
            exit();
        } else {
            $message = "Erreur lors de la création du match.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un match</title>
    <link rel="stylesheet" href="./CSS/CreationMatch.css"> <!-- Lien vers le CSS -->
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
        <h1>Créer un match</h1>

        <!-- Affichage du message -->
        <?php if (!empty($message)) : ?>
            <p class="error-message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <!-- Formulaire de création -->
        <form method="POST" action="CreationMatch.php" class="form-container">
            <label for="date_match">Date du match :</label>
            <input type="date" id="date_match" name="date_match" required>

            <label for="heure_match">Heure du match :</label>
            <input type="time" id="heure_match" name="heure_match" required>

            <label for="equipe_adverse">Nom de l'équipe adverse :</label>
            <input type="text" id="equipe_adverse" name="equipe_adverse" required>

            <label for="lieu">Lieu de rencontre :</label>
            <select id="lieu" name="lieu" required>
                <option value="Domicile">Domicile</option>
                <option value="Extérieur">Extérieur</option>
            </select>

            <label for="resultat_equipe">Résultat (équipe) :</label>
            <input type="text" id="resultat_equipe" name="resultat_equipe">

            <label for="resultat_adverse">Résultat (adverse) :</label>
            <input type="text" id="resultat_adverse" name="resultat_adverse">

            <button type="submit" class="btn btn-primary">Créer le match</button>
        </form>
    </div>
</body>
</html>
