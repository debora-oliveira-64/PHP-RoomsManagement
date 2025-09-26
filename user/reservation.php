<?php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../public/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$stmtRooms = $pdo->prepare("SELECT * FROM rooms WHERE status = 'available'");
$stmtRooms->execute();
$rooms = $stmtRooms->fetchAll(PDO::FETCH_ASSOC);

$stmtReservations = $pdo->prepare("
    SELECT r.room_id, r.reservation_date, r.reservation_time, r.reservation_time_end, ro.name AS room_name 
    FROM reservations r 
    JOIN rooms ro ON r.room_id = ro.id 
    WHERE r.user_id = ? AND (r.reservation_date > CURDATE() 
        OR (r.reservation_date = CURDATE() AND r.reservation_time > CURTIME()))
    ORDER BY r.reservation_date, r.reservation_time
");
$stmtReservations->execute([$user_id]);
$reservations = $stmtReservations->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = htmlspecialchars($_POST['room_id']);
    $reservation_date = htmlspecialchars($_POST['reservation_date']);
    $reservation_time = htmlspecialchars($_POST['reservation_time']);
    $reservation_time_end = htmlspecialchars($_POST['reservation_time_end']);
    $user_id = $_SESSION['user_id'];

    $current_date = date('Y-m-d');
    $max_date = date('Y-m-d', strtotime('+1 month'));

    $dayOfWeek = date('N', strtotime($reservation_date));

    if ($dayOfWeek >= 6) {
        $error = "Reservas só são permitidas durante os dias úteis (segunda a sexta-feira).";
    } elseif (
        ($reservation_date > $current_date || ($reservation_date === $current_date && $reservation_time > date('H:i'))) &&
        $reservation_date <= $max_date
    ) {
        $timePattern = '/^([0-1]?[0-9]|2[0-3]):(00|30)$/';
        if (
            strtotime($reservation_time) >= strtotime("09:00") &&
            strtotime($reservation_time_end) <= strtotime("18:00") &&
            preg_match($timePattern, $reservation_time) &&
            preg_match($timePattern, $reservation_time_end)
        ) {
            $stmtCheck = $pdo->prepare("SELECT * FROM reservations WHERE room_id = ? AND reservation_date = ? AND ((? >= reservation_time AND ? < reservation_time_end) OR (? > reservation_time AND ? <= reservation_time_end))");
            $stmtCheck->execute([$room_id, $reservation_date, $reservation_time, $reservation_time, $reservation_time_end, $reservation_time_end]);

            if ($stmtCheck->rowCount() === 0) {
                $stmtInsert = $pdo->prepare("INSERT INTO reservations (room_id, user_id, reservation_date, reservation_time, reservation_time_end, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmtInsert->execute([$room_id, $user_id, $reservation_date, $reservation_time, $reservation_time_end]);
                header('Location: reservation.php');
                exit();
            } else {
                $error = "A sala já está reservada para o horário solicitado.";
            }
        } else {
            $error = "O horário deve estar entre 09:00 e 18:00 e terminar em :00 ou :30.";
        }
    } else {
        $error = "A reserva deve ser para uma data e horário futuros e no máximo dentro de um mês.";
    }
}

?>


<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva de Salas</title>
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
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
        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        label {
            display: block;
            margin: 10px 0 5px;
        }
        input, select, button {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            background-color: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .error {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
<?php include '../assets/navbar.php'; ?>
    <header>
        <h1>Reserva de Salas</h1>
        <h2>Disponiveis de segunda a sexta, entre as 9h até ás 18h.</h2>
    </header>
    <main>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

        <section>
            <h2>Reservas Ativas</h2>
            <table>
                <tr>
                    <th>Nome da Sala</th>
                    <th>Data</th>
                    <th>Hora Início</th>
                    <th>Hora Fim</th>
                </tr>
                <?php if (!empty($reservations)): ?>
                    <?php foreach ($reservations as $reservation): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($reservation['room_name']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['reservation_date']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['reservation_time']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['reservation_time_end']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">Não há reservas ativas no momento.</td>
                    </tr>
                <?php endif; ?>
            </table>
        </section>

        <section>
            <h2>Requisitar Sala</h2>
            <form method="POST">
                <label for="room_id">Sala:</label>
                <select name="room_id" id="room_id" required>
                    <?php foreach ($rooms as $room): ?>
                        <option value="<?php echo htmlspecialchars($room['id']); ?>">
                            <?php echo htmlspecialchars($room['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="reservation_date">Data:</label>
                <input type="date" name="reservation_date" id="reservation_date" required>

                <label for="reservation_time">Hora Início:</label>
                <input type="time" name="reservation_time" id="reservation_time" required>

                <label for="reservation_time_end">Hora Fim:</label>
                <input type="time" name="reservation_time_end" id="reservation_time_end" required>

                <button type="submit">Requisitar</button>
            </form>
        </section>
    </main>
</body>
</html>
