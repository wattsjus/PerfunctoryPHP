<?php
class Color {
  public $Color;
  public $Message;
  public $Count;
  public $Page;
}
$i = 0;
$message1 = new Color();
$message1->Color = "Green";
$message1->Message = "Everything to lower";
$message1->Count = $i+=4;
$message1->Page = "next$i.php";

$message2 = new Color();
$message2->Color = "DarkRed";
$message2->Message = "Normal casing";
$message2->Count = $i+=4;
$message2->Page = "next$i.php";

$message3 = new Color();
$message3->Color = "Silver";
$message3->Message = "Everything to upper";
$message3->Count = $i+=4;
$message3->Page = "next$i.php";

$model->Colors = array($message1, $message2, $message3);
$model->Title = "Color Talk";

$model->HelloWorld = function($test, $test2) 
{
  return "$test $test2";
};
 ?>