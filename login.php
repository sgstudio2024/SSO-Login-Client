<?php
// 配置：将以下值改为你的实际 SSO 服务与 client_id
$ssoServer = 'https://login.sgstudio2025.xyz'; // SSO 服务根地址
$clientId = 'forum_client'; // 在 sso_clients 中注册的 client_id

// 构造回调地址（callback.php 在同一目录）
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$redirectUri = $scheme . '://' . $host . $basePath . '/callback.php';
$state = bin2hex(random_bytes(8));

// 授权入口：指向 SSO 的 sso_authorize.php（服务端会记录请求并展示登录页面）
$authUrl = $ssoServer . '/sso_authorize.php?client_id=' . urlencode($clientId)
         . '&redirect_uri=' . urlencode($redirectUri)
         . '&response_type=code&state=' . urlencode($state);

// 立即通过 HTTP 302 重定向到 SSO 授权入口
header('Location: ' . $authUrl);
exit;
?>
<!doctype html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<title>Forum 客户端 - 登录</title>
<meta http-equiv="refresh" content="0;url=<?php echo htmlspecialchars($authUrl, ENT_QUOTES); ?>">
<style>
body{font-family:Arial,Helvetica,sans-serif;padding:40px;background:#f7f7fb;}
.container{max-width:640px;margin:0 auto;background:#fff;padding:20px;border-radius:8px;box-shadow:0 6px 18px rgba(0,0,0,0.06);}
.info{margin-top:12px;color:#666;font-size:13px;}
</style>
</head>
<body>
<div class="container">
  <h2>Forum 子域客户端（SSO）</h2>
  <p>正在跳转到 SSO 授权入口...</p>
  <p class="info">如果没有自动跳转，请点击以下链接：<br>
    <a href="<?php echo htmlspecialchars($authUrl, ENT_QUOTES); ?>"><?php echo htmlspecialchars($authUrl, ENT_QUOTES); ?></a>
  </p>
</div>
</body>
</html>
