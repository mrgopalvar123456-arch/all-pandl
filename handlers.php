<?php
// ==========================================
// File: handlers.php (Fully Integrated Bot Menus, Keyboards & Routers)
// ==========================================

require_once 'config.php';

// প্রধান নেভিগেশন কিবোর্ড (100 Bulk Number সম্পূর্ণ রিমুভড)
function main_menu($user_id, $admins) {
    $keyboard = [
        ['Number', 'Custom Number'],
        ['Live Support Admin'],
        ['Referral', 'Withdrawal']
    ];
    if (in_array($user_id, $admins) || $user_id == OWNER_ID) {
        $keyboard[] = ['Admin Panel'];
    }
    return ['keyboard' => $keyboard, 'resize_keyboard' => true];
}

function admin_panel_keyboard() {
    return ['inline_keyboard' => [
        [['text' => 'Leader Board System', 'callback_data' => 'lb_main']],
        [['text' => 'Upload Number', 'callback_data' => 'upload_num'], ['text' => 'Delete Files', 'callback_data' => 'delete_files']],
        [['text' => 'Broadcast', 'callback_data' => 'broadcast_msg'], ['text' => 'System', 'callback_data' => 'system_settings']],
        [['text' => 'Close', 'callback_data' => 'close_msg']]
    ]];
}

// সিস্টেম সেটিংস কিবোর্ড (ছবি অনুযায়ী পরিশোধিত)
function system_settings_keyboard() {
    return ['inline_keyboard' => [
        [['text' => 'Force Join System', 'callback_data' => 'manage_fj'], ['text' => 'Admin Management', 'callback_data' => 'manage_admins']],
        [['text' => 'OTP Group', 'callback_data' => 'manage_otp_groups']],
        [['text' => 'Panel Management', 'callback_data' => 'manage_panels']],
        [['text' => 'Rahin Control', 'callback_data' => 'dxa_control']],
        [['text' => 'Menu Design', 'callback_data' => 'menu_design_list'], ['text' => 'Test Flow', 'callback_data' => 'test_message_flow']],
        [['text' => 'Back', 'callback_data' => 'back_to_admin']]
    ]];
}

function dxa_control_keyboard($settings) {
    $w_status = $settings['withdraw_on'] ? "ON" : "OFF";
    return ['inline_keyboard' => [
        [['text' => "Withdraw: $w_status", 'callback_data' => 'dxa_toggle_w']],
        [['text' => "Min Withdraw: {$settings['min_withdraw']}", 'callback_data' => 'dxa_min_w'], ['text' => "OTP Reward: {$settings['otp_reward']}", 'callback_data' => 'dxa_otp_r']],
        [['text' => "Refer Reward: {$settings['refer_reward']}", 'callback_data' => 'dxa_ref_r'], ['text' => "Cooldown: {$settings['cooldown']}s", 'callback_data' => 'dxa_cool']],
        [['text' => "Num/Req: {$settings['num_req']}", 'callback_data' => 'dxa_num_req'], ['text' => "Num/Share: {$settings['num_share']}", 'callback_data' => 'dxa_num_share']],
        [['text' => "Support Link", 'callback_data' => 'dxa_sup_link'], ['text' => "Withdrawal Methods", 'callback_data' => 'manage_w_methods']],
        [['text' => "Back", 'callback_data' => 'system_settings']]
    ]];
}

function menu_design_list_keyboard() {
    return ['inline_keyboard' => [
        [['text' => 'Edit /start Menu', 'callback_data' => 'md_edit_start']],
        [['text' => 'Edit GET NUMBER', 'callback_data' => 'md_edit_get_number'], ['text' => 'Edit Search Number', 'callback_data' => 'md_edit_search_number']],
        [['text' => 'Edit Select Country', 'callback_data' => 'md_edit_select_country']],
        [['text' => 'Edit Traffic', 'callback_data' => 'md_edit_traffic'], ['text' => 'Edit Refer', 'callback_data' => 'md_edit_refer']],
        [['text' => 'Edit Withdrawal', 'callback_data' => 'md_edit_withdrawal'], ['text' => 'Edit Support', 'callback_data' => 'md_edit_support']],
        [['text' => 'Reset Defaults', 'callback_data' => 'md_reset_defaults']],
        [['text' => 'Back', 'callback_data' => 'system_settings']]
    ]];
}

function fj_settings_keyboard($settings) {
    $status = $settings['fj_on'] ? "ON" : "OFF";
    $kb = [[['text' => "Status: $status", 'callback_data' => 'toggle_fj']]];
    foreach ($settings['fj_channels'] as $idx => $ch) {
        $kb[] = [['text' => "Delete: $ch", 'callback_data' => "del_fj_$idx"]];
    }
    $kb[] = [['text' => 'Add Channel', 'callback_data' => 'add_fj']];
    $kb[] = [['text' => 'Back', 'callback_data' => 'system_settings']];
    return ['inline_keyboard' => $kb];
}

function admin_settings_keyboard($settings) {
    $kb = [];
    foreach ($settings['admins'] as $idx => $adm) {
        $btn_text = ($adm == OWNER_ID) ? "Owner: $adm" : "Delete: $adm";
        $cb_data = ($adm == OWNER_ID) ? "ignore" : "del_adm_$idx";
        $kb[] = [['text' => $btn_text, 'callback_data' => $cb_data]];
    }
    $kb[] = [['text' => 'Add Admin', 'callback_data' => 'add_adm']];
    $kb[] = [['text' => 'Back', 'callback_data' => 'system_settings']];
    return ['inline_keyboard' => $kb];
}

function otp_groups_list_keyboard($settings) {
    $kb = [
        [['text' => "Edit OTP Button Link", 'callback_data' => 'edit_otp_link']],
        [['text' => "Edit Main Channel Link", 'callback_data' => 'edit_main_channel']]
    ];
    foreach ($settings['fw_groups'] as $idx => $fg) {
        $kb[] = [['text' => "Group: " . $fg['chat_id'], 'callback_data' => "manage_fw_$idx"]];
    }
    $kb[] = [['text' => 'Add Forward Group', 'callback_data' => 'add_fw']];
    $kb[] = [['text' => 'Back', 'callback_data' => 'system_settings']];
    return ['inline_keyboard' => $kb];
}

function specific_fw_group_keyboard($settings, $idx) {
    $group = $settings['fw_groups'][$idx];
    $kb = [];
    foreach (($group['buttons'] ?? []) as $b_idx => $btn) {
        $kb[] = [['text' => "Del: " . $btn['text'], 'callback_data' => "del_fwbtn_{$idx}_{$b_idx}"]];
    }
    $kb[] = [['text' => 'Add Inline Button', 'callback_data' => "add_fwbtn_$idx"]];
    $kb[] = [['text' => 'Delete Entire Group', 'callback_data' => "del_fw_$idx"]];
    $kb[] = [['text' => 'Back to Groups', 'callback_data' => 'manage_otp_groups']];
    return ['inline_keyboard' => $kb];
}

function typed_panels_list_keyboard($panels, $p_type) {
    $kb = [];
    foreach ($panels as $idx => $p) {
        if (($p['type'] ?? 'API Panel') !== $p_type) continue;
        $act_text = $p['status'] === 'ON' ? "Turn OFF {$p['name']}" : "Turn ON {$p['name']}";
        $kb[] = [
            ['text' => $act_text, 'callback_data' => "tog_pnl_$idx"],
            ['text' => $p['name'], 'callback_data' => "conf_pnl_$idx"]
        ];
    }
    $add_cb = $p_type === 'API Panel' ? 'add_api_panel' : 'add_cpt_panel';
    $kb[] = [['text' => 'Add New Provider', 'callback_data' => $add_cb]];
    $kb[] = [['text' => 'Back', 'callback_data' => 'manage_panels']];
    return ['inline_keyboard' => $kb];
}

function panel_config_keyboard($panels, $idx) {
    $p = $panels[$idx];
    $kb = [];
    $act_text = $p['status'] === 'ON' ? "Turn OFF" : "Turn ON";
    $kb[] = [['text' => $act_text, 'callback_data' => "tog_pnl_$idx"]];
    if (($p['type'] ?? 'API Panel') !== 'Auto Captcha Panel') {
        $kb[] = [['text' => 'Set API URL', 'callback_data' => "set_p_api_$idx"]];
        $kb[] = [['text' => 'Set Token', 'callback_data' => "set_p_tok_$idx"]];
        $kb[] = [['text' => 'Full API (URL+Token)', 'callback_data' => "set_p_fapi_$idx"]];
    } else {
        $kb[] = [['text' => 'Set Login URL', 'callback_data' => "set_p_lurl_$idx"]];
        $kb[] = [['text' => 'Set Username', 'callback_data' => "set_p_user_$idx"]];
        $kb[] = [['text' => 'Set Password', 'callback_data' => "set_p_pass_$idx"]];
        $kb[] = [['text' => 'Set Message Link', 'callback_data' => "set_p_mlink_$idx"]];
    }
    $kb[] = [['text' => 'Test Connection', 'callback_data' => "test_p_conn_$idx"]];
    $back_data = ($p['type'] ?? 'API Panel') === 'API Panel' ? 'manage_api_panels' : 'manage_cpt_panels';
    $kb[] = [['text' => 'Back to Providers', 'callback_data' => $back_data]];
    return ['inline_keyboard' => $kb];
}

function get_admin_text($db_data) {
    $users_count = count($db_data['user_data'] ?? []);
    $total_files = count($db_data['number_batches'] ?? []);
    $available_nums = 0;
    foreach ($db_data['number_batches'] as $b) {
        $available_nums += count($b['numbers']);
    }
    return "📊 <b>ADMIN CONTROL PANEL</b>\n"
         . "━━━━━━━━━━━━━━━━━━\n\n"
         . "👤 Users      » $users_count\n"
         . "📁 Files      » $total_files\n"
         . "🚀 Available  » $available_nums\n";
}

// মেসেজ রাউটার
function handle_message($message, &$db_data) {
    $chat_id = $message['chat']['id'];
    $text = isset($message['text']) ? trim($message['text']) : '';
    $settings = &$db_data['bot_settings'];

    // ইউজার ভ্যালিডেশন
    if (!isset($db_data['user_data'][$chat_id])) {
        $db_data['user_data'][$chat_id] = [
            'balance' => 0.0,
            'total_refers' => 0,
            'total_otps' => 0,
            'banned' => false
        ];
    }

    $user = &$db_data['user_data'][$chat_id];
    if ($user['banned']) {
        send_message($chat_id, "❌ You are banned from this bot.");
        return;
    }

    // ফাইল আপলোড প্রোসেসিং (লোকাল স্টক যুক্তকরণ)
    if (isset($message['document'])) {
        $doc = $message['document'];
        if (str_ends_with(strtolower($doc['file_name']), '.txt')) {
            $file_id = $doc['file_id'];
            $file_info = api_call('getFile', ['file_id' => $file_id]);
            if (isset($file_info['result']['file_path'])) {
                $file_path = $file_info['result']['file_path'];
                $content = file_get_contents("https://api.telegram.org/file/bot" . TOKEN . "/" . $file_path);
                
                $db_data['temp_data'][$chat_id] = [
                    'numbers' => explode("\n", str_replace("\r", "", $content)),
                    'filename' => $doc['file_name']
                ];
                $db_data['user_states'][$chat_id] = 'wait_for_service';
                send_message($chat_id, "✅ File received.\n\n📌 Enter the service name (e.g., WHATSAPP):");
                return;
            }
        } else {
            send_message($chat_id, "❌ Please upload a .txt file only.");
        }
        return;
    }

    // Force Join
    if ($settings['fj_on'] && !empty($settings['fj_channels'])) {
        $joined = true;
        foreach ($settings['fj_channels'] as $ch) {
            $status = api_call('getChatMember', ['chat_id' => $ch, 'user_id' => $chat_id]);
            if (!isset($status['result']['status']) || in_array($status['result']['status'], ['left', 'kicked'])) {
                $joined = false;
                break;
            }
        }
        if (!$joined) {
            $kb = [];
            foreach ($settings['fj_channels'] as $ch) {
                $url = str_starts_with($ch, '@') ? "https://t.me/" . substr($ch, 1) : $ch;
                $kb[] = [['text' => 'Join Channel', 'url' => $url]];
            }
            $kb[] = [['text' => 'Check Joined', 'callback_data' => 'check_fj']];
            send_message($chat_id, "⚠️ Please join our channels to continue using the bot:", ['inline_keyboard' => $kb]);
            return;
        }
    }

    // রেফারেল প্রোসেস
    if (str_starts_with($text, '/start ')) {
        $ref_by = (int)substr($text, 7);
        if ($ref_by !== $chat_id && isset($db_data['user_data'][$ref_by]) && !isset($user['referred_by'])) {
            $user['referred_by'] = $ref_by;
            $db_data['user_data'][$ref_by]['balance'] += $settings['refer_reward'];
            $db_data['user_data'][$ref_by]['total_refers']++;
            send_message($ref_by, "🎁 <b>New Referral!</b>\nYou received {$settings['refer_reward']} TK.");
        }
        $text = '/start';
    }

    // স্টেট মেশিন প্রসেসিং
    $state = $db_data['user_states'][$chat_id] ?? '';
    if (!empty($state) && !in_array($text, ['Number', 'Custom Number', 'Live Support Admin', 'Referral', 'Withdrawal', '/start'])) {
        
        // Custom Number বা Search range ইনপুট
        if ($state === 'wait_for_search' && !empty($text)) {
            unset($db_data['user_states'][$chat_id]);
            $query = preg_replace('/\D/', '', $text);
            $found_numbers = [];
            
            foreach ($db_data['number_batches'] as $bid => &$batch) {
                foreach ($batch['numbers'] as $n_idx => $n_obj) {
                    $clean_num = str_replace('+', '', $n_obj['num']);
                    if (str_starts_with($clean_num, $query)) {
                        $found_numbers[] = [
                            'bid' => $bid,
                            'idx' => $n_idx,
                            'num' => $n_obj['num']
                        ];
                        if (count($found_numbers) >= $settings['num_req']) break 2;
                    }
                }
            }
            
            if (!empty($found_numbers)) {
                $assigned = [];
                foreach ($found_numbers as $f_item) {
                    $bid = $f_item['bid'];
                    $n_idx = $f_item['idx'];
                    $assigned[] = $f_item['num'];
                    
                    $db_data['number_batches'][$bid]['numbers'][$n_idx]['shares']++;
                    if ($db_data['number_batches'][$bid]['numbers'][$n_idx]['shares'] >= $settings['num_share']) {
                        $db_data['used_numbers_list'][] = $f_item['num'];
                        unset($db_data['number_batches'][$bid]['numbers'][$n_idx]);
                    }
                }
                foreach ($db_data['number_batches'] as $bid => &$batch) {
                    $batch['numbers'] = array_values($batch['numbers']);
                }
                
                $db_data['user_active_sessions'][$chat_id] = ['nums' => $assigned, 'time' => time()];
                
                $display = implode("\n", $assigned);
                send_message($chat_id, "✅ <b>Numbers Assigned:</b>\n\n<code>$display</code>", [
                    'inline_keyboard' => [
                        [['text' => 'Get OTP', 'url' => $settings['otp_link']]],
                        [['text' => 'Expire Number', 'callback_data' => 'expire_num']]
                    ]
                ]);
            } else {
                send_message($chat_id, "❌ No numbers available starting with '$query'.");
            }
            return;
        }

        // লোকাল ফাইল প্রোসেসিং স্টেটসমুহ
        if ($state === 'wait_for_service' && !empty($text)) {
            $db_data['temp_data'][$chat_id]['service'] = strtoupper($text);
            $db_data['user_states'][$chat_id] = 'wait_for_country';
            send_message($chat_id, "🌍 Enter the country name (e.g., BANGLADESH):");
            return;
        }

        if ($state === 'wait_for_country' && !empty($text)) {
            unset($db_data['user_states'][$chat_id]);
            $country = strtoupper($text);
            $service = $db_data['temp_data'][$chat_id]['service'];
            $raw_numbers = $db_data['temp_data'][$chat_id]['numbers'];
            $filename = $db_data['temp_data'][$chat_id]['filename'];
            unset($db_data['temp_data'][$chat_id]);
            
            $clean_nums = [];
            foreach ($raw_numbers as $num) {
                $num = trim($num);
                if (!empty($num)) {
                    if (!str_starts_with($num, '+')) $num = '+' . $num;
                    $clean_nums[] = ['num' => $num, 'shares' => 0, 'used_by' => []];
                }
            }
            
            $batch_id = uniqid();
            $db_data['number_batches'][$batch_id] = [
                'filename' => $filename,
                'service' => $service,
                'country' => $country,
                'numbers' => $clean_nums
            ];
            
            send_message($chat_id, "✅ Successfully added " . count($clean_nums) . " numbers for $service ($country)!");
            return;
        }

        // অ্যাডমিন প্যানেল ক্রিয়েটর ও কনফিগ স্টেট হ্যান্ডলিং
        if ($state === 'wait_for_panel_name' && !empty($text)) {
            unset($db_data['user_states'][$chat_id]);
            $type = $db_data['temp_data'][$chat_id]['add_type'] === 'api' ? 'API Panel' : 'Auto Captcha Panel';
            unset($db_data['temp_data'][$chat_id]);
            
            $settings['panels'][] = [
                'name' => $text,
                'type' => $type,
                'status' => 'OFF',
                'api_url' => '',
                'token' => '',
                'login_url' => '',
                'username' => '',
                'password' => '',
                'msg_link' => '',
                'login_status' => '⏳ Pending Setup'
            ];
            send_message($chat_id, "✅ Provider '$text' added. Configure it from Panel Management.", main_menu($chat_id, $settings['admins']));
            return;
        }

        if (in_array($state, ['wait_for_p_api', 'wait_for_p_tok', 'wait_for_p_fapi', 'wait_for_p_lurl', 'wait_for_p_user', 'wait_for_p_pass', 'wait_for_p_mlink']) && !empty($text)) {
            unset($db_data['user_states'][$chat_id]);
            $idx = $db_data['temp_data'][$chat_id]['p_idx'];
            unset($db_data['temp_data'][$chat_id]);
            
            if ($state === 'wait_for_p_api') $settings['panels'][$idx]['api_url'] = $text;
            elseif ($state === 'wait_for_p_tok') $settings['panels'][$idx]['token'] = $text;
            elseif ($state === 'wait_for_p_fapi') $settings['panels'][$idx]['full_api_url'] = $text;
            elseif ($state === 'wait_for_p_lurl') $settings['panels'][$idx]['login_url'] = $text;
            elseif ($state === 'wait_for_p_user') $settings['panels'][$idx]['username'] = $text;
            elseif ($state === 'wait_for_p_pass') $settings['panels'][$idx]['password'] = $text;
            elseif ($state === 'wait_for_p_mlink') $settings['panels'][$idx]['msg_link'] = $text;

            send_message($chat_id, "✅ Setting updated successfully.");
            return;
        }

        // লাইভ সাপোর্ট ও সাপোর্ট রিপ্লাই হ্যান্ডলার
        if ($state === 'wait_for_support_msg' && !empty($text)) {
            unset($db_data['user_states'][$chat_id]);
            $admin_msg = "💬 <b>Live Support Message</b>\n\n"
                       . "👤 <b>User:</b> <a href='tg://user?id=$chat_id'>$chat_id</a>\n"
                       . "📝 <b>Message:</b> $text";
            
            $reply_btn = ['inline_keyboard' => [[['text' => 'Reply', 'callback_data' => "sup_reply_$chat_id"]]]];
            foreach ($settings['admins'] as $adm) {
                send_message($adm, $admin_msg, $reply_btn);
            }
            send_message($chat_id, "✅ Message sent to admin. Please wait for a reply!");
            return;
        }

        if ($state === 'wait_for_admin_reply' && !empty($text)) {
            unset($db_data['user_states'][$chat_id]);
            $target = $db_data['temp_data'][$chat_id]['target_user'];
            unset($db_data['temp_data'][$chat_id]);
            send_message($target, "💬 <b>Admin Reply:</b>\n\n$text");
            send_message($chat_id, "✅ Reply sent successfully!");
            return;
        }
        
        // উইথড্র অ্যামাউন্ট এবং নাম্বার ইনপুট
        if ($state === 'wait_for_withdraw_amt' && !empty($text)) {
            $amt = floatval($text);
            $min_w = floatval($settings['min_withdraw']);
            if ($amt < $min_w || $amt > $user['balance']) {
                send_message($chat_id, "❌ Invalid Amount. Minimum: $min_w TK. Your balance: {$user['balance']} TK. Enter again:");
                return;
            }
            $db_data['temp_data'][$chat_id]['w_amt'] = $amt;
            $db_data['user_states'][$chat_id] = 'wait_for_withdraw_num';
            send_message($chat_id, "📱 Enter your Account number:");
            return;
        }

        if ($state === 'wait_for_withdraw_num' && !empty($text)) {
            unset($db_data['user_states'][$chat_id]);
            $amt = $db_data['temp_data'][$chat_id]['w_amt'];
            $method = $db_data['temp_data'][$chat_id]['w_method'];
            unset($db_data['temp_data'][$chat_id]);

            $user['balance'] -= $amt;
            $req_id = 'W_' . uniqid();
            
            $db_data['pending_withdrawals'][$req_id] = [
                'user_id' => $chat_id,
                'amount' => $amt,
                'method' => $method,
                'number' => $text
            ];

            $admin_txt = "🎙 <b>New Withdrawal Request</b>\n\n"
                       . "👤 <b>User:</b> <a href='tg://user?id=$chat_id'>$chat_id</a>\n"
                       . "🏦 <b>Method:</b> $method\n"
                       . "💳 <b>Amount:</b> $amt TK\n"
                       . "🍏 <b>Account:</b> <code>$text</code>\n\n"
                       . "🧾 <b>Req ID:</b> <code>$req_id</code>";

            $approve_kb = ['inline_keyboard' => [[
                ['text' => 'APPROVE', 'callback_data' => "wapp_$req_id"],
                ['text' => 'REJECT', 'callback_data' => "wrej_$req_id"]
            ]]];

            if (!empty($settings['w_group'])) {
                send_message($settings['w_group'], $admin_txt, $approve_kb);
            }
            foreach ($settings['admins'] as $adm) {
                send_message($adm, $admin_txt, $approve_kb);
            }

            send_message($chat_id, "✅ Withdrawal Request sent. ID: <code>$req_id</code>");
            return;
        }

        // ওটিপি গ্রুপ সেটিংস আপডেটারস
        if (in_array($state, ['wait_for_add_fw_id', 'wait_for_otp_link', 'wait_for_main_channel', 'wait_for_menu_text']) && !empty($text)) {
            unset($db_data['user_states'][$chat_id]);
            if ($state === 'wait_for_add_fw_id') {
                $settings['fw_groups'][] = ['chat_id' => $text, 'buttons' => []];
                send_message($chat_id, "✅ Forward group added.");
            } elseif ($state === 'wait_for_otp_link') {
                $settings['otp_link'] = $text;
                send_message($chat_id, "✅ OTP Group Link updated.");
            } elseif ($state === 'wait_for_main_channel') {
                $settings['main_channel'] = $text;
                send_message($chat_id, "✅ Main Channel Link updated.");
            } elseif ($state === 'wait_for_menu_text') {
                $key = $db_data['temp_data'][$chat_id]['menu_key'];
                unset($db_data['temp_data'][$chat_id]);
                $settings['custom_messages'][$key]['text'] = $text;
                send_message($chat_id, "✅ Menu text updated.");
            }
            return;
        }

        if ($state === 'wait_for_add_fw_btn' && !empty($text)) {
            unset($db_data['user_states'][$chat_id]);
            $idx = $db_data['temp_data'][$chat_id]['fw_idx'];
            unset($db_data['temp_data'][$chat_id]);
            
            $parts = explode('-', $text, 2);
            if (count($parts) === 2) {
                $settings['fw_groups'][$idx]['buttons'][] = ['text' => trim($parts[0]), 'url' => trim($parts[1])];
                send_message($chat_id, "✅ Custom button added.");
            } else {
                send_message($chat_id, "❌ Invalid format. Use: Button Text - https://link.com");
            }
            return;
        }
    }

    // মেনু কমান্ডসমূহ
    if ($text === '/start') {
        $msg = $settings['custom_messages']['start']['text'];
        send_message($chat_id, $msg, main_menu($chat_id, $settings['admins']));
    } 
    elseif ($text === 'Number') {
        $services = [];
        foreach ($db_data['number_batches'] as $batch) {
            if (!empty($batch['numbers'])) $services[] = $batch['service'];
        }
        $services = array_unique($services);
        if (empty($services)) {
            send_message($chat_id, "❌ Currently no numbers available in local stock.");
            return;
        }
        $kb = [];
        foreach ($services as $srv) {
            $kb[] = [['text' => $srv, 'callback_data' => "g_s_$srv"]];
        }
        send_message($chat_id, "📍 Choose Service to see available countries:", ['inline_keyboard' => $kb]);
    } 
    elseif ($text === 'Custom Number') {
        $db_data['user_states'][$chat_id] = 'wait_for_search';
        send_message($chat_id, "🔎 Enter Range prefix digits (e.g. 880 or 236):", ['inline_keyboard' => [[['text' => 'Cancel', 'callback_data' => 'cancel_state']]]]);
    } 
    elseif ($text === 'Live Support Admin') {
        $db_data['user_states'][$chat_id] = 'wait_for_support_msg';
        send_message($chat_id, "📝 Send your message for support:", ['inline_keyboard' => [[['text' => 'Cancel', 'callback_data' => 'cancel_state']]]]);
    }
    elseif ($text === 'Referral') {
        $ref_link = "https://t.me/" . BOT_USERNAME . "?start=" . $chat_id;
        $msg = str_replace(
            ['{ref_link}', '{total_ref}', '{ref_reward}'], 
            [$ref_link, $user['total_refers'], $settings['refer_reward']], 
            $settings['custom_messages']['refer']['text']
        );
        send_message($chat_id, $msg, ['inline_keyboard' => [[['text' => 'Copy Link', 'copy_text' => ['text' => $ref_link]]]]]);
    } 
    elseif ($text === 'Withdrawal') {
        $msg = str_replace(['{bal}', '{min_w}'], [$user['balance'], $settings['min_withdraw']], $settings['custom_messages']['withdrawal']['text']);
        $kb = [];
        if ($settings['withdraw_on']) {
            foreach ($settings['w_methods'] as $m) {
                $kb[] = [['text' => "Withdraw via $m", 'callback_data' => "w_req_$m"]];
            }
        }
        send_message($chat_id, $msg, ['inline_keyboard' => $kb]);
    } 
    elseif ($text === 'Admin Panel' && (in_array($chat_id, $settings['admins']) || $chat_id == OWNER_ID)) {
        send_message($chat_id, get_admin_text($db_data), admin_panel_keyboard());
    }
}

// বাটন ক্লিক রাউটার (Callback Queries)
function handle_callback($call, &$db_data) {
    $chat_id = $call['message']['chat']['id'];
    $data = $call['data'];
    $msg_id = $call['message']['message_id'];
    $settings = &$db_data['bot_settings'];

    if ($data === 'close_msg') {
        delete_message($chat_id, $msg_id);
    } 
    elseif ($data === 'cancel_state') {
        unset($db_data['user_states'][$chat_id]);
        unset($db_data['temp_data'][$chat_id]);
        delete_message($chat_id, $msg_id);
    } 
    elseif ($data === 'expire_num') {
        unset($db_data['user_active_sessions'][$chat_id]);
        edit_message($chat_id, $msg_id, "❌ Number session expired.");
    } 
    elseif ($data === 'system_settings') {
        edit_message($chat_id, $msg_id, "⚙️ <b>System Settings Menu</b>", system_settings_keyboard());
    } 
    elseif ($data === 'back_to_admin') {
        edit_message($chat_id, $msg_id, get_admin_text($db_data), admin_panel_keyboard());
    }
    elseif ($data === 'manage_fj') {
        edit_message($chat_id, $msg_id, "🔗 <b>Force Join Settings</b>", fj_settings_keyboard($settings));
    } 
    elseif ($data === 'toggle_fj') {
        $settings['fj_on'] = !$settings['fj_on'];
        edit_message($chat_id, $msg_id, "🔗 <b>Force Join Settings</b>", fj_settings_keyboard($settings));
    } 
    elseif ($data === 'add_fj') {
        $db_data['user_states'][$chat_id] = 'wait_for_add_fj';
        edit_message($chat_id, $msg_id, "📝 Send Channel Username (e.g. @MyChannel):", ['inline_keyboard' => [[['text' => 'Cancel', 'callback_data' => 'cancel_state']]]]);
    }
    elseif (str_starts_with($data, 'del_fj_')) {
        $idx = (int)str_replace('del_fj_', '', $data);
        unset($settings['fj_channels'][$idx]);
        $settings['fj_channels'] = array_values($settings['fj_channels']);
        edit_message($chat_id, $msg_id, "🔗 <b>Force Join Settings</b>", fj_settings_keyboard($settings));
    }
    elseif ($data === 'manage_admins') {
        edit_message($chat_id, $msg_id, "👤 <b>Admin Management</b>", admin_settings_keyboard($settings));
    } 
    elseif ($data === 'add_adm') {
        $db_data['user_states'][$chat_id] = 'wait_for_add_adm';
        edit_message($chat_id, $msg_id, "📝 Send User ID of the new admin:", ['inline_keyboard' => [[['text' => 'Cancel', 'callback_data' => 'cancel_state']]]]);
    }
    elseif (str_starts_with($data, 'del_adm_')) {
        $idx = (int)str_replace('del_adm_', '', $data);
        unset($settings['admins'][$idx]);
        $settings['admins'] = array_values($settings['admins']);
        edit_message($chat_id, $msg_id, "👤 <b>Admin Management</b>", admin_settings_keyboard($settings));
    }
    elseif ($data === 'dxa_control') {
        edit_message($chat_id, $msg_id, "🕹 <b>RAHIN CONTROL PANEL</b>", dxa_control_keyboard($settings));
    }
    elseif ($data === 'dxa_toggle_w') {
        $settings['withdraw_on'] = !$settings['withdraw_on'];
        edit_message($chat_id, $msg_id, "🕹 <b>RAHIN CONTROL PANEL</b>", dxa_control_keyboard($settings));
    }
    elseif (str_starts_with($data, 'w_req_')) {
        $method = str_replace('w_req_', '', $data);
        $db_data['temp_data'][$chat_id] = ['w_method' => $method];
        $db_data['user_states'][$chat_id] = 'wait_for_withdraw_amt';
        edit_message($chat_id, $msg_id, "💰 Enter amount to withdraw (Min: {$settings['min_withdraw']} TK):", ['inline_keyboard' => [[['text' => 'Cancel', 'callback_data' => 'cancel_state']]]]);
    }
    elseif (str_starts_with($data, 'sup_reply_')) {
        $target = str_replace('sup_reply_', '', $data);
        $db_data['user_states'][$chat_id] = 'wait_for_admin_reply';
        $db_data['temp_data'][$chat_id] = ['target_user' => $target];
        send_message($chat_id, "📝 Enter support reply for user <code>$target</code>:");
    }
    elseif (str_starts_with($data, 'wapp_')) {
        $req_id = str_replace('wapp_', '', $data);
        if (isset($db_data['pending_withdrawals'][$req_id])) {
            $req = $db_data['pending_withdrawals'][$req_id];
            send_message($req['user_id'], "✅ Your withdrawal request for {$req['amount']} TK has been <b>APPROVED</b>!");
            unset($db_data['pending_withdrawals'][$req_id]);
            edit_message($chat_id, $msg_id, "✅ Withdrawal Approved!");
        }
    }
    elseif (str_starts_with($data, 'wrej_')) {
        $req_id = str_replace('wrej_', '', $data);
        if (isset($db_data['pending_withdrawals'][$req_id])) {
            $req = $db_data['pending_withdrawals'][$req_id];
            $db_data['user_data'][$req['user_id']]['balance'] += $req['amount'];
            send_message($req['user_id'], "❌ Your withdrawal request for {$req['amount']} TK has been <b>REJECTED</b> and refunded.");
            unset($db_data['pending_withdrawals'][$req_id]);
            edit_message($chat_id, $msg_id, "❌ Withdrawal Rejected.");
        }
    }
    
    // ==========================================
    // প্যানেল ম্যানেজমেন্ট ব্যাকএন্ড রাউটিং (নতুন যুক্তকৃত)
    // ==========================================
    elseif ($data === 'manage_panels') {
        $api_count = 0; $cpt_count = 0;
        foreach ($settings['panels'] as $p) {
            if (($p['type'] ?? 'API Panel') === 'API Panel') $api_count++;
            else $cpt_count++;
        }
        $kb = ['inline_keyboard' => [
            [['text' => "Manage API Panels ($api_count)", 'callback_data' => 'manage_api_panels']],
            [['text' => "Manage Auto Captcha Panels ($cpt_count)", 'callback_data' => 'manage_cpt_panels']],
            [['text' => 'Back to System', 'callback_data' => 'system_settings']]
        ]];
        edit_message($chat_id, $msg_id, "⚙️ <b>Panel Management</b>\nSelect which type of panel system you want to manage:", $kb);
    }
    elseif ($data === 'manage_api_panels' || $data === 'manage_cpt_panels') {
        $p_type = ($data === 'manage_api_panels') ? 'API Panel' : 'Auto Captcha Panel';
        edit_message($chat_id, $msg_id, "⚙️ <b>Manage $p_type Providers</b>", typed_panels_list_keyboard($settings['panels'], $p_type));
    }
    elseif ($data === 'add_api_panel' || $data === 'add_cpt_panel') {
        $p_type = ($data === 'add_api_panel') ? 'api' : 'logc';
        $db_data['user_states'][$chat_id] = 'wait_for_panel_name';
        $db_data['temp_data'][$chat_id] = ['add_type' => $p_type];
        edit_message($chat_id, $msg_id, "📝 Enter the name of the new provider:", ['inline_keyboard' => [[['text' => 'Cancel', 'callback_data' => 'manage_panels']]]]);
    }
    elseif (str_starts_with($data, 'conf_pnl_')) {
        $idx = (int)str_replace('conf_pnl_', '', $data);
        edit_message($chat_id, $msg_id, "⚙️ <b>Configure: {$settings['panels'][$idx]['name']}</b>", panel_config_keyboard($settings['panels'], $idx));
    }
    elseif (str_starts_with($data, 'tog_pnl_')) {
        $idx = (int)str_replace('tog_pnl_', '', $data);
        $settings['panels'][$idx]['status'] = ($settings['panels'][$idx]['status'] === 'ON') ? 'OFF' : 'ON';
        edit_message($chat_id, $msg_id, "⚙️ <b>Configure: {$settings['panels'][$idx]['name']}</b>", panel_config_keyboard($settings['panels'], $idx));
    }
    elseif (str_starts_with($data, 'set_p_api_') || str_starts_with($data, 'set_p_tok_') || str_starts_with($data, 'set_p_fapi_') || str_starts_with($data, 'set_p_lurl_') || str_starts_with($data, 'set_p_user_') || str_starts_with($data, 'set_p_pass_') || str_starts_with($data, 'set_p_mlink_')) {
        $parts = explode('_', $data);
        $idx = (int)end($parts);
        $key = implode('_', array_slice($parts, 0, -1));
        
        $state_map = [
            'set_p_api' => 'wait_for_p_api', 'set_p_tok' => 'wait_for_p_tok', 'set_p_fapi' => 'wait_for_p_fapi',
            'set_p_lurl' => 'wait_for_p_lurl', 'set_p_user' => 'wait_for_p_user', 'set_p_pass' => 'wait_for_p_pass',
            'set_p_mlink' => 'wait_for_p_mlink'
        ];
        
        $db_data['user_states'][$chat_id] = $state_map[$key];
        $db_data['temp_data'][$chat_id] = ['p_idx' => $idx];
        edit_message($chat_id, $msg_id, "📝 Send the new value for this setting:", ['inline_keyboard' => [[['text' => 'Cancel', 'callback_data' => "conf_pnl_$idx"]]]]);
    }
    
    // ==========================================
    // ওটিপি গ্রুপ ম্যানেজমেন্ট (নতুন যুক্তকৃত)
    // ==========================================
    elseif ($data === 'manage_otp_groups') {
        edit_message($chat_id, $msg_id, "🛡 <b>OTP Group Management</b>", otp_groups_list_keyboard($settings));
    }
    elseif ($data === 'add_fw') {
        $db_data['user_states'][$chat_id] = 'wait_for_add_fw_id';
        edit_message($chat_id, $msg_id, "📝 Send Group ID or Username to forward updates:", ['inline_keyboard' => [[['text' => 'Cancel', 'callback_data' => 'manage_otp_groups']]]]);
    }
    elseif (str_starts_with($data, 'manage_fw_')) {
        $idx = (int)str_replace('manage_fw_', '', $data);
        edit_message($chat_id, $msg_id, "🛡 <b>Manage Group:</b> " . $settings['fw_groups'][$idx]['chat_id'], specific_fw_group_keyboard($settings, $idx));
    }
    elseif (str_starts_with($data, 'add_fwbtn_')) {
        $idx = (int)str_replace('add_fwbtn_', '', $data);
        $db_data['user_states'][$chat_id] = 'wait_for_add_fw_btn';
        $db_data['temp_data'][$chat_id] = ['fw_idx' => $idx];
        edit_message($chat_id, $msg_id, "📝 Send Custom Inline Button format:\n<code>Button Text - https://link.com</code>", ['inline_keyboard' => [[['text' => 'Cancel', 'callback_data' => "manage_fw_$idx"]]]]);
    }
    elseif (str_starts_with($data, 'del_fwbtn_')) {
        $parts = explode('_', $data);
        $idx = (int)$parts[2]; $b_idx = (int)$parts[3];
        unset($settings['fw_groups'][$idx]['buttons'][$b_idx]);
        $settings['fw_groups'][$idx]['buttons'] = array_values($settings['fw_groups'][$idx]['buttons']);
        edit_message($chat_id, $msg_id, "🛡 <b>Manage Group:</b> " . $settings['fw_groups'][$idx]['chat_id'], specific_fw_group_keyboard($settings, $idx));
    }
    elseif (str_starts_with($data, 'del_fw_')) {
        $idx = (int)str_replace('del_fw_', '', $data);
        unset($settings['fw_groups'][$idx]);
        $settings['fw_groups'] = array_values($settings['fw_groups']);
        edit_message($chat_id, $msg_id, "🛡 <b>OTP Group Management</b>", otp_groups_list_keyboard($settings));
    }
    elseif ($data === 'edit_otp_link' || $data === 'edit_main_channel') {
        $db_data['user_states'][$chat_id] = ($data === 'edit_otp_link') ? 'wait_for_otp_link' : 'wait_for_main_channel';
        edit_message($chat_id, $msg_id, "📝 Send the new URL Link:", ['inline_keyboard' => [[['text' => 'Cancel', 'callback_data' => 'manage_otp_groups']]]]);
    }
    
    // ==========================================
    // মেনু ডিজাইন এবং অন্যান্য সেটিংস (নতুন যুক্তকৃত)
    // ==========================================
    elseif ($data === 'menu_design_list') {
        edit_message($chat_id, $msg_id, "🎨 <b>Menu Design Editor</b>\nSelect a menu block to edit its body text:", menu_design_list_keyboard());
    }
    elseif (str_starts_with($data, 'md_edit_')) {
        $key = str_replace('md_edit_', '', $data);
        $db_data['user_states'][$chat_id] = 'wait_for_menu_text';
        $db_data['temp_data'][$chat_id] = ['menu_key' => $key];
        edit_message($chat_id, $msg_id, "🎨 <b>Editing: " . strtoupper($key) . "</b>\n\nSend the new HTML formatted text:", ['inline_keyboard' => [[['text' => 'Cancel', 'callback_data' => 'menu_design_list']]]]);
    }
    elseif ($data === 'md_reset_defaults') {
        global $default_settings;
        $settings['custom_messages'] = $default_settings['custom_messages'];
        answer_callback($call['id'], "✅ Messages reset to defaults!", true);
    }
    
    // লোকাল ফাইল ডিলিট
    elseif ($data === 'delete_files') {
        $kb = [];
        foreach ($db_data['number_batches'] as $bid => $batch) {
            $kb[] = [['text' => "Del: " . $batch['filename'] . " (" . count($batch['numbers']) . ")", 'callback_data' => "del_b_$bid"]];
        }
        $kb[] = [['text' => 'Back', 'callback_data' => 'back_to_admin']];
        edit_message($chat_id, $msg_id, "🗑 Select a file to delete:", ['inline_keyboard' => $kb]);
    }
    elseif (str_starts_with($data, 'del_b_')) {
        $bid = str_replace('del_b_', '', $data);
        unset($db_data['number_batches'][$bid]);
        answer_callback($call['id'], "✅ File deleted!", true);
        
        $kb = [];
        foreach ($db_data['number_batches'] as $bid => $batch) {
            $kb[] = [['text' => "Del: " . $batch['filename'] . " (" . count($batch['numbers']) . ")", 'callback_data' => "del_b_$bid"]];
        }
        $kb[] = [['text' => 'Back', 'callback_data' => 'back_to_admin']];
        edit_message($chat_id, $msg_id, "🗑 Select a file to delete:", ['inline_keyboard' => $kb]);
    }
}