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
    try {
        $input = file_get_contents('php://input');
        error_log("Entrada recebida: " . $input); // Log para debug

        $data = json_decode($input, true);

        if (isset($data['entry'][0]['changes'][0]['value']['messages'][0])) {
            $msg = $data['entry'][0]['changes'][0]['value']['messages'][0];
            $numero = $msg['from'] ?? '';
            $mensagem = $msg['text']['body'] ?? '';

            error_log("Número: $numero");
            error_log("Mensagem: $mensagem");

            if ($numero && $mensagem) {
                $stmt = $pdo->prepare("INSERT INTO pedidos (numero_cliente, mensagem) VALUES (:numero, :mensagem)");
                $stmt->execute([
                    ':numero' => $numero,
                    ':mensagem' => $mensagem
                ]);
                error_log("Mensagem salva com sucesso!");

                $cardapio = $pdo->query("SELECT item, preco FROM cardapio")->fetchAll(PDO::FETCH_ASSOC);

                enviarMensagemWhatsApp($numero, "Olá! Este é o nosso cardápio: \n\n" . implode("\n", array_map(function ($item) {
                    return $item['item'] . " - R$ " . $item['preco'];
                }, $cardapio)));

            } else {
                error_log("Dados incompletos: número ou mensagem vazios.");
            }
        } else {
            error_log("Estrutura de mensagem não encontrada.");
        }

        echo "Mensagem recebida";
    } catch (Exception $e) {
        error_log("Erro ao processar webhook: " . $e->getMessage());
        http_response_code(500);
        echo "Erro interno ao processar a mensagem";
    }
    exit;
}

function enviarMensagemWhatsApp($numero, $mensagemTexto) {
    $token = getenv('META_VERIFY_TOKEN');
    $phoneId = getenv('PHONE_NUMBER_ID');
    
    $url = "https://graph.facebook.com/v19.0/$phoneId/messages";

    $dados = [
        'messaging_product' => 'whatsapp',
        'to' => $numero,
        'type' => 'text',
        'text' => ['body' => $mensagemTexto]
    ];

    $headers = [
        "Authorization: Bearer $token",
        "Content-Type: application/json"
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $resposta = curl_exec($ch);
    $erro = curl_error($ch);
    curl_close($ch);

    if ($erro) {
        error_log("Erro ao enviar mensagem: " . $erro);
    } else {
        error_log("Mensagem enviada: " . $resposta);
    }
}