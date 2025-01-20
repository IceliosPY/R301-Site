<?php
session_start(); // Démarrer la session

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: Connexion.php");
    exit;
}

require_once __DIR__ . '/librairie/BD.php'; // Inclure les fonctions liées à la base de données

// Récupérer l'ID du match depuis l'URL
if (!isset($_GET['match_id']) || empty($_GET['match_id'])) {
    die("ID du match non spécifié.");
}

$match_id = (int)$_GET['match_id'];
$message = "";

// Récupérer les détails du match
$match = getMatchParId($match_id);
if (!$match) {
    die("Match non trouvé.");
}

// Créer un objet DateTime pour la date/heure du match
$date_match = new DateTime($match['date_match'] . ' ' . $match['heure_match']);
$current_time = new DateTime();
$is_match_past = $date_match < $current_time; // Vérifie si le match est passé

// Initialiser la session pour la feuille de match si elle n'existe pas
if (!isset($_SESSION['feuille_match'][$match_id])) {
    $_SESSION['feuille_match'][$match_id] = ['titulaire' => [], 'remplacant' => []];

    $joueurs_feuille = getJoueursDeFeuilleMatchComplet($match_id);
    foreach ($joueurs_feuille as $joueur) {
        $_SESSION['feuille_match'][$match_id][$joueur['statut']][$joueur['joueur_id']] = $joueur['poste_prefere'];
    }
}

$joueurs_actifs = getJoueursActifs(); // Récupérer les joueurs actifs

// Gestion des ajouts ou suppressions de joueurs
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$is_match_past) {
    $joueur_id = (int)($_POST['joueur_id'] ?? 0);
    $statut = $_POST['statut'] ?? '';
    $poste_prefere = $_POST['poste_prefere'] ?? '';
    $action = $_POST['action'] ?? '';

    if ($action === 'ajouter') {
        if (isset($_SESSION['feuille_match'][$match_id][$statut][$joueur_id])) {
            $message = "Ce joueur est déjà sélectionné.";
        } elseif ($statut === 'titulaire' && count($_SESSION['feuille_match'][$match_id]['titulaire']) >= 5) {
            $message = "Vous ne pouvez pas sélectionner plus de 5 titulaires.";
        } elseif ($statut === 'remplacant' && count($_SESSION['feuille_match'][$match_id]['remplacant']) >= 3) {
            $message = "Vous ne pouvez pas sélectionner plus de 3 remplaçants.";
        } else {
            $_SESSION['feuille_match'][$match_id][$statut][$joueur_id] = $poste_prefere;
            $message = "Joueur ajouté avec succès.";
        }
    } elseif ($action === 'supprimer') {
        foreach (['titulaire', 'remplacant'] as $key) {
            if (isset($_SESSION['feuille_match'][$match_id][$key][$joueur_id])) {
                unset($_SESSION['feuille_match'][$match_id][$key][$joueur_id]);
                $message = "Joueur supprimé avec succès.";
                break;
            }
        }
    } elseif (isset($_POST['valider'])) {
        $total_joueurs = count($_SESSION['feuille_match'][$match_id]['titulaire']) + count($_SESSION['feuille_match'][$match_id]['remplacant']);
        if ($total_joueurs !== 8) {
            $message = "Vous devez sélectionner exactement 5 titulaires et 3 remplaçants (8 joueurs au total).";
        } else {
            supprimerTousJoueursDeFeuilleMatch($match_id);
            foreach ($_SESSION['feuille_match'][$match_id]['titulaire'] as $joueur_id => $poste_prefere) {
                ajouterJoueurFeuilleMatch($match_id, $joueur_id, 'titulaire', $poste_prefere);
            }
            foreach ($_SESSION['feuille_match'][$match_id]['remplacant'] as $joueur_id => $poste_prefere) {
                ajouterJoueurFeuilleMatch($match_id, $joueur_id, 'remplacant', $poste_prefere);
            }
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
    <link rel="stylesheet" href="./CSS/ModifierFeuilleMatch.css">
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <a href="ListeJoueur.php" class="btn btn-primary">Liste des joueurs</a>
            <a href="ListeMatch.php" class="btn btn-primary">Liste des matchs</a>
            <a href="Statistiques.php" class="btn btn-primary">Statistiques</a>
            <a href="?deconnexion=1" class="btn btn-secondary">Se déconnecter</a>
        </nav>
    </header>

    <div class="container">
        <h1>Modifier Feuille de Match</h1>

        <?php if (!empty($message)): ?>
            <p class="error-message"><?= htmlspecialchars($message) ?></p>
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
                        <input type="hidden" name="action" value="supprimer">
                        <button type="submit">Supprimer</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>

        <h2>Ajouter un joueur</h2>
        <form method="post" class="form-container">
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

            <button type="submit" name="action" value="ajouter" class="btn btn-primary">Ajouter</button>
        </form>

        <h2>Valider la sélection</h2>
        <form method="post">
            <button type="submit" name="valider" class="btn btn-success">Valider la sélection</button>
        </form>
    </div>
</body>
</html>
