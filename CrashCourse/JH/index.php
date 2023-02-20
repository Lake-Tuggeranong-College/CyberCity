<?php include "template.php"; ?>
<title>Cyber City</title>

<h1 class='text-primary'>Welcome to The Cyber City</h1>

<ul class="top">
    <?php if(!isset($_SESSION['login_user'])) { ?>
        <li class="hover"><a href="#" onClick="revealModal('modalPage')">Login</a>
        </li>
    <?php } else {?>
        <li class="hover"><a href="logout.php">Logout</a>
            Welcome <?php echo $_SESSION['login_user']; ?>
        </li>
    <?php } ?>
    <li><a href="register.php" class="about">Registration</a>
    </li>
</ul>

</body>
</html>