<?php
require_once 'db.php';

echo "API está funcionando!";

// Exemplo: salvar um pedido de teste
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $stmt = $pdo->prepare("INSERT INTO pedidos (numero_cliente, mensagem) VALUES (?, ?)");
    $stmt->execute([$input['numero_cliente'], $input['mensagem']]);
    echo "Pedido salvo com sucesso.";
}
