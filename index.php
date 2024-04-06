<?php include "template.php";
/** @var $conn */ ?>


<title>Cyber City</title>
<div style="text-align: center;">
    <h1>Welcome to our Cyber City</h1>


    <?php

    if (isset($_SESSION["username"])) {
        echo
        "<h2 class='text'> You're logged in, you may now play the CyberCity CTF game</h2>";

        ?>
        <?php
    } else {
        echo

        "<h2 class='text'> Please log in or register to participate in the CyberCity CTF Challenge</h2>";
    }
    ?>
    <div style="max-width: 100%; min-height: 50%">
    <img  src="images/MainImage.jpg" alt="" class="center" height="50%" width="50%">
    </div>
</div>
<p></p>
</body>
</html>