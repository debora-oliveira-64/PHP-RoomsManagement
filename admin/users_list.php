<?php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../public/login.php');
    exit();
}

$stmt = $pdo->prepare("SELECT id, name, email, role, created_at, profile_image FROM users ORDER BY name ASC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Utilizadores</title>
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
            color: white;
            padding: 15px;
            text-align: center;
        }

        main {
            max-width: 1000px;
            margin: 30px auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #ddd;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th,
        td {
            text-align: center;
            padding: 10px;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        img {
            border-radius: 50%;
        }

        @media (max-width: 768px) {

            table,
            thead,
            tbody,
            th,
            td,
            tr {
                display: block;
            }

            thead tr {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }

            tr {
                margin-bottom: 15px;
            }

            td {
                border: none;
                position: relative;
                padding-left: 50%;
            }

            td:before {
                content: attr(data-label);
                position: absolute;
                left: 0;
                width: 45%;
                padding-left: 10px;
                font-weight: bold;
                text-align: left;
            }
        }
    </style>
</head>

<body>
<?php include '../assets/navbar.php'; ?>
    <header>
        <h1>Lista de Utilizadores</h1>
    </header>
    <main>
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Função</th>
                    <th>Data de Criação</th>
                    <th>Imagem de Perfil</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($users) > 0): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td data-label="Nome"><?php echo htmlspecialchars($user['name']); ?></td>
                            <td data-label="Email"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td data-label="Função"><?php echo htmlspecialchars($user['role']); ?></td>
                            <td data-label="Data de Criação"><?php echo htmlspecialchars($user['created_at']); ?></td>
                            <td data-label="Imagem de Perfil">
                                <?php if ($user['profile_image']): ?>
                                    <img src="../assets/show_image.php?id=<?php echo htmlspecialchars($user['id']); ?>" alt="Imagem de perfil" width="50" height="50">
                                <?php else: ?>
                                    <img src="../public/images/placeholder.png" alt="Sem imagem de perfil">
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">Nenhum utilizador encontrado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>

</html>