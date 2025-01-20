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
require_once __DIR__ . '/librairie/BD.php';

$message = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $numero_licence = $_POST['numero_licence'];
    $date_naissance = $_POST['date_naissance'];
    $taille = (float) $_POST['taille'];
    $poids = (float) $_POST['poids'];
    $statut = $_POST['statut'];

    if (empty($nom) || empty($prenom) || empty($numero_licence) || empty($date_naissance) || empty($taille) || empty($poids) || empty($statut)) {
        $message = "Tous les champs sont obligatoires.";
    } else {
        if (ajouterJoueur($nom, $prenom, $numero_licence, $date_naissance, $taille, $poids, $statut)) {
            // Redirection vers ListeJoueur.php après une création réussie
            header("Location: ListeJoueur.php");
            exit();
        } else {
            $message = "Erreur lors de l'ajout du joueur.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un joueur</title>
    <link rel="stylesheet" href="./CSS/CreationJoueur.css"> <!-- Lien vers le CSS -->
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
        <h1>Créer un joueur</h1>

        <!-- Affichage du message -->
        <?php if (!empty($message)) : ?>
            <p class="error-message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <!-- Formulaire -->
        <form method="POST" action="CreationJoueur.php" class="form-container">
            <label for="nom">Nom :</label>
            <input type="text" id="nom" name="nom" required>

            <label for="prenom">Prénom :</label>
            <input type="text" id="prenom" name="prenom" required>

            <label for="numero_licence">N° Licence :</label>
            <input type="text" id="numero_licence" name="numero_licence" required>

            <label for="date_naissance">Date de naissance :</label>
            <input type="date" id="date_naissance" name="date_naissance" required>

            <label for="taille">Taille (en cm) :</label>
            <input type="number" id="taille" name="taille" step="0.1" required>

            <label for="poids">Poids (en kg) :</label>
            <input type="number" id="poids" name="poids" step="0.1" required>

            <label for="statut">Statut :</label>
            <select id="statut" name="statut" required>
                <option value="Actif">Actif</option>
                <option value="Blessé">Blessé</option>
                <option value="Suspendu">Suspendu</option>
                <option value="Absent">Absent</option>
            </select>

            <button type="submit" class="btn btn-primary">Créer le joueur</button>
        </form>
    </div>
</body>
</html>
