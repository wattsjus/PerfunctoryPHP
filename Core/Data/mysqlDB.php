<?php 
class MySqlDataAccess {
    function __construct() {
      $this->PrimaryKey = get_class($this)."Id";
    }
    public $PrimaryKey;
    public $CreateAt;
    public $UpdateAt;
    public function Get($param) {
      global $conn;
      $class = get_class($this);
      if(is_string($param)) {
        $result = mysql_query("SELECT * FROM `$class` WHERE $param", $conn);
        $results = array();
        while($row = mysql_fetch_assoc($result)) {
          $temp = new $class();
          foreach($temp as $key => $value) {
            $temp->$key = $row[$key];
          }
          $results[] = $temp;
        }
        return $results;
      } else if(is_integer($param)) {
        $result = mysql_query("SELECT * FROM `$class` WHERE `$this->PrimaryKey` = $param", $conn) or die(mysql_error());
        $row = mysql_fetch_assoc($result);
        foreach($this as $key => $value) {
	  if($key != "PrimaryKey") {
            $this->$key = $row[$key];
          }
        }
        return $temp;
      }
    }
    public function Save() {
      global $conn;
      $class = get_class($this);
      $PrimaryKey = $this->PrimaryKey;
      if($this->$PrimaryKey <= 0) {
        $fields = '';
        $values = '';
        $this->CreateAt = date('Y-m-d H:i:s', time());
        $this->UpdateAt = date('Y-m-d H:i:s', time());
        $super = $this->getFields($this);
        foreach($super as $key => $value) {
          if($key != $PrimaryKey && $key != "PrimaryKey") {
	          $fields .= "`$key`,";
          if(checkDateTime($value)) {
            $values .= "'".getDateTime($value)."',";
          } else if(is_string($value)) {
            $values .= "'".mysql_real_escape_string($value)."',";
          } else {
            $values .= "$value,";
          }
          }
        }
        $fields = substr($fields,0,-1);
        $values = substr($values,0,-1);
        mysql_query("INSERT INTO $class ($fields) VALUES($values)", $conn) or die(mysql_error());
        $this->$PrimaryKey = mysql_insert_id();
      } else {
        $updateVals = '';
        $this->UpdateAt = date('Y-m-d H:i:s', time());
        $super = $this->getFields($this);
        foreach($super as $key => $value) {
                  if($key != $PrimaryKey && $key != "PrimaryKey") {
          if(checkDateTime($value)) {
            $updateVals .= "`$key` = '".getDateTime($value)."',";
          } else if(is_string($value)) {
            $updateVals .= "`$key` = '".mysql_real_escape_string($value)."',";
          } else {
            $updateVals .= "`$key` = $value,";
          }
          }
        }
        $updateVals = substr($updateVals, 0, -1);
        $id = $this->$PrimaryKey;
        mysql_query("UPDATE `$class` SET $updateVals WHERE `$this->PrimaryKey` = $id", $conn) or die(mysql_error());
      }
    }
    private function getFields($obj)
    {
	$getFields = create_function('$obj', 'return get_object_vars($obj);');
	return $getFields($obj);
    }
  }

global $conn;
global $model;
$conn = mysql_connect($model->MySqlServer, $model->MySqlUserName, $model->MySqlPassword);
mysql_select_db($model->MySqlDatabase, $conn);
function checkDateTime($data) {
    if (date('Y-m-d H:i:s', strtotime($data)) == $data) {
        return true;
    } else {
        return false;
    }
}
function getDateTime($data) {
    if (date('Y-m-d H:i:s', strtotime($data)) == $data) {
        return date('Y-m-d H:i:s', strtotime($data));
    } else {
        return false;
    }
}

?>