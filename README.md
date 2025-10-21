# 📚 Tosho – Application de gestion de prêt de livres

---

## 1. 💡 Présentation du projet

### 🧭 Contexte  
Le projet **Tosho** — qui signifie *« bibliothèque »* ou *« livre »* en japonais — est une application web développée pour moderniser **la gestion des prêts de livres au sein d’une école japonaise associative**.  
Cette école, gérée par des parents bénévoles, propose des cours de japonais aux enfants d’origine japonaise et met à leur disposition une petite bibliothèque afin d’encourager la lecture en langue japonaise.

Jusqu’à présent, la gestion repose sur une application dévéloppé par un ancien parent bénévol il y a plusieurs années : pas d’accès administrateur, difficulté de recherche (notamment en japonais) et interface peu ergonomique.  
**Tosho** a été conçu pour répondre à ces besoins, en offrant une solution moderne, fluide et adaptée aux bénévoles.

---

## 2. 🎯 Objectifs

- ✅ **Faciliter** la gestion des prêts et retours de livres.  
- 📊 **Simplifier** la tenue de l’inventaire de la bibliothèque.  
- 👥 **Donner autonomie** aux bénévoles via une interface claire et intuitive.  
- 🔐 **Sécuriser** l’accès selon les rôles (Admin / Bibliothécaire).  

---

## 3. 👥 Utilisateurs cibles

### 👑 **Administrateurs (parents élus)**
- Gèrent les **familles adhérentes**, les **bibliothécaires** et le **catalogue des livres**.  
- Peuvent planifier les **sessions d’inventaire**, consulter leur avancement, et **mettre à jour les statuts** des livres signalés (perdu, abîmé, mal rangé, etc.).  

### 📘 **Bibliothécaires (parents bénévoles)**
- Enregistrent les **prêts** et **retours**.  
- Participent à l’**inventaire** et signalent les anomalies.  

---

## 4. ⚙️ Fonctionnalités principales

### 📦 Gestion des prêts
- 📝 Enregistrement d’un **prêt** (livre, date, famille).  
- ✅ Enregistrement du **retour** du livre.  

### 📋 Inventaire
- 🔍 Saisie du **code du livre** pour vérifier sa présence.  
- ⚠️ Possibilité de **signaler une anomalie** : livre non trouvé, mal rangé, abîmé, etc.  

### 🛠️ Gestion des livres *(Admin uniquement)*
- ➕ Ajouter un livre  
- 👁️ Consulter les détails (titre, auteur, statut)  
- ✏️ Modifier les informations  
- 🗑️ Supprimer un livre obsolète  

### 🏠 Gestion des familles *(Admin uniquement)*
- ➕ Ajouter une famille adhérente  
- 👁️ Consulter ses informations  
- ✏️ Modifier les données  
- 🗑️ Supprimer un adhérent  

### 👥 Gestion des bibliothécaires *(Admin uniquement)*
- ➕ Créer un compte bibliothécaire  
- 👁️ Voir la liste des comptes  
- ✏️ Modifier ou désactiver un compte  
- 🗑️ Supprimer un compte inactif  

### 📅 Gestion des sessions d’inventaire *(Admin uniquement)*
- 🗓️ Programmer une session  
- 📊 Suivre l’avancement (livres vérifiés/restants)  
- 🔄 Mettre à jour le statut des livres signalés  

---

## 5. 🔐 Connexion & Sécurité

- 🔑 Page de connexion (Admin / Bibliothécaire)  
- 🔁 Mot de passe oublié et réinitialisation  
- 🔒 Changement ou initialisation de mot de passe  
- 🔄 Passage possible entre l’interface Admin et Bibliothécaire (pour les parents élus)  

---

## 6. 🧱 Architecture technique

| Technologie | Usage |
|--------------|--------|
| **Symfony 6.4** | Back-end (framework PHP) |
| **Twig** | Templates front-end |
| **CSS / JavaScript** | Interface utilisateur |
| **MySQL** | Base de données |

**Tosho** est conçu pour être :
- 💻 **Simple d’utilisation** pour les bénévoles non techniques  
- 📱 **Responsive**, utilisable sur mobile et desktop  

---


## 7. 🌱 Évolutions futures (V2)

- 📬 Envoi d’e-mails de rappel pour les retours en retard  
- 📌 Réservation des livres  
- 🌏 Interface multilingue (français / japonais)  
- 🗓️ Planning des parents bibliothécaires  

---

