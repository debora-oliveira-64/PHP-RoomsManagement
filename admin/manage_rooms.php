<?php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../public/login.php');
    exit();
}

if (isset($_GET['delete'])) {
    $room_id = htmlspecialchars($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
    $stmt->execute([$room_id]);
    header('Location: manage_rooms.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $description = htmlspecialchars($_POST['description']);
    $status = htmlspecialchars($_POST['status']);
    $id = htmlspecialchars($_POST['id']);

    if (!preg_match('/^[a-zA-Z0-9 ]{1,30}$/', $name)) {
        $error = "O nome da sala deve conter apenas letras, números e ter no máximo 30 caracteres.";
    } elseif (!preg_match('/^[a-zA-Z0-9 ]{1,100}$/', $description)) {
        $error = "A descrição deve conter apenas letras, números e ter no máximo 100 caracteres.";
    } else {
    
        if (!empty($id)) {
            $stmt = $pdo->prepare("UPDATE rooms SET name = ?, status = ?, description = ? WHERE id = ?");
            $stmt->execute([$name, $status, $description, $id]);
        } else { 
            $stmt = $pdo->prepare("INSERT INTO rooms (name, status, description) VALUES (?, ?, ?)");
            $stmt->execute([$name, $status, $description]);
        }
    
        header('Location: manage_rooms.php');
        exit();
    }
    
}

$room = [];
if (isset($_GET['edit'])) {
    $room_id = htmlspecialchars($_GET['edit']);
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->execute([$room_id]);
    $room = $stmt->fetch(PDO::FETCH_ASSOC);
}

$rooms = $pdo->query("SELECT * FROM rooms")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerir Salas</title>
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
            padding: 15px;
            text-align: center;
        }

        main {
            max-width: 800px;
            margin: 30px auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #007bff;
        }

        form {
            margin: 20px 0;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-size: 14px;
            font-weight: bold;
        }

        input[type="text"], textarea, select {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 100%;
        }

        textarea {
            resize: vertical;
        }

        button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            width: 150px;
            align-self: center;
        }

        button:hover {
            background-color: #0056b3;
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
            text-align: center;
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

        .actions a {
            text-decoration: none;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
        }

        .actions a.edit {
            background-color: #28a745;
        }

        .actions a.edit:hover {
            background-color: #218838;
        }

        .actions a.delete {
            background-color: #dc3545;
        }

        .actions a.delete:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
<?php include '../assets/navbar.php'; ?>
    <header>
        <h1>Gerir Salas</h1>
    </header>
    <main>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <h2>Cadastrar ou Alterar Sala</h2>
        <form method="POST">
            <label for="name">Nome da Sala:</label>
            <input type="text" name="name" required value="<?php echo isset($room['name']) ? htmlspecialchars($room['name']) : ''; ?>">

            <label for="status">Status:</label>
            <select name="status" required>
                <option value="available" <?php echo (isset($room['status']) && $room['status'] == 'available') ? 'selected' : ''; ?>>Disponível</option>
                <option value="unavailable" <?php echo (isset($room['status']) && $room['status'] == 'unavailable') ? 'selected' : ''; ?>>Indisponível</option>
                <option value="soon" <?php echo (isset($room['status']) && $room['status'] == 'soon') ? 'selected' : ''; ?>>Em Breve</option>
            </select>

            <label for="description">Descrição:</label>
            <textarea name="description" rows="4"><?php echo isset($room['description']) ? htmlspecialchars($room['description']) : ''; ?></textarea>

            <input type="hidden" name="id" value="<?php echo isset($room['id']) ? htmlspecialchars($room['id']) : ''; ?>">
            <button type="submit">Salvar</button>
        </form>

        <h2>Salas Existentes</h2>
        <table>
            <tr>
                <th>Nome da Sala</th>
                <th>Status</th>
                <th>Descrição</th>
                <th>Ação</th>
            </tr>
            <?php foreach ($rooms as $room): ?>
            <tr>
                <td><?php echo htmlspecialchars($room['name']); ?></td>
                <td><?php echo htmlspecialchars($room['status']); ?></td>
                <td><?php echo htmlspecialchars($room['description']); ?></td>
                <td class="actions">
                    <a href="manage_rooms.php?edit=<?php echo $room['id']; ?>" class="edit">Editar</a>
                    <a href="manage_rooms.php?delete=<?php echo $room['id']; ?>" class="delete">Excluir</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </main>
</body>
</html>