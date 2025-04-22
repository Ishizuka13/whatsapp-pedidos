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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $entry = $input['entry'][0] ?? null;
    $changes = $entry['changes'][0] ?? null;
    $value = $changes['value'] ?? null;
    $messages = $value['messages'][0] ?? null;

    if ($messages) {
        $from = $messages['from'];
        $text = $messages['text']['body'];

        $stmt = $pdo->prepare("INSERT INTO pedidos (numero_cliente, mensagem) VALUES (?, ?)");
        $stmt->execute([$from, $text]);

        file_put_contents('mensagens.txt', "De $from: $text\n", FILE_APPEND);

        $pedido = ['numero' => $from, 'mensagem' => $text, 'data' => date('Y-m-d H:i:s')];
        $pedidos = file_exists('pedidos.json') ? json_decode(file_get_contents('pedidos.json'), true) : [];
        $pedidos[] = $pedido;
        file_put_contents('pedidos.json', json_encode($pedidos, JSON_PRETTY_PRINT));
    }

    http_response_code(200);
    echo 'OK';
    exit;
}
?>