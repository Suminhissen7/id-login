<?php

class Garena {
    public $player;
    public $cookies;
    public $ua;
    public $sec_ch_ua;
    public $apiKey;

    public function __construct() {
        require 'settings.php';

        $this->apiKey = $apiKey;
        $this->player = [];
        $this->cookies = [];
        $ua = file('user-agents.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (!empty($ua)) {
            $this->ua = $ua[array_rand($ua)];
            $this->sec_ch_ua = $this->generateSecChUa($this->ua);
        }
    }

    private function generateSecChUa($userAgent) {
        // Match Chrome or Chromium
        if (preg_match('/(Chrome|Chromium)\/(\d+)/', $userAgent, $matches)) {
            $browser = $matches[1];  // Chrome or Chromium
            $version = $matches[2];  // Version number
            
            // Return sec-ch-ua in the required format
            return '"'.$browser.'";v="'.$version.'", "Not:A-Brand";v="24", "Google Chrome";v="'.$version.'"';
        }
        // Add more browser handling logic if needed

        // Default fallback
        return '"Unknown";v="0"';
    }
    
    
    
    
    

    public function init() {
        $url = 'https://shop.garena.my/app';
        $headers = [
            'accept: */*',
            'accept-language: id-ID,id;q=0.9',
            'dnt: 1',
            'origin: https://shop.garena.my',
            'priority: u=1, i',
            'sec-ch-ua: '.$this->sec_ch_ua,
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
        $postFields = 'jspl=71pnKsblmR0B-C3G_tKpK2fJaTg4Askp18fqeUHFM3nYQx57pFq1zw-iPPmltZgI-02rR0gZ5HQ_kBJh6Al9KWCYNYSY2nS0wqI810PK22Vujmp36LyA0VObU77M3my-bm_MVYpOtlzjABSYVlgNCT2bL-yx30grtcK0dYRehopvAMcuD778dXNGZ8sU6N41z2dRhaxv6Uglm6T2yqXctJ2yzTp391j-ZXKcSfhKhgElN3EaxCJWjRNA9BZH4TPsNtIeaIO7Fq8t_fao8Gp7IVZmIP2Dt8flHheDozA9dI2KtSdI8YNSIk0-ER0AZ2wQbcRiJd9eexfuhad0u14fW2BMo9ADtFGi-v3Lso2K89Rn3vt0kB0oKwULRA5OPpOE_rWpOrjMGgCKF4BvuX15z3oG26YDjbMsOH5UwEGcs3mEwp_tOfNaEe_TbQRSqIRnuPMGTcONVQwXsa5cM2qqCl6n5eySJ6So1c8Fn1qJ1gBOVhDDqAhMnZpXs_YZyu3XXcciHa-YjxmJS59pyzycx8ym9ZcyYy5aMHxfuaQ58IEdmf-sJE0sGVF2LzodWAAXj4FGz7gXdzZbcKOXzbvJ2j70N0TyorM1d6yC5UNbJjM3jo5B6KQUFwLc1gCCfd51uZ30bjVLoop_mElWdG6i6Jc0Y7sLC1eJwAf9FZkmTBIGkqiWY3aX-jVya3bM8C6NDpsN_O-m3LqyXojmqm5Wvjnzqm_ooxoJL5SADP5bkndeOzN27hV5v3CFTL2oWPK0zf6f_FyvnWD2JRhLrfx4rBwHwGiUvYcCRLeCRDAXSnhdVg0HPpWOwYnt7ZLK6HZeEapq6rhdpT2dqUeEqcW4rNbefVtxNix06vDHLBu5L6N48osXAcoOEBSg4rbafvXveDXErpdBHHQpP3kX6VZwi6t_VFZhI2pqkB_h6t0iohZKLnLHsfJpmBrt7iVYp0h0tYfHDybrLEAIccrD6eNaaTJpK8kTuhZPzcNivhUKWwxnpzHvPppUIrzLLhYpi3DEB4oKLyrMBcyX9HtIE3fsk1klbP0GiTwGoKDmyqU704qXxyxZiD0m8IBGaDv3oexKIy4BEyjun5jZb6wWNpQMThMryLIROkKiFnP9JW50Q96g-MpUVQUtlMHvbtbCxNQUcM9bX8T0kF3yl4vMz0gJo2zpOFwmmMQmxXEN47ejGniavmNdiV2LfFwHcxeCAZlR6Yhdc3cJIw296arJe_sF9zIPeU0C_TR6zItVI6YTKRn-Yho1KbLNJ90oU0Bx5IzNm049dP7wuRgTqnfhoYOcfgJkg_jyAjVixhvB-i2a8aHaEiY7QEOS3YDhan5lsnmfi7iigrec9JSlK_7k3rRj0j45wJE8-hOd07yIrQMdD_uda0Rhn2uJ6XupdWy4AQP0l2EPczhMSxMtrllg0H50KRPTvrIFEoPmB6yPSH-xU7a2cKSE_nQqaX31owqEP2uQZBIVEliz-DjpCDs95QWJWHpoOevVcP-4K4YIGRyjVVuqG9qqzhGckiOeaGkvazSu8FVtnw0BIwVbYIO1oDtWYU6YGywk2QkxuxRTG282gXK8rTqJx74kQT7WW_rvZUjiFcmC2J5iDXlioozVmS6hiTxqyqp4Zf435u85WsGP-hNIUwDpSJqw5Q2E5jYosEKehtAa-NvIOfSrS-xTenkH84KapzOPR7ZaQVso4tjPj9-Vz4JKVJkic-paSKL_L4yhGM9y3Abxc6zDlm8k2Ls2B3nVkh3VJcAGHTD10-HSxbAQQHw-wbcoHFSBzt9elfxlhIzXRUVmX0cKFNK0DSzZGobGUqp0qn6gifNFKNKAp1i9ANdRJKzyVN_GvxhL2WqtwqsUjLS5-lnnrkC0ujcoEFv7XjvVJZ4hmjhYWx3GsuYum_LfrdcNKYB5SiqfKgTnsyi1Xc8g-N6gFhviIBjEhtq6u8uoWpVi3bY7AVXlcOQBqzHlffYZbTiuUYMHAAKKeIPMn4nOa_w5AsP_nT_NmUojIIwbrh1p4Qirs8sa3MT7EnqQD0rAtI9AGGvon20y9sSkFypn6u6fyFJR9v8kmqeQ9Hzb83CLLY33aAY9kmIUB1MKPaRcCsM9nL-EWs_qffIrf6dY633gtbuJbHZ38YlW9MTeIPa9CrXOtbWVRq2dPqx4R_GTZmVCsvL87SgLBq5CVWq3ulkMjiMiQDzJOWbqLSe9GVsj5fjC75AxA9NSFNN_JnX_2a4KCsCD6HoPfflfk_KHL2RcrXfpiNtKtLWCR4vmfVt1Fpv0_W0bYkr9FRn3cjzk_NZB93bZzPD4oF_UQux8cszLaxSAVe-Q90MzZ4dZ0TphP34OZM_Fmj2-gu370AQ_qs1Qdarl5lvXF-6d1Wo0Bjt8DJSlcw7JVLbp5TfqmDE2c5RbitD4fut4b9MVMwYpewlnSDfa5ugeDoJb71BfHzYaOcOy5nJ4l29jUNF6Guvn53Ax-YwEvws2-R6vzWPWEtgH5KRnljIi9I1o4pVmUHRQDGvNdM5CnRBSXZ8SX7iroUah3I6C3Ow-PaoWns2XSHslcxBkfBfuA8JzX10bjb3U0Ka9I9LF3EvEhp4JtaRdXnvT8LwGmzEkwhDHzcCjCVBamB4cPuhG5Ps-LsQhC9ssM2TRmpoEErYZnCFnn56OjUZzkWjlZeUkN3_F_BpyGj4XYDBxrc9IW0n5fkts47Eg2tMdJt4xJ-U7wG7TZbo82jNt0JSBmM_Fh6MgQmzQh9mHeEuzZtqeEw4Ej3hAjmbYaOUXXjirQVgSfZH66Z10Vcu5zzdO3VNBPwQHrJIseWE0vceV1VaZxi4s1e7UFf98nS5G6DW75IAymT7Ygs5F8Dk8o5IM-ZcJq23vY0E5_KYUtLmtPW3I_lTu-CITyPLfz-TRRp7pPmxpUkcRXsz_yykycqomNshIhRd2PRChTcvE1uZMiZthaxFY3FZAfBPNK47M-fv9KrtKLaoEhAMgbNf2YGOGp6qxHtvCA68TsiM1GCGHWGENuw0AKliWm6jaPh2U9uANRxNlkJzI16IoByKoSPgq70ejgnR8G0M_wtTiPC9Ln5Etia0KNCYsF9WfW9W3tnZ7WHgSgIaeEa44ZJgnaHvxD7ZYHnSKZs6irPvUPTLtOhkX8I33p782T_-asnIeWcm9nJJKP0wXOazxyTYUWXPXc0u7Ieqqm4dIA8540ExlIsVSPfsCvDrLpBWZV-CsGE0IyujPH4vBpTlOo31HwvTx69s3rIGUziMC_RW9yAonP-ju-SCn-cvsPWXr8dd_VTSQgcekqFXsLYm_Yr7-xNJ8bqdx9Ui4ZcmMKnOvTRhxraS2xrTGuEcuoKOVcpkoBiyQAVZ2q3YDWr3IkzeRSPd5GBb1SGYI77tWZyc6p0gLzpip0altuHSKSMGSXROai6eUyxo1beO6imuM6eYMH_izB4vfqdwdnMos-1uf0JzcXcPyAVaWggA8A1Z3vAx-PYcsLjkb0Q_A17sHaHWslu1P5iEQDWR-PIqntCJJw3nBdtCzi1_GHqZeHkVI8GcIoMSNz9WSoHBsLllKG_U9YrZ1yxAYEv5J5a-Llyy-IQo3hRdEkRetcETWAkirDcitRUmPZNCWFUligoIVLx4ROwHIgCMvVVB8j35UL6tHnT3KIpaupomu4d-n5KMV7IrjsstZQitHDSotYvrjs0qm7RTMPA5WOY4wtcWMyLLSbec9fXU1H19vOsFZKHfZtLKs9h0JgbpiSEiIwtNKCWarrRxoMIkZoEZEhpoYMJ5AmdpUDuVIjl8eX-Z3Qrx9IwKVBU0u-m2N-jE9TO9h1e_y0RFZgsK585p0PIJb-rP1IvK5qf-7aA3GjfqAOK8JZZM8r5qbktU1wCcbiF2d4u_Bui4t8KIrxNrXPjANfUlzA7S2YfRPQgAok5fvkHvpOF3G267mG-1fQNbkzeXEQIsKGJh6_x9YP37gEG3ByeKltgoK084W8Gyh2gZ2XRhWK-epxk3CKTU2cFj21kfQHRzSNMfuT0NUWv7H89XF0GNit0XndKWIgBA0QoWqYXJYVtL2-67rlW-JTKFHHbO4YGt2rThoUBfeIStint8WXKidH7BKpllS5C0iljfNlx-kalvUyRZkcLjHRfmfaE7E1QbjNfh3hpwAmEq0TqauWJOkBM0SeYNccZlzKKqb8-W6_nZPb9L-_8ZCNGJBYUOtuB79HHn3WkB1bPQRpFihDlRT5Ds_Nc8f5trVKemX3-ZwMxiv_ILIktG96_z4b3mmiuCFBIjc2k6BRy8HoEkJrGi6BBi5hX_WOTI3BVj6g0sAVVqxP7shbOYForAqhlWI3czAddmhVlqKE6lH-WpMpPRc8JL4RTGhF2RljpOwzveUDbwAREuMhMB66YMHLjAFgMSusVcP_vEeWk6A_yMkuutA8EQt_yOjUl7iSd5omPMyh7D-aA0UhL7-KcDnnKVhFY7ZEO98BDW9WQ2SNCNXKdpqGo85HHbdADGoFl7AaObWZv9AGXYGyi4UaWu-_j-gMB5R15qpCqpSlfiT3L_RjFwaS2ORphYB6JwGIep3wVhmYL1A1cwoiaZ6dQARsdX4SGfp6jpPwzys5qG4DzPsMWhiS5glJGbPeDbJze_Eh9Skxv_qPzdmCwxuF3Tyw3Xg274TMiOOPgvhcQqfVFX9g9vFj5QuVxqPsX9YEB8DiBYUzvg9x7hfo12fDwQV-UBPk4Gn0dcG85NVCDl07w4FysmCqWPaXkAATpGsteH8pQmZ2gp6Lcdm129tn-7viNuOZoYAiWy5wsBryK3gGwpLmjbsrjMvz9pbjAsuknmGSYjiv7tE5d0SQ5Wc9XfDEg4_9osgexTmIPUGzo3fQwBLx1d-8lYYNaY5QCgl-E2_3CzNc_uBEQ4W35yLj720PuE2eVFa8RHOObc30OL-Wt42q-GuI0Gu78j-NqxmeLqp2YTxLVooW-NDalYOSrTx1lgDlUwE-bsrVZzuoFdtSKlMtC68xjpBTR5NXaJZIrMH9-Yl5qKbv3ms4NJXsieJQS3puA423Nk3DiaBsanpTQcVJXu4cfy2QA9y6S33zbN3Fu_zxzsr9kisX2WkR1yvFH0sG42eFmruNo&eventCounters=%7B%22mousemove%22%3A0%2C%22click%22%3A0%2C%22scroll%22%3A2%2C%22touchstart%22%3A0%2C%22touchend%22%3A0%2C%22touchmove%22%3A0%2C%22keydown%22%3A0%2C%22keyup%22%3A0%7D&jsType=le&cid=B9lbOpsBig832ughqDmCUw0SCYZ94gPaxGdajMMvYG32j9034WSc8IoyeOcGPh2VIe3y6cpXPvnh0sXZ6zu1b21tm423lFuFKnyerLvfvEiwUjNZLoejWtXrALYFglRK&ddk=AE3F04AD3F0D3A462481A337485081&Referer=https%253A%252F%252Fshop.garena.my%252F%253Fchannel%253D202953&request=%252F%253Fchannel%253D202953&responsePage=origin&ddv=4.43.1-next';
        $headers = [
            'authority: dd.garena.com',
            'accept: */*',
            'accept-language: en-US,en;q=0.9',
            'cache-control: no-cache',
            'content-type: application/x-www-form-urlencoded',
            'origin: https://shop.garena.my',
            'pragma: no-cache',
            'referer: https://shop.garena.my/',
            'sec-ch-ua: '.$this->sec_ch_ua,
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
if ($cookie && preg_match('/datadome=([^;]+)/', $cookie, $matches)) {
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
            'Accept-Language: id-ID,id;q=0.9',
            'Connection: keep-alive',
            'Content-Length: ' . strlen($postData),
            'DNT: 1',
            'Host: shop.garena.my',
            'Origin: https://shop.garena.my',
            'Referer: https://shop.garena.my/app',
            'Sec-Fetch-Dest: empty',
            'Sec-Fetch-Mode: cors',
            'Sec-Fetch-Site: same-origin',
            'User-Agent: '.$this->ua,
            'accept: application/json',
            'content-type: application/json',
            'sec-ch-ua: '.$this->sec_ch_ua,
            'sec-ch-ua-mobile: ?0',
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
            'Host: shop.garena.my',
            'Sec-Ch-Ua: '.$this->sec_ch_ua,
            'Accept: application/json',
            'Accept-Language: en-US',
            'Sec-Ch-Ua-Mobile: ?0',
            'User-Agent: '.$this->ua,
            'Sec-Ch-Ua-Platform: "Android"',
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

    public function regionVerify() {
        $url = "https://shop.garena.my/api/shop/apps/roles?app_id=100067&region=MY&source=mb";
        $headers = [
            'Host: shop.garena.my',
            'Sec-Ch-Ua: '.$this->sec_ch_ua,
            'Accept: application/json',
            'Accept-Language: en-US',
            'Sec-Ch-Ua-Mobile: ?0',
            'User-Agent: '.$this->ua,
            'Sec-Ch-Ua-Platform: "Android"',
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

    // ✅ Proxy Details (এখানে সরাসরি সেট করা)
    $proxy_ip = '198.23.239.134';   // Proxy IP
    $proxy_port = '6540';         // Proxy Port
    $proxy_user = 'wifpsyit';      // Proxy Username (যদি লাগে)
    $proxy_pass = '64eblydgxci2';      // Proxy Password (যদি লাগে)

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

        // ✅ Proxy সেট করা
        curl_setopt($ch, CURLOPT_PROXY, $proxy_ip);
        curl_setopt($ch, CURLOPT_PROXYPORT, $proxy_port);

        // যদি Proxy authentication প্রয়োজন হয় (username & password)
        if (!empty($proxy_user) && !empty($proxy_pass)) {
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, "$proxy_user:$proxy_pass");
        }

        // Execute request
        $response = curl_exec($ch);

        // Log request details
        $logData = "URL: " . $url . "\n";
        $logData .= "Headers: " . json_encode($headers, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
        $logData .= "Cookies: " . $cookie . "\n";
        $logData .= "Post Fields: " . (is_array($postFields) ? json_encode($postFields, JSON_PRETTY_PRINT) : $postFields) . "\n";
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

        // Log file লেখার অপশন (ডিবাগের জন্য)
        file_put_contents($logFile, $logData, FILE_APPEND);

        curl_close($ch);
    }

    if (!$success) {
        $this->respond(true, 'error_proxy_timeout: ' . $err);
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