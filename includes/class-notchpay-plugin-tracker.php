<?php

// Add a prefix to this class name to avoid conflict with other plugins
class notchpay_give_plugin_tracker {
    var $public_key;
    var $plugin_name;
    function __construct($plugin, $pk){
        //configure plugin name
        //configure public key
        $this->plugin_name = $plugin;
        $this->public_key = $pk;
    }

   

    function log_transaction_success($trx_ref){
        //send reference to logger along with plugin name
         $url = "https://api.notchpay.co/log/charge_success";

    
        
         $body = wp_json_encode( [
             'plugin_name'  => $this->plugin_name,
             'transaction_reference' => $trx_ref,
         ] );
         
         $options = [
             'body'        => $body,
             'headers'     => [
                 'Content-Type' => 'application/json',
             ],
             'timeout'     => 60,
             'redirection' => 5,
             'blocking'    => true,
             'httpversion' => '1.0',
             'sslverify'   => false,
             'data_format' => 'body',
         ];
         
         wp_remote_post( $url, $options );
    }
}

?>