<?php include "includes/template.php"; /** @var $conn */ ?>

<title>Cyber City</title>

<script>
    // Check localStorage for theme preference
    window.onload = () => {
        const savedTheme = localStorage.getItem('theme');
        const body = document.body;

        if (savedTheme) {
            // Apply the saved theme on page load
            body.className = savedTheme;

            // Check if dark mode is active and update the "wideBox" class accordingly
            if (savedTheme.includes('bg-dark')) {
                // If dark mode is active, replace wideBox with wideBoxDark
                const wideBoxes = document.querySelectorAll('.wideBox');
                wideBoxes.forEach(box => {
                    box.classList.replace('wideBox', 'wideBoxDark');

            else {
                const wideBoxes = document.querySelectorAll('.wideBoxDark');
                wideBoxes.forEach(box => {
                    box.classList.replace('wideBoxDark', 'wideBox');
                });
            }
        }
    }


</script>

<div class="wideBox">
    <div class="title">
        <h1>Rebels we need your help</h1>

        <?php
        if (isset($_SESSION["username"])) {
            echo "<h2 class=''>You're logged in, you may now contribute to the cause</h2>";
        } else {
            echo "<h2 class=''> Please log in or register to gain access to the cause</h2>";
        }
        ?>
    </div>
</div>

<div class="wideBox">
    <div class="subBoxWhite">
        <div class="title">
            <h2>Beginnings</h2>
            <p>In 1850 a rural town was created, referred to as Latafa...</p>
        </div>
    </div>

    <div class="subBoxWhite">
        <div class="title">
            <h2>Currently</h2>
            <p>Oak-Crack is the remains of the town...</p>
        </div>
    </div>
</div>