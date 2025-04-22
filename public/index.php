<?php
require_once '../db.php';

$stmt = $pdo->query("SELECT * FROM pedidos ORDER BY created_at DESC");
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Pedidos por WhatsApp</title>
  <link rel="stylesheet" href="../style.css">
</head>
<body>
  <div class="container">
    <h1>📦 Pedidos Recebidos pelo WhatsApp</h1>
    <?php if (count($pedidos) === 0): ?>
      <p>Nenhum pedido ainda.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>Cliente</th>
            <th>Mensagem</th>
            <th>Data</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pedidos as $pedido): ?>
            <tr>
              <td><?= htmlspecialchars($pedido['numero_cliente']) ?></td>
              <td><?= nl2br(htmlspecialchars($pedido['mensagem'])) ?></td>
              <td><?= date('d/m/Y H:i', strtotime($pedido['created_at'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</body>
</html>