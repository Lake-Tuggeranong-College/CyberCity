<?php include "template.php";
/** @var $conn */ ?>


<title>Cyber City</title>
<div style="text-align: center;">
    <h1 class='text-primary'>Welcome to our Cyber City</h1>


    <?php

    if (isset($_SESSION["username"])) {
        echo
        "<h2 class='text'> Your logged in, you now may play the CyberCity CTF game</h2>";

        ?>
        <?php
    } else {
        echo

        "<h2 class='text'> Please login or register to participate in the CyberCity CTF Challenge</h2>";
    }
    ?>
    <img src="images/MainImage.jpg" alt="" width="50%" height="50%">
</div>
<p></p>
</body>
</html>