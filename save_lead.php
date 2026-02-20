<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// --- CONFIGURATION ---
// Google Apps Script URL for Google Sheets Integration
$google_sheet_webhook = "https://script.google.com/macros/s/AKfycbyX91lQ5h-TIIvehz98KC5-3fsMdGUkQo69p5qTfkDQZqKgPUiIYi56fDQ2JQtdNWEeKA/exec"; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if ($data) {
        // 1. Save to local JSON as primary backup
        $file = 'leads.json';
        $current_data = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
        $current_data[] = $data;
        file_put_contents($file, json_encode($current_data, JSON_PRETTY_PRINT));
        
        // 2. Prepare Data for Email
        $userEmail = $data['email'] ?? '';
        $userName = $data['name'] ?? 'Professional';
        $userRole = $data['role'] ?? 'Beta Member';
        $userPhone = $data['phone'] ?? 'N/A';
        $userSociety = $data['society'] ?? 'N/A';
        
        // 3. Send Personalized Welcome Email
        $subject = "Welcome to the AthletaGig Node - Delhi NCR Alpha";
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: AthletaGig <founder@athletagig.com>" . "\r\n";
        
        $message = "
        <html>
        <body style='background-color: #000D1A; color: #ffffff; font-family: sans-serif; padding: 40px;'>
            <div style='max-width: 600px; margin: 0 auto; background: #001529; border: 1px solid #28A745; border-radius: 20px; padding: 40px;'>
                <h1 style='color: #ffffff; text-transform: uppercase; letter-spacing: -1px;'>ATHLETA<span style='color: #28A745;'>GIG</span></h1>
                <p style='color: #D4AF37; font-weight: bold; text-transform: uppercase; font-size: 12px; letter-spacing: 2px;'>Verified. Play. Thrive.</p>
                <hr style='border: 0; border-top: 1px solid #ffffff14; margin: 30px 0;'>
                <h2 style='font-size: 24px;'>Welcome, $userName.</h2>
                <p style='color: #b0b0b0; line-height: 1.6;'>Your professional profile has been captured for the <b>$userRole</b> node. Our team is currently verifying the athletic infrastructure at <b>$userSociety</b> to activate your hub.</p>
                
                <div style='background: #ffffff05; border-radius: 12px; padding: 20px; margin: 30px 0;'>
                    <p style='margin: 0; font-size: 14px; color: #888;'>Node Details:</p>
                    <p style='margin: 5px 0 0 0; color: #fff;'><b>Role:</b> $userRole</p>
                    <p style='margin: 5px 0 0 0; color: #fff;'><b>Hub:</b> $userSociety</p>
                </div>
                
                <p style='color: #b0b0b0; line-height: 1.6;'>You will receive a follow-up once your residential cluster node reaches the verified quorum.</p>
                <a href='https://athletagig.com' style='display: inline-block; background: #D4AF37; color: #000D1A; padding: 15px 30px; border-radius: 10px; text-decoration: none; font-weight: bold; margin-top: 20px; text-transform: uppercase;'>Portal Dashboard</a>
                <div style='margin-top: 40px; padding-top: 20px; border-top: 1px solid #ffffff14; font-size: 10px; color: #555; text-transform: uppercase; letter-spacing: 2px;'>
                    © 2026 AthletaGig Systems • Delhi NCR Node Alpha
                </div>
            </div>
        </body>
        </html>";
        
        mail($userEmail, $subject, $message, $headers);

        // 4. Push to Google Sheets Webhook (Free Integration)
        if (!empty($google_sheet_webhook)) {
            $ch = curl_init($google_sheet_webhook);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Required for Google script redirects
            curl_exec($ch);
            curl_close($ch);
        }

        // 5. Admin Alert
        $adminSubject = "New AthletaGig Registration: $userName ($userRole)";
        $adminMsg = "Details:\nName: $userName\nEmail: $userEmail\nPhone: $userPhone\nRole: $userRole\nSociety: $userSociety\nCity: " . ($data['city'] ?? "N/A");
        mail("founder@athletagig.com", $adminSubject, $adminMsg, "From: system@athletagig.com");

        echo json_encode(["status" => "success", "message" => "All systems operational"]);
    } else {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Invalid data"]);
    }
}
?>
