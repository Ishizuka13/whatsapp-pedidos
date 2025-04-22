<?php
require_once 'db.php';

$verify_token = getenv('META_VERIFY_TOKEN'); // Adicione no painel do Railway

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $mode = $_GET['hub_mode'] ?? '';
    $token = $_GET['hub_verify_token'] ?? '';
    $challenge = $_GET['hub_challenge'] ?? '';

    if ($mode === 'subscribe' && $token === $verify_token) {
        echo $challenge;
    } else {
        http_response_code(403);
        echo 'Token inválido.';
    }
    exit;
}

// Recebendo a mensagem real (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (isset($data['entry'][0]['changes'][0]['value']['messages'][0])) {
        $msg = $data['entry'][0]['changes'][0]['value']['messages'][0];
        $numero = $msg['from'] ?? '';
        $mensagem = $msg['text']['body'] ?? '';

        if ($numero && $mensagem) {
            $stmt = $pdo->prepare("INSERT INTO pedidos (numero_cliente, mensagem) VALUES (:numero, :mensagem)");
            $stmt->execute([
                ':numero' => $numero,
                ':mensagem' => $mensagem
            ]);
        }
    }

    echo "Mensagem recebida";
    exit;
}
?>