/** FeuilleMatch.php */

<?php

session_start(); // Démarrer la session

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

require_once __DIR__ . '/librairie/BD.php'; // Inclure le fichier BD.php

// Vérifier si un match est sélectionné
if (!isset($_GET['match_id'])) {
    die("Aucun match spécifié.");
}

$match_id = (int) $_GET['match_id'];

// Récupérer le match
$match = getMatchParId($match_id);
if (!$match) {
    die("Le match spécifié n'existe pas.");
}

// Récupérer les joueurs actifs
$joueurs = getJoueursActifs();

// Si la feuille de match est soumise
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulaire = isset($_POST['titulaire']) ? $_POST['titulaire'] : [];
    $remplaçant = isset($_POST['remplacant']) ? $_POST['remplacant'] : [];

    // Valider la sélection
    if (count($titulaire) + count($remplaçant) < 8) {
        $message = "Une équipe de 8 joueurs minimum est requise.";
    } else if (count($titulaire) > 5) {
        $message = "Vous ne pouvez pas avoir plus de 5 titulaires.";
    } else if (count($remplaçant) > 3) {
        $message = "Vous ne pouvez pas avoir plus de 3 remplaçants.";
    } else {
        // Vérifier si un joueur est déjà sélectionné
        $joueurs_selectionnes = array_merge($titulaire, $remplaçant);
        $joueurs_existants = getJoueursDeFeuilleMatch($match_id);

        foreach ($joueurs_selectionnes as $joueur_id) {
            if (in_array($joueur_id, $joueurs_existants)) {
                $message = "Le joueur avec l'ID $joueur_id est déjà sélectionné dans cette feuille de match.";
                break;
            }
        }

        // Vérification pour éviter de sélectionner plusieurs fois le même joueur
        $joueurs_selectionnes_uniques = array_unique($joueurs_selectionnes);
        if (count($joueurs_selectionnes) !== count($joueurs_selectionnes_uniques)) {
            $message = "Vous avez sélectionné un ou plusieurs joueurs plusieurs fois.";
        }

        // Si tout est valide, ajouter la sélection à la base de données
        if (!isset($message)) {
            // Supprimer les anciens joueurs de la feuille de match
            supprimerJoueursFeuilleMatch($match_id);

            foreach ($titulaire as $joueur_id) {
                ajouterJoueurFeuilleMatch($match_id, $joueur_id, 'titulaire');
            }
            foreach ($remplaçant as $joueur_id) {
                ajouterJoueurFeuilleMatch($match_id, $joueur_id, 'remplaçant');
            }
            $message = "La sélection a été enregistrée avec succès.";

            // Rediriger vers la page ListeMatch après validation
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
    <title>Feuille de match</title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>
    <h1>Feuille de match - <?= htmlspecialchars($match['equipe_adverse']) ?> (<?= htmlspecialchars($match['date_match']) ?>)</h1>

    <?php if (isset($message)): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="post">
        <h3>Choisir les titulaires (max 5)</h3>
        <?php foreach ($joueurs as $joueur): ?>
            <div>
                <input type="checkbox" name="titulaire[]" value="<?= $joueur['id'] ?>" id="titulaire_<?= $joueur['id'] ?>">
                <label for="titulaire_<?= $joueur['id'] ?>"><?= htmlspecialchars($joueur['nom']) ?> <?= htmlspecialchars($joueur['prenom']) ?> (Taille: <?= htmlspecialchars($joueur['taille']) ?>, Poids: <?= htmlspecialchars($joueur['poids']) ?>)</label>
            </div>
        <?php endforeach; ?>

        <h3>Choisir les remplaçants (max 3)</h3>
        <?php foreach ($joueurs as $joueur): ?>
            <div>
                <input type="checkbox" name="remplacant[]" value="<?= $joueur['id'] ?>" id="remplacant_<?= $joueur['id'] ?>">
                <label for="remplacant_<?= $joueur['id'] ?>"><?= htmlspecialchars($joueur['nom']) ?> <?= htmlspecialchars($joueur['prenom']) ?> (Taille: <?= htmlspecialchars($joueur['taille']) ?>, Poids: <?= htmlspecialchars($joueur['poids']) ?>)</label>
            </div>
        <?php endforeach; ?>

        <button type="submit">Valider la sélection</button>
    </form>

</body>
</html>

/** Fin code FeuilleMatch.php */
/** BD.php pour FeuilleMatch.php */

function getJoueursDeFeuilleMatch($match_id) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT joueur_id FROM feuillematch WHERE match_id = :match_id");
    $stmt->execute(['match_id' => $match_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function supprimerJoueursFeuilleMatch($match_id) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("DELETE FROM feuillematch WHERE match_id = :match_id");
    $stmt->execute(['match_id' => $match_id]);
}

/** Fin code BD.php pour FeuilleMatch.php */
