<?php
session_start(); // Démarrer la session

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: Connexion.php"); // Rediriger vers la page de connexion si non connecté
    exit;
}

require_once __DIR__ . '/librairie/BD.php'; // Inclure le fichier BD.php

// Récupérer l'ID du match depuis l'URL
if (!isset($_GET['match_id']) || empty($_GET['match_id'])) {
    die("ID du match non spécifié.");
}

$match_id = (int)$_GET['match_id'];
$message = "";

// Initialiser ou récupérer les joueurs stockés en session
if (!isset($_SESSION['feuille_match'][$match_id])) {
    $_SESSION['feuille_match'][$match_id] = [
        'titulaire' => [],
        'remplacant' => []
    ];

    // Charger les joueurs existants de la feuille de match
    $joueurs_feuille = getJoueursDeFeuilleMatchComplet($match_id); // Inclut les statuts
    foreach ($joueurs_feuille as $joueur_id => $statut) {
        $_SESSION['feuille_match'][$match_id][$statut][] = $joueur_id;
    }
}

// Récupérer les joueurs actifs
$joueurs_actifs = getJoueursActifs();

// Gestion des ajouts/suppressions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $joueur_id = (int)($_POST['joueur_id'] ?? 0);
        $statut = $_POST['statut'] ?? '';

        if ($_POST['action'] === 'ajouter') {
            if (in_array($joueur_id, $_SESSION['feuille_match'][$match_id]['titulaire']) ||
                in_array($joueur_id, $_SESSION['feuille_match'][$match_id]['remplacant'])) {
                $message = "Ce joueur est déjà sélectionné.";
            } elseif ($statut === 'titulaire' && count($_SESSION['feuille_match'][$match_id]['titulaire']) >= 5) {
                $message = "Vous ne pouvez pas sélectionner plus de 5 titulaires.";
            } elseif ($statut === 'remplacant' && count($_SESSION['feuille_match'][$match_id]['remplacant']) >= 3) {
                $message = "Vous ne pouvez pas sélectionner plus de 3 remplaçants.";
            } else {
                $_SESSION['feuille_match'][$match_id][$statut][] = $joueur_id;
                $message = "Joueur ajouté avec succès.";
            }
        } elseif ($_POST['action'] === 'supprimer') {
            foreach (['titulaire', 'remplacant'] as $key) {
                if (($index = array_search($joueur_id, $_SESSION['feuille_match'][$match_id][$key])) !== false) {
                    unset($_SESSION['feuille_match'][$match_id][$key][$index]);
                    $_SESSION['feuille_match'][$match_id][$key] = array_values($_SESSION['feuille_match'][$match_id][$key]);
                    $message = "Joueur supprimé avec succès.";
                    break;
                }
            }
        }
    } elseif (isset($_POST['valider'])) {
        $total_joueurs = count($_SESSION['feuille_match'][$match_id]['titulaire']) + count($_SESSION['feuille_match'][$match_id]['remplacant']);
        if ($total_joueurs !== 8) {
            $message = "Vous devez sélectionner exactement 5 titulaires et 3 remplaçants (8 joueurs au total).";
        } else {
            // Supprimer les joueurs qui ne sont plus dans la feuille de match
            foreach ($_SESSION['feuille_match'][$match_id]['titulaire'] as $joueur_id) {
                supprimerJoueurDeFeuilleMatch($match_id, $joueur_id);  // Supprimer les anciens titulaires
            }
            foreach ($_SESSION['feuille_match'][$match_id]['remplacant'] as $joueur_id) {
                supprimerJoueurDeFeuilleMatch($match_id, $joueur_id);  // Supprimer les anciens remplaçants
            }
    
            // Ajouter les nouveaux joueurs à la base de données
            foreach ($_SESSION['feuille_match'][$match_id]['titulaire'] as $joueur_id) {
                ajouterJoueurFeuilleMatch($match_id, $joueur_id, 'titulaire');
            }
            foreach ($_SESSION['feuille_match'][$match_id]['remplacant'] as $joueur_id) {
                ajouterJoueurFeuilleMatch($match_id, $joueur_id, 'remplacant');
            }
    
            // Redirection après mise à jour réussie
            header("Location: ListeMatch.php");
            exit;
        }
    }    
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Feuille de Match</title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>
    <h1>Feuille de Match</h1>

    <?php if (!empty($message)): ?>
        <p style="<?= strpos($message, 'succès') !== false ? 'color: green;' : 'color: red;' ?>">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>

    <h2>Joueurs sélectionnés</h2>
    <h3>Titulaires</h3>
    <ul>
        <?php foreach ($_SESSION['feuille_match'][$match_id]['titulaire'] as $joueur_id): ?>
            <?php 
                $joueur = array_filter($joueurs_actifs, fn($j) => $j['id'] === $joueur_id);
                $joueur = reset($joueur);
            ?>
            <li>
                <?= htmlspecialchars($joueur['nom'] . ' ' . $joueur['prenom']) ?>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="joueur_id" value="<?= $joueur_id ?>">
                    <input type="hidden" name="action" value="supprimer">
                    <button type="submit">Supprimer</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>

    <h3>Remplaçants</h3>
    <ul>
        <?php foreach ($_SESSION['feuille_match'][$match_id]['remplacant'] as $joueur_id): ?>
            <?php 
                $joueur = array_filter($joueurs_actifs, fn($j) => $j['id'] === $joueur_id);
                $joueur = reset($joueur);
            ?>
            <li>
                <?= htmlspecialchars($joueur['nom'] . ' ' . $joueur['prenom']) ?>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="joueur_id" value="<?= $joueur_id ?>">
                    <input type="hidden" name="action" value="supprimer">
                    <button type="submit">Supprimer</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>

    <h2>Ajouter un joueur</h2>
    <form method="post">
        <label for="joueur_id">Joueur :</label>
        <select name="joueur_id" id="joueur_id" required>
            <option value="">-- Sélectionnez un joueur --</option>
            <?php foreach ($joueurs_actifs as $joueur): ?>
                <?php if (!in_array($joueur['id'], $_SESSION['feuille_match'][$match_id]['titulaire']) &&
                          !in_array($joueur['id'], $_SESSION['feuille_match'][$match_id]['remplacant'])): ?>
                    <option value="<?= $joueur['id'] ?>">
                        <?= htmlspecialchars($joueur['nom'] . ' ' . $joueur['prenom']) ?>
                    </option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>

        <label for="statut">Statut :</label>
        <select name="statut" id="statut" required>
            <option value="titulaire">Titulaire</option>
            <option value="remplacant">Remplaçant</option>
        </select>

        <input type="hidden" name="action" value="ajouter">
        <button type="submit">Ajouter</button>
    </form>

    <form method="post">
        <button type="submit" name="valider">Valider la sélection</button>
    </form>
</body>
</html>
