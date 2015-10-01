<?php

namespace CJSDevelopment;

use \SimpleXMLElement;

class CMtextConnector
{
     public function __construct() {
        return $this;
    }

    static private function buildMessageXml($recipient, $message) {
        $xml = new SimpleXMLElement('<MESSAGES/>');

        $authentication = $xml->addChild('AUTHENTICATION');
        $authentication->addChild('PRODUCTTOKEN', config("cmtextconnector.product_token"));

        $msg = $xml->addChild('MSG');
        $msg->addChild('FROM', config("cmtextconnnector.company_name"));
        $msg->addChild('TO', $recipient);
        $msg->addChild('BODY', $message);

        return $xml->asXML();
    }

    static public function sendMessage($recipient, $message) {
        $gateway = "https://sgw01.cm.nl/gateway.ashx";

        $xml = self::buildMessageXml($recipient, $message);

        // Let us first check if the standard gateway is live.
        $gatewayCheck = get_headers($gateway);
        if ($gatewayCheck[0] != "HTTP/1.1 200 OK") {
            $gateway = "https://sgw02.cm.nl/gateway.ashx";
        }

        // Send the request
        $ch = curl_init(); // cURL v7.18.1+ and OpenSSL 0.9.8j+ are required
        curl_setopt_array($ch, array(
                CURLOPT_URL            => $gateway,
                CURLOPT_HTTPHEADER     => array('Content-Type: application/xml'),
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $xml,
                CURLOPT_RETURNTRANSFER => true
            )
        );

        $response = curl_exec($ch);

        curl_close($ch);

        return $response;
    }
}