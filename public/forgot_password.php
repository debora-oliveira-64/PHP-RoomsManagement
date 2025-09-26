<?php
require_once '../config/db.php';
session_start();

use PHPMailer\PHPMailer\PHPMailer;
require './phpMailer/vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = htmlspecialchars($_POST['email']);
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'O endereço de email é inválido.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Generate new password
            $new_password = bin2hex(random_bytes(4)); // 8-character random password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Update password in database
            $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            $update_stmt->execute([$hashed_password, $email]);

            // Send email with new password
            $mail = new PHPMailer;
            $mail->CharSet = 'utf-8';
            $mail->isSMTP();
            $mail->SMTPDebug = 0;
            $mail->Host = 'smtp.gmail.com';
            $mail->Port = 587;
            $mail->SMTPSecure = 'tls';
            $mail->SMTPAuth = true;
            $mail->Username = "Sender_email";
            $mail->Password = "password";
            $mail->setFrom('Sender_email', 'Your App Name');
            $mail->addAddress($email);
            $mail->Subject = 'Redefinição de Senha';
            $mail->IsHTML(true);
            $mail->Body = "Sua nova senha é: <b>$new_password</b><br>Por favor, altere sua senha após o login.";
            $mail->SMTPOptions = array('ssl' => array('verify_peer' => false,'verify_peer_name' => false,'allow_self_signed' => true));

            if (!$mail->send()) {
                $error = "Erro ao enviar o email: " . $mail->ErrorInfo;
            } else {
                $success = "Uma nova senha foi enviada para seu email.";
            }
        } else {
            $error = "Nenhuma conta foi encontrada com este email.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<?php include '../assets/navbar.php'; ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esqueci Minha Senha</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            width: 100%;
            max-width: 400px;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #007bff;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-top: 15px;
            font-size: 14px;
        }
        input[type="email"], button {
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            margin-top: 20px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .error {
            color: red;
            font-size: 14px;
            text-align: center;
            margin-top: 10px;
        }
        .success {
            color: green;
            font-size: 14px;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Esqueci Minha Senha</h1>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>
        <form method="POST">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>

            <button type="submit">Enviar Nova Senha</button>
        </form>
    </div>
</body>
</html>
