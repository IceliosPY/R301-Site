<?php
// Configuration pour la connexion à la base de données
const DB_HOST = 'localhost';
const DB_NAME = 'Gestion-Equipe';
const DB_USER = 'root';
const DB_PASSWORD = '';

/**
 * Connexion à la base de données via PDO.
 * @return PDO
 */
function getDbConnection(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }
    return $pdo;
}

/**
 * Ajoute un joueur dans la base de données.
 * 
 * @param string $nom
 * @param string $prenom
 * @param string $numero_licence
 * @param string $date_naissance
 * @param float $taille
 * @param float $poids
 * @param string $statut
 * @return bool
 */
function ajouterJoueur(string $nom, string $prenom, string $numero_licence, string $date_naissance, float $taille, float $poids, string $statut): bool {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("
        INSERT INTO joueurs (nom, prenom, numero_licence, date_naissance, taille, poids, statut)
        VALUES (:nom, :prenom, :numero_licence, :date_naissance, :taille, :poids, :statut)
    ");
    return $stmt->execute([
        ':nom' => $nom,
        ':prenom' => $prenom,
        ':numero_licence' => $numero_licence,
        ':date_naissance' => $date_naissance,
        ':taille' => $taille,
        ':poids' => $poids,
        ':statut' => $statut,
    ]);
}

function getAllPlayers($pdo) {
    $query = "SELECT id, nom, prenom, numero_licence, date_naissance, taille, poids, statut FROM joueurs";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupère tous les joueurs de la base de données.
 * 
 * @return array
 */
function getTousLesJoueurs(): array {
    $pdo = getDbConnection();
    $stmt = $pdo->query("SELECT id, nom, prenom, numero_licence, date_naissance, taille, poids, statut, evaluation FROM joueurs");
    return $stmt->fetchAll();
}

function getJoueurParId($id) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT * FROM joueurs WHERE id = :id");
    $stmt->execute(['id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function modifierJoueur($id, $nom, $prenom, $numero_licence, $date_naissance, $taille, $poids, $statut, $commentaires, $evaluation) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("
        UPDATE joueurs 
        SET nom = :nom, prenom = :prenom, numero_licence = :numero_licence, 
            date_naissance = :date_naissance, taille = :taille, poids = :poids, 
            statut = :statut, commentaires = :commentaires, evaluation = :evaluation
        WHERE id = :id
    ");
    return $stmt->execute([
        'id' => $id,
        'nom' => $nom,
        'prenom' => $prenom,
        'numero_licence' => $numero_licence,
        'date_naissance' => $date_naissance,
        'taille' => $taille,
        'poids' => $poids,
        'statut' => $statut,
        'commentaires' => $commentaires,
        'evaluation' => $evaluation,
    ]);
}

/**
 * Supprime un joueur de la base de données.
 * 
 * @param int $id
 * @return bool
 */
function supprimerJoueur($id): bool {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("DELETE FROM joueurs WHERE id = :id");
    return $stmt->execute(['id' => $id]);
}

/**
 * Ajouter un match dans la base de données
 *
 * @param string $date_match
 * @param string $heure_match
 * @param string $equipe_adverse
 * @param string $lieu
 * @param string|null $resultat_equipe
 * @param string|null $resultat_adverse
 * @return bool
 */
function ajouterMatch(string $date_match, string $heure_match, string $equipe_adverse, string $lieu, string $resultat_equipe = null, string $resultat_adverse = null) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("
        INSERT INTO matchs (date_match, heure_match, equipe_adverse, lieu, resultat_equipe, resultat_adverse)
        VALUES (:date_match, :heure_match, :equipe_adverse, :lieu, :resultat_equipe, :resultat_adverse)
    ");
    $stmt->execute([
        ':date_match' => $date_match,
        ':heure_match' => $heure_match,
        ':equipe_adverse' => $equipe_adverse,
        ':lieu' => $lieu,
        ':resultat_equipe' => $resultat_equipe,
        ':resultat_adverse' => $resultat_adverse
    ]);
    
    // Récupérer l'ID du match ajouté
    return $pdo->lastInsertId();  // Retourne l'ID du match
}


/**
 * Récupérer tous les matchs
 *
 * @return array
 */
function getAllMatchs() {
    $pdo = getDbConnection(); // Appel à la fonction de connexion
    $stmt = $pdo->query("SELECT id, date_match, heure_match, equipe_adverse, lieu, resultat_equipe, resultat_adverse FROM matchs ORDER BY date_match DESC, heure_match DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Supprime un match de la base de données.
 * 
 * @param int $id
 * @return bool
 */
function supprimerMatch($id): bool {
    $pdo = getDbConnection();
    
    // Supprimer d'abord les joueurs associés au match
    $stmt = $pdo->prepare("DELETE FROM feuillematch WHERE match_id = :id");
    $stmt->execute(['id' => $id]);

    // Supprimer ensuite le match
    $stmt = $pdo->prepare("DELETE FROM matchs WHERE id = :id");
    return $stmt->execute(['id' => $id]);
}


function getMatchParId($id) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT * FROM matchs WHERE id = :id");
    $stmt->execute(['id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function modifierMatch($id, $date_match, $heure_match, $equipe_adverse, $lieu, $resultat_equipe, $resultat_adverse) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("
        UPDATE matchs
        SET date_match = :date_match,
            heure_match = :heure_match,
            equipe_adverse = :equipe_adverse,
            lieu = :lieu,
            resultat_equipe = :resultat_equipe,
            resultat_adverse = :resultat_adverse
        WHERE id = :id
    ");
    return $stmt->execute([
        'date_match' => $date_match,
        'heure_match' => $heure_match,
        'equipe_adverse' => $equipe_adverse,
        'lieu' => $lieu,
        'resultat_equipe' => $resultat_equipe !== null ? $resultat_equipe : null,
        'resultat_adverse' => $resultat_adverse !== null ? $resultat_adverse : null,
        'id' => $id
    ]);
}

function getJoueursActifs() {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT id, nom, prenom, taille, poids, poste_prefere FROM joueurs WHERE statut = 'actif' ORDER BY nom, prenom");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function ajouterJoueurFeuilleMatch($match_id, $joueur_id, $statut, $poste_prefere) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("
        INSERT INTO feuillematch (match_id, joueur_id, statut, poste_prefere)
        VALUES (:match_id, :joueur_id, :statut, :poste_prefere)
    ");
    $stmt->execute([
        ':match_id' => $match_id,
        ':joueur_id' => $joueur_id,
        ':statut' => $statut,
        ':poste_prefere' => $poste_prefere
    ]);
}

function getJoueursDeFeuilleMatchComplet($match_id) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT joueur_id, statut, poste_prefere FROM feuillematch WHERE match_id = :match_id");
    $stmt->execute(['match_id' => $match_id]);
    return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}

function supprimerJoueurDeFeuilleMatch($match_id, $joueur_id) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("DELETE FROM feuillematch WHERE match_id = :match_id AND joueur_id = :joueur_id");
    $stmt->execute(['match_id' => $match_id, 'joueur_id' => $joueur_id]);
}

// Fonction pour récupérer les statistiques des matchs et des joueurs
function getStatistiques() {
    $pdo = getDbConnection();
    
    // Statistiques générales sur les matchs, exclure les matchs sans résultat
    $query = "SELECT 
                COUNT(*) AS total_matchs,
                SUM(CASE WHEN resultat_equipe > resultat_adverse THEN 1 ELSE 0 END) AS matchs_gagnés,
                SUM(CASE WHEN resultat_equipe < resultat_adverse THEN 1 ELSE 0 END) AS matchs_perdus,
                SUM(CASE WHEN resultat_equipe = resultat_adverse THEN 1 ELSE 0 END) AS matchs_nuls
              FROM matchs
              WHERE resultat_equipe IS NOT NULL AND resultat_adverse IS NOT NULL";  // Exclure les matchs sans résultat
    $stmt = $pdo->query($query);
    $matchs = $stmt->fetch(PDO::FETCH_ASSOC);

    // Récupérer les statistiques des joueurs, exclure les matchs sans résultat
    $queryJoueurs = "SELECT 
                        j.id,
                        j.nom,
                        j.prenom,
                        j.statut,
                        COUNT(CASE WHEN f.statut = 'titulaire' THEN 1 END) AS titulaire_count,
                        COUNT(CASE WHEN f.statut = 'remplacant' THEN 1 END) AS remplaçant_count,
                        AVG(j.evaluation) AS moyenne_evaluation,
                        SUM(CASE WHEN m.resultat_equipe > m.resultat_adverse THEN 1 ELSE 0 END) / COUNT(*) * 100 AS pourcentage_victoires,
                        -- Calcul du poste préféré basé sur les occurrences des différents postes
                        (SELECT f.poste_prefere
                         FROM feuillematch f
                         WHERE f.joueur_id = j.id
                         GROUP BY f.poste_prefere
                         ORDER BY COUNT(f.poste_prefere) DESC
                         LIMIT 1) AS poste_prefere
                     FROM joueurs j
                     LEFT JOIN feuillematch f ON j.id = f.joueur_id
                     LEFT JOIN matchs m ON f.match_id = m.id
                     WHERE m.resultat_equipe IS NOT NULL AND m.resultat_adverse IS NOT NULL  -- Exclure les matchs sans résultat
                     GROUP BY j.id";
    $stmtJoueurs = $pdo->query($queryJoueurs);
    $joueurs = $stmtJoueurs->fetchAll(PDO::FETCH_ASSOC);

    return [
        'matchs' => $matchs,
        'joueurs' => $joueurs
    ];
}

?>