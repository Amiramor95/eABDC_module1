<?php
try {
    $conn = new PDO('mysql:host=192.168.2.56:6000;dbname=module1', 'fimm_user', 'fimm_db890!@#');
} catch (PDOException $e) {

    http_response_code(400);
    $r = new response();
    $r->result = "Connection Failed";
    $r->reason = "Error to connect to server. Please Contact FiMM technical support";
    echo json_encode($r);
    exit();

}
?>