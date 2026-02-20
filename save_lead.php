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
            
            // --- Welcome Email Logic ---
            $userEmail = $data['email'];
            $userName = isset($data['name']) ? $data['name'] : 'Professional';
            $userRole = isset($data['role']) ? $data['role'] : 'Beta Member';
            
            $subject = "Welcome to the AthletaGig Node - Delhi NCR Alpha";
            
            // Premium HTML Email Template
            $message = "
            <html>
            <body style='background-color: #000D1A; color: #ffffff; font-family: sans-serif; padding: 40px;'>
                <div style='max-width: 600px; margin: 0 auto; background: #001529; border: 1px solid #28A745; border-radius: 20px; padding: 40px;'>
                    <h1 style='color: #ffffff; text-transform: uppercase; letter-spacing: -1px;'>ATHLETA<span style='color: #28A745;'>GIG</span></h1>
                    <p style='color: #D4AF37; font-weight: bold; text-transform: uppercase; font-size: 12px; letter-spacing: 2px;'>Verified. Play. Thrive.</p>
                    
                    <hr style='border: 0; border-top: 1px solid #ffffff14; margin: 30px 0;'>
                    
                    <h2 style='font-size: 24px;'>Welcome to the Professional Sports Network, $userName.</h2>
                    <p style='color: #b0b0b0; line-height: 1.6;'>Thank you for securing your professional node in our Delhi NCR private beta. You are now part of an elite ecosystem bridging the gap between talent and opportunity.</p>
                    
                    <div style='background: #ffffff05; border-radius: 12px; padding: 20px; margin: 30px 0;'>
                        <p style='margin: 0; font-size: 14px; color: #888;'>Assigned Role:</p>
                        <p style='margin: 5px 0 0 0; font-weight: bold; color: #28A745; text-transform: uppercase;'>$userRole</p>
                    </div>
                    
                    <p style='color: #b0b0b0; line-height: 1.6;'>Our team is currently verifying credentials for your residential cluster. You will receive a secondary notification once your professional handle is fully activated.</p>
                    
                    <a href='https://athletagig.com' style='display: inline-block; background: #D4AF37; color: #000D1A; padding: 15px 30px; border-radius: 10px; text-decoration: none; font-weight: bold; margin-top: 20px; text-transform: uppercase;'>Visit Portal</a>
                    
                    <div style='margin-top: 40px; padding-top: 20px; border-top: 1px solid #ffffff14; font-size: 10px; color: #555; text-transform: uppercase; letter-spacing: 2px;'>
                        © 2026 AthletaGig Systems • Delhi NCR Node Alpha
                    </div>
                </div>
            </body>
            </html>";
            
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: AthletaGig <founder@athletagig.com>" . "\r\n";
            
            // Send to User
            mail($userEmail, $subject, $message, $headers);
            
            // Send notification to Founder
            $adminSubject = "New Lead Captured: " . $userName;
            $adminMessage = "New registration on AthletaGig:\n\nName: $userName\nEmail: $userEmail\nRole: $userRole\nSociety: " . ($data['society'] ?? 'N/A');
            mail("founder@athletagig.com", $adminSubject, $adminMessage, "From: system@athletagig.com");

            echo json_encode(["status" => "success", "message" => "Lead captured and email sent"]);
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
