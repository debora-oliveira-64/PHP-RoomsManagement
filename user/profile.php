<?php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../public/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

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
        if (!empty($_FILES['profile_image']['tmp_name'])) {
            $image_data = file_get_contents($_FILES['profile_image']['tmp_name']);
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, profile_image = ? WHERE id = ?");
            $stmt->execute([$name, $email, $image_data, $user_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $stmt->execute([$name, $email, $user_id]);
        }
    }

    header('Location: profile.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
        }

        header h1 {
            margin: 0;
            color: #ddd;
            font-size: 24px;
        }

        .logout-btn {
            background-color: #ff4d4d;
            color: #fff;
            border: none;
            padding: 10px 15px;
            font-size: 14px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        .logout-btn:hover {
            background-color: #e63939;
        }

        main {
            max-width: 600px;
            margin: 30px auto;
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
            margin-bottom: 5px;
        }

        input[type="text"], input[type="email"], input[type="file"] {
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

        .profile-image {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
        }

        .profile-image img {
            border-radius: 50%;
            border: 2px solid #ddd;
            width: 100px;
            height: 100px;
            object-fit: cover;
        }
    </style>
</head>
<body>
<?php include '../assets/navbar.php'; ?>
    <header>
        <h1>Meu Perfil</h1>
    </header>
    <main>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <div class="profile-image">
            <?php if ($user['profile_image']): ?>
                <img src="../assets/show_image.php?id=<?php echo $user_id; ?>" alt="Imagem de perfil">
            <?php else: ?>
                <img src="../public/images/placeholder.jpg" alt="Sem imagem de perfil">
            <?php endif; ?>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <label for="name">Nome:</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>

            <label for="email">Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

            <label for="profile_image">Imagem de Perfil:</label>
            <input type="file" name="profile_image">

            <button type="submit">Atualizar</button>
        </form>
    </main>
</body>
</html>
