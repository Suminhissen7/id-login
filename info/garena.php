<?php 

class Garena {

    public $player;
    public $cookies;
    public $ua;
    public $secChUa;
    public $apiKey; // ডিফাইন করা প্রপার্টি
    public $ddata;  // ডিফাইন করা প্রপার্টি

    public function __construct() {
        require 'settings.php';

        $this->apiKey = $apiKey;
        $this->ddata = $ddata;
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
            'sec-ch-ua: '.$this->secChUa,
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

    public function setDatadome() {
        $url = "https://dd.garena.com/js/";
        // Raw form-urlencoded data
        $postFields = $this->ddata;
        $headers = [
            'authority: dd.garena.com',
            'accept: */*',
            'accept-language: en-US,en;q=0.9',
            'cache-control: no-cache',
            'content-type: application/x-www-form-urlencoded',
            'origin: https://shop.garena.my',
            'pragma: no-cache',
            'referer: https://shop.garena.my/',
            'sec-ch-ua: '.$this->secChUa,
            'sec-ch-ua-mobile: ?1',
            'sec-ch-ua-platform: "Android"',
            'sec-fetch-dest: empty',
            'sec-fetch-mode: cors',
            'sec-fetch-site: cross-site',
            'user-agent: '.$this->ua
        ];

        // Send the request
        $response = $this->fetch($url, $postFields, $headers);

        // Decode the response to extract the cookie value
        $cookie = json_decode($response['body'], true)['cookie'] ?? null;

        // Extract the 'datadome' value from the cookie
        if ($cookie && preg_match('/datadome=([a-zA-Z0-9\-_]+)/', $cookie, $matches)) {
            $datadome = $matches[1];
            $this->cookies['datadome'] = $datadome;  // Store in the cookies array
        }

        return $this->cookies['datadome'] ?? null;
    }

    public function setPlayerId($player_id) {
        $url = 'https://shop.garena.my/api/auth/player_id_login';
        $postData = json_encode([
            'app_id' => 100067,
            'login_id' => $player_id,
            'app_server_id' => 0
        ]);

        $headers = [
            'Accept: application/json, text/plain, */*',
            'Accept-Encoding: gzip, deflate, br',
            'Accept-Language: en-US,en;q=0.9',
            'Cache-Control: no-cache',
            'Content-Length: ' . strlen($postData),
            'Content-Type: application/json',
            'Host: shop.garena.my',
            'Origin: https://shop.garena.my',
            'Referer: https://shop.garena.my/?channel=202953',
            'Sec-Fetch-Dest: empty',
            'Sec-Fetch-Mode: cors',
            'Sec-Fetch-Site: same-origin',
            'User-Agent: '.$this->ua,
            'sec-ch-ua: '.$this->secChUa,
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

    public function checkPlayerId($player_id, $session_key) {
        $url = "https://shop.garena.my/api/auth/get_user_info/multi";
        $this->cookies['session_key'] = $session_key;

        $headers = [
            'Accept: application/json, text/plain, */*',
            'Accept-Encoding: gzip, deflate, br',
            'Accept-Language: en-US,en;q=0.9',
            'Cache-Control: no-cache',
            'Connection: keep-alive',
            'Host: shop.garena.my',
            'Pragma: no-cache',
            'sec-ch-ua: '.$this->secChUa,
            'Referer: https://shop.garena.my/?channel=202278&item=100712',
            'Sec-Ch-Ua-Mobile: ?0',
            'User-Agent: '.$this->ua,
            'Sec-Ch-Ua-Platform: "Android"',
            'Sec-Fetch-Site: same-origin',
            'Sec-Fetch-Mode: cors',
            'Sec-Fetch-Dest: empty'
        ];

        $response = $this->fetch($url, null, $headers, $this->getCookie());

        $res = json_decode($response['body'], true);
        if (isset($res['player_id']['id_login'])) {
            return $this->regionVerify();
        }

        return $this->setPlayerId($player_id);
    }

    public function regionVerify() {
        $url = "https://shop.garena.my/api/shop/apps/roles?app_id=100067&region=MY&source=mb";
        $headers = [
            'Accept: application/json, text/plain, */*',
            'Accept-Encoding: gzip, deflate, br',
            'Accept-Language: en-US,en;q=0.9',
            'Cache-Control: no-cache',
            'Connection: keep-alive',
            'Host: shop.garena.my',
            'Pragma: no-cache',
            'Referer: https://shop.garena.my/?channel=202278&item=100712',
            'Sec-Fetch-Dest: empty',
            'Sec-Fetch-Mode: cors',
            'Sec-Fetch-Site: same-origin',
            'sec-ch-ua-mobile: ?1',
            'sec-ch-ua-platform: "Android"',
            'sec-ch-ua: '.$this->secChUa,
            'User-Agent: '.$this->ua
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
        // Your existing fetch logic
    }

    public function setCookiesFromHeader($header) {
        // Your existing setCookiesFromHeader logic
    }

    public function setCookie($name, $value) {
        $this->cookies[$name] = $value;
        return $this->cookies[$name]; 
    }

    public function getCookies() {
        return $this->cookies;
    }

    public function getCookie($cookieName = null) {
        // Your existing getCookie logic
    }

    public function authenticate() {
        // Your existing authenticate logic
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
