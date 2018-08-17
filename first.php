<?php

$deliverySdek = 500;
$deliveryPochta = 400;
$sdekItog = "СДЭК(".$deliverySdek."руб)";
$pochtaItog = "Почта России(".$deliveryPochta."руб)";
$weight = variable_get('weight2', $default = '1');
$height = variable_get('height2', $default = '1');
$length = variable_get('length2', $default = '1');
$price = variable_get('price2', $default = '1');
$width = variable_get('width2', $default = '1');
$test2 = $_POST['text'];
return array('cdek' => "$sdekItog",'pochta' => "$pochtaItog", 'ves' => "$weight", 'vysota' => "$height", 'dlinna' => "$length", 'cena' => "$price", 'shirina' => "$test2");

?>
