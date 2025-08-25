<?php
#This redirects the user to the /website/index.php, the real index.
#kalden is whining at me fr
include "website/includes/config.php";

header("Location: ". BASE_URL ."index.php");
exit;

?>
