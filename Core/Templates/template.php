<?php 
session_start();
require_once("config.php");
require_once("Core/Data/mysqlDB.php");
require_once("Core/Data/parseDB.php");
require_once("Models/".basename($_SERVER["PHP_SELF"])); 
if(isset($_GET["formSubmitted"])) {
  SubmitForm($_GET["formName"]);
} else if(isset($_POST["formSubmitted"])) {
  SubmitForm($_POST["formName"]);
}

require_once("Core/qrcode/qrlib.php");

global $is_wireless;
global $is_smarttv;
global $is_tablet;
global $is_phone;
global $is_mobile_device;
global $is_desktop;

global $model;
global $modelLoads;
$modelLoads = array();
if(isset($model->LoadModel)) {
$modelLoads[] = $model->LoadModel;
}
$is_wireless = false;
$is_smarttv = false;
$is_tablet = false;
$is_phone = false;
$is_mobile_device = false;
$is_desktop = false;

DetectDevice();

function DetectDevice() {
  require_once('Core/WURFL/wurfl_config_standard.php');
  $requestingDevice = $wurflManager->getDeviceForHttpRequest($_SERVER);
  
  global $is_wireless;
  global $is_smarttv;
  global $is_tablet;
  global $is_phone;
  global $is_mobile_device;
  global $is_desktop;

  $is_wireless = ($requestingDevice->getCapability('is_wireless_device') == 'true');
  $is_smarttv = ($requestingDevice->getCapability('is_smarttv') == 'true');
  $is_tablet = ($requestingDevice->getCapability('is_tablet') == 'true');
  $is_phone = ($requestingDevice->getCapability('can_assign_phone_number') == 'true');
  $is_mobile_device = ($is_wireless || $is_tablet);
  $is_desktop = !($is_wireless || $is_smarttv || $is_tablet || $is_phone || $is_mobile_device);
}
class FunctionFinder {
  public $Params;
  public $InnerFunction;
  public $EntireFunction;
  public function ProcessNext($name, $text, $paramOnly = false) {
    $origStart = strpos($text, "%$name");
    $start = strpos($text,"(",$origStart)+1;
    $end = strpos($text, ")", $start);
    $this->Params = substr($text, $start, $end - $start);
    if($paramOnly) {
      $this->InnerFunction = "";
      $InnerLength = 0;
      $length = $end - $origStart + 1;
    } else {
      $this->InnerFunction = GetTextBetweenCurly($text, strpos($text, "{", $start));
      $InnerLength = strlen($this->InnerFunction) + 1;
      $length = (strpos($text,"{",$start) + 1 + $InnerLength) - $origStart;
    }
    $this->EntireFunction = substr($text, $origStart, $length);
  }
}
ob_start(); 

function DetectTemplate($name) {
  global $is_wireless;
  global $is_smarttv;
  global $is_tablet;
  global $is_phone;
  global $is_mobile_device;
  global $is_desktop;
  global $model;

  $useTemplate = "$name.php";
  if (!$is_mobile_device) {
    if ($is_smarttv) {
        $useTemplate = "$name.smarttv.php";
    }
  } else {
    if ($is_tablet) {
        $useTemplate = "$name.tablet.php";
    } else if ($is_phone) {
        $useTemplate = "$name.phone.php";
    } else {
        $useTemplate = "$name.mobile.php";
    }
  }
  if(isset($_GET["view"]) && $_GET["view"] == "desktop") {
    $is_wireless = false;
    $is_smarttv = false;
    $is_tablet = false;
    $is_phone = false;
    $is_mobile_device = false;
    $is_desktop = true;
    $_SESSION["view"] = "desktop";
  } else if(isset($_GET["view"]) && $_GET["view"] == "mobile") {
    $is_wireless = false;
    $is_smarttv = false;
    $is_tablet = false;
    $is_phone = true;
    $is_mobile_device = false;
    $is_desktop = false;
    $_SESSION["view"] = "mobile";
  }
  if($is_wireless) { $deviceType = "Wireless"; }
  if($is_smarttv) { $deviceType = "Smart TV"; }
  if($is_tablet) { $deviceType = "Tablet"; }
  if($is_phone) { $deviceType = "Phone"; }
  if($is_mobile_device) { $deviceType = "Mobile"; }
  if($is_desktop) { $deviceType = "Desktop"; }
  $model->DeviceType = $deviceType;
  if(file_exists("Models/$name.php")) {
    require_once("Models/$name.php");
  } else {
    throw new Exception("Could not find a $name model.");
  }
  if(file_exists("Templates/$useTemplate")) {
    require_once("Templates/$useTemplate");
  } else {
    if(file_exists("Templates/$name.php")) {
      require_once("Templates/$name.php");
    } else {
      throw new Exception("Could not find a $name template.");
    }
  }
}
function SubmitForm($name) {
  global $model;
  $function = "Form".$name."Submitted";
  $params = null;
  foreach($_POST as $key => $value) {
    $params->$key = $value;
  }
  $function($params, $model);
}
function GetTextBetweenCurly($text, $start) {
  $i = substr($text, $start, 1) == "{" ? 1 : 0;
  if($i ==1) $diff = 1; else $diff = 0;
  $pos = $start;
  do {
    $nextLess = strpos($text, "{", $pos + 1);
    $nextMore = strpos($text, "}", $pos + 1);
    if($nextLess == false || $nextLess > $nextMore) {
      $i--;
      $pos = $nextMore;
    } else {
      $i++;
      $pos = $nextLess;
    }
  } while($i > 0);
  return substr($text, $start + $diff, $pos - $start + 1 - (2 * $diff));
}
function CopyToModel($name, $params) {
   global $model;
   $model->$name = $params->$name;
}
function CopyAllToModel($params) {
  global $model;
  foreach($params as $key => $value) {
    $model->$key = $value;
  }
}
function EndPage() {
  global $is_wireless;
  global $is_smarttv;
  global $is_tablet;
  global $is_phone;
  global $is_mobile_device;
  global $is_desktop;
  
  global $html;
  global $model;
  $modModel = array();
  foreach($model as $key => $value) {
    $modModel[$key] = $value;
  }
  krsort($modModel);
  $model = null;
  foreach($modModel as $key => $value) {
    $model->$key = $value;
  }
  $html = str_replace("%PageContent", trim(ob_get_clean()), $html);
  $funcFinder = new FunctionFinder();
  while(strpos($html, "%ContentFor") > 0) {
    $funcFinder->ProcessNext("ContentFor", $html);
    $devices = explode(",", $funcFinder->Params);
    $display = false;
    foreach($devices as $device) {
      $device = trim($device);
      $display = $display || ($device == "Wireless" && $is_wireless);
      $display = $display || ($device == "Smart TV" && $is_smarttv);
      $display = $display || ($device == "Tablet" && $is_tablet);
      $display = $display || ($device == "Phone" && $is_phone);
      $display = $display || ($device == "Mobile Device" && $is_mobile_device);
      $display = $display || ($device == "Desktop" && $is_desktop); 
    }
    $html = str_replace($funcFinder->EntireFunction, $display ? $funcFinder->InnerFunction : "", $html);
  }
  while(strpos($html, "%ContentNotFor") > 0) {
    $funcFinder->ProcessNext("ContentNotFor", $html);
    $devices = explode(",", $funcFinder->Params);
    $display = false;
    foreach($devices as $device) {
      $device = trim($device);
      $display = $display || ($device == "Wireless" && $is_wireless);
      $display = $display || ($device == "Smart TV" && $is_smarttv);
      $display = $display || ($device == "Tablet" && $is_tablet);
      $display = $display || ($device == "Phone" && $is_phone);
      $display = $display || ($device == "Mobile Device" && $is_mobile_device);
      $display = $display || ($device == "Desktop" && $is_desktop); 
    }
    $html = str_replace($funcFinder->EntireFunction, $display ? "" : $funcFinder->InnerFunction, $html);
  }
  global $modelLoads;
  while(strpos($html, "%SubTemplate") > 0) {
    $funcFinder->ProcessNext("SubTemplate", $html, true);
    $funcFinder->Params = trim($funcFinder->Params);
    if(file_exists("Models/$funcFinder->Params.php")) {
      require_once("Models/$funcFinder->Params.php");
      if(isset($model->LoadModel)) {
      $modelLoads[] = $model->LoadModel;
      }
    } else {
      throw new Exception("Model not found for subtemplate $funcFinder->Params.");
    }
    $html = str_replace($funcFinder->EntireFunction, file_get_contents("Templates/$funcFinder->Params.php"), $html);
  }
  foreach($modelLoads as $value) {
    $value($model);
  }
  while(strpos($html, "%Form") > 0) {
    $funcFinder->ProcessNext("Form", $html);
    $formName = $funcFinder->Params;
    $page = $_SERVER["PHP_SELF"];
    if(count(explode(":", $formName)) > 1) {
      $parts = explode(":", $formName);
      $formName = $parts[0];
      $page = $parts[1];
    }
    $innerForm = $funcFinder->InnerFunction;
    $formContent = $funcFinder->EntireFunction;
    $html = str_replace("$formContent", "<form id='$formName' style='margin-bottom:0em' method='POST' action='".$page."' enctype='multipart/form-data'>$innerForm<input type='hidden' name='formSubmitted' value='true' /><input type='hidden' name='formName' value='$formName' /></form>", $html);
  }
  while(strpos($html, "%ForEach") > 0) {
    $funcFinder->ProcessNext("ForEach", $html);
    $var = $funcFinder->Params;
    $alis = "%".$var;
    if(count(explode(" as ",$var)) > 1) {
      $parts = explode(" as ", $var);
      $var = trim($parts[0]);
      $alis = "%".trim($parts[1]);
    }
    $before = $funcFinder->EntireFunction;
    $after = $funcFinder->InnerFunction;
    $after = Recurse($alis, $model->$var, $after, true);
    $html = str_replace($before, $after, $html);
  }
  $html = Recurse("", $model, $html, false);
  while(strpos($html, "%If") > 0) {
    $funcFinder->ProcessNext("If",$html);
    if(eval("return $funcFinder->Params;")) {
      $html = str_replace($funcFinder->EntireFunction, $funcFinder->InnerFunction, $html);
    } else {
      $html = str_replace($funcFinder->EntireFunction, "", $html);
    }
  }
  while(strpos($html, "%QRCode") >0) {
    $funcFinder->ProcessNext("QRCode", $html, true);
    $size = "";
    if(strripos($funcFinder->Params,",") >= 0) {
      $posibleSize = strtoupper(trim(substr($funcFinder->Params, strripos($funcFinder->Params,",")+1)));
      if($posibleSize == "M" || $posibleSize == "S" || $posibleSize == "L") {
        $size = $posibleSize;
        $funcFinder->Params = substr($funcFinder->Params, 0, stripos($funcFinder->Params,","));
      }
    }
    if($size == "S") { $size = "width='75px' height='75px'"; }
    if($size == "M") { $size = "width='100px' height='100px'"; }
    if($size == "L") { $size = "width='150px' height='150px'"; }
    $url = "<img $size src='".dirname($_SERVER["PHP_SELF"])."/Core/qrcode.php?data=$funcFinder->Params' />";
    $html = str_replace($funcFinder->EntireFunction, $url, $html);
  }
  foreach($model as $key => $value) {
    if(is_callable($value)) {
      while(strpos($html, "%$key(") > 0) {
        $funcFinder->ProcessNext("$key", $html, true);
        $params = explode(",",$funcFinder->Params);
        if(count($params) > 0) {
          $results = call_user_func_array($model->$key, $params);
        } else {
          $results = $value();
        }
        $html = str_replace($funcFinder->EntireFunction, $results, $html); 
      }
    }
  }
  while(strpos($html, "%MobileLink") > 0) {
    $funcFinder->ProcessNext("MobileLink", $html, true);
    if(isset($_SESSION["view"]) && $_SESSION["view"] == "desktop") {
      $opDevice = "mobile device";
    } else {
      $opDevice = "desktop";
    }
    $linkText = "Click here to view this site as a ".$opDevice;
    if(isset($funcFinder->Params) && trim($funcFinder->Params) != '') {
      $linkText = $funcFinder->Params;
    }
    if(isset($_SESSION["view"]) && $_SESSION["view"] == "desktop") {
      $html = str_replace($funcFinder->EntireFunction, "<a href='".$_SERVER["PHP_SELF"]."?view=mobile'>$linkText</a>", $html);
    } else if(!$is_desktop) {
      $html = str_replace($funcFinder->EntireFunction, "<a href='".$_SERVER["PHP_SELF"]."?view=desktop'>$linkText</a>", $html);
    } else {
      $html = str_replace($funcFinder->EntireFunction, "", $html);
    }
  }
  echo $html;
}
function Recurse($toReplace, $replacement, $text, $forLoop) {
  if(is_callable($replacement)) {
  } else if(is_string($toReplace) && (!is_object($replacement)) && (!is_array($replacement))) {
    $text = trim(str_replace($toReplace, $replacement, $text));
  } else if(is_array($replacement) && $forLoop) {
    $temp = "";
    foreach($replacement as $key => $value) {
      if(is_object($value)) {
        $temp .= Recurse($toReplace, $value, $text, $forLoop);
      } else if(is_string($value)) {
        if($temp == '') $temp = $text;
        $temp = Recurse("%Value", $value, $temp, $forLoop);
      }
    }
    $text = $temp;
  } else if(is_object($replacement)) {
    if(strpos($toReplace, "%") < 0 && !is_int($toReplace)) {
      $toReplace = "%$toReplace";
    }
    $temp = "";
    foreach($replacement as $key => $value) {
      if($temp == '') $temp = $text;
      if(empty($toReplace)) {
        $temp = Recurse("%$key", $value, $temp, $forLoop);
      } else {
        $temp = Recurse("$toReplace->$key", $value, $temp, $forLoop);
      }
    }
    $text = $temp;
  } 
  return $text;
}
function EndTemplate() {
  global $html;
  $html = ob_get_clean();
  ob_start();
}
?>