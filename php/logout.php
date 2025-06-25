<?php
session_start();
session_unset();
session_destroy();

// Redirect to home page (use absolute URL)
header("Location: /website/index.php");
exit;


?>
