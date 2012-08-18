<?php
$model->Title = "Next Page";


function FormDarkRedSubmitted($params, $model) {
  CopyAllToModel($params);
  $model->Title = $params->tempTitle;
}
function FormGreenSubmitted($params, $model) {
  foreach($params as $key => $value) {
    $params->$key = strtolower($value);
  }
  CopyAllToModel($params);
  $model->Title = $params->tempTitle;
}
function FormSilverSubmitted($params, $model) {
  foreach($params as $key => $value) {
    $params->$key = strtoupper($value);
  }
  CopyAllToModel($params);
  $model->Title = $params->tempTitle;
}
?>