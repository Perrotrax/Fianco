<?php
require_once __DIR__ . '/api_common.php';
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/conexion.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Método no permitido.';
    api_json($response);
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) { api_json($response); }

$correo = isset($data['correo']) ? trim($data['correo']) : '';
$credentialId_b64 = isset($data['credentialId']) ? $data['credentialId'] : '';
$clientDataJSON_b64 = isset($data['clientDataJSON']) ? $data['clientDataJSON'] : '';
$authenticatorData_b64 = isset($data['authenticatorData']) ? $data['authenticatorData'] : '';
$signature_b64 = isset($data['signature']) ? $data['signature'] : '';

if (!$correo || !$credentialId_b64 || !$clientDataJSON_b64 || !$authenticatorData_b64 || !$signature_b64) {
    $response['message'] = 'Faltan parámetros.';
    api_json($response);
}

function base64url_decode_str($b) {
    $b = strtr($b, '-_', '+/');
    $pad = strlen($b) % 4;
    if ($pad) $b .= str_repeat('=', 4 - $pad);
    return base64_decode($b);
}

$credRaw = base64url_decode_str($credentialId_b64);
if ($credRaw === false) { api_json($response); }

// Load stored credential
$sql = "SELECT wc.*, u.id_usuario, u.nombre FROM webauthn_credentials wc JOIN usuarios u ON wc.id_usuario = u.id_usuario WHERE u.correo = ? LIMIT 1";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    $response['message'] = 'Error DB';
    $response['detail'] = $conn->error;
    api_json($response);
}
$stmt->bind_param('s', $correo);
$stmt->execute();
$res = $stmt->get_result();
if (!$res) {
    $response['message'] = 'Error al obtener credencial.';
    $response['detail'] = $stmt->error;
    api_json($response);
}
if ($res->num_rows === 0) {
    $response['message'] = 'Credencial no encontrada.';
    api_json($response);
}
$row = $res->fetch_assoc();
$stmt->close();

$storedCredentialId = $row['credential_id'];
$publicKeyJson = $row['public_key'];
$storedSignCount = (int)$row['sign_count'];

$clientDataJSON = base64url_decode_str($clientDataJSON_b64);
$authenticatorData = base64url_decode_str($authenticatorData_b64);
$signature = base64url_decode_str($signature_b64);

function normalizeChallenge($challenge) {
    if (!is_string($challenge)) {
        return null;
    }
    if (strpos($challenge, '%3D') !== false) {
        $challenge = urldecode($challenge);
    }
    $challenge = strtr($challenge, '-_', '+/');
    $pad = strlen($challenge) % 4;
    if ($pad) {
        $challenge .= str_repeat('=', 4 - $pad);
    }
    return $challenge;
}

$expectedChallenge = normalizeChallenge(isset($_SESSION['webauthn_challenge']) ? $_SESSION['webauthn_challenge'] : null);
if (!$expectedChallenge) {
    $response['message'] = 'No challenge en sesión.';
    api_json($response);
}

// Simplified verification: check that a credential with the provided id exists for the user
// and that a challenge was previously generated. This is NOT a cryptographic verification
// but provides a compatibility layer while keeping the WebAuthn UX.

$clientData = json_decode($clientDataJSON, true);
if (!$clientData) {
    $response['message'] = 'clientData inválido.';
    api_json($response);
}

$gotChallenge = normalizeChallenge(isset($clientData['challenge']) ? $clientData['challenge'] : null);
$expectedChallengeRaw = $expectedChallenge ? base64url_decode_str($expectedChallenge) : false;
$gotChallengeRaw = $gotChallenge ? base64url_decode_str($gotChallenge) : false;
if ($expectedChallengeRaw === false || $gotChallengeRaw === false || !hash_equals($expectedChallengeRaw, $gotChallengeRaw)) {
    $response['message'] = 'Challenge no coincide.';
    api_json($response);
}

// Check credential id matches stored credential (we stored it base64url)
$credB64 = $credentialId_b64;
    if ($credB64 !== $storedCredentialId) {
    $response['message'] = 'Credencial no coincide con la registrada.';
    api_json($response);
}

// If we have a stored public key JWK, try to cryptographically verify the signature
if ($publicKeyJson && trim($publicKeyJson) !== '') {
    $jwk = json_decode($publicKeyJson, true);
    if ($jwk && isset($jwk['kty']) && $jwk['kty'] === 'EC') {
        // build verification data
        $hashClient = hash('sha256', $clientDataJSON, true);
        $verificationData = $authenticatorData . $hashClient;

        // convert JWK EC to PEM
        function base64url_decode($input) {
            $remainder = strlen($input) % 4;
            if ($remainder) $input .= str_repeat('=', 4 - $remainder);
            $input = strtr($input, '-_', '+/');
            return base64_decode($input);
        }

        function jwk_ec_pem($jwk) {
            $x = base64url_decode($jwk['x']);
            $y = base64url_decode($jwk['y']);
            $pubkey = "\x04" . $x . $y;
            $der = "\x30\x59\x30\x13\x06\x07\x2A\x86\x48\xCE\x3D\x02\x01\x06\x08\x2A\x86\x48\xCE\x3D\x03\x01\x07\x03\x42\x00" . $pubkey;
            $pem = "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($der), 64) . "-----END PUBLIC KEY-----\n";
            return $pem;
        }

        $pem = jwk_ec_pem($jwk);
        $ok = openssl_verify($verificationData, $signature, $pem, OPENSSL_ALGO_SHA256);
        if ($ok === 1) {
            $_SESSION['id_usuario'] = $row['id_usuario'];
            $_SESSION['nombre'] = $row['nombre'];
            
            unset($_SESSION['temp_id_usuario']);
            unset($_SESSION['temp_nombre']);
            unset($_SESSION['temp_foto']);
            unset($_SESSION['temp_correo']);

            $response['success'] = true;
            $response['message'] = 'Autenticación WebAuthn exitosa.';
            $stmt = $conn->prepare("UPDATE webauthn_credentials SET sign_count = sign_count + 1 WHERE id_cred = ?");
            if ($stmt) { $stmt->bind_param('i', $row['id_cred']); $stmt->execute(); $stmt->close(); }
        } else {
            $response['message'] = 'Firma inválida.';
        }
    } else {
        $response['message'] = 'Public key inválida.';
    }
} else {
    // No public key stored, fallback to simple success (compatibility)
    $_SESSION['id_usuario'] = $row['id_usuario'];
    $_SESSION['nombre'] = $row['nombre'];
    
    unset($_SESSION['temp_id_usuario']);
    unset($_SESSION['temp_nombre']);
    unset($_SESSION['temp_foto']);
    unset($_SESSION['temp_correo']);

    $response['success'] = true;
    $response['message'] = 'Autenticación WebAuthn exitosa (compatibility no-public-key).';
    $stmt = $conn->prepare("UPDATE webauthn_credentials SET sign_count = sign_count + 1 WHERE id_cred = ?");
    if ($stmt) { $stmt->bind_param('i', $row['id_cred']); $stmt->execute(); $stmt->close(); }
}

api_json($response);

?>
