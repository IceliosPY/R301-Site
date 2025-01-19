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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Évaluations - Feuille de Match</title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>
    <h1>Feuille de Match (Évaluation)</h1>

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
            </li>
        <?php endforeach; ?>
    </ul>

    <h2>Evaluation Complète</h2>
    <p>Cette page affiche la feuille de match. Aucune modification n'est permise.</p>
    
</body>
</html>
