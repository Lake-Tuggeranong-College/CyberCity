<?php include "template.php";
/** @var $conn */

if (!authorisedAccess(false, false, true)) {
    header("Location:index.php");
}

?>
<h1>Read Contact requests</h1>


