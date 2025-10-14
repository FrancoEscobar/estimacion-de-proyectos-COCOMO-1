<?php
session_start();
unset($_SESSION['proyectos']);
header('Location: index.php');
exit;