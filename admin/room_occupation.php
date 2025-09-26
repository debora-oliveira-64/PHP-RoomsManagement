<?php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../public/login.php');
    exit();
}

if (isset($_GET['cancel_id'])) {
    $cancel_id = (int) $_GET['cancel_id'];
    $stmtCancel = $pdo->prepare("DELETE FROM reservations WHERE id = ?");
    $stmtCancel->execute([$cancel_id]);
    header('Location: room_occupation.php');
    exit();
}

$stmtReservations = $pdo->prepare(
    "SELECT r.id, r.room_id, r.user_id, r.reservation_date, r.reservation_time, r.reservation_time_end, r.created_at, ro.name AS room_name, u.name AS user_name
    FROM reservations r
    JOIN rooms ro ON r.room_id = ro.id
    JOIN users u ON r.user_id = u.id
    ORDER BY r.reservation_date, r.reservation_time"
);
$stmtReservations->execute();
$reservations = $stmtReservations->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Reservas</title>
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
            padding: 10px 20px;
            text-align: center;
        }
        main {
            padding: 20px;
        }
        h1 {
            color: #ddd;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        table th, table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        table th {
            background-color: #007bff;
            color: #fff;
        }
        .cancel-btn {
            background-color: #ff4d4d;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .cancel-btn:hover {
            background-color: #e63939;
        }
    </style>
</head>
<body>
<?php include '../assets/navbar.php'; ?>
    <header>
        <h1>Lista de Reservas</h1>
    </header>
    <main>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Sala</th>
                    <th>Utilizador</th>
                    <th>Data</th>
                    <th>Hora Início</th>
                    <th>Hora Fim</th>
                    <th>Data de Criação</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservations as $reservation): ?>
                <tr>
                    <td><?php echo htmlspecialchars($reservation['id']); ?></td>
                    <td><?php echo htmlspecialchars($reservation['room_name']); ?></td>
                    <td><?php echo htmlspecialchars($reservation['user_name']); ?></td>
                    <td><?php echo htmlspecialchars($reservation['reservation_date']); ?></td>
                    <td><?php echo htmlspecialchars($reservation['reservation_time']); ?></td>
                    <td><?php echo htmlspecialchars($reservation['reservation_time_end']); ?></td>
                    <td><?php echo htmlspecialchars($reservation['created_at']); ?></td>
                    <td>
                        <a href="?cancel_id=<?php echo $reservation['id']; ?>" class="cancel-btn">Cancelar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
