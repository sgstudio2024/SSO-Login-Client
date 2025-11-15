SSO-Login-Sever的客户端系统
1.上传源代码到你的服务器
2.修改login.php中的  $ssoServer  以及  $clientId （确保在服务端中create_clients_helper.php注册的是否一致）
3.修改callback.php中的  $ssoServer  以及  $clientId  以及  $clientSecret
