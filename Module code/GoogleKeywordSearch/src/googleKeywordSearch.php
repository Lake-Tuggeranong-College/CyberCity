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
        <section>
            <p class="text-start pt-4 pb-2">
                Lorem ipsum dolor sit amet consectetur adipisicing elit. Neque, dolore.
            </p>
            
            <!-- Indicate table section. -->
            <table class="table table-bordered table-light table-responsive user-select-none">
                <!-- Heading of the table. -->
                <thead class="table-active ">
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Requirement</th>
                        <th>Skill gained</th>
                    </tr>
                </thead>

                <!-- Body of the table. -->
                <tbody>
                    <tr>
                        <td>Spot the Keyword</td>
                        <td>Lorem ipsum dolor sit amet consectetur adipisicing elit. Error, repellendus.</td>
                        <td>Any seach engine.</td>
                        <td>Analyising & Problem-solving.</td>
                    </tr>
                </tbody>
            </table>

            <!-- Indicate table section. -->
            <table class="table table-bordered table-light table-responsive">
                <!-- Body of the table. -->
                <tbody>
                    <tr>
                        <td>Good luck and have fun!</td>
                        <td>Hidden flag: </td>
                        <td>Yes/No</td>
                        <td>Score: 25</td>
                    </tr>
                </tbody>
            </table>

            <!-- Inline CSS styling for Horizontal line. -->
            <hr style="
                border: none; 
                position: relative; 
                margin-top: 1.5rem; 
                height: 4px; /* Adjust horizontal line thickness.*/
                color: white; /* Compatible for old IE users.*/
                background-color: white;
            ">

            <p>
                Lorem, ipsum dolor sit amet consectetur adipisicing elit. 
                Cumque doloribus esse quam et voluptatum tenetur delectus exercitationem 
                aliquid deleniti expedita?
            </p>
        </section>
    </div>
</body>
</html>