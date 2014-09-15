<?php
/**
 * @author Albert Peschar <albert@peschar.net>
 */

use Tygh\Http;
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$redirect_timeout = "
    <script>
    window.setTimeout(function() {
        window.location = " . json_encode(fn_url('checkout.checkout')) . ";
    }, 5000);
    </script>
    <p>You will be redirected in 5 seconds.</p>
";

if (defined('PAYMENT_NOTIFICATION')) {
    if(!isset($_GET['order_id'])) {
        echo "<p>Order ID not specified.</p>", $redirect_timeout;
        exit;
    }
    $order_info = fn_get_order_info($_GET['order_id']);
    if(!$order_info) {
        echo "<p>Order not found.</p>", $redirect_timeout;
        exit;
    }
    $order_id = $order_info['order_id'];

    $processor_data = fn_get_payment_method_data($order_info['payment_id']);
    if(!$processor_data) {
        echo "<p>Payment method not found.</p>", $redirect_timeout;
        exit;
    }

    if(!isset($_GET['litepaid_id'])) {
        echo "<p>LitePaid ID not specified.</p>", $redirect_timeout;
        exit;
    }
    $litepaid_id = $_GET['litepaid_id'];
    if(empty($order_info['payment_info']['transaction_id'])
       || $order_info['payment_info']['transaction_id'] != $litepaid_id
    ) {
        echo "<p>LitePaid ID is incorrect.</p>", $redirect_timeout;
        exit;
    }

    $response = file_get_contents('https://www.litepaid.com/api?' . http_build_query(array(
        'key' => trim($processor_data['processor_params']['api_key']),
        'id'  => $litepaid_id,
    )));

    if(!$response || !($response = @json_decode($response, true))) {
        echo "<p>LitePaid API request failed. Contact support.</p>";
        echo $redirect_timeout;
        exit;
    }

    if(!empty($response['result']) && $response['result'] == 'success') {
        $payment_info = array(
            'order_status'   => 'P',
            'transaction_id' => $litepaid_id,
            'reason_text'    => isset($response['data']['error_name']) ? $response['data']['error_name'] : '',
        );

        fn_finish_payment($order_id, $payment_info, false);
        fn_order_placement_routines('route', $order_id);
    } else {
        fn_order_placement_routines('route', $order_id);
    }
} else {
    $data = array(
        'key' => trim($processor_data['processor_params']['api_key']),
        'value' => number_format($order_info['total'], 2, '.', ''),
        'return_url' => fn_url("payment_notification.return?payment=litepaid&order_id=$order_id", AREA, 'current'),
        'description' => 'Order #' . $order_id,
        'test' => !empty($processor_data['processor_params']['test_mode']) ? '1' : '0',
    );

    $response = file_get_contents('https://www.litepaid.com/api?' . http_build_query($data));

    if(!$response
       || !($response = @json_decode($response, true))
       || empty($response['result'])
       || $response['result'] != 'success'
       || empty($response['data']['invoice_token'])
    ) {
        echo "<p>LitePaid API request failed. Choose another payment method to complete your order.</p>";
        if(!empty($response['data']['error_name']))
            echo "<p><b>Error:</b> " . htmlentities($response['data']['error_name'], ENT_QUOTES, 'UTF-8') . "</p>";
        echo $redirect_timeout;
        exit;
    }

    $litepaid_id = $response['data']['invoice_token'];

    // store transaction id
    fn_update_order_payment_info($order_id, array(
        'transaction_id' => $litepaid_id,
    ));

    // redirect
    $url = 'https://www.litepaid.com/invoice/id:' . $litepaid_id;
    echo "<script>\nwindow.location = " . json_encode($url) . ";\n</script>\n";
}

exit;
