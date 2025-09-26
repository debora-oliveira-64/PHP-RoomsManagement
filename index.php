<?php
session_start();
include './config/db.php';

$sql = "SELECT * FROM rooms";
$result = $pdo->query($sql);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Salas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            color: #333;
        }
        header {
            background-color: #007bff;
            color: #fff;
            padding: 20px;
            text-align: center;
        }
        header h1 {
            margin: 0;
        }
        nav {
            margin-top: 10px;
        }
        nav a {
            color: #fff;
            text-decoration: none;
            margin: 0 10px;
            font-size: 16px;
        }
        nav a:hover {
            text-decoration: underline;
        }
        main {
            padding: 20px;
        }
        h2 {
            color: #007bff;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        table th, table td {
            padding: 15px;
            text-align: left;
            border: 1px solid #ddd;
        }
        table th {
            background-color: #007bff;
            color: #fff;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        table tr:hover {
            background-color: #f1f1f1;
        }
        footer {
            text-align: center;
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            position: absolute;
            width: 100%;
            bottom: 0;
        }
    </style>
</head>
<body>
<?php include './assets/navbarIndex.php'; ?>
    <header>
        <h1>Bem-vindo ao Sistema de Gestão de Salas</h1>
    </header>

    <main>
        <h2>Lista Pública de Salas</h2>
        <table>
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Nome</th>
                    <th>Descrição</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->rowCount() > 0) {
                    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>
                                <td>" . htmlspecialchars($row['status']) . "</td>
                                <td>" . htmlspecialchars($row['name']) . "</td>
                                <td>" . htmlspecialchars($row['description']) . "</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>Nenhuma sala disponível.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </main>

    <footer>
        <p>&copy; 2024 Gestão de Salas</p>
    </footer>
</body>
</html>