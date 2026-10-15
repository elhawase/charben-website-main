<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

const SMTP_HOST      = 'smtp.hostinger.com';
const SMTP_PORT      = 465;
const MAIL_USER      = 'contact@charben-industrie.com';
const MAIL_PASS      = '0w!LbN^1/Q';
const MAIL_FROM      = 'contact@charben-industrie.com';
const MAIL_TO        = 'contact@charben-industrie.com';
const MAIL_FROM_NAME = 'Site Charben Industrie';

function respond_error(string $msg): void {
    echo json_encode(['ok' => false, 'error' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

function respond_ok(string $method, ?string $smtpError = null): void {
    $out = ['ok' => true, 'method' => $method];
    if ($smtpError !== null) {
        $out['smtp_error'] = $smtpError;
    }
    echo json_encode($out, JSON_UNESCAPED_UNICODE);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if (strtoupper($method) !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    respond_error('Method not allowed (POST attendu)');
}

// Lecture du corps : JSON d'abord, fallback formulaire URL-encodé.
$data = null;
$raw  = file_get_contents('php://input');
if ($raw !== false && trim($raw) !== '') {
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        $data = $decoded;
    }
}
if ($data === null) {
    $data = $_POST;
}
if (!is_array($data) || $data === []) {
    http_response_code(422);
    respond_error('Corps de requête illisible (JSON attendu)');
}

foreach (['nom', 'email', 'message'] as $k) {
    if (!array_key_exists($k, $data) || trim((string)$data[$k]) === '') {
        http_response_code(422);
        respond_error('Champ requis manquant : ' . $k);
    }
}

$nom      = trim((string)($data['nom'] ?? ''));
$societe  = trim((string)($data['societe'] ?? ''));
$email    = trim((string)($data['email'] ?? ''));
$tel      = trim((string)($data['telephone'] ?? ''));
$pays     = trim((string)($data['pays'] ?? ''));
$sujet    = trim((string)($data['sujet'] ?? ''));
$message  = trim((string)($data['message'] ?? ''));
$produits = (isset($data['produits']) && is_array($data['produits']))
    ? implode(', ', array_map('strval', $data['produits']))
    : trim((string)($data['produits'] ?? ''));

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    respond_error('Adresse e-mail invalide : ' . $email);
}
if (mb_strlen($message, 'UTF-8') > 20000) {
    http_response_code(422);
    respond_error('Message trop long (20000 caractères max)');
}

// --- Nettoyage / encodage des entêtes -------------------------------
function header_safe(string $s): string {
    return preg_replace('/[\r\n:;]+/', ' ', (string)$s);
}

function encode_mime(string $s): string {
    return '=?UTF-8?B?' . base64_encode($s) . '?=';
}

$subject = 'Demande de devis - ' . $nom . ($societe !== '' ? ' (' . $societe . ')' : '');
$subject = header_safe($subject);

$lines   = [];
$lines[] = 'Nom : ' . $nom;
$lines[] = 'Société : ' . ($societe !== '' ? $societe : '-');
$lines[] = 'E-mail : ' . $email;
$lines[] = 'Téléphone : ' . ($tel !== '' ? $tel : '-');
$lines[] = 'Pays : ' . ($pays !== '' ? $pays : '-');
$lines[] = 'Objet : ' . ($sujet !== '' ? $sujet : '-');
$lines[] = "Produits d'intérêt : " . ($produits !== '' ? $produits : '-');
$lines[] = '';
$lines[] = '--- Message ---';
$lines[] = $message;
$body    = implode("\r\n", $lines);

// --- Client SMTP minimaliste -----------------------------------------
$transcript = [];

function smtp_read_line($sock, array &$transcript): string {
    $resp = '';
    do {
        $line = fgets($sock, 512);
        if ($line === false) {
            throw new RuntimeException('Réponse SMTP introuvable (connexion coupée ou timeout)');
        }
        $resp = $line;
        $transcript[] = 'S: ' . rtrim($line, "\r\n");
    } while (isset($line[3]) && $line[3] === '-');

    return $resp;
}

function smtp_send_cmd($sock, string $cmd, int $expected, array &$transcript, bool $log = true): string {
    if ($cmd !== '') {
        fwrite($sock, $cmd . "\r\n");
    }
    $resp = smtp_read_line($sock, $transcript);
    $code = (int)substr($resp, 0, 3);
    if ($code !== $expected) {
        $detail = trim(substr($resp, 4));
        throw new RuntimeException('SMTP a répondu "' . $detail . '" (attendu ' . $expected . ')');
    }

    return $resp;
}

// --- Envoi SMTP (primaire) --------------------------------------------
$smtpError = null;
$smtpTail  = '';
$transcript = [];

set_error_handler(function (int $severity, string $errstr) {
    throw new RuntimeException('Erreur PHP interne : ' . $errstr);
});

$context = stream_context_create([
    'ssl' => [
        'verify_peer'       => true,
        'verify_peer_name'  => true,
        'allow_self_signed' => false,
    ],
]);

$openssl = extension_loaded('openssl') ? 'oui' : 'non';

$sock = @stream_socket_client(
    'ssl://' . SMTP_HOST . ':' . SMTP_PORT,
    $errno,
    $errstr,
    30,
    STREAM_CLIENT_CONNECT,
    $context
);

if ($sock === false) {
    $smtpError = 'Connexion SMTP impossible (' . $errno . ') : ' . $errstr . ' (openssl: ' . $openssl . ')';
} else {
    stream_set_timeout($sock, 30);

    try {
        $resp = smtp_read_line($sock, $transcript);
        $code = (int)substr($resp, 0, 3);
        if ($code !== 220) {
            throw new RuntimeException('Salutation serveur inattendue : ' . trim($resp));
        }

        smtp_send_cmd($sock, 'EHLO charben-industrie.com', 250, $transcript);

        smtp_send_cmd($sock, 'AUTH LOGIN', 334, $transcript);
        smtp_send_cmd($sock, base64_encode(MAIL_USER), 334, $transcript, false);
        smtp_send_cmd($sock, base64_encode(MAIL_PASS), 235, $transcript, false);

        smtp_send_cmd($sock, 'MAIL FROM:<' . MAIL_FROM . '>', 250, $transcript);
        smtp_send_cmd($sock, 'RCPT TO:<' . MAIL_TO . '>', 250, $transcript);
        smtp_send_cmd($sock, 'DATA', 354, $transcript);

        $headers   = [];
        $headers[] = 'From: ' . encode_mime(MAIL_FROM_NAME) . ' <' . MAIL_FROM . '>';
        $headers[] = 'To: ' . 'Contact Charben <' . MAIL_TO . '>';
        $headers[] = 'Reply-To: ' . encode_mime($nom) . ' <' . $email . '>';
        $headers[] = 'Subject: ' . encode_mime($subject);
        $headers[] = 'Date: ' . date('r');
        $headers[] = 'Message-ID: <' . bin2hex(random_bytes(16)) . '@charben-industrie.com>';
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: text/plain; charset=UTF-8';
        $headers[] = 'Content-Transfer-Encoding: base64';
        $headers[] = 'X-Mailer: Charben-Site/1.0';

        $payload = implode("\r\n", $headers) . "\r\n\r\n" . chunk_split(base64_encode($body)) . "\r\n.\r\n";
        fwrite($sock, $payload);

        $resp = smtp_read_line($sock, $transcript);
        $code = (int)substr($resp, 0, 3);
        if ($code !== 250) {
            throw new RuntimeException('Refus du message : ' . trim($resp));
        }

        smtp_send_cmd($sock, 'QUIT', 221, $transcript);
    } catch (Throwable $ex) {
        $smtpError = $ex->getMessage();
        $tail      = implode(' | ', array_slice($transcript, -6));
        $smtpTail  = ($tail !== '') ? ' [SMTP: ' . $tail . ']' : '';
    } finally {
        if (is_resource($sock)) {
            @fwrite($sock, "QUIT\r\n");
            @fclose($sock);
        }
    }
}

restore_error_handler();

if ($smtpError === null) {
    respond_ok('smtp');
}

// --- Repli : mail() natif ---------------------------------------------
$mailResult = false;
$mailError  = null;

if (function_exists('mail')) {
    $mailHeaders = 'From: ' . encode_mime(MAIL_FROM_NAME) . ' <' . MAIL_FROM . ">\r\n"
                 . 'Reply-To: <' . $email . ">\r\n"
                 . 'MIME-Version: 1.0' . "\r\n"
                 . 'Content-Type: text/plain; charset=UTF-8' . "\r\n"
                 . 'Content-Transfer-Encoding: base64';

    $mailResult = @mail(MAIL_TO, encode_mime($subject), chunk_split(base64_encode($body)), $mailHeaders);

    if ($mailResult === false) {
        $le = error_get_last();
        $mailError = ($le !== null && isset($le['message'])) ? $le['message'] : 'mail() a retourné false';
    }
} else {
    $mailError = 'fonction mail() indisponible';
}

if ($mailResult === true) {
    respond_ok('mail', $smtpError . $smtpTail);
}

respond_error('SMTP : ' . $smtpError . $smtpTail . ' | mail() : ' . ($mailError ?? 'retour false'));