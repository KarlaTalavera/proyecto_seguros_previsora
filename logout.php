<?php
session_start();
session_destroy();
header('Location: index.php?vista=login');
exit();
?>