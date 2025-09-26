<?php
require_once '../config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = htmlspecialchars($_POST['email']);
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $email = filter_var($email, FILTER_SANITIZE_SPECIAL_CHARS);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die('O endereço de email é inválido.');
    }

    $password = $_POST['password'];
    $remember = isset($_POST['remember']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];

        if ($remember) {
            setcookie('user_email', $user['email'], time() + (86400 * 30), '/');
            setcookie('user_password', $user['password'], time() + (86400 * 30), '/');
        }

        if ($user['role'] === 'admin') {
            header('Location: ../admin');
        } else {
            header('Location: ../user/profile.php');
        }
        exit();
    } else {
        $error = "Credenciais inválidas!";
    }
}

?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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

        .remember-me {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        a {
            text-decoration: none;
            color: #007bff;
            text-align: center;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const emailField = document.getElementById('email');
            const passwordField = document.getElementById('password');
            const rememberCheckbox = document.getElementById('remember');

            function getCookie(name) {
                const value = `; ${document.cookie}`;
                const parts = value.split(`; ${name}=`);
                if (parts.length === 2) return decodeURIComponent(parts.pop().split(';').shift());
            }

            const storedEmail = getCookie('user_email');
            if (storedEmail) {
                emailField.value = storedEmail;
                rememberCheckbox.checked = true; 
            }

            emailField.addEventListener('blur', () => {
                const email = emailField.value;
                if (email) {
                    fetch(`?email=${encodeURIComponent(email)}`)
                        .then(response => response.json())
                        .then(data => {
                            passwordField.value = data.password || '';
                        });
                }
            });
        });
    </script>
</head>

<body>
    <?php include '../assets/navbar.php'; ?>
    <header>
        <h1>Login</h1>
    </header>
    <main>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>

            <label for="password">Senha:</label>
            <input type="password" name="password" id="password" required>

            <div class="remember-me">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember">Lembrar-me</label>
            </div>

            <button type="submit">Entrar</button>
        </form>
        <a href="./forgot_password.php">Forgot password?</a>
    </main>
</body>
</html>
