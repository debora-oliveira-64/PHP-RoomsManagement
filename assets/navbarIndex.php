<?php
?>

<nav>
    <ul>
        <li><a href="./index.php">Home</a></li>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <li><a href="./public/login.php">Login</a></li>
        <?php else: ?>
            <li><a href="./public/logout.php">Logout</a></li>
        <?php endif; ?>
        <li><a href="./public/register.php">Register</a></li>

        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'user'): ?>
            <li><a href="./user/profile.php">Profile</a></li>
            <li><a href="./user/reservation.php">Add Reservation</a></li>
            <li><a href="./user/view_rooms.php">My Reservations</a></li>
        <?php endif; ?>

        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
            <li><a href="./admin/room_occupation.php">Reservations</a></li>
            <li><a href="./admin/manage_rooms.php">Rooms</a></li>
            <li><a href="./admin/users_list.php">Users</a></li>
        <?php endif; ?>
    </ul>
</nav>

<style>
    nav {
        background-color: #333;
        padding: 10px;
        position: sticky;
        top: 0;
        z-index: 1000;
    }

    nav ul {
        list-style-type: none;
        margin: 0;
        padding: 0;
    }

    nav ul li {
        display: inline;
        margin-right: 20px;
    }

    nav ul li a {
        color: white;
        text-decoration: none;
        font-weight: bold;
    }

    nav ul li a:hover {
        color: #f1f1f1;
    }
</style>
