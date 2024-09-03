<?php include "includes/template.php";
/** @var $conn */ ?>


<title>Cyber City</title>






<div class = "wideBox">

        <div class = "title" >

            <h1>Rebels we need your help</h1>

            <?php

            if (isset($_SESSION["username"])) {
                echo
                "<h2 class=''>You're logged in, you may now contribute to the cause</h2>";

                ?>
                <?php
            } else {
                echo

                "<h2 class=''> Please log in or register to gain access to the cause</h2>";
            }
            ?>

        </div>

</div>


<!--<div class = "wideBox">-->
<!--    <div class ="subBoxWhite">-->
<!--        <div class = "title" >-->
<!---->
<!--            <h1 style ="color: cornflowerblue">Learn</h1>-->
<!--            <h2>The basics of Cyber</h2>-->
<!---->
<!--        </div>-->
<!--    </div>-->
<!---->
<!--    <div class ="subBoxWhite">-->
<!--        <div class = "title" >-->
<!---->
<!--            <h1 style ="color: lightgreen">Thrive</h1>-->
<!--            <h2>Among us</h2>-->
<!---->
<!--        </div>-->
<!--    </div>-->
<!---->
<!--    <div class ="subBoxWhite">-->
<!--        <div class = "title" >-->
<!---->
<!--            <h1 style ="color: yellow">Connect</h1>-->
<!--            <h2>Literally.</h2>-->
<!---->
<!--        </div>-->
<!--    </div>-->
<!---->
<!---->
<!--</div>-->


<div class = "WideBox">
    <div class ="subBoxWhite">
        <div class = "title" >

            <h3>Rebels this is a call to action we have caught wind that the organisation TBW(The Black Watchman) has created a Super virus in Lab 404. It is your mission to assist us in putting an end to this sinister scheme, we want to cause mayhem to their system as the advanced hackers can take down the incubation pod to the virus, exterminating it by the roots.</h3>

        </div>
    </div>


    <div class ="subBoxWhite">
        <div class = "title" >

            <h3>We have sent you a list of operations we've found information on, please use the information given to cause havoc to TBW feel free to use the guide to help you.</h3>
            <h4>Sincerely hacker-x.</h4>

        </div>
    </div>

</div>
