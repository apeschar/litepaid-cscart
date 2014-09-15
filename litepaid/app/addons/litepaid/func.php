<?php
/**
 * @author Albert Peschar <albert@peschar.net>
 */

use Tygh\Http;
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_litepaid_install()
{
    db_query("DELETE FROM ?:payment_processors WHERE processor_script = ?s", "litepaid.php");

    $_data = array(
        'processor' => 'LitePaid',
        'processor_script' => 'litepaid.php',
        'processor_template' => 'views/orders/components/payments/cc_outside.tpl',
        'admin_template' => 'litepaid.tpl',
        'callback' => 'Y',
        'type' => 'P',
    );

    db_query("INSERT INTO ?:payment_processors ?e", $_data);
}

function fn_litepaid_uninstall()
{
    db_query("DELETE FROM ?:payment_processors WHERE processor_script = ?s", "litepaid.php");
}
