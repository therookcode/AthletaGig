<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if ($data) {
        $file = 'leads.json';
        $current_data = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
        $current_data[] = $data;
        
        if (file_put_contents($file, json_encode($current_data, JSON_PRETTY_PRINT))) {
            echo json_encode(["status" => "success", "message" => "Lead captured successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to save data"]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Invalid data"]);
    }
} else {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
}
?>
