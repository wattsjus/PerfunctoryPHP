<?php require_once("qrcode/qrlib.php"); 
print_r(QRcode::png($_GET["data"], false, 4, 10, 2));
?>