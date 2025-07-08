<?php

class Regulator {
    protected $session_key;
    protected $cookies;
    protected $cookies_unipin;
    protected $revamp_experiment;
    protected $user;
    protected $player;
    protected $session;
    protected $unipinEmail;
    protected $unipinPass;
    protected $unipinPin;
    protected $apiKey;
    protected $encKey;
    protected $proxyServer;
    protected $proxyAuth;
    protected $proxyTimeout;
    protected $proxyMaxAttempts;
    protected $products;
    protected $product_channel;
    protected $product_id;
    protected $product_details;
    protected $unipin_data;
    protected $unipin_data_action;
    protected $unipin_payment;
    protected $unipin_payment_name;
    public $unipin_voucher;
    public $unipin_gift_card;
    public $unipin_points;
    public $unipin_pm;
    public $authFile;
    public $ua;

    public function __construct() {
        require 'settings.php';
        
        $this->proxyServer = $proxyServer;
        $this->proxyAuth = $proxyAuth;
        $this->proxyTimeout = $proxyTimeout;
        $this->proxyMaxAttempts = $proxyMaxAttempts;
        $this->apiKey = $apiKey;
        $this->encKey = $encKey;
        $this->unipinEmail = $unipinEmail;
        $this->unipinPass = $unipinPass;
        $this->unipinPin = $unipinPin;
        $this->session_key = null;
        $this->cookies = [];
        $this->cookies_unipin = [];
        $this->revamp_experiment = [];
        $this->user = [];
        $this->player = [];
        $this->session == [];
        $this->authFile = $authFile;
        $this->products = [];
        $this->product_channel = 221179;
        $this->product_id = 0;
        $this->product_details = [];
        $this->unipin_data = [];
        $this->unipin_data_action = null;
        $this->unipin_payment = null;
        $this->unipin_pm = null;
        $this->unipin_voucher = 'unipin_voucher';
        $this->unipin_gift_card = 'up_gift_card';
        $this->unipin_points = 'up_points';

        $ua = file('user-agents.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (!empty($ua)) {
            $this->ua = $ua[array_rand($ua)];
        }
    }

    public function initiate() {
        $url = 'https://shop.garena.my/app/100067/buy/0?target_channel_id=202070';
        $headers = [
            'accept: */*',
            'accept-language: id-ID,id;q=0.9',
            'dnt: 1',
            'origin: https://shop.garena.my',
            'priority: u=1, i',
            'referer: https://shop.garena.my/',
            'sec-ch-ua: "Not-A.Brand";v="99","Chromium";v="124"',
    'sec-ch-ua-mobile: ?1',
            'sec-ch-ua-mobile: ?0',
            'sec-ch-ua-platform: "Windows"',
            'sec-fetch-dest: empty',
            'sec-fetch-mode: cors',
            'sec-fetch-site: same-origin',
            'user-agent: '.$this->ua
        ];
        $response = $this->fetchWithProxy($url, null, $headers);
        $this->setCookiesFromHeader($response['header']);

        $dom = new DOMDocument;

        libxml_use_internal_errors(true);
        $dom->loadHTML($response['body']);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $scriptNodes = $xpath->query("//script[contains(text(), 'window.__CLIENT_IP__')]");

        $data = [];
        foreach ($scriptNodes as $script) {
            $scriptContent = $script->nodeValue;

            preg_match('/window\.__CLIENT_IP__\s*=\s*"([^"]+)"/', $scriptContent, $matches);
            $clientIp = $matches[1] ?? null;

            preg_match('/window\.__SERVICE_VERSION__\s*=\s*"([^"]+)"/', $scriptContent, $matches);
            $serviceVersion = $matches[1] ?? null;

            preg_match('/window\.__SPLIT_GROUP__\s*=\s*"([^"]+)"/', $scriptContent, $matches);
            $splitGroup = $matches[1] ?? null;

            preg_match('/window\.__SOURCE__\s*=\s*"([^"]+)"/', $scriptContent, $matches);
            $source = $matches[1] ?? null;

            $data = [
                'session_id' => $this->cookies['mspid2'],
                'group' => $splitGroup,
                'service_version' => $serviceVersion,
                'source' => $source,
                'domain' => 'shop.garena.my'
            ];
        }

        if (empty($data)) {
            $this->respond(true, 'Cannot get Revamp Experiment.');
        }

        $this->revamp_experiment = array_merge($this->revamp_experiment, $data);

        return true;
    }

public function setPlayerId($player_id) {
    $url = "https://id.tobd.top/ff2.php";
    $headers = [
        'Content-Type: application/json',
        'X-Api-Key: kR9pW2sD8vG4jQ6zH1bN5mC0xL3fT7yUaE9iOsVqgPZtXhY'
    ];
    $payload = json_encode([
        'login_id' => $player_id
    ]);

    $attempt = 0;
    $success = false;
    $response = null;
    $err = null;

    while ($attempt < $this->proxyMaxAttempts && !$success) {
        $response = $this->fetchWithProxy($url, $payload, $headers, 'POST');
        $response = json_decode($response['body'], true);

        if ($response['error']) {
            if ($response['msg'] == 'captcha_error' || $response['msg'] == 'fetch_error') {
                $attempt++;
                $err = ($response['msg'] == 'fetch_error') ? $response['data']['msg'] : $response['msg'];
            } else {
                $err = $response['msg'];
                $success = true;
            }
        } else {
            $err = null;
            $success = true;
        }
    }

    if (!$success || $err !== null) {
        $this->respond(true, $err);
    }

    if (array_key_exists('region', $response['data']) && $response['data']['region'] !== 'BD') {
        $this->respond(true, 'Invalid player region.');
    }

    $this->cookies = array_merge($this->cookies, $response['data']['cookies']);
    unset($response['data']['cookies']);
    $this->player = array_merge($this->player, $response['data']);

    return true;
}

    public function setLoginUI() {
        $url = 'https://www.unipin.com/login';
        $headers = [
            'accept: */*',
            'accept-language: id-ID,id;q=0.9',
            'dnt: 1',
            'origin: https://www.unipin.com',
            'sec-fetch-dest: empty',
            'sec-fetch-mode: cors',
            'sec-fetch-site: same-origin',
            'user-agent: '. $this->ua
        ];

        $response = $this->fetchWithProxy($url, null, $headers, $this->getUnipinCookie());
        $this->setCookiesFromHeaderUnipin($response['header']);

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($response['body']);
        libxml_clear_errors();

        $form = $dom->getElementById('signin-form-loginpage');
        if (!$form) {
            $this->respond(true, 'Failed to set login UI #183.');
        }

        $inputs = $form->getElementsByTagName('input');

        $postData = [];

        foreach ($inputs as $input) {
            $name = $input->getAttribute('name');
            $value = $input->getAttribute('value');
            if ($input->getAttribute('type') == 'email') {
                $value = $this->unipinEmail;
            } elseif ($input->getAttribute('type') == 'password') {
                $value = $this->unipinPass;
            }
            
            $postData[$name] = $value;
        }

        $this->unipin_data = $postData;
    }

    private function login() {
        $url = 'https://www.unipin.com/login';
        $headers = [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'Accept-Language: id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7,ar;q=0.6',
            'Cache-Control: max-age=0',
            'Content-Type: application/x-www-form-urlencoded',
            'DNT: 1',
            'Origin: https://www.unipin.com',
            'Priority: u=0, i',
            'Referer: https://www.unipin.com/login',
            'Sec-Fetch-Dest: empty',
            'Sec-Fetch-Mode: navigate',
            'Sec-Fetch-Site: same-origin',
            'Upgrade-Insecure-Requests: 1',
            'User-Agent: '.$this->ua
        ];

        if (empty($this->unipin_data)) {
            return $this->respond(true, 'UniPin data not found #228.');
        }
        $response = $this->fetchWithProxy($url, http_build_query($this->unipin_data), $headers, $this->getUnipinCookie());
        $this->setCookiesFromHeaderUnipin($response['header']);

        preg_match('/location:\s*(.*)/mi', $response['header'], $matches);
        $location = isset($matches[1]) ? trim($matches[1]) : null;

        if ($location !== null && preg_match('/https:\/\/www\.unipin\.com\/code_auth\//', $location)) {
            return $this->respond(true, 'Invalid UniPin region.');
        }

        return $response['header'];
    }

    public function fetchUser() {
        $url = 'https://www.unipin.com';
        $headers = [
            'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'accept-language: en-US,en;q=0.9',
            'cache-control: max-age=0',
            'dnt: 1',
            'priority: u=0, i',
            'referer: https://www.unipin.com/login',
            'sec-fetch-dest: empty',
            'sec-fetch-mode: navigate',
            'sec-fetch-site: same-origin',
            'upgrade-insecure-requests: 1',
            'user-agent: '.$this->ua
        ];

        $response = $this->fetchWithProxy($url, null, $headers, $this->getUnipinCookie());
        $this->setCookiesFromHeaderUnipin($response['header']);
        $this->cookies_unipin['region'] = 'BGD';

        $html = $response['body'];

        preg_match('/\$\(document\)\.ready\(function\(\)\{\s*window\.insider_object\s*=\s*window\.insider_object\s*\|\|\s*{};\s*window\.insider_object\.user\s*=\s*\{([\s\S]*?)\}\s*\}/', $html, $matches);

        if (isset($matches[1])) {
            $jsonString = '{' . $matches[1] . '}}';
            $jsonString = preg_replace('/:\s*([a-zA-Z_][a-zA-Z0-9_.()"-]*)/', ': null', $jsonString);

            $data = json_decode($jsonString, true);
            $this->user = array_merge($this->user, [
                'email' => $data['email'] ?? null,
                'name' => $data['name'] ?? null,
                'co' => $data['co'] ?? null,
                'uc' => $data['custom']['unipin_credits'] ?? 0,
                'up' => $data['custom']['point_rewards'] ?? 0,
            ]);
            return true;
        } else {
            return false;
        }
    }

    public function auth() {
        $url = 'https://www.unipin.com/login';
        $header = $this->login();

        preg_match('/location:\s*(.*)/mi', $header, $matches);
        $location = isset($matches[1]) ? trim($matches[1]) : null;

        if ($location == $url) {
            $headers = [
                'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                'accept-language: en-US,en;q=0.9',
                'cache-control: max-age=0',
                'dnt: 1',
                'priority: u=0, i',
                'sec-fetch-dest: empty',
                'sec-fetch-mode: navigate',
                'sec-fetch-site: same-origin',
                'upgrade-insecure-requests: 1',
                'user-agent: '.$this->ua
            ];
            $response = $this->fetchWithProxy($url, null, $headers, $this->getUnipinCookie());
            $this->setCookiesFromHeaderUnipin($response['header']);

            preg_match('/location:\s*(.*)/mi', $response['header'], $matches);
            $location = isset($matches[1]) ? trim($matches[1]) : null;

            if ($location !== null && $this->fetchUser()) {
                $userAuth = [
                    'user'          => $this->getUser(),
                    'cookies'       => $this->getUnipinCookies()
                ];
                file_put_contents($this->authFile, $this->encrypt($userAuth));
                return true;
            }

            $dom = new DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML($response['body']);
            libxml_clear_errors();

            $xpath = new DOMXPath($dom);
            $errors = $xpath->query("//div[contains(@class, 'validationError')]");

            if ($errors->length > 0) {
                foreach ($errors as $error) {
                    $alerts = $error->getElementsByTagName('div');
                    foreach ($alerts as $alert) {
                        if (strpos($alert->getAttribute('class'), 'alert-danger') !== false) {
                            $this->respond(true, trim($alert->nodeValue));
                        }
                    }
                }
            } else {
                $this->respond(true, 'Undefined Error.');
            }
        }
    }

    public function setLanguage($lang) {
        $url = 'https://www.unipin.com/language/'.$lang;
        $headers = [
            'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'accept-language: en-US,en;q=0.9,id-ID;q=0.8,id;q=0.7',
            'dnt: 1',
            'priority: u=0, i',
            'sec-fetch-dest: empty',
            'sec-fetch-mode: navigate',
            'sec-fetch-site: same-origin',
            'upgrade-insecure-requests: 1',
            'user-agent: '.$this->ua,
        ];
        $response = $this->fetchWithProxy($url, null, $headers);
        $this->setCookiesFromHeaderUnipin($response['header']);
    }

    public function checkLogin() {
        if (!file_exists($this->authFile)) {
            return false;
        }

        $data = $this->decrypt(file_get_contents($this->authFile));

        if ($data == null) {
            unlink($this->authFile);
            return false;
        }

        $this->user = array_merge($data['user'], $this->user) ?? [];
        $this->cookies_unipin = array_merge($data['cookies'], $this->cookies_unipin) ?? [];

        if (!$this->fetchUser()) {
            unlink($this->authFile);
            return false;
        }
        
        return true;
    }

    public function getUser() {
        if ($this->user == null) {
            $this->respond(true, 'Cannot get user.');
        }

        return $this->user;
    }

    public function getPlayer() {
        if ($this->player == null) {
            $this->respond(true, 'Cannot get player.');
        }

        return $this->player;
    }

    public function getProductDetails() {
        if ($this->product_details == null) {
            $this->respond(true, 'Cannot get product details.');
        }

        return $this->product_details;
    }

    public function getAllProducts() {
        if ($this->products == null) {
            return null;
        }

        return $this->products;
    }

    public function getRevampExperiment() {
        if ($this->revamp_experiment == null) {
            $this->respond(true, 'Cannot get Revamp Experiment.');
        }

        return $this->revamp_experiment;
    }

    public function setCsrf() {
        $url = 'https://shop.garena.my/api/preflight';
        $postData = json_encode([]);
        $headers = [
            'Host: shop.garena.my',
            'Content-Length: ' . strlen($postData),
            'sec-ch-ua: "Not-A.Brand";v="99", "Chromium";v="124"',
            'X-Datadome-Clientid: ' . $this->cookies['datadome'],
            'Accept-Language: en-US',
            'Sec-Ch-Ua-Mobile: ?1',
            'User-Agent: '.$this->ua,
            'Content-Type: application/json',
            'Accept: application/json',
            'Sec-Ch-Ua-Platform: "Android"',
            'Origin: https://shop.garena.my',
            'Sec-Fetch-Site: same-origin',
            'Sec-Fetch-Mode: cors',
            'Sec-Fetch-Dest: empty',
            'Priority: u=1, i',
            'Connection: keep-alive'
        ];
        $response = $this->fetchWithProxy($url, $postData, $headers, $this->getCookie());
    
        $this->setCookiesFromHeader($response['header']);

        if (!array_key_exists('__csrf__', $this->cookies)) {
            $this->respond(true, 'Cannot get CSRF.');
        }

        return $this->cookies['__csrf__'];
    }

    public function getProducts() {
        $url = 'https://shop.garena.my/api/shop/pay/init?language=en&region=MY';
        $data = json_encode([
            "app_id" => 100067,
            "packed_role_id" => $this->player['packed_role_id'],
            "channel_id" => $this->product_channel,
            "service" => $this->revamp_experiment['source'],
            "channel_data" => [
                "need_return" => true,
                "payment_channel" => null
            ],
            "revamp_experiment" => $this->revamp_experiment
        ]);
        $headers = [
            'Host: shop.garena.my',
            'Content-Length: ' . strlen($data),
            'sec-ch-ua: "Not-A.Brand";v="99", "Chromium";v="124"',
            'X-Datadome-Clientid: ' . $this->cookies['datadome'],
            'X-Csrf-Token: ' . $this->cookies['__csrf__'],
            'Accept-Language: en-US',
            'Sec-Ch-Ua-Mobile: ?1',
            'User-Agent: '.$this->ua,
            'Content-Type: application/json',
            'Accept: application/json',
            'Origin: https://shop.garena.my',
            'Sec-Fetch-Site: same-origin',
            'Sec-Fetch-Mode: cors',
            'Sec-Fetch-Dest: empty',
            'Priority: u=1, i',
            'Connection: keep-alive'
        ];

        $response = $this->fetchWithProxy($url, $data, $headers, $this->getCookie());
        $res = json_decode($response['body'], true);
        if (!is_array($res)) {
            return $this->respond(true, 'Please update Channel ID.');
        }
        if (array_key_exists('init', $res) && array_key_exists('url', $res['init'])) {
            $url = (preg_match('/^https:\/\/www\.unipin\.com\/unibox\/d\/.*$/', $res['init']['url'])) 
                ? str_replace('https://www.unipin.com/unibox/d/', 'https://www.unipin.com/unibox/select_denom/', $res['init']['url']) 
                : $res['init']['url'];
            $headers = [
                'accept: */*',
                'accept-language: id-ID,id;q=0.9',
                'dnt: 1',
                'origin: https://www.unipin.com',
                'referer: '.$res['init']['url'],
                'sec-ch-ua: "Not/A)Brand";v="8", "Chromium";v="126", "Google Chrome";v="126"',
                'sec-ch-ua-mobile: ?0',
                'sec-ch-ua-platform: "Windows"',
                'sec-fetch-dest: empty',
                'sec-fetch-mode: cors',
                'sec-fetch-site: same-origin',
                'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36'
            ];

            $response = $this->fetchWithProxy($url, null, $headers, $this->getUnipinCookie());
            $this->setCookiesFromHeaderUnipin($response['header']);
            
            $dom = new DOMDocument;
            libxml_use_internal_errors(true);
            $dom->loadHTML($response['body']);
            libxml_clear_errors();

            $form = $dom->getElementById('form');
            if (!$form) {
                $this->respond(true, 'Products not found #343.');
            }

            $action = $form->getAttribute('action');
            $inputElements = $form->getElementsByTagName('input');
            $postData = [];
            foreach ($inputElements as $input) {
                $inputName = $input->getAttribute('name');
                $inputValue = $input->getAttribute('value');

                if (!empty($inputName)) {
                    $postData[$inputName] = $inputValue;
                }
            }

            if (!array_key_exists('denomination', $postData)) {
                $this->respond(true, 'Products not found #359.');
            }

            $xpath = new DOMXPath($dom);
            $divs = $xpath->query('//div[@class="payment-denom-button"]');

            $products = [];
            foreach ($divs as $div) {
                $onclick = $div->getAttribute('onclick');
                preg_match('/submit_form\((.*?)\)/', $onclick, $matches);
                if ($matches) {
                    $jsonString = trim($matches[1], "'\"");
                    $denom = json_decode(html_entity_decode($jsonString), true);
                    if ($denom) {
                        $products[] = $denom;
                    }
                }
            }

            $this->unipin_data = $postData;
            $this->unipin_data_action = $action;
            $this->products = $products;
            return true;
        } else {
            return $this->respond(true, 'Init URI not found.');
        }
    }

    public function setPayment($paymentMethod) {
        if ($this->unipin_data_action == null) {
            $this->respond(true, 'Unipin data action not found.');
        }
        $headers = [
            'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'accept-language: id-ID,id;q=0.9',
            'cache-control: max-age=0',
            'content-length: ' . strlen(http_build_query($this->unipin_data)),
            'content-type: application/x-www-form-urlencoded',
            'dnt: 1',
            'origin: https://www.unipin.com',
            'priority: u=0, i',
            'sec-fetch-dest: empty',
            'sec-fetch-mode: navigate',
            'sec-fetch-site: same-origin',
            'upgrade-insecure-requests: 1',
            'user-agent: '.$this->ua,
        ];
        $response = $this->fetchWithProxy($this->unipin_data_action, http_build_query($this->unipin_data), $headers, $this->getUnipinCookie());
        $this->setCookiesFromHeaderUnipin($response['header']);

        preg_match('/location:\s*(.*)/mi', $response['header'], $matches);
        $location = isset($matches[1]) ? trim($matches[1]) : null;

        if ($location !== null && preg_match('/^https:\/\/www\.unipin\.com\/unibox\/d\/.*$/', $location)) {
            $headers = [
                'accept: */*',
                'accept-language: id-ID,id;q=0.9',
                'dnt: 1',
                'origin: https://www.unipin.com',
                'sec-fetch-dest: empty',
                'sec-fetch-mode: cors',
                'sec-fetch-site: same-origin',
                'user-agent: '.$this->ua
            ];
            $response = $this->fetchWithProxy($location, null, $headers, $this->getUnipinCookie());
            $this->setCookiesFromHeaderUnipin($response['header']);
            
            $dom = new DOMDocument;
            libxml_use_internal_errors(true);
            $dom->loadHTML($response['body']);
            libxml_clear_errors();

            $xpath = new DOMXPath($dom);
            
            $paymentPanel = $xpath->query('//*[@id="accordionPayment"]')->item(0);
            $paymentNames = [$this->unipin_voucher, $this->unipin_gift_card, $this->unipin_points];

            $results = [];

            foreach ($paymentNames as $name) {
                $channelButtons = $xpath->query('.//div[contains(@class, "payment-channel-button")]', $paymentPanel);
                foreach ($channelButtons as $channel) {
                    $channelNameNode = $xpath->query('.//div[contains(@class, "payment-channel-name")]', $channel)->item(0);
                    
                    if ($channelNameNode && strtolower(trim($channelNameNode->textContent)) == str_replace('_', ' ', strtolower($name))) {
                        $onclick = $channel->getAttribute('onclick');
                        if ($onclick) {
                            preg_match('/window\.location\s*=\s*\'(.*?)\'/', $onclick, $matches);
                            if ($matches) {
                                $results[$name]['url'] = $matches[1];
                                $results[$name]['name'] = trim($channelNameNode->textContent);
                            }
                        }
                    }
                }
            }

            if (isset($results[$paymentMethod])) {
                $this->unipin_payment = $results[$paymentMethod]['url'];
                $this->unipin_payment_name = $paymentMethod;
                $this->unipin_pm = $results[$paymentMethod]['name'];
                return true;
            } else {
                return false;
            }
        } else {
            return $this->respond(true, 'Cannot set payments.');
        }
    }

    function paymentInteract($serial = null, $pin = null) {
        $headers = [
            'accept: */*',
            'accept-language: id-ID,id;q=0.9',
            'dnt: 1',
            'origin: https://www.unipin.com',
            'sec-fetch-dest: empty',
            'sec-fetch-mode: cors',
            'sec-fetch-site: same-origin',
            'user-agent: '.$this->ua
        ];
        $response = $this->fetchWithProxy($this->unipin_payment, null, $headers, $this->getUnipinCookie());
        $this->setCookiesFromHeaderUnipin($response['header']);

        preg_match('/location:\s*(.*)/mi', $response['header'], $matches);
        $location = isset($matches[1]) ? trim($matches[1]) : null;

        if ($location !== null && preg_match('/^https:\/\/www\.unipin\.com\/login.*$/', $location)) {
            return $this->respond(true, 'Login invalid. Try again.');
        }

        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($response['body']);
        libxml_clear_errors();

        $url = $dom->getElementsByTagName('form')->item(0)->getAttribute('action');
        $inputs = [];

        if ($this->unipin_payment_name == $this->unipin_gift_card || $this->unipin_payment_name == $this->unipin_voucher) {
            foreach ($dom->getElementsByTagName('input') as $input) {
                $name = $input->getAttribute('name');
                $value = $input->getAttribute('value');
                $inputs[$name] = $value;
            }

            if (preg_match('/\w{4}-([a-zA-Z0-9]{1})-\w{1}-(\d{8})/', $serial, $serialMatches)) {
    // শুধু সিরিয়ালের মধ্যে থেকে '-' রিমুভ করবো
    $inputs['serial'] = str_replace('-', '', $serial);
}



            if (preg_match('/(\d{4})-(\d{4})-(\d{4})-(\d{4})/', $pin, $pinMatches)) {
    $pinKeys = preg_grep('/^pin_\d+$/', array_keys($inputs));
    $pinValues = array_slice($pinMatches, 1); // 1 থেকে শুরু করলে শুধু ৪ টুকরো পাবে

    // রিসেট করে index matching করবো
    $pinKeys = array_values($pinKeys);

    foreach ($pinKeys as $index => $key) {
        $inputs[$key] = $pinValues[$index] ?? '';
    }
}

        } else {
            $inputs['security_key'] = $this->unipinPin;
        }

        $data = http_build_query($inputs);
        $headers = [
            'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'accept-language: en-US,en;q=0.9,id-ID;q=0.8,id;q=0.7',
            'cache-control: max-age=0',
            'content-length: '.strlen($data),
            'content-type: application/x-www-form-urlencoded',
            'dnt: 1',
            'origin: https://www.unipin.com',
            'priority: u=0, i',
            'sec-fetch-dest: empty',
            'sec-fetch-mode: navigate',
            'sec-fetch-site: same-origin',
            'upgrade-insecure-requests: 1',
            'user-agent: '.$this->ua,
        ];
        $response = $this->fetchWithProxy($url, $data, $headers, $this->getUnipinCookie());
        $this->setCookiesFromHeaderUnipin($response['header']);

        preg_match('/location:\s*(.*)/mi', $response['header'], $matches);
        $location = isset($matches[1]) ? trim($matches[1]) : null;

        if ($location !== null) {
            if (preg_match('/^https:\/\/www\.unipin\.com\/unibox\/result\/.*$/', $location)) {
                return $this->viewResult($location);
            } else if (preg_match('/^https:\/\/www\.unipin\.com\/unibox\/error\/Consumed.*$/', $location)) {
                return $this->respond(true, 'Consumed Voucher.', [
                    'serial' => $serial,
                    'pin'    => $pin
                ]);
            } else if (preg_match('/^https:\/\/www\.unipin\.com\/unibox\/error\/Insufficient.*$/', $location)) {
                return $this->respond(true, 'Insufficient UP Points.');
            } else {
                return $this->getTrxError($location);
            }
        } else {
            return $this->respond(true, 'Unknown error during transaction.');
        }
    }

    private function viewResult($url) {
        $headers = [
            'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'accept-language: en-US,en;q=0.9,id-ID;q=0.8,id;q=0.7',
            'cache-control: max-age=0',
            'dnt: 1',
            'priority: u=0, i',
            'sec-fetch-dest: empty',
            'sec-fetch-mode: navigate',
            'sec-fetch-site: same-origin',
            'upgrade-insecure-requests: 1',
            'user-agent: '.$this->ua,
        ];
        $response = $this->fetchWithProxy($url, null, $headers, $this->getUnipinCookie());
        $this->setCookiesFromHeaderUnipin($response['header']);
    
        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($response['body']);
        libxml_clear_errors();
    
        $xpath = new DOMXPath($dom);
    
        $transactionDetails = [];
    
        $status = $xpath->query('//script[contains(text(), "pResult")]');
        if ($status->length > 0) {
            $scriptContent = $status->item(0)->textContent;
            preg_match('/var\s+pResult\s*=\s*({[^}]+})\s*;/', $scriptContent, $matches);
            if (isset($matches[1])) {
                $jsonString = preg_replace('/(\w+)\s*:\s*"(.*?)"/', '"$1": "$2"', $matches[1]);
                $transactionDetails = json_decode($jsonString, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->respond(true, 'Error view result JSON.');
                }
            } else {
                $this->respond(true, 'Result not found.');
            }
        } else {
            $this->respond(true, 'Cannot get result.');
        }
    
        return $transactionDetails;
    }

    private function getTrxError($url) {
        $headers = [
            'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'accept-language: en-US,en;q=0.9,id-ID;q=0.8,id;q=0.7',
            'cache-control: max-age=0',
            'dnt: 1',
            'priority: u=0, i',
            'sec-fetch-dest: empty',
            'sec-fetch-mode: navigate',
            'sec-fetch-site: same-origin',
            'upgrade-insecure-requests: 1',
            'user-agent: '.$this->ua,
        ];
        $response = $this->fetchWithProxy($url, null, $headers, $this->getUnipinCookie());
        $this->setCookiesFromHeaderUnipin($response['header']);

        preg_match('/location:\s*(.*)/mi', $response['header'], $matches);
        $location = isset($matches[1]) ? trim($matches[1]) : null;

        $headers = [
            'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'accept-language: en-US,en;q=0.9,id-ID;q=0.8,id;q=0.7',
            'cache-control: max-age=0',
            'dnt: 1',
            'priority: u=0, i',
            'sec-fetch-dest: empty',
            'sec-fetch-mode: navigate',
            'sec-fetch-site: same-origin',
            'upgrade-insecure-requests: 1',
            'user-agent: '.$this->ua,
        ];
        $response = $this->fetchWithProxy($location, null, $headers, $this->getUnipinCookie());
        $this->setCookiesFromHeaderUnipin($response['header']);

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($response['body']);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $errors = $xpath->query("//div[contains(@class, 'validationError')]");

        if ($errors->length > 0) {
            foreach ($errors as $error) {
                $alerts = $error->getElementsByTagName('div');
                foreach ($alerts as $alert) {
                    if (strpos($alert->getAttribute('class'), 'alert-danger') !== false) {
                        $this->respond(true, trim($alert->nodeValue));
                    }
                }
            }
        } else {
            $this->respond(true, 'Undefined Error.');
        }
    }

    public function setProductId($product_id) {
        if (isset($this->products[$product_id])) {
            $this->product_id = $product_id;
            $this->products[$product_id]['id'] = $this->product_id;
            $this->product_details = $this->products[$product_id];
            $this->unipin_data['denomination'] = json_encode($this->product_details);
            return true;
        }
        return false;
    }

    public function fetchWithProxy($url, $postFields = null, $headers = [], $cookie = null, $proxy = false) {
        $attempt = 0;
        $success = false;
        $response = null;
        $err = null;
        $logFile = 'log.txt';
        
        while ($attempt < $this->proxyMaxAttempts && !$success) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            if ($proxy) {
                curl_setopt($ch, CURLOPT_PROXY, $this->proxyServer);
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->proxyAuth);
                curl_setopt($ch, CURLOPT_TIMEOUT, $this->proxyTimeout);
            }
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
            file_put_contents($logFile, $logData, FILE_APPEND);
            
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

    public function setCookiesFromHeaderUnipin($header) {
        preg_match_all('/^set-cookie:\s*([^;]*)/mi', $header, $matches);
        $cookies = [];
        foreach($matches[1] as $item) {
            parse_str($item, $cookie);
            $cookies = array_merge($cookies, $cookie);
        }
        $this->cookies_unipin = array_merge($this->cookies_unipin, $cookies);
        return $this->cookies_unipin;
    }

    public function setCookie($name, $value) {
        $this->cookies[$name] = $value;
        return $this->cookies[$name]; 
    }

    public function getCookies() {
        return $this->cookies;
    }

    public function getUnipinCookies() {
        return $this->cookies_unipin;
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

    public function getUnipinCookie($cookieName = null) {
        if ($cookieName == null) {
            return http_build_query($this->cookies_unipin, '', '; ');
        } else {
            if (array_key_exists($cookieName, $this->cookies_unipin)) {
                return "$cookieName=".$this->cookies_unipin[$cookieName];
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
    
    public function encrypt($data)
    {
        $method = "AES-256-CBC";
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
    
        $encrypted = openssl_encrypt(json_encode($data), $method, $this->encKey, 0, $iv);
        $encrypted = base64_encode($encrypted . '::' . base64_encode($iv));
    
        return $encrypted;
    }
    
    public function decrypt($encryptedData)
    {
        $method = "AES-256-CBC";
        
        list($encryptedData, $iv) = explode('::', base64_decode($encryptedData), 2);
        $iv = base64_decode($iv);
    
        $decrypted = openssl_decrypt($encryptedData, $method, $this->encKey, 0, $iv);
        return json_decode($decrypted, true);
    }
}
