<?php
session_start();
require_once 'librairie/BD.php'; // Inclure votre fichier BD pour la connexion

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Connexion à la base de données
    $pdo = getDbConnection();

    // Vérifier si l'utilisateur existe
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE username = :username AND password = :password");
    $stmt->execute([
        'username' => $username,
        'password' => $password, // Pas de hachage, utilisation directe
    ]);
    $user = $stmt->fetch();

    if ($user) {
        // Connexion réussie : enregistrer l'utilisateur dans la session
        $_SESSION['user_id'] = $user['id']; // Stocke l'ID utilisateur dans la session
        $_SESSION['user'] = $user['username'];

        // Redirection vers ListeJoueur.php
        header("Location: ListeJoueur.php");
        exit; // S'assurer que le script s'arrête ici
    } else {
        $erreur = "Nom d'utilisateur ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <!-- Inclusion du fichier CSS -->
    <link rel="stylesheet" href="./CSS/connexion.css">
</head>
<body>
    <div class="form-container">
        <h1>Connexion</h1>
        <?php if (isset($erreur)): ?>
            <p style="color:red;"><?= htmlspecialchars($erreur) ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <label for="username">Nom d'utilisateur :</label>
            <input type="text" id="username" name="username" required>
            <br>
            <label for="password">Mot de passe :</label>
            <input type="password" id="password" name="password" required>
            <br>
            <button type="submit">Se connecter</button>
        </form>
    </div>
</body>
</html>
