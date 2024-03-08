<?php 
    include "CyberCity/template.php";
    /** @var $conn */

    if (!authorisedAccess(true, true, true)) {
        header("Location:index.php");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- Responsive mobile device compatible. -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Search Keyword</title>

    <!-- Latest compiled and minified Boostrap CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Latest compiled Boostrap JavaScript -->
    <script src="js/bootstrap.bundle.min.js"></script>

</head>
    <body class="bg-dark">
        <div class="container-fluid text-white">
            <!-- Indicate heading secion of the whole page. -->
            <header class="d-flex align-items-center justify-content-center text-uppercase">
                <h1>Google Keyword Search challenge</h1>
            </header>

            <!-- Indicate section (middle part) section of the whole page. -->
            <section class="pt-4 pd-2">
                <!-- Boostrap Grid Table System. -->
                <div class="container-fluid text-center bg-light text-dark user-select-none">
                    <div class="row border border-dark-subtle border-2">
                        <div class="col fw-bold border-end border-dark-subtle border-2">Name</div>
                        <div class="col fw-bold border-end border-dark-subtle border-2">Description</div>
                        <div class="col fw-bold border-end border-dark-subtle border-2">Requirement</div>
                        <div class="col fw-bold border-end border-dark-subtle border-2">Points</div>
                        <div class="col fw-bold">Skill gained</div>
                    </div>
                    
                    <div class="row">
                        <div class="col border-start border-dark-subtle border-2">Spot the Keyword</div>
                        <!-- TODO: Proper description needed here. -->
                        <div class="col border-start border-dark-subtle border-2">Lorem ipsum dolor sit amet consectetur adipisicing elit. Doloribus, accusamus?</div>
                        <div class="col border-end border-start border-dark-subtle border-2">Any seach engine.</div>
                        <div class="col border-end border-dark-subtle border-2">25</div>
                        <div class="col border-end border-dark-subtle border-2">Analyising & Problem-solving.</div>
                    </div>

                    <div class="row">
                        <div class="col border border-dark-subtle border-2">Good luck and have fun!</div>                   
                    </div>
                </div>

                <!-- Inline CSS styling for Horizontal line. -->
                <hr style="
                    border: none; 
                    position: relative; 
                    margin: 1.5rem 0; 
                    height: 4px; /* Adjust horizontal line thickness.*/
                    color: white; /* Compatible for old IE users.*/
                    background-color: white;
                ">
                
                <!-- Entered the from and directs to correspond page if validated. -->
                <!-- TODO: Valid & In-valid check system. -->
                <form action="/challengesList.php" method="post">
                    <div class="form-floating text-dark">
                        <input type="text" class="form-control" id="hflag" name="hiddenflag" placeholder="Enter the flag here.">
                        <label for="hflag">Hidden flag</label>
                    </div>
                </form>
            </section>
        </div>
    </body>
</html>