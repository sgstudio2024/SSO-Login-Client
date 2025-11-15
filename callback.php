<?php
// 配置：改为实际 SSO 服务与客户端凭据（与服务端 sso_clients 中一致）
$ssoServer = 'https://login.sgstudio2025.xyz';
$clientId = 'sgstudio';
$clientSecret = '114514'; // 注册时设定的明文 secret

// 获取 code 与 state
$code = isset($_GET['code']) ? trim((string)$_GET['code']) : '';
$state = isset($_GET['state']) ? trim((string)$_GET['state']) : '';

if ($code === '') {
    header('Content-Type: text/plain; charset=utf-8');
    echo "未收到授权 code。";
    exit;
}

// 辅助：发送 POST 并返回解析的 JSON
function postJson($url, $data) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $resp = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($resp === false) return ['error' => 'curl_error', 'error_description' => $err];
    $json = json_decode($resp, true);
    if ($json === null) return ['error' => 'invalid_response', 'raw' => $resp];
    return $json;
}

// 1) 交换 code -> access_token
$tokenEndpoint = rtrim($ssoServer, '/') . '/sso_token.php';
$tokenResp = postJson($tokenEndpoint, [
    'grant_type' => 'authorization_code',
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
    'code' => $code
]);

if (isset($tokenResp['error'])) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "换取 token 失败: " . htmlspecialchars(json_encode($tokenResp, JSON_UNESCAPED_UNICODE));
    exit;
}

$accessToken = isset($tokenResp['access_token']) ? $tokenResp['access_token'] : '';
if (!$accessToken) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "未返回 access_token。响应: " . htmlspecialchars(json_encode($tokenResp, JSON_UNESCAPED_UNICODE));
    exit;
}

// 2) 使用 access_token 获取用户信息
$userInfoEndpoint = rtrim($ssoServer, '/') . '/sso_userinfo.php';
$ch = curl_init($userInfoEndpoint . '?access_token=' . urlencode($accessToken));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$userRespRaw = curl_exec($ch);
$curlErr = curl_error($ch);
curl_close($ch);
$userResp = json_decode($userRespRaw, true);

// 显示结果
header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<html lang="zh-CN">
<head><meta charset="utf-8"><title>Forum SSO 回调</title></head>
<body style="font-family:Arial,Helvetica,sans-serif;padding:20px;">
<h3>Forum SSO 登录回调</h3>
<?php if (isset($userResp['success']) && $userResp['success'] === true && isset($userResp['data'])): ?>
  <p>登录成功，用户信息：</p>
  <ul>
    <li>ID: <?php echo htmlspecialchars($userResp['data']['id']); ?></li>
    <li>用户名: <?php echo htmlspecialchars($userResp['data']['username']); ?></li>
    <li>邮箱: <?php echo htmlspecialchars($userResp['data']['email']); ?></li>
  </ul>
<?php else: ?>
  <p>获取用户信息失败。</p>
  <pre><?php echo htmlspecialchars($userRespRaw ?: json_encode($tokenResp, JSON_UNESCAPED_UNICODE)); ?></pre>
<?php endif; ?>
</body>
</html>
