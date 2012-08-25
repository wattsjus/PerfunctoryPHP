<?php 
global $page;
$page = $_GET["page"];
if(empty($page) || $page == "") $page = "index.php";
require_once("Templates/template.php");
$regex = "/<!--(.*?)-->/ims";
$view = preg_match_all($regex, file_get_contents("../Views/".str_replace(".php",".html",$page)), $matches);
foreach($matches as $key => $value) {
if(is_array($value)) {
  $parts = explode(":",trim($value[0]));
  if(trim($parts[0]) == "template") {
    DetectTemplate(trim($parts[1]));
    break;
  }
}
}
include("../Views/".str_replace(".php",".html",$page));
EndPage();
?>