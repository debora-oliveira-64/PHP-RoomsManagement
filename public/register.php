<?php
require_once '../config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $name = filter_var($name, FILTER_SANITIZE_SPECIAL_CHARS);
    $email = htmlspecialchars($_POST['email']);
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $email = filter_var($email, FILTER_SANITIZE_SPECIAL_CHARS);

    list(, $domain) = explode('@', $email);

    if (!preg_match('/^[a-zA-Z0-9 ]{1,30}$/', $name)) {
        $error = "O username deve conter apenas letras, números e ter no máximo 30 caracteres.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || (!checkdnsrr($domain, 'MX') && !checkdnsrr($domain, 'A'))){
        $error = "Email inválido.";
    } else{
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $password]);
            header('Location: login.php');
            exit();
        } catch (PDOException $e) {
            $error = "Erro ao registrar: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
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
            text-align: center;
            padding: 20px;
        }

        header h1 {
            margin: 0;
            color: #ddd;
        }

        main {
            max-width: 400px;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #007bff;
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-size: 14px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 100%;
        }

        button {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .error {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
<?php include '../assets/navbar.php'; ?>
    <header>
        <h1>Registro</h1>
    </header>
    <main>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <form method="POST">
            <label for="name">Nome:</label>
            <input type="text" name="name" id="name" required>

            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>

            <label for="password">Senha:</label>
            <input type="password" name="password" id="password" required>

            <button type="submit">Registrar</button>
        </form>
    </main>
</body>

</html>