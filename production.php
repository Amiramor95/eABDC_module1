<?php

$currentStat = file_get_contents('./app_status.json');
$data = json_decode($currentStat, true);

$data['isDBConnected'] = false;

$newStat = json_encode($data);
file_put_contents('./app_status.json', $newStat);
?>