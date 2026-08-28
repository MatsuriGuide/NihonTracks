# Roadmap — évolutions envisagées

Idées notées pour plus tard, pas encore développées.

## Newsletter (envoi quotidien filtré)

L'utilisateur choisit des filtres (artiste(s) et/ou tags — réutilise le
mécanisme déjà en place sur `/videos?artist_id=...&tag_ids[]=...`) et reçoit
une fois par jour un email récapitulant les nouvelles sorties qui
correspondent.

Éléments à construire :
- Une table de préférences d'abonnement (utilisateur, filtres enregistrés,
  fréquence — même si seul "quotidien" est prévu au départ, autant prévoir
  le champ pour plus tard)
- Écran "Mes alertes" côté utilisateur pour créer/gérer ses filtres
  enregistrés (proche de l'UI de filtrage de `/videos`, avec un bouton
  "M'alerter sur cette recherche")
- Un script CRON quotidien qui, pour chaque abonnement actif, cherche les
  vidéos publiées dans les dernières 24h correspondant aux filtres, et
  envoie un email récapitulatif s'il y a du nouveau (pas d'email si rien de
  neuf, pour éviter le spam)
- Un mécanisme d'envoi d'email : à déterminer selon ce que permet
  l'hébergement Hostinger (PHP `mail()` basique, ou SMTP via un service
  tiers si la délivrabilité de `mail()` s'avère mauvaise — assez courant sur
  mutualisé)
- Lien de désabonnement dans chaque email (obligatoire légalement, RGPD/CAN-SPAM)

## Classement / Top des sorties

Une sorte de "top 50" basé sur la popularité, dans l'esprit des classements
musicaux traditionnels. Nécessite de suivre l'évolution des vues dans le
temps, pas juste un instantané.

Éléments à construire :
- Récupération périodique de `statistics.viewCount` via l'API YouTube
  (`videos.list`) — actuellement seule la chaîne (abonnés) est suivie dans
  le temps via le scan, pas les vues par vidéo
- Une table d'historique (vidéo, date, nombre de vues) pour pouvoir calculer
  une évolution, pas seulement un total brut
- **Mode de calcul à définir** — plusieurs approches possibles, à trancher
  plus tard :
  - Classement par vues totales (simple, mais favorise toujours les plus
    anciennes vidéos)
  - Classement par croissance sur une période glissante (ex. vues gagnées
    sur 7 jours) — plus proche de l'esprit "hit du moment", mais plus
    complexe et plus coûteux en appels API si fait sur beaucoup de vidéos
  - Une pondération mixte (ex. vues récentes comptent plus que les
    anciennes)
- Coût API à surveiller : contrairement au scan de chaînes (1 appel par
  chaîne), un suivi de vues par vidéo individuelle coûte 1 unité par lot de
  50 vidéos (via `videos.list` avec plusieurs IDs) — gérable, mais à
  dimensionner selon le volume de vidéos une fois le catalogue plus grand

## Fait

- [x] Nombre de vidéos publiées et d'artistes approuvés affiché en bas de
  page (footer), sur tout le site
