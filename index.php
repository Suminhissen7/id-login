<?php
// maintenance.php

// আপনি চাইলে এখানে হেডার কোড দিতে পারেন
header("HTTP/1.1 503 Service Temporarily Unavailable");
header("Retry-After: 3600"); // 1 hour
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সাইট মেইন্টেনেন্স চলছে</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: "Poppins", sans-serif;
            background: linear-gradient(135deg, #667eea, #764ba2);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            text-align: center;
        }
        .container {
            max-width: 500px;
            padding: 40px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 20px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        h1 {
            font-size: 3em;
            margin-bottom: 20px;
        }
        p {
            font-size: 1.2em;
            line-height: 1.5;
        }
        .loader {
            margin: 30px auto;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #ffffff;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg);}
            100% { transform: rotate(360deg);}
        }
        footer {
            margin-top: 20px;
            font-size: 0.9em;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>শীঘ্রই ফিরে আসবো</h1>
        <div class="loader"></div>
        <p>আমাদের ওয়েবসাইট বর্তমানে সংরক্ষণে রয়েছে।  
           আমরা উন্নত সেবা প্রদানের জন্য কাজ করছি।  
           অনুগ্রহ করে একটু পরে আবার চেষ্টা করুন।</p>
        <footer>&copy; <?php echo date("Y"); ?> TOBD</footer>
    </div>
</body>
</html>
