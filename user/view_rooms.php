<?php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../public/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['cancel_reservation'])) {
    $reservation_id = $_POST['reservation_id'];

    $stmt = $pdo->prepare("DELETE FROM reservations WHERE id = ? AND user_id = ?");
    $stmt->execute([$reservation_id, $user_id]);

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$stmt = $pdo->prepare("
    SELECT 
        reservations.id, 
        reservations.reservation_date, 
        reservations.reservation_time, 
        reservations.reservation_time_end, 
        rooms.name AS room_name
    FROM reservations
    INNER JOIN rooms ON reservations.room_id = rooms.id
    WHERE reservations.user_id = ? 
      AND (reservations.reservation_date < CURDATE() 
           OR (reservations.reservation_date = CURDATE() AND reservations.reservation_time <= CURTIME()))
    ORDER BY reservations.reservation_date DESC, reservations.reservation_time DESC
");
$stmt->execute([$user_id]);
$past_reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT 
        reservations.id, 
        reservations.reservation_date, 
        reservations.reservation_time, 
        reservations.reservation_time_end, 
        rooms.name AS room_name
    FROM reservations
    INNER JOIN rooms ON reservations.room_id = rooms.id
    WHERE reservations.user_id = ? 
      AND (reservations.reservation_date > CURDATE() 
           OR (reservations.reservation_date = CURDATE() AND reservations.reservation_time > CURTIME()))
    ORDER BY reservations.reservation_date ASC, reservations.reservation_time ASC
");
$stmt->execute([$user_id]);
$future_reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Reservas</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
            color: #333;
        }

        header {
            background-color: #0056b3;
            color: white;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        h1 {
            margin: 0;
            font-size: 24px;
        }

        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 15px;
            text-align: center;
            border: 1px solid #ddd;
        }

        th {
            background-color: #0056b3;
            color: white;
            text-transform: uppercase;
        }

        td {
            background-color: #f9f9f9;
        }

        tr:nth-child(even) td {
            background-color: #f1f5f9;
        }

        tr:hover td {
            background-color: #eaf4ff;
        }

        .no-reservations {
            text-align: center;
            color: #777;
            font-style: italic;
            margin: 20px 0;
        }

        .btn-cancel {
            display: inline-block;
            padding: 8px 15px;
            background-color: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }

        .btn-cancel:hover {
            background-color: #c82333;
        }

        footer {
            text-align: center;
            margin-top: 20px;
            padding: 10px 0;
            font-size: 14px;
            color: #555;
        }
    </style>
</head>
<body>
<?php include '../assets/navbar.php'; ?>
    <header>
        <h1>Minhas Reservas</h1>
    </header>

    <div class="container">
        <h2>Histórico de Reservas</h2>
        <?php if (!empty($past_reservations)): ?>
            <table>
                <tr>
                    <th>Data</th>
                    <th>Hora Inicial</th>
                    <th>Hora Final</th>
                    <th>Sala</th>
                </tr>
                <?php foreach ($past_reservations as $reservation): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($reservation['reservation_date']); ?></td>
                        <td><?php echo htmlspecialchars($reservation['reservation_time']); ?></td>
                        <td><?php echo htmlspecialchars($reservation['reservation_time_end']); ?></td>
                        <td><?php echo htmlspecialchars($reservation['room_name']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p class="no-reservations">Você ainda não tem reservas passadas.</p>
        <?php endif; ?>

        <h2>Reservas Futuras</h2>
        <?php if (!empty($future_reservations)): ?>
            <table>
                <tr>
                    <th>Data</th>
                    <th>Hora Inicial</th>
                    <th>Hora Final</th>
                    <th>Sala</th>
                    <th>Ação</th>
                </tr>
                <?php foreach ($future_reservations as $reservation): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($reservation['reservation_date']); ?></td>
                        <td><?php echo htmlspecialchars($reservation['reservation_time']); ?></td>
                        <td><?php echo htmlspecialchars($reservation['reservation_time_end']); ?></td>
                        <td><?php echo htmlspecialchars($reservation['room_name']); ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                <button type="submit" name="cancel_reservation" class="btn-cancel">Cancelar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p class="no-reservations">Você ainda não tem reservas futuras.</p>
        <?php endif; ?>
    </div>

    <footer>
        &copy; <?php echo date('Y'); ?> Sistema de Reserva de Salas. Todos os direitos reservados.
    </footer>
</body>
</html>
