CREATE TABLE joueurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    numero_licence VARCHAR(50) NOT NULL,
    date_naissance DATE NOT NULL,
    taille FLOAT NOT NULL,
    poids FLOAT NOT NULL,
    statut ENUM('Actif', 'Blessé', 'Suspendu', 'Absent') NOT NULL
);

CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

INSERT INTO utilisateurs (username, password) VALUES ('Joueur@gmail.com', 'Password');

CREATE TABLE feuillematch (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_match INT,
    id_joueur INT,
    statut ENUM('titulaire', 'remplaçant') NOT NULL,
    FOREIGN KEY (id_match) REFERENCES matchs(id),
    FOREIGN KEY (id_joueur) REFERENCES joueurs(id)
);
