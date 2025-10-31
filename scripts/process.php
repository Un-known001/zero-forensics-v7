<?php
// --- 1. LIVE TELEGRAM CONFIGURATION (Hardcoded for Termux Simplicity) ---
// WARNING: In production, use Environment Variables!
$TELEGRAM_BOT_TOKEN = "6568778555:AAHmQzF9KoHY6C20Fsn5jOGd8pyogeiKnEk";
$TELEGRAM_CHAT_ID = "6609528491"; // Note: Leading space removed by PHP trim/int, but we keep the string for simplicity

// --- 2. Telegram Notification Function ---
function sendTelegramAlert($message, $token, $chat_id) {
    // Termux requires cURL package to be installed (pkg install curl)
    $url = "https://api.telegram.org/bot{$token}/sendMessage";
    $data = [
        'chat_id' => $chat_id,
        'text' => "[ZERO-FORENSICS ALERT V7.0]\n" . $message,
        'parse_mode' => 'HTML'
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // Execute the request
    $response = curl_exec($ch);
    // Check for cURL errors
    if(curl_errno($ch)){
        error_log('cURL Error: ' . curl_error($ch));
    }
    curl_close($ch);
    // Optional: return the response for debugging, but we log it here
    return $response; 
}

// --- 3. Main Processing Logic ---
$action = $_POST['action'] ?? '';
$inputValue = $_POST['input_value'] ?? '';

$final_output = "";
$telegram_message = "";

// --- Termux Path Reminder: Use absolute path for scripts ---
$script_path = "~/storage/shared/zero-forensics/scripts/"; 

switch ($action) {
    case 'decodeIP':
        // Execute Python script (assuming ip_tracker.py is in the scripts folder)
        $python_output = shell_exec("python3 {$script_path}ip_tracker.py " . escapeshellarg($inputValue));
        $final_output = "🟢 PHP/API Success: IP traced. Python Output:\n" . $python_output;
        $telegram_message = "New IP Trace: <b>{$inputValue}</b> analyzed. Result: " . strip_tags($python_output);
        break;

    case 'analyzeMalware':
        $python_output = shell_exec("python3 {$script_path}malware_analyzer.py " . escapeshellarg($inputValue));
        $final_output = "🟢 PHP/Python ML Success: Analysis Complete. Python Output:\n" . $python_output;
        $telegram_message = "CRITICAL: Malware analysis run on <b>{$inputValue}</b>.";
        break;

    case 'uploadFile':
        // File handling logic
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $temp_file = $_FILES['file']['tmp_name'];
            $file_name = basename($_FILES['file']['name']);
            // Termux temporary upload location
            $upload_path = "/data/data/com.termux/files/home/temp_uploads/{$file_name}"; 
            
            // Create the directory if it doesn't exist
            @mkdir(dirname($upload_path), 0777, true);

            if (move_uploaded_file($temp_file, $upload_path)) {
                // Now run Python analysis on the uploaded file
                $python_output = shell_exec("python3 {$script_path}file_scanner.py " . escapeshellarg($upload_path));
                $final_output = "🟢 Upload Success: File {$file_name} scanned. Python Output:\n" . $python_output;
                $telegram_message = "NEW EVIDENCE: File <b>{$file_name}</b> uploaded and scanned successfully.";
            } else {
                $final_output = "Error: Could not move uploaded file on Termux server.";
            }
        } else {
            $final_output = "Error: File upload failed. Error code: " . ($_FILES['file']['error'] ?? 'N/A');
        }
        break;
    
    case 'trackCrypto':
        $python_output = shell_exec("python3 {$script_path}crypto_tracker.py " . escapeshellarg($inputValue));
        $final_output = "🟢 PHP/Python Success: Wallet traced. Python Output:\n" . $python_output;
        $telegram_message = "Crypto Trace: Wallet <b>{$inputValue}</b> being tracked.";
        break;
        
    default:
        $final_output = "Error: Unknown action requested.";
}

// --- 4. Final Output and Telegram Alert ---
echo $final_output . "\n\n✅ <b>Telegram Alert Sent to Team Zero.</b>";
sendTelegramAlert($telegram_message, $TELEGRAM_BOT_TOKEN, $TELEGRAM_CHAT_ID);

?>
