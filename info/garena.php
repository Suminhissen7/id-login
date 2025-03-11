<?php

class Garena {
    public $player;
    public $cookies;
    public $ua;
    public $secChUa;

    public function __construct() {
        require 'settings.php';

        $this->apiKey = $apiKey;
        $this->player = [];
        $this->cookies = [];

        // User-Agent লোড করা
        $uaList = file('user-agents.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (!empty($uaList)) {
            $this->ua = $uaList[array_rand($uaList)];
            $this->secChUa = $this->generateSecChUa($this->ua);
        }
    }

    private function generateSecChUa($userAgent) {
        // Chrome ভার্সন বের করা
        if (preg_match('/Chrome\/(\d+)/', $userAgent, $matches)) {
            $chromeVersion = $matches[1];
        } else {
            $chromeVersion = "124"; // ডিফল্ট Chrome ভার্সন
        }
return "\"Not(A:Brand\";v=\"99\", \"Chromium\";v=\"$chromeVersion\", \"Google Chrome\";v=\"$chromeVersion\"";

    }



    
    public function init() {
        $url = 'https://shop.garena.my/app';
        $headers = [
            'accept: */*',
            'accept-language: id-ID,id;q=0.9',
            'dnt: 1',
            'origin: https://shop.garena.my',
            'priority: u=1, i',
            'sec-ch-ua: '.$this->sechcha,
            'sec-ch-ua-mobile: ?0',
            'sec-ch-ua-platform: "Android"',
            'sec-fetch-dest: empty',
            'sec-fetch-mode: cors',
            'sec-fetch-site: same-origin',
            'user-agent: '.$this->ua
        ];

        $response = $this->fetch($url, null, $headers);
        $this->setCookiesFromHeader($response['header']);
        return true;
    }


----------------------------------------------------------------------------------------

    
    
    public function setDatadome() {
        $url = "https://api.apighor.com/datadome-solver/";
        $headers = [
            'Content-Type: application/json',
            'X-Api-Key: 8fdc3a581fd12d0d6cb8074c8eff6050'
        ];
        $response = $this->fetch($url, null, $headers);
        $datadome = json_decode($response['body'], true)['datadome'];
        
        $this->cookies['datadome'] = $datadome;

        return $this->cookies['datadome'];
    }


-------------------------------------------------------------------------------------------------------
    

    public function setPlayerId($player_id) {
        $url = 'https://shop.garena.my/api/auth/player_id_login';
        $postData = json_encode([
            'app_id' => 100067,
            'login_id' => $player_id,
            'app_server_id' => 0
        ]);
        $headers = [
            'Accept: application/json, text/plain, */*',
            'Accept-Encoding: gzip, deflate, br','Accept-Language: en-US,en;q=0.9',
            'Accept-Language: en-US,en;q=0.9',
            'Cache-Control: no-cache',
            'Connection: keep-alive',
            'Content-Length: ' . strlen($postData),
            'Content-Type: application/json',
            'Host: shop.garena.my',
            'Origin: https://shop.garena.my',
            'Pragma: no-cache',
            'Referer: https://shop.garena.my/?channel=202953',
            'Sec-Fetch-Dest: empty',
            'Sec-Fetch-Mode: cors',
            'Sec-Fetch-Site: same-origin',
            'User-Agent: '.$this->ua,
            'sec-ch-ua: '.$this->sechcha,
            'sec-ch-ua-mobile: ?1',
            'sec-ch-ua-platform: "Android"',
            'x-datadome-clientid: ' . $this->cookies['datadome'],
        ];
        $response = $this->fetch($url, $postData, $headers, $this->getCookie());

        $login = json_decode($response['body'], true);
        if (!empty($login)) {
            if (isset($login['error'])) {
                $this->respond(true, $login['error']);
            }
            
            if (array_key_exists('url', $login)) {
                $this->respond(true, 'captcha_error');
            }

            $this->setCookiesFromHeader($response['header']);

            return $this->regionVerify();
        }
    }


------------------------------------------------------------------------------------------

    
    public function checkPlayerId($player_id, $session_key) {
        $url = "https://shop.garena.my/api/auth/get_user_info/multi";
        $this->cookies['session_key'] = $session_key;

        $headers = [
            'Host: shop.garena.my',
            'Sec-Ch-Ua: "Chromium";v="127", "Not)A;Brand";v="99"',
            'Accept: application/json',
            'Accept-Language: en-US',
            'Sec-Ch-Ua-Mobile: ?0',
            'User-Agent: '.$this->ua,
            'Sec-Ch-Ua-Platform: "Windows"',
            'Sec-Fetch-Site: same-origin',
            'Sec-Fetch-Mode: cors',
            'Sec-Fetch-Dest: empty',
            'Priority: u=1, i',
            'Connection: keep-alive',
        ];
        $response = $this->fetch($url, null, $headers, $this->getCookie());
    
        $res = json_decode($response['body'], true);
        if ($res['player_id']['id_login']) {
            return $this->regionVerify();
        }
        
        return $this->setPlayerId($player_id);
    }


---------------------------------------------------------------------------------

    
    public function regionVerify() {
        $url = "https://shop.garena.my/api/shop/apps/roles?app_id=100067&region=MY&source=pc";
        $headers = [
            'Host: shop.garena.my',
            'Sec-Ch-Ua: "Chromium";v="127", "Not)A;Brand";v="99"',
            'Accept: application/json',
            'Accept-Language: en-US',
            'Sec-Ch-Ua-Mobile: ?0',
            'User-Agent: '.$this->ua,
            'Sec-Ch-Ua-Platform: "Windows"',
            'Sec-Fetch-Site: same-origin',
            'Sec-Fetch-Mode: cors',
            'Sec-Fetch-Dest: empty',
            'Referer: https://shop.garena.my/app/100067/buy/0',
            'Priority: u=1, i',
            'Connection: keep-alive',
        ];
        $response = $this->fetch($url, null, $headers, $this->getCookie());
    
        $res = json_decode($response['body'], true)['100067'][0] ?? null;
        if ($res) {
            if (isset($res['account_id'])) {
                $this->setCookiesFromHeader($response['header']);
                $this->player = $res;
                return true;
            } else {
                $this->respond(true, 'region_mismatch');
            }
        }
    
        $this->respond(true, 'cannot_fetch_region');
    }

    public function fetch($url, $postFields = null, $headers = [], $cookie = null) {
        $attempt = 0;
        $success = false;
        $response = null;
        $err = null;
        $logFile = 'log_requests.txt';
        
        while ($attempt < 3 && !$success) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            if ($postFields !== null) {
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            }
            if (!empty($headers)) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            }
            if ($cookie !== null) {
                curl_setopt($ch, CURLOPT_COOKIE, $cookie);
            }
    
            // Eksekusi request
            $response = curl_exec($ch);
            
            // Log permintaan
            $logData = "URL: " . $url . "\n";
            $logData .= "Headers: " . json_encode($headers, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
            $logData .= "Cookies: " . $cookie . "\n";

            if (is_array(json_decode($postFields, true))) {
                $postFields = json_decode($postFields, true);
                $logData .= "Post Fields: " . json_encode($postFields, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
            } else {
                $logData .= "Post Fields: " . $postFields . "\n";
            }
            $logData .= "Attempt: " . ($attempt + 1) . "\n";
            
            if (curl_errno($ch)) {
                $err = curl_error($ch);
                $attempt++;
                $logData .= "Error: " . $err . "\n\n";
            } else {
                $success = true;
            }
            
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $header = substr($response, 0, $header_size);
            $body = substr($response, $header_size);
            
            // Log response
            $logData .= "Status Code: " . $statusCode . "\n";
            $logData .= "Response Headers: " . $header . "\n";
            $logData .= "Response Body: " . $body . "\n\n";
            
            // Tulis ke log file
            // file_put_contents($logFile, $logData, FILE_APPEND);
            
            curl_close($ch);
        }
    
        if (!$success) {
            $this->respond(true, 'error_proxy_timeout: '.$err);
        }
    
        return ['status' => $statusCode, 'header' => $header, 'body' => $body];
    }    

    public function setCookiesFromHeader($header) {
        preg_match_all('/^set-cookie:\s*([^;]*)/mi', $header, $matches);
        $cookies = [];
        foreach($matches[1] as $item) {
            parse_str($item, $cookie);
            $cookies = array_merge($cookies, $cookie);
        }
        $this->cookies = array_merge($this->cookies, $cookies);
        return $this->cookies;
    }

    public function setCookie($name, $value) {
        $this->cookies[$name] = $value;
        return $this->cookies[$name]; 
    }

    public function getCookies() {
        return $this->cookies;
    }
    
    public function getCookie($cookieName = null) {
        if ($cookieName == null) {
            return http_build_query($this->cookies, '', '; ');
        } else {
            if (array_key_exists($cookieName, $this->cookies)) {
                return "$cookieName=".$this->cookies[$cookieName];
            }
        }
        return null;
    }

    public function authenticate() {
        $headers = getallheaders();
        if (isset($headers['X-Api-Key']) || isset($headers['x-api-key'])) {
            $authHeader = $headers['X-Api-Key'] ?? $headers['x-api-key'];
            $token = trim($authHeader);
            if ($token === $this->apiKey) {
                return true;
            }
        }
        return false;
    }
    
    public function respond($error, $message, $data = null) {
        header("Content-Type:application/json; charset=utf-8");
        $response = ['error' => $error, 'msg' => $message];
        if ($data) {
            $response['data'] = $data;
        }
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }
}
