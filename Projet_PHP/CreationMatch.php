<?php

session_start(); // Démarre la session

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: Connexion.php"); // Redirige vers la page de connexion si non connecté
    exit;
}

require_once __DIR__ . '/librairie/BD.php'; // Inclure le fichier BD.php

$message = '';

// Traitement du formulaire d'ajout d'un match
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_match = $_POST['date_match']; // format 'YYYY-MM-DD'
    $heure_match = $_POST['heure_match']; // format 'HH:MM'
    $equipe_adverse = $_POST['equipe_adverse'];
    $lieu = $_POST['lieu'];
    $resultat = $_POST['resultat'];

    // Combiner date et heure pour la comparaison
    $datetime_saisie = new DateTime($date_match . ' ' . $heure_match);
    $datetime_actuelle = new DateTime(); // Date et heure actuelles

    // Vérifier si la date et l'heure sont dans le futur ou égales à l'heure actuelle
    if ($datetime_saisie < $datetime_actuelle) {
        $message = "La date et l'heure du match doivent être au moins égales à la date et l'heure actuelles.";
    } else {
        // Si la date et l'heure sont valides, on enregistre le match et on récupère son ID
        $match_id = ajouterMatch($date_match, $heure_match, $equipe_adverse, $lieu, $resultat);

        // Vérifier si l'insertion a réussi
        if ($match_id) {
            // Enregistrer l'ID du match dans la session pour l'utiliser dans ModifierFeuilleMatch.php
            $_SESSION['match_id_temporaire'] = $match_id;

            // Rediriger vers la page de création de la feuille de match
            header("Location: ModifierFeuilleMatch.php?match_id=" . $match_id);
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
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>
    <h1>Créer un match</h1>

    <?php if (!empty($message)) : ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST" action="CreationMatch.php">
        <label for="date_match">Date du match :</label>
        <input type="date" id="date_match" name="date_match" required><br>

        <label for="heure_match">Heure du match :</label>
        <input type="time" id="heure_match" name="heure_match" required><br>

        <label for="equipe_adverse">Nom de l'équipe adverse :</label>
        <input type="text" id="equipe_adverse" name="equipe_adverse" required><br>

        <label for="lieu">Lieu de rencontre :</label>
        <select id="lieu" name="lieu" required>
            <option value="Domicile">Domicile</option>
            <option value="Extérieur">Extérieur</option>
        </select><br>

        <label for="resultat">Résultat :</label>
        <input type="text" id="resultat" name="resultat"><br>

        <button type="submit">Créer le match</button>
    </form>
</body>
</html>
