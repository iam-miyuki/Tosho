# 📚 Application de gestion de prêt de livres

## 1. 💡 Présentation du projet

### 🧭 Contexte  
Ce projet a pour but de développer une application web pour gérer les prêts de livres destinés aux enfants, au sein d'une association japonaise. Les livres sont empruntés par les familles adhérentes, et des parents bénévoles assurent la gestion des prêts et des retours.

### 🎯 Objectif principal  
Faciliter la gestion des prêts et retours de livres ainsi que l’inventaire de la bibliothèque de l’association.

### 🏷️ Nom du projet  
**Tosho** – « Tosho » signifie *livre* ou *bibliothèque* en japonais.

---

## 2. 👥 Utilisateurs cibles

- **Admin** : gestion des familles adhérentes,des livres et des bibliothécaires(CRUD), 
- **Parents bibliothécaires** : peuvent enregistrer les prêts et retours, et gérer l'inventaire des livres.

---

## 3. ⚙️ Fonctionnalités principales

### 3.1 📦 Gestion des prêts

- 📝 Enregistrement d’un prêt (livre emprunté, date, famille emprunteuse)  
- ✅ Enregistrement du retour du livre  

### 3.2 📋 Inventaire

**Objectif :** Permettre aux parents bibliothécaires de confirmer la présence des livres connus lors de l’inventaire physique.

---

#### Description

- Saisie de le code du livre  
- Bouton **« Valider »** pour valider chaque livre présent physiquement  
- Bouton **« Signaler »** pour signaler une anomalie :  
  - l'étiquette déchiré
  - le livre mal rangé 
  - autre

---


### 3.3 🛠️ Gestion des livres (CRUD)

- ➕ **Créer** : Ajouter un nouveau livre dans l'inventaire  
- 👁️ **Lire** : Consulter les détails d’un livre (infos + état d'emprunt)  
- ✏️ **Mettre à jour** : Modifier les informations d’un livre  
- 🗑️ **Supprimer** : Supprimer un livre de l’inventaire

---

### 3.4 🏠 Gestion des familles adhérentes (CRUD)

- ➕ **Créer** : Ajouter une nouvelle famille adhérente  
- 👁️ **Lire** : Consulter les informations d’une famille  
- ✏️ **Mettre à jour** : Modifier les informations d’une famille  
- 🗑️ **Supprimer** : Supprimer une famille de la liste des adhérents  
- Gestion accessible uniquement aux administrateurs

---

##  5. 👉 Contrainte

- 💻 Interface simple, adaptée aux utilisateurs non techniques  
- 📱 Responsive : utilisable sur mobile  
- Multilangue (fr/jap) ->V2?

---

## 7. 🌱 Évolutions futures (V2)

- 📬 Envoi d’e-mails de rappel pour les retours en retard  
- 📌 Réservation des livres
- Planning des parents bibliothécaires


pour lancer la bdd docker:
```bash
docker compose -f docker/docker-compose.yaml -p tosho up --build db app
```
Run image :
```bash
docker run -it --name mysql_db_test -e MYSQL_ROOT_PASSWORD=1234 -e MYSQL_DATABASE=test_db -p 3307:3306 mysql:latest
```

