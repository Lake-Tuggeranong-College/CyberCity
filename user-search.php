<?php include "template.php"; ?>

<title>Search Users</title>
<h1>Search Users</h1>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">

    <div class="form-group">
        <label>Type in UserName</label>
        <input type="search" name="search-user" class="form-control" required="required"/>
    </div>

    <center>
        <button name="search" class="btn btn-primary">Search</button>
    </center>

</form>


<?php
if (isset($_SESSION['access_level']) == 2 ) {

    $userCount = $conn->query("SELECT COUNT(*)  FROM `Users`");
    $row = $userCount->fetch();
    $userCountNumber = $row[0];
    // this uses row 0 (id) to display how mnay user accounts have been created
    echo "<br>The number of users is :" . $userCountNumber . "</br>";


    if (isset($_POST['search'])) {
        $userToSearch = sanitise_data($_POST['search-user']);

        $userSearch = $conn->query("SELECT COUNT(*) as count  FROM Users WHERE Username like '$userToSearch'");
        $row = $userSearch->fetch();
        $userNumberOfRows = $row[0];
        if ($userNumberOfRows > 0) {
            $username= $row[1];
            $accessLevel = $row[3];
            $enabled = $row[4];

            ?>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">

                        <h3>Username : <?php echo  $username;  ?></h3>
                        <h3>AccessLevel: <?php echo  $accessLevel;  ?></h3>
                        <h3>Enabled: <?php echo $enabled; ?></h3>
                </div>
            </div>
            <?php
        } else {
            //echo "No Users Found";
            echo "<br> No user found under:". $userToSearch . "</br>";
        }

    }
}
?>
