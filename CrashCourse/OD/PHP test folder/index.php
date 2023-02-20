<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css"
          crossorigin="anonymous">
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>
    <link href="vendor/fontawesome/css/all.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php
if (! empty($_GET["i"])) {
    $template = intval($_GET["i"]);
}
if (empty($template)) {
    $template = 1;
}
require_once __DIR__ . '/template/login-template' . $template . '.php';
?>
</body>
</html>