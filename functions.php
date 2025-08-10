<?php
if (!defined('pp_allowed_access')) { die('Direct access not allowed'); }

add_action('pp_transaction_ipn', 'send_sms_notification_transaction_ipn');
add_action('pp_invoice_ipn', 'send_sms_notification_invoice_ipn');

function send_sms_notification_transaction_ipn($transaction_id) {
    global $conn;
    $transaction_details = pp_get_transation($transaction_id);
    $plugin_slug = 'sms-notification';
    $sms_settings = pp_get_plugin_setting($plugin_slug);
    if (empty($sms_settings)) { return; }

    // Validate mobile
    $mobile_number_raw = $transaction_details['response'][0]['c_email_mobile'] ?? '';
    $mobile_number = validate_mobile_number($mobile_number_raw);
    if (!$mobile_number) { return; }

    // Check enable
    if (!isset($sms_settings['enable_transaction_complete'])) { return; }

    // Message
    $t = $transaction_details['response'][0];
    $message = "Dear {$t['c_name']},\n";
    $message .= "Your transaction #{$transaction_id} has been processed.\n";
    $message .= "Amount: {$t['transaction_amount']} {$t['transaction_currency']}\n";
    $message .= "Status: {$t['transaction_status']}\n";
    $message .= "Date: {$t['created_at']}\n";
    $message .= "Thank you!";

    // Send
    route_sms_send($sms_settings, $mobile_number, $message);
}

function send_sms_notification_invoice_ipn($invoice_id) {
    global $conn;
    $invoice_details = pp_get_invoice($invoice_id);
    $invoice_items   = pp_get_invoice_items($invoice_id);

    $plugin_slug = 'sms-notification';
    $sms_settings = pp_get_plugin_setting($plugin_slug);
    if (empty($sms_settings)) { return; }

    // Validate mobile
    $mobile_number_raw = $invoice_details['response'][0]['c_email_mobile'] ?? '';
    $mobile_number = validate_mobile_number($mobile_number_raw);
    if (!$mobile_number) { return; }

    // Compute total from items
    $total_amount = 0;
    if (!empty($invoice_items['response'])) {
        foreach ($invoice_items['response'] as $item) {
            $item_total  = floatval($item['amount']) * floatval($item['quantity']);
            $item_discount = min(floatval($item['discount']), $item_total);
            $after_discount = $item_total - $item_discount;
            $item_vat = $after_discount * (floatval($item['vat']) / 100);
            $total_amount += $after_discount + $item_vat;
        }
    }
    if (isset($invoice_details['response'][0]['i_amount_shipping'])) {
        $total_amount += floatval($invoice_details['response'][0]['i_amount_shipping']);
    }

    $i = $invoice_details['response'][0];
    $send_sms = false;
    $message = "";

    switch($i['i_status']) {
        case 'unpaid':
            if (isset($sms_settings['enable_invoice_created'])) {
                $send_sms = true;
                $message  = "Dear {$i['c_name']},\n";
                $message .= "Invoice #{$invoice_id} has been created.\n";
                $message .= "Amount: ".number_format($total_amount, 2)." {$i['i_currency']}\n";
                $message .= "Due Date: {$i['i_due_date']}\n";
                $message .= "Status: Unpaid\n";
                $payment_link = get_invoice_link($invoice_id);
                if ($payment_link) { $message .= "Pay now: ".$payment_link; }
            }
            break;

        case 'paid':
            if (isset($sms_settings['enable_invoice_paid'])) {
                $send_sms = true;
                $message  = "Dear {$i['c_name']},\n";
                $message .= "Invoice #{$invoice_id} has been paid.\n";
                $message .= "Amount: ".number_format($total_amount, 2)." {$i['i_currency']}\n";
                $message .= "Thank you for your payment!";
            }
            break;
    }

    if (!$send_sms) { return; }

    route_sms_send($sms_settings, $mobile_number, $message);
}

/**
 * Route to the selected gateway
 */
function route_sms_send($settings, $mobile, $message) {
    if (!isset($settings['sms_gateway'])) { return; }

    switch (strtolower($settings['sms_gateway'])) {
        case 'bulksmsbd':
            send_via_bulksmsbd($settings, $mobile, $message);
            break;
        case 'mimsms':
            send_via_mimsms($settings, $mobile, $message);
            break;
        case 'greenweb':
            send_via_greenweb($settings, $mobile, $message);
            break;
        case 'custom':
            send_via_custom_gateway($settings, $mobile, $message);
            break;
    }
}

/* ------------ SMS Gateway Implementations ------------ */

function send_via_bulksmsbd($settings, $mobile, $message) {
    $api_key  = $settings['bulksmsbd_api_key'] ?? '';
    $sender_id= $settings['bulksmsbd_sender_id'] ?? '';
    $type     = $settings['bulksmsbd_type'] ?? 'text';

    $url = "http://bulksmsbd.net/api/smsapi";
    $data = [
        "api_key"  => $api_key,
        "senderid" => $sender_id,
        "number"   => $mobile,
        "message"  => $message,
        "type"     => $type
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
}

function send_via_mimsms($settings, $mobile, $message) {
    $url = "https://api.mimsms.com/api/SmsSending/SMS";

    $data = [
        "UserName"        => $settings['mimsms_username'] ?? '',
        "Apikey"          => $settings['mimsms_api_key'] ?? '',
        "MobileNumber"    => $mobile,
        "CampaignId"      => null,
        "SenderName"      => $settings['mimsms_sender_id'] ?? '',
        "TransactionType" => "T",
        "Message"         => $message
    ];

    $payload = json_encode($data);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Accept: application/json"
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
}

function send_via_greenweb($settings, $mobile, $message) {
    $to    = $mobile;
    $token = $settings['greenweb_api_token'] ?? '';
    $url   = "https://api.bdbulksms.net/api.php?json";

    $data = [
        'to'      => $to,
        'message' => $message,
        'token'   => $token
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_ENCODING, '');
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
}

/**
 * Custom SMS Gateway (GET request with query parameters).
 * Example target:
 * https://example.com/api/send.php?key=API_KEY&number=NUMBER&message=MSG&option=DEVICE&type=sms&prioritize=0
 */
function send_via_custom_gateway($settings, $mobile, $message) {
    $base = trim($settings['custom_base_url'] ?? '');
    $api  = trim($settings['custom_api_key']  ?? '');
    $dev  = trim($settings['custom_device']   ?? '');

    if ($base === '' || $api === '') { return; }

    // Normalize base (no trailing '?')
    $base = rtrim($base, " \t\n\r\0\x0B");

    // Build query
    $query = [
        'key'        => $api,
        'number'     => $mobile,
        'message'    => $message,
        'option'     => $dev,     // device id
        'type'       => 'sms',
        'prioritize' => 0
    ];

    // Always send as GET per provided spec
    $finalUrl = $base . (strpos($base, '?') === false ? '?' : '&') . http_build_query($query);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $finalUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_exec($ch);
    curl_close($ch);
}

/* ------------ Helpers ------------ */

function validate_mobile_number($number) {
    // Remove any non-digit characters
    $number = preg_replace('/[^0-9]/', '', (string)$number);

    // Valid Bangladeshi numbers: 01[3-9]XXXXXXXX
    if (preg_match('/^(?:\+?88)?(01[3-9]\d{8})$/', $number, $m)) {
        return '880' . $m[1]; // Return 8801XXXXXXXXX format
    }
    return false;
}