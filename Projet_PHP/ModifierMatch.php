<?php

session_start(); // Démarre la session

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: Connexion.php"); // Redirige vers la page de connexion si non connecté
    exit;
}

require_once __DIR__ . '/librairie/BD.php';

$message = '';
$match = null;

// Vérifie si un ID est passé en paramètre
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $match = getMatchParId($id); // Fonction pour récupérer un match par ID

    if (!$match) {
        $message = "Match introuvable.";
    }
} else {
    $message = "ID manquant.";
}

// Traite le formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) $_POST['id'];
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
        if (modifierMatch($id, $date_match, $heure_match, $equipe_adverse, $lieu)) {
            header("Location: ListeMatch.php");
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
    <link rel="stylesheet" href="/CSS/styles.css">
</head>
<body>
    <h1>Modifier un match</h1>

    <?php if (!empty($message)) : ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <?php if ($match) : ?>
        <form method="POST" action="ModifierMatch.php">
            <input type="hidden" name="id" value="<?= htmlspecialchars($match['id']) ?>">

            <label for="date_match">Date :</label>
            <input type="date" id="date_match" name="date_match" value="<?= htmlspecialchars($match['date_match']) ?>" required><br>

            <label for="heure_match">Heure :</label>
            <input type="time" id="heure_match" name="heure_match" value="<?= htmlspecialchars($match['heure_match']) ?>" required><br>

            <label for="equipe_adverse">Équipe adverse :</label>
            <input type="text" id="equipe_adverse" name="equipe_adverse" value="<?= htmlspecialchars($match['equipe_adverse']) ?>" required><br>

            <label for="lieu">Lieu :</label>
            <input type="text" id="lieu" name="lieu" value="<?= htmlspecialchars($match['lieu']) ?>" required><br>

            <button type="submit">Enregistrer</button>
        </form>
    <?php endif; ?>
</body>
</html>
