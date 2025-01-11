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
    $date_match = $_POST['date_match'];
    $heure_match = $_POST['heure_match'];
    $equipe_adverse = $_POST['equipe_adverse'];
    $lieu = $_POST['lieu'];
    $resultat = $_POST['resultat'] ?? null; // Si le résultat n'est pas saisi, il restera NULL

    // Vérifier que tous les champs sont remplis (sauf le résultat qui est optionnel)
    if (empty($date_match) || empty($heure_match) || empty($equipe_adverse) || empty($lieu)) {
        $message = "Tous les champs sont obligatoires, sauf le résultat.";
    } else {
        // Appeler la fonction ajouterMatch() de BD.php
        if (ajouterMatch($date_match, $heure_match, $equipe_adverse, $lieu, $resultat)) {
            $message = "Match ajouté avec succès.";
            header("Location: ListeMatch.php"); // Redirection vers la liste des matchs
            exit();
        } else {
            $message = "Erreur lors de l'ajout du match.";
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
