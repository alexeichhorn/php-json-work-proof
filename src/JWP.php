<?php

namespace JSONWorkProof;

class DecodeException extends \Exception {}
class InvalidFormatException extends DecodeException {}
class InvalidProofException extends DecodeException {}
class ExpiredException extends DecodeException {}



// - Helper

function base64url_decode($data) { 
    return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT)); 
}

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}



class JWP {
    private $difficulty;
    private $salt_length;

    public function __construct($difficulty = 20, $salt_length = 16) {
        $this->difficulty = $difficulty;
        $this->salt_length = $salt_length;
    }


    private function is_zero_prefixed($data, $bit_count) {
        $bytes = (int)($bit_count / 8 + (8 - ($bit_count % 8)) / 8);
        $data = substr($data, 0, $bytes);
        $last = $bytes - 1;

        return substr($data, 0, $last) == str_repeat("\x00", $last) &&
            ord(substr($data, -1)) >> ($bytes * 8 - $bit_count) == 0;
    }
    
    public function decode($stamp, $verify = true) {

        $components = explode(".", $stamp);
        if (count($components) != 3) throw new InvalidFormatException();

        $encoded_header = $components[0];
        $encoded_body = $components[1];

        $header_data = base64url_decode($encoded_header);
        $body_data = base64url_decode($encoded_body);

        $header = json_decode($header_data);
        $body = json_decode($body_data);

        if (!$verify) return $body;

        // - check proof

        $digest = hash('sha256', $stamp, true);

        if (!$this->is_zero_prefixed($digest, $this->difficulty)) {
            throw new InvalidProofException();
        }


        // - check expiration range

        date_default_timezone_set("UTC");
        $default_timerange_start = time(); //new \Datetime('now', nw \DateTimeZone("UTC"));
        $default_timerange_end = time() + 1800; // new \Datetime('now') + 1800;

        if (!isset($body->exp)) throw new ExpiredException;

        $expiration = floatval($body->exp);

        if ($expiration <= $default_timerange_start || $expiration >= $default_timerange_end) {
            throw new ExpiredException;
        }

        return $body;
    }

}

// to remove
//$jwp = new JWP(20);
//$stamp = "eyJ0eXAiOiAiSldQIiwgImFsZyI6ICJTSEEyNTYiLCAiZGlmIjogMjB9.eyJoaSI6IDMsICJleHAiOiAxNjE2OTU4NDY5LjI3ODA3MX0.8-rRCc2aCNmJXzTdDBGzoAK_-p";
//var_dump($jwp->decode($stamp));

?>