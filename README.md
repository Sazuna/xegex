# Installation des dépendances

```
sudo apt install apache2 php libapache2-mod-php php-mysql
```

# Initialisation du projet

Créer un utilisateur et une base de données nommée xegex, vide
Lancer:

```
./init_db.sh
```

Cette commande initialise la base de données et crée un projet par défaut (projet tutoriel)

```
./upload.sh
```

Cette commande déplace tous les fichiers dans le bon dossier pour que Apache puisse y accéder
