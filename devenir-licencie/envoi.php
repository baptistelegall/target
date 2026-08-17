<?php
/**
 * Ma Target — traitement du formulaire « Devenir licencié »
 * À déposer dans /vannes/devenir-licencie/envoi.php
 *
 * Fonctionnement : reçoit le POST, valide, envoie le mail, puis redirige vers
 * index.html?envoi=ok (ou ?envoi=erreur). La page lit ce paramètre en JS et
 * affiche le bon message.
 */

/* ===================== CONFIGURATION ===================== */

// Destinataire(s) des candidatures. Plusieurs adresses : séparer par une virgule.
$DESTINATAIRE = 'matargetbrest@gmail.com';

// Expéditeur technique. OBLIGATOIRE chez IONOS : doit être une adresse du
// domaine ma-target.fr. Depuis janvier 2024, IONOS refuse purement et
// simplement les envois dont le From: est en @gmail.com ou tout autre domaine
// extérieur au contrat ("Sender address is not allowed").
$EXPEDITEUR      = 'site@ma-target.fr';
$EXPEDITEUR_NOM  = 'Site Ma Target';

// Page du formulaire (pour la redirection).
$PAGE_RETOUR = 'index.html';

// Accusé de réception automatique au candidat.
$ACCUSE_RECEPTION = true;

// Copie de secours des candidatures sur le serveur (laisser vide pour désactiver).
// Le dossier doit être inscriptible et, idéalement, hors racine web.
$FICHIER_LOG = __DIR__ . '/candidatures.csv';

// Délai minimum entre l'affichage de la page et l'envoi (secondes). Anti-robot.
$DELAI_MINIMUM = 3;

/* ===================== OUTILS ===================== */

/** Redirige vers la page du formulaire avec un statut, puis stoppe le script. */
function retour($statut, $page) {
    header('Location: ' . $page . '?envoi=' . $statut, true, 303);
    exit;
}

/** Récupère un champ POST nettoyé. */
function champ($nom) {
    if (!isset($_POST[$nom])) return '';
    $v = $_POST[$nom];
    if (!is_string($v)) return '';
    $v = str_replace(array("\0"), '', $v);
    return trim($v);
}

/** Supprime tout ce qui permettrait une injection d'en-tête mail. */
function propre_entete($v) {
    return trim(str_replace(array("\r", "\n", "%0a", "%0d"), ' ', $v));
}

/* ===================== GARDE-FOUS ===================== */

// 1. Uniquement en POST.
if (!isset($_SERVER['REQUEST_METHOD']) || strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST') {
    header('Location: ' . $PAGE_RETOUR, true, 303);
    exit;
}

// 2. Pot de miel : rempli => robot. On simule un succès pour ne rien lui apprendre.
if (champ('site_web') !== '') {
    retour('ok', $PAGE_RETOUR);
}

// 3. Formulaire soumis trop vite => robot.
$ts = (int) champ('ts');
if ($ts > 0 && (time() - $ts) < $DELAI_MINIMUM) {
    retour('ok', $PAGE_RETOUR);
}

/* ===================== RÉCUPÉRATION ===================== */

$donnees = array(
    'prenom'       => champ('prenom'),
    'nom'          => champ('nom'),
    'email'        => champ('email'),
    'telephone'    => champ('telephone'),
    'ville'        => champ('ville'),
    'departement'  => champ('departement'),
    'horizon'      => champ('horizon'),
    'local'        => champ('local'),
    'situation'    => champ('situation'),
    'experience'   => champ('experience'),
    'apport'       => champ('apport'),
    'message'      => champ('message'),
    'source'       => champ('source'),
);
$consentement = isset($_POST['consentement']);

/* ===================== VALIDATION SERVEUR ===================== */
/* Ne jamais se fier à la seule validation JavaScript. */

$erreurs = array();

foreach (array('prenom','nom','email','telephone','ville','departement',
               'horizon','local','situation','apport','message') as $obligatoire) {
    if ($donnees[$obligatoire] === '') {
        $erreurs[] = $obligatoire;
    }
}

if ($donnees['email'] !== '' && !filter_var($donnees['email'], FILTER_VALIDATE_EMAIL)) {
    $erreurs[] = 'email';
}
if (strlen(preg_replace('/[^0-9]/', '', $donnees['telephone'])) < 9) {
    $erreurs[] = 'telephone';
}
if (!preg_match('/^(0[1-9]|[1-8][0-9]|9[0-5]|2[abAB]|97[1-6])$/', $donnees['departement'])) {
    $erreurs[] = 'departement';
}
if (!in_array($donnees['local'], array('oui', 'recherche', 'non'), true)) {
    $erreurs[] = 'local';
}
if (mb_strlen($donnees['message']) < 40) {
    $erreurs[] = 'message';
}
if (mb_strlen($donnees['message']) > 5000) {
    $erreurs[] = 'message';
}
if (!$consentement) {
    $erreurs[] = 'consentement';
}

if (count($erreurs) > 0) {
    retour('erreur', $PAGE_RETOUR);
}

/* ===================== COMPOSITION DU MAIL ===================== */

$libelleLocal = array(
    'oui'        => 'Oui, local identifié',
    'recherche'  => 'En cours de recherche',
    'non'        => 'Pas encore',
);

$corps  = "Nouvelle candidature de licence reçue depuis le site.\r\n";
$corps .= "----------------------------------------------------------\r\n\r\n";
$corps .= "CANDIDAT\r\n";
$corps .= "  Nom            : " . $donnees['prenom'] . " " . $donnees['nom'] . "\r\n";
$corps .= "  Email          : " . $donnees['email'] . "\r\n";
$corps .= "  Téléphone      : " . $donnees['telephone'] . "\r\n\r\n";
$corps .= "PROJET\r\n";
$corps .= "  Ville          : " . $donnees['ville'] . "\r\n";
$corps .= "  Département    : " . $donnees['departement'] . "\r\n";
$corps .= "  Ouverture      : " . $donnees['horizon'] . "\r\n";
$corps .= "  Local          : " . (isset($libelleLocal[$donnees['local']]) ? $libelleLocal[$donnees['local']] : $donnees['local']) . "\r\n\r\n";
$corps .= "PROFIL\r\n";
$corps .= "  Situation      : " . $donnees['situation'] . "\r\n";
$corps .= "  Expérience CHR : " . ($donnees['experience'] !== '' ? $donnees['experience'] : 'non renseignée') . "\r\n";
$corps .= "  Apport         : " . $donnees['apport'] . "\r\n\r\n";
$corps .= "MESSAGE\r\n";
$corps .= "  " . str_replace("\n", "\n  ", $donnees['message']) . "\r\n\r\n";
$corps .= "DIVERS\r\n";
$corps .= "  Origine        : " . ($donnees['source'] !== '' ? $donnees['source'] : 'non renseignée') . "\r\n";
$corps .= "  Consentement   : accordé le " . date('d/m/Y à H:i') . "\r\n";
$corps .= "  Adresse IP     : " . (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'inconnue') . "\r\n";

$sujet = 'Candidature licence - ' . $donnees['ville']
       . ' (' . $donnees['departement'] . ') - '
       . $donnees['prenom'] . ' ' . $donnees['nom'];
$sujet = propre_entete($sujet);

$entetes  = 'From: ' . propre_entete($EXPEDITEUR_NOM) . ' <' . $EXPEDITEUR . '>' . "\r\n";
$entetes .= 'Reply-To: ' . propre_entete($donnees['prenom'] . ' ' . $donnees['nom'])
          . ' <' . propre_entete($donnees['email']) . '>' . "\r\n";
$entetes .= 'Content-Type: text/plain; charset=UTF-8' . "\r\n";
$entetes .= 'Content-Transfer-Encoding: 8bit' . "\r\n";
$entetes .= 'X-Mailer: PHP/' . phpversion();

/* IONOS : ne pas passer de 5e paramètre à mail(), il est bloqué sur le
   mutualisé. L'enveloppe est imposée par le serveur. */
$envoye = @mail(
    $DESTINATAIRE,
    '=?UTF-8?B?' . base64_encode($sujet) . '?=',
    $corps,
    $entetes
);

/* ===================== COPIE SUR LE SERVEUR ===================== */
/* Filet de sécurité si l'envoi de mail échoue silencieusement. */

if ($FICHIER_LOG !== '') {
    $nouveau = !file_exists($FICHIER_LOG);
    $fp = @fopen($FICHIER_LOG, 'a');
    if ($fp) {
        if ($nouveau) {
            fwrite($fp, "\xEF\xBB\xBF"); // BOM UTF-8 pour Excel
            fputcsv($fp, array('date','prenom','nom','email','telephone','ville',
                               'departement','horizon','local','situation',
                               'experience','apport','source','message','mail_envoye'), ';');
        }
        fputcsv($fp, array(
            date('Y-m-d H:i:s'),
            $donnees['prenom'], $donnees['nom'], $donnees['email'], $donnees['telephone'],
            $donnees['ville'], $donnees['departement'], $donnees['horizon'], $donnees['local'],
            $donnees['situation'], $donnees['experience'], $donnees['apport'], $donnees['source'],
            $donnees['message'], $envoye ? 'oui' : 'non',
        ), ';');
        fclose($fp);
        @chmod($FICHIER_LOG, 0640);
    }
}

/* ===================== ACCUSÉ DE RÉCEPTION ===================== */

if ($ACCUSE_RECEPTION && $envoye) {
    $corpsAR  = "Bonjour " . $donnees['prenom'] . ",\r\n\r\n";
    $corpsAR .= "Nous avons bien reçu votre candidature pour l'ouverture d'un Ma Target sous licence à "
              . $donnees['ville'] . ".\r\n\r\n";
    $corpsAR .= "Un membre de l'équipe développement revient vers vous sous 10 jours ouvrés "
              . "au numéro que vous nous avez indiqué.\r\n\r\n";
    $corpsAR .= "En attendant, le meilleur moyen de comprendre le concept reste de venir jouer.\r\n\r\n";
    $corpsAR .= "À bientôt,\r\n";
    $corpsAR .= "L'équipe Ma Target\r\n";

    $entetesAR  = 'From: ' . propre_entete($EXPEDITEUR_NOM) . ' <' . $EXPEDITEUR . '>' . "\r\n";
    $entetesAR .= 'Content-Type: text/plain; charset=UTF-8' . "\r\n";
    $entetesAR .= 'Content-Transfer-Encoding: 8bit';

    @mail(
        propre_entete($donnees['email']),
        '=?UTF-8?B?' . base64_encode('Votre candidature Ma Target') . '?=',
        $corpsAR,
        $entetesAR
    );
}

/* ===================== REDIRECTION ===================== */

retour($envoye ? 'ok' : 'erreur', $PAGE_RETOUR);