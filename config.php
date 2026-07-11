<?php
// ==========================================
// File: config.php (Core Configurations & Helpers)
// ==========================================

define('TOKEN', '8312212025:AAGQVy_EzFkFqlcqsSsi8r4yPDAQw_01dFw');
define('BASE_URL', 'https://api.telegram.org/bot' . TOKEN);
define('OWNER_ID', 7940416120);
define('BOT_USERNAME', '𝗫𝗣𝗔𝗡𝗡𝗘𝗜 𝗕𝗢𝗧');
define('DB_FILE', 'bot_data.json');

// ডিফল্ট সেটিংস (Stex, Voltx, Zenex, Premium Emoji, User Management ও 100 Bulk সম্পূর্ণ রিমুভড)
$default_settings = [
    'admins' => [5409553122, OWNER_ID],
    'panels' => [],
    'fw_groups' => [],
    'otp_link' => 'https://t.me/rmmtzotpgroup',
    'main_channel' => 'https://t.me',
    'withdraw_on' => true,
    'min_withdraw' => 30.0,
    'otp_reward' => 0.1,
    'service_otp_rates' => [],
    'service_reward_enabled' => [],
    'refer_reward' => 0.2,
    'cooldown' => 10,
    'num_req' => 3,
    'num_share' => 1,
    'support_link' => 'https://t.me/rahi455',
    'w_methods' => ['bKash', 'Nagad'],
    'w_group' => '',
    'fj_on' => false,
    'fj_channels' => [],
    'custom_messages' => [
        'start' => [
            'text' => "╔═══════════╗\n       📊 NUMBER BOT\n╚═══════════╝\n🚀 Welcome to 𝗫𝗣𝗔𝗡𝗡𝗘𝗜 𝗕𝗢𝗧 Service\n━━━━━━━━━━━━\n✅ Choose an option below\nto continue using the bot.\n━━━━━━━━━━━━\n✨ Premium OTP Service",
            'buttons' => []
        ],
        'get_number' => ['text' => "📍 Select a service:", 'buttons' => []],
        'select_country' => ['text' => "📌 Select a country for {service}:", 'buttons' => []],
        'search_number' => ['text' => "╔═══════════╗\n     🔍 <b>SEARCH NUMBER</b>\n╚═══════════╝\n✅ Enter 3 to 9 digits\nto search for a number.\n━━━━━━━━━━━━━\n📝 Example:\n➥ 880\n━━━━━━━━━━━━━\n🔍 Fast Number Lookup System", 'buttons' => []],
        'traffic' => ['text' => "📊 <b>Traffic Overview</b>\n\n✅ Available Numbers: {avail}\n🚀 Assigned Numbers: {assigned}", 'buttons' => []],
        'refer' => ['text' => "➖➖➖➖➖➖➖\n« 🎁 REFER & EARN »\n➖➖➖➖➖➖➖\n🔗 YOUR LINK:\n<code>{ref_link}</code>\n➖➖➖➖➖➖➖\n👤 TOTAL REFERS: <b>{total_ref}</b>\n➖➖➖➖➖➖➖\n💸 PER REFER: <b>{ref_reward} TK</b>\n➖➖➖➖➖➖➖", 'buttons' => []],
        'withdrawal' => ['text' => "➖➖➖➖➖➖➖\n《 💰 WITHDRAWAL 》\n➖➖➖➖➖➖➖\n📅 BALANCE: {bal}৳\n🔐 MINIMUM: {min_w} ৳\n➖➖➖➖➖➖➖\nSELECT METHOD:", 'buttons' => []],
        'support' => ['text' => "💬 Contact us for any help:", 'buttons' => []]
    ]
];

// ডাটাবেস লোডার ও সেভার (Thread-safe flock সহ)
function load_db() {
    global $default_settings;
    if (!file_exists(DB_FILE)) {
        $initial_db = [
            'bot_settings' => $default_settings,
            'number_batches' => [],
            'used_numbers_list' => [],
            'user_data' => [],
            'user_states' => [],
            'temp_data' => [],
            'user_active_sessions' => [],
            'pending_withdrawals' => [],
            'support_msg_map' => [],
            'recent_traffic' => [],
            'processed_otps' => []
        ];
        file_put_contents(DB_FILE, json_encode($initial_db, JSON_PRETTY_PRINT));
    }
    $file = fopen(DB_FILE, 'r');
    flock($file, LOCK_SH);
    $size = filesize(DB_FILE);
    $content = $size > 0 ? fread($file, $size) : '{}';
    fclose($file);
    return json_decode($content, true) ?: [];
}

function save_db($data) {
    $file = fopen(DB_FILE, 'w');
    if (flock($file, LOCK_EX)) {
        fwrite($file, json_encode($data, JSON_PRETTY_PRINT));
        fflush($file);
        flock($file, LOCK_UN);
    }
    fclose($file);
}

// Telegram API ফাংশনসমূহ
function api_call($method, $payload = []) {
    $ch = curl_init(BASE_URL . '/' . $method);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true) ?: [];
}

function send_message($chat_id, $text, $reply_markup = null) {
    $payload = ['chat_id' => $chat_id, 'text' => $text, 'parse_mode' => 'HTML', 'disable_web_page_preview' => true];
    if ($reply_markup) $payload['reply_markup'] = $reply_markup;
    return api_call('sendMessage', $payload);
}

function edit_message($chat_id, $message_id, $text, $reply_markup = null) {
    $payload = ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $text, 'parse_mode' => 'HTML', 'disable_web_page_preview' => true];
    if ($reply_markup) $payload['reply_markup'] = $reply_markup;
    return api_call('editMessageText', $payload);
}

function delete_message($chat_id, $message_id) {
    return api_call('deleteMessage', ['chat_id' => $chat_id, 'message_id' => $message_id]);
}

function answer_callback($callback_id, $text = "", $show_alert = false) {
    return api_call('answerCallbackQuery', ['callback_query_id' => $callback_id, 'text' => $text, 'show_alert' => $show_alert]);
}

function send_document($chat_id, $filename, $content) {
    $boundary = uniqid();
    $delimiter = '-------------' . $boundary;
    $post_data = "--" . $delimiter . "\r\n"
        . "Content-Disposition: form-data; name=\"chat_id\"\r\n\r\n" . $chat_id . "\r\n"
        . "--" . $delimiter . "\r\n"
        . "Content-Disposition: form-data; name=\"document\"; filename=\"" . $filename . "\"\r\n"
        . "Content-Type: text/plain\r\n\r\n" . $content . "\r\n"
        . "--" . $delimiter . "--\r\n";

    $ch = curl_init(BASE_URL . '/sendDocument');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: multipart/form-data; boundary=" . $boundary,
        "Content-Length: " . strlen($post_data)
    ]);
    curl_exec($ch);
    curl_close($ch);
}

// ওটিপি কোড এক্সট্র্যাক্টর
function extract_otp_code($text) {
    $clean_text = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', strval($text));
    
    if (preg_match('/(\d{3}[-\s]+\d{3})|(\d{2}[-\s]+\d{2}[-\s]+\d{2})/', $clean_text, $m)) {
        return str_replace(' ', '', $m[0]);
    }
    
    $otp_keywords = 'code|is|otp|pin|verification|auth|your code|কোড';
    if (preg_match('/(?:' . $otp_keywords . ')\s*(?:is|:|-|=)?\s*([a-z0-9]{4,10})/i', $clean_text, $m)) {
        if (ctype_digit($m[1])) return $m[1];
    }
    
    if (preg_match('/([a-z0-9]{4,10})\s*(?:is your|is the|কোড)/i', $clean_text, $m)) {
        if (ctype_digit($m[1])) return $m[1];
    }
    
    if (preg_match('/G-(\d{6})/i', $clean_text, $m)) return $m[1];
    if (preg_match_all('/(?<!\d)\d{4,8}(?!\d)/', $clean_text, $matches)) {
        if (!empty($matches[0])) return $matches[0][0];
    }
    
    return null;
}

// সার্ভিস ডিটেক্টর
function detect_service($text) {
    $text_lower = strtolower($text);
    $keywords = [
        'WHATSAPP' => ['whatsapp', 'wa.me', 'wa code'],
        'FACEBOOK' => ['facebook', 'fb', 'meta'],
        'INSTAGRAM' => ['instagram', 'insta', 'ig code'],
        'TELEGRAM' => ['telegram', 'tg code', 't.me'],
        'TIKTOK' => ['tiktok', 'tik tok'],
        'BKASH' => ['bkash', 'b-kash'],
        'NAGAD' => ['nagad'],
        'GOOGLE' => ['google', 'gmail', 'youtube', 'g-'],
        'IMO' => ['imo']
    ];
    foreach ($keywords as $srv => $kws) {
        foreach ($kws as $kw) {
            if (strpos($text_lower, $kw) !== false) return $srv;
        }
    }
    return null;
}

// সার্ভিস ইমোজি প্রোভাইডার
function get_service_info_html($service_name, $msg_text = "") {
    $detected = detect_service($msg_text) ?: strtoupper(trim($service_name));
    $emojis = [
        'WHATSAPP' => '🟢', 'FACEBOOK' => '🔵', 'INSTAGRAM' => '📸',
        'TELEGRAM' => '✈️', 'TIKTOK' => '🎵', 'BKASH' => '🌸',
        'NAGAD' => '🟠', 'GOOGLE' => '🔴', 'IMO' => '💬'
    ];
    $emoji = $emojis[$detected] ?? '📱';
    return [ucfirst(strtolower($detected)), $emoji];
}

// ভাষা ডিটেক্টর
function detect_language($text) {
    if (preg_match('/[\x{0600}-\x{06FF}]/u', $text)) return "#AR";
    if (preg_match('/[\x{0980}-\x{09FF}]/u', $text)) return "#BN";
    if (preg_match('/[\x{0900}-\x{097F}]/u', $text)) return "#HI";
    if (preg_match('/[\x{0400}-\x{04FF}]/u', $text)) return "#RU";
    return "#EN";
}

function get_service_reward($app_name, $settings) {
    $srv_key = strtoupper(trim($app_name));
    $rates = $settings['service_otp_rates'] ?? [];
    $enabled = $settings['service_reward_enabled'] ?? [];
    $matched = null;
    foreach ($rates as $k => $rate) {
        if (strpos($srv_key, $k) !== false || strpos($k, $srv_key) !== false) {
            $matched = $k;
            break;
        }
    }
    if ($matched) {
        return [floatval($rates[$matched]), $enabled[$matched] ?? true];
    }
    return [floatval($settings['otp_reward']), true];
}
