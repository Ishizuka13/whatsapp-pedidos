<?php ?>
<style>
    header {
        background-color: #333;
        color: #fff;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
    }

    header img {
        width: 10%;
    }

    nav ul {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
    }

    nav ul li {
        margin-right: 10px;
    }

    nav ul li a {
        color: #fff;
        text-decoration: none;
    }
</style>
<header>

    <img src="../../public/assets/images/logo.png" alt="Logo">

    <h1><?php echo $title ?? ''; ?></h1>

    <nav>
        <ul>
            <li><a href="cardapio.php">Cardápio</a></li>
            <li><a href="index.php">Pedidos</a></li>
        </ul>
    </nav>
</header>