<?php
// Conectando ao banco de dados
 require_once '../db.php';
// DELETE em massa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['method']) && $_POST['method'] === 'DELETE_MASS') {
    try {
        $ids = json_decode($_POST['ids_json'], true);
        if (is_array($ids) && count($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("DELETE FROM cardapio WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            echo "<p>" . count($ids) . " item(ns) deletado(s) com sucesso!</p>";
        } else {
            echo "<p>Nenhum item selecionado.</p>";
        }
    } catch (Exception $e) {
        echo "<p>Erro ao deletar em massa: " . $e->getMessage() . "</p>";
    }
}


// Inserir novo item no cardápio
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item']) && isset($_POST['preco']) && isset($_POST['quantidade'])) {
    try {
        $item = $_POST['item'];
        $preco = $_POST['preco'];
        $quantidade = $_POST['quantidade'];

        $verify_item_exist = $pdo->prepare("
            SELECT * FROM cardapio 
            WHERE LOWER(TRIM(item)) = LOWER(TRIM(:item))
        ");
        $verify_item_exist->execute([
            ':item' => trim($item)
        ]);
        if ($verify_item_exist->rowCount() > 0) {
            echo "<p>Item ja cadastrado!</p>";
            header("Location: cardapio.php?erro=2");
        } else 
        {
            $stmt = $pdo->prepare("INSERT INTO cardapio (item, preco, quantidade) VALUES (:item, :preco, :quantidade)");
            $stmt->execute([
                ':item' => $item,
                ':preco' => $preco,
                ':quantidade' => $quantidade
            ]);
    
            header("Location: cardapio.php?sucesso=1");

        }


    } catch (Exception $e) {
        print_r($e);
        echo "<p>Erro ao adicionar o item: " . $e->getMessage() . "</p>";
    }
}

// Buscar itens do cardápio
$stmt = $pdo->query("SELECT * FROM cardapio ORDER BY id");
$cardapio = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cardápio</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        h2 {
            color: #333;
        }
        form {
            margin-top: 20px;
        }
        input, button {
            padding: 10px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            margin: 20px auto;
            background-color: #f5f5f5;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        /* .container form label {
            margin-left: -80px;
        } */

        .form-input {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin: 10px auto;
        }
    </style>
</head>
<body>

    <?php
    $title = 'Cardápio';
    require_once '../components/header/index.php'; ?>


    <h2>Cardápio</h2>


    
    <div class="container">
        <h2>Adicionar Novo Item</h2>

        <?php if (isset($_GET['sucesso'])): ?>
            <p style="color: green;">Item adicionado com sucesso!</p>
        <?php endif; ?>

        <?php if (isset($_GET['erro']) && $_GET['erro'] == 2): ?>
            <p style="color: green;">Item ja cadastrado!</p>
        <?php endif; ?>

        <form action="cardapio.php" method="POST">
            <div>
                <label for="item">Nome do Item:</label><br/>
                <input class="form-input" type="text" id="item" name="item" required><br>
            </div>
            <div>
                <label for="preco">Preço (R$):</label><br/>
                <input class="form-input" type="text" id="preco" name="preco" required><br>
            </div>
            <div>
                <label for="quantidade">Quantidade:</label><br/>
                <input class="form-input" type="number" id="quantidade" name="quantidade" required><br>
            </div>
            <button type="submit">Adicionar Item</button>
        </form>
    </div>

        <input type="hidden" name="method" value="DELETE_MASS">
        <table>
            <thead>
                <tr>
                    <th><input type="checkbox" id="select-all" /></th>
                    <th>Item</th>
                    <th>Preço</th>
                    <th>Quantidade</th>
                    <th>Data de Criação</th>
                    <th>Última Atualização</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cardapio as $item): ?>
                    <tr>
                        <td><input type="checkbox" class="checkbox-item" value="<?= $item['id'] ?>"></td>
                        <td><?= htmlspecialchars($item['item']) ?></td>
                        <td>R$ <?= number_format($item['preco'], 2, ',', '.') ?></td>
                        <td><?= $item['quantidade'] ?></td>
                        <td><?= $item['created_at'] ?></td>
                        <td><?= $item['updated_at'] ?></td>
                        <td>
                            <form action="cardapio.php" method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                <input type="hidden" name="method" value="DELETE">
                                <button type="submit" style="background-color:red; border-radius:2px; font-weight:bold;">X</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>


        <form id="form-deletar-em-massa" action="cardapio.php" method="POST">
            <input type="hidden" name="method" value="DELETE_MASS">
            <input type="hidden" name="ids_json" id="ids-json">
            <button type="submit" style="margin: 20px 0; background-color: #c00; color: white; padding: 10px; border: none; border-radius: 5px;">
                Deletar Selecionados
            </button>
        </form>

        <script>
            // Marcar/desmarcar todos
            document.getElementById('select-all').addEventListener('change', function () {
                document.querySelectorAll('.checkbox-item').forEach(cb => cb.checked = this.checked);
            });

            // Quando clicar no botão de deletar em massa
            document.getElementById('form-deletar-em-massa').addEventListener('submit', function (e) {
                const checkboxes = document.querySelectorAll('.checkbox-item:checked');
                const ids = Array.from(checkboxes).map(cb => cb.value);

                if (ids.length === 0) {
                    alert("Selecione pelo menos um item para deletar.");
                    e.preventDefault();
                    return;
                }

                document.getElementById('ids-json').value = JSON.stringify(ids);
            });
        </script>

</body>
</html>
