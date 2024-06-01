INSERT INTO Projets (projet_name, last_modif) VALUES ("POS tagging", NOW());
INSERT INTO Tags (label, color) VALUES ("NOUN", "#3333ff");
INSERT INTO Tag_Projet (id_tag, id_projet) VALUES(1, 1);

INSERT INTO Regex (regex, description, id_tag) VALUES ("\\w+tion", "Noms en -tion", 1);
INSERT INTO Regex (regex, description, id_tag) VALUES ("\\w+isme", "Noms en -isme", 1);

INSERT INTO Tags (label, color) VALUES("PREP", "#ff3333");
INSERT INTO Tag_Projet(id_tag, id_projet) VALUES (2, 1);

INSERT INTO Lexique (mot, id_tag) VALUES("à", 2);
INSERT INTO Lexique (mot, id_tag) VALUES("sans", 2);
INSERT INTO Lexique (mot, id_tag) VALUES("avec", 2);
INSERT INTO Lexique (mot, id_tag) VALUES("sous", 2);
INSERT INTO Lexique (mot, id_tag) VALUES("sur", 2);
INSERT INTO Lexique (mot, id_tag) VALUES("pour", 2);
INSERT INTO Lexique (mot, id_tag) VALUES("par", 2);
INSERT INTO Lexique (mot, id_tag) VALUES("en", 2);
INSERT INTO Lexique (mot, id_tag) VALUES("parmi", 2);
INSERT INTO Lexique (mot, id_tag) VALUES("autour", 2);
INSERT INTO Regex (regex, description, id_tag) VALUES("aux?", "au singulier et pluriel", 2);
INSERT INTO Regex (regex, description, id_tag) VALUES("de?", "d ou d'", 2);

INSERT INTO Tags(label, color) VALUES ("NUM", "#33ff33");
INSERT INTO Tag_Projet(id_tag, id_projet) VALUES(3, 1);
INSERT INTO Regex (regex, description, id_tag) VALUES ("\\d+( \\d{3})*,?[\\d ]*", "nombres avec possibilité d'espace ou de virgule", 3);
