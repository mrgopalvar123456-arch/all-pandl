<?php
// ==========================================
// File: index.php (Endpoint & Auto Scraper)
// ==========================================

header("Content-Type: application/json");
require_once 'config.php';
require_once 'handlers.php';

// অটো লগইন স্ক্র্যাপার (math captcha parser)
function attempt_captcha_login(&$panel) {
    $login_url = rtrim($panel['login_url'], '/') . '/login';
    $cookie_file = __DIR__ . '/cookie_' . md5($panel['name']) . '.txt';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $login_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $html = curl_exec($ch);
    
    // ম্যাথ ক্যাপচা ডিটেক্টর
    preg_match('/(\d+)\s*([\+\-\*])\s*(\d+)\s*[=\?:]/', $html, $matches);
    $ans = '0';
    if (!empty($matches)) {
        $a = (int)$matches[1];
        $op = $matches[2];
        $b = (int)$matches[3];
        if ($op === '+') $ans = strval($a + $b);
        elseif ($op === '-') $ans = strval($a - $b);
        elseif ($op === '*') $ans = strval($a * $b);
    }
    
    $post_fields = [
        'username' => $panel['username'],
        'password' => $panel['password'],
        'answer' => $ans
    ];
    
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_fields));
    $resp = curl_exec($ch);
    
    if (stripos($resp, 'logout') !== false || stripos($resp, 'dashboard') !== false) {
        $panel['login_status'] = "✅ Active & Fetching";
        curl_close($ch);
        return true;
    }
    
    $panel['login_status'] = "❌ Login Failed";
    curl_close($ch);
    return false;
}

// স্ক্র্যাপার মনিটর
function fetch_otp_from_panels(&$db_data) {
    $panels = &$db_data['bot_settings']['panels'];
    foreach ($panels as $idx => &$p) {
        if (($p['status'] ?? 'OFF') !== 'ON' || ($p['type'] ?? 'API Panel') !== 'Auto Captcha Panel') continue;
        
        $cookie_file = __DIR__ . '/cookie_' . md5($p['name']) . '.txt';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $p['msg_link']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $html = curl_exec($ch);
        curl_close($ch);
        
        // সেশন এক্সপায়ার্ড হলে রিলগইন
        if (stripos($html, 'login') !== false || stripos($html, 'signin') !== false) {
            attempt_captcha_login($p);
            continue;
        }
        
        // DataTable বা aaData এবং fallback HTML parsing
        $data_dict = json_decode($html, true);
        $rows = $data_dict['aaData'] ?? $data_dict['data'] ?? [];
        
        if (empty($rows)) {
            // HTML parser
            $dom = new DOMDocument();
            @$dom->loadHTML($html);
            $tables = $dom->getElementsByTagName('table');
            if ($tables->length > 0) {
                $tr_list = $tables->item(0)->getElementsByTagName('tr');
                foreach ($tr_list as $row_idx => $tr) {
                    if ($row_idx == 0) continue;
                    $tds = $tr->getElementsByTagName('td');
                    if ($tds->length >= 2) {
                        $num = preg_replace('/\D/', '', $tds->item(1)->textContent);
                        $msg = trim($tds->item(2)->textContent);
                        $otp = extract_otp_code($msg);
                        if ($otp && strlen($num) >= 8) {
                            process_received_otp($num, $msg, $otp, $p['name'], $db_data);
                        }
                    }
                }
            }
        } else {
            foreach ($rows as $row) {
                if (is_array($row)) {
                    $row = array_values($row);
                }
                $num = preg_replace('/\D/', '', $row[1] ?? '');
                $msg = $row[2] ?? '';
                $otp = extract_otp_code($msg);
                if ($otp && strlen($num) >= 8) {
                    process_received_otp($num, $msg, $otp, $p['name'], $db_data);
                }
            }
        }
    }
}

function process_received_otp($num, $msg, $otp, $service, &$db_data) {
    $uid = $num . '_' . $otp;
    $processed = &$db_data['processed_otps'];
    if (in_array($uid, $processed)) return;
    $processed[] = $uid;
    
    // ইউজার অ্যাসাইন করা আছে কি না পরীক্ষা
    $owner = null;
    foreach ($db_data['user_active_sessions'] as $user_id => $sess) {
        foreach ($sess['nums'] as $s_num) {
            if (str_replace('+', '', $s_num) == $num) {
                $owner = $user_id;
                break 2;
            }
        }
    }
    
    $settings = $db_data['bot_settings'];
    $info = get_service_info_html($service, $msg);
    $app_title = $info[0];
    $emoji = $info[1];
    
    // গ্রুপে ফরওয়ার্ড
    foreach ($settings['fw_groups'] as $grp) {
        $txt = "$emoji <b>$app_title OTP Received</b>\n"
             . "📱 Number: $num\n"
             . "📝 Message: $msg\n"
             . "🔑 Code: <code>$otp</code>";
        send_message($grp['chat_id'], $txt);
    }
    
    // ইনবক্স ও ব্যালেন্স রিসিভ
    if ($owner) {
        $reward_info = get_service_reward($app_title, $settings);
        $reward = $reward_info[0];
        $db_data['user_data'][$owner]['balance'] += $reward;
        $db_data['user_data'][$owner]['total_otps']++;
        
        $u_txt = "✅ <b>OTP Received!</b>\n"
               . "📱 Number: $num\n"
               . "🔑 Code: <code>$otp</code>\n\n"
               . "💰 +$reward TK added to your balance.";
        send_message($owner, $u_txt);
        unset($db_data['user_active_sessions'][$owner]);
    }
}

// এন্ট্রি পয়েন্ট রানার
$input = file_get_contents('php://input');
$update = json_decode($input, true);

$db_data = load_db();

if (isset($update['message'])) {
    handle_message($update['message'], $db_data);
} elseif (isset($update['callback_query'])) {
    handle_callback($update['callback_query'], $db_data);
}

// স্ক্র্যাপার প্রতিবার রিকোয়েস্টে অথবা ক্রন জবে রান করানো
fetch_otp_from_panels($db_data);

save_db($db_data);

// যদি কনসোলে বা Cron CLI দিয়ে রান করানো হয়
if (php_sapi_name() === 'cli') {
    echo "📡 Monitor started...\n";
    while (true) {
        $db_data = load_db();
        fetch_otp_from_panels($db_data);
        save_db($db_data);
        sleep(2);
    }
}
