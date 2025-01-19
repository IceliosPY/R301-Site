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

// Récupérer tous les matchs
$matchs = getAllMatchs();

// Trouver le match spécifique avec l'ID fourni
$match = null;
foreach ($matchs as $m) {
    if ($m['id'] === $match_id) {
        $match = $m;
        break;
    }
}

if (!$match) {
    die("Match non trouvé.");
}

// Créer un objet DateTime pour la date/heure du match
$date_match = new DateTime($match['date_match'] . ' ' . $match['heure_match']);
$current_time = new DateTime(); // Heure actuelle

// Vérifier si la date du match est passée
$is_match_past = $date_match < $current_time;

if ($is_match_past) {
    // Empêcher les modifications si le match est passé
    $message = "Ce match est déjà passé. Vous ne pouvez plus modifier la feuille de match.";
}

// Initialiser ou récupérer les joueurs stockés en session
if (!isset($_SESSION['feuille_match'][$match_id])) {
    $_SESSION['feuille_match'][$match_id] = [
        'titulaire' => [],
        'remplacant' => []
    ];

    // Charger les joueurs existants de la feuille de match avec leur poste préféré
    $joueurs_feuille = getJoueursDeFeuilleMatchComplet($match_id); // Inclut les statuts et les postes
    foreach ($joueurs_feuille as $joueur) {
        $joueur_id = $joueur['joueur_id'];
        $statut = $joueur['statut'];
        $poste_prefere = $joueur['poste_prefere'];
        
        // Ajouter le joueur avec son poste préféré dans la session
        $_SESSION['feuille_match'][$match_id][$statut][$joueur_id] = $poste_prefere;
    }
}

// Récupérer les joueurs actifs
$joueurs_actifs = getJoueursActifs();

// Gestion des ajouts/suppressions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$is_match_past) {
    if (isset($_POST['action'])) {
        $joueur_id = (int)($_POST['joueur_id'] ?? 0);
        $statut = $_POST['statut'] ?? '';
        $poste_prefere = $_POST['poste_prefere'] ?? ''; // Récupérer le poste préféré

        // Lors de l'ajout d'un joueur
if ($_POST['action'] === 'ajouter') {
    $joueur_id = (int)($_POST['joueur_id'] ?? 0);
    $statut = $_POST['statut'] ?? '';
    $poste_prefere = $_POST['poste_prefere'] ?? ''; // Récupérer le poste préféré

    if (in_array($joueur_id, array_keys($_SESSION['feuille_match'][$match_id][$statut]))) {
        $message = "Ce joueur est déjà sélectionné.";
    } elseif ($statut === 'titulaire' && count($_SESSION['feuille_match'][$match_id]['titulaire']) >= 5) {
        $message = "Vous ne pouvez pas sélectionner plus de 5 titulaires.";
    } elseif ($statut === 'remplacant' && count($_SESSION['feuille_match'][$match_id]['remplacant']) >= 3) {
        $message = "Vous ne pouvez pas sélectionner plus de 3 remplaçants.";
    } else {
        // Ajouter le joueur avec son poste préféré dans la session
        $_SESSION['feuille_match'][$match_id][$statut][$joueur_id] = $poste_prefere;
        $message = "Joueur ajouté avec succès.";
    }
}elseif ($_POST['action'] === 'supprimer') {
    foreach (['titulaire', 'remplacant'] as $key) {
        if (isset($_SESSION['feuille_match'][$match_id][$key][$joueur_id])) {
            unset($_SESSION['feuille_match'][$match_id][$key][$joueur_id]);
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
            // Supprimer tous les joueurs du match (titulaire et remplaçant)
            supprimerTousJoueursDeFeuilleMatch($match_id);  // Supprimer tous les joueurs liés au match
    
            // Ajouter les nouveaux joueurs à la base de données
            foreach ($_SESSION['feuille_match'][$match_id]['titulaire'] as $joueur_id => $poste_prefere) {
                ajouterJoueurFeuilleMatch($match_id, $joueur_id, 'titulaire', $poste_prefere);
            }
            foreach ($_SESSION['feuille_match'][$match_id]['remplacant'] as $joueur_id => $poste_prefere) {
                ajouterJoueurFeuilleMatch($match_id, $joueur_id, 'remplacant', $poste_prefere);
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
        <?php foreach ($_SESSION['feuille_match'][$match_id]['titulaire'] as $joueur_id => $poste_prefere): ?>
            <?php 
                $joueur = array_filter($joueurs_actifs, fn($j) => $j['id'] === $joueur_id);
                $joueur = reset($joueur);
            ?>
            <li>
                <?= htmlspecialchars($joueur['nom'] . ' ' . $joueur['prenom'] . ' - ' . $poste_prefere) ?>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="joueur_id" value="<?= $joueur_id ?>">
                    <input type="hidden" name="statut" value="titulaire">
                    <input type="hidden" name="action" value="supprimer">
                    <button type="submit">Supprimer</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>

    <h3>Remplaçants</h3>
    <ul>
        <?php foreach ($_SESSION['feuille_match'][$match_id]['remplacant'] as $joueur_id => $poste_prefere): ?>
            <?php 
                $joueur = array_filter($joueurs_actifs, fn($j) => $j['id'] === $joueur_id);
                $joueur = reset($joueur);
            ?>
            <li>
                <?= htmlspecialchars($joueur['nom'] . ' ' . $joueur['prenom'] . ' - ' . $poste_prefere) ?>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="joueur_id" value="<?= $joueur_id ?>">
                    <input type="hidden" name="statut" value="remplacant">
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
                <?php if (!isset($_SESSION['feuille_match'][$match_id]['titulaire'][$joueur['id']]) && 
                          !isset($_SESSION['feuille_match'][$match_id]['remplacant'][$joueur['id']])): ?>
                    <option value="<?= $joueur['id'] ?>"><?= htmlspecialchars($joueur['nom'] . ' ' . $joueur['prenom']) ?></option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>

        <label for="statut">Statut :</label>
        <select name="statut" id="statut" required>
            <option value="titulaire">Titulaire</option>
            <option value="remplacant">Remplaçant</option>
        </select>

        <label for="poste_prefere">Poste :</label>
        <select name="poste_prefere" id="poste_prefere" required>
            <option value="Top Lane">Top Lane</option>
            <option value="Mid Lane">Mid Lane</option>
            <option value="Bot Lane ADC">Bot Lane ADC</option>
            <option value="Bot Lane Support">Bot Lane Support</option>
            <option value="Jungler">Jungler</option>
        </select>

        <button type="submit" name="action" value="ajouter">Ajouter</button>
    </form>

    <h2>Valider la sélection</h2>
    <form method="post">
        <button type="submit" name="valider">Valider la sélection</button>
    </form>

</body>
</html>