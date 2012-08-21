<?php
  require_once("parse.php");

  class ParseDataAccess {
    private $parse;
    function __construct() {
      $this->parse = new parseRestClient(array(
        'appid' => $model->ParseAppId,
        'restkey' => $model->ParseRestKey
      ));
    }
    public $objectId;
    public $createdAt;
    public $updatedAt;
    public function Get($param) {
      if(is_array($param)) {
        $params = array(
          'className' => get_class($this),
          'object' => array(),
          'query' => $param
        );
        $result = $this->parse->query($params);
        $res = json_decode($result);
        if(count($res->results) > 1) {
          return $res->results;
        } else {
          if(count($res->results) == 0) {
            return '';
          } else {
            $request= $res->results[0];
          }
        }
      } else {
        $params = array(
          'className' => get_class($this),
          'objectId' => $param
        );
        $request = json_decode($this->parse->get($params));
      }
      $values = $this->getFields($request);
      foreach($values as $key => $value) {
          $this->$key = $value;
      }
      return $request;
    }
    public function Save() {
      $values = $this->getFields($this);
      if(empty($this->objectId)) {
        $params = array(
          'className' => get_class($this),
          'object' => $values
        );
        return json_decode($this->parse->create($params));
      } else {
        array_splice($values, array_search('createdAt',array_keys($values)), 1);
        array_splice($values, array_search('updatedAt',array_keys($values)), 1);
        $params = array(
          'className' => get_class($this),
          'objectId' => $this->objectId,
          'object' => $values
        );
        return json_decode($this->parse->update($params));
      }
    }
    private function getFields($obj)
    {
	$getFields = create_function('$obj', 'return get_object_vars($obj);');
	return $getFields($obj);
    }
  }
?>