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

require_once __DIR__ . '/librairie/BD.php';

$message = '';
$joueur = null;

// Vérifie si un ID est passé en paramètre
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = (int) $_GET['id'];
    $joueur = getJoueurParId($id); // Fonction pour récupérer un joueur par ID

    if (!$joueur) {
        $message = "Joueur introuvable.";
    }
} else {
    $message = "ID manquant.";
}

// Traite le formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) $_POST['id'];
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $numero_licence = $_POST['numero_licence'];
    $date_naissance = $_POST['date_naissance'];
    $taille = (float) $_POST['taille'];
    $poids = (float) $_POST['poids'];
    $statut = $_POST['statut'];
    $commentaires = $_POST['commentaires'];

    if (empty($nom) || empty($prenom) || empty($numero_licence) || empty($date_naissance) || empty($taille) || empty($poids) || empty($statut)) {
        $message = "Tous les champs sont obligatoires.";
    } else {
        if (modifierJoueur($id, $nom, $prenom, $numero_licence, $date_naissance, $taille, $poids, $statut, $commentaires)) {
            header("Location: ListeJoueur.php");
            exit();
        } else {
            $message = "Erreur lors de la modification.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un joueur</title>
    <link rel="stylesheet" href="./CSS/ModifierJoueur.css"> <!-- Lien vers le CSS -->
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
        <h1>Modifier un joueur</h1>

        <!-- Affichage du message -->
        <?php if (!empty($message)) : ?>
            <p class="error-message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <!-- Formulaire -->
        <?php if ($joueur) : ?>
            <form method="POST" action="ModifierJoueur.php" class="form-container">
                <input type="hidden" name="id" value="<?= htmlspecialchars($joueur['id']) ?>">

                <label for="nom">Nom :</label>
                <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($joueur['nom']) ?>" required>

                <label for="prenom">Prénom :</label>
                <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($joueur['prenom']) ?>" required>

                <label for="numero_licence">N° Licence :</label>
                <input type="text" id="numero_licence" name="numero_licence" value="<?= htmlspecialchars($joueur['numero_licence']) ?>" required>

                <label for="date_naissance">Date de naissance :</label>
                <input type="date" id="date_naissance" name="date_naissance" value="<?= htmlspecialchars($joueur['date_naissance']) ?>" required>

                <label for="taille">Taille (en cm) :</label>
                <input type="number" id="taille" name="taille" step="0.1" value="<?= htmlspecialchars($joueur['taille']) ?>" required>

                <label for="poids">Poids (en kg) :</label>
                <input type="number" id="poids" name="poids" step="0.1" value="<?= htmlspecialchars($joueur['poids']) ?>" required>

                <label for="statut">Statut :</label>
                <select id="statut" name="statut" required>
                    <option value="Actif" <?= $joueur['statut'] === 'Actif' ? 'selected' : '' ?>>Actif</option>
                    <option value="Blessé" <?= $joueur['statut'] === 'Blessé' ? 'selected' : '' ?>>Blessé</option>
                    <option value="Suspendu" <?= $joueur['statut'] === 'Suspendu' ? 'selected' : '' ?>>Suspendu</option>
                    <option value="Absent" <?= $joueur['statut'] === 'Absent' ? 'selected' : '' ?>>Absent</option>
                </select>

                <label for="commentaires">Commentaires :</label>
                <textarea id="commentaires" name="commentaires" maxlength="255"><?= htmlspecialchars($joueur['commentaires'] ?? '') ?></textarea>

                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
