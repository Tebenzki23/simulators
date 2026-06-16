<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>phpinfo() — PHP 8.2.12</title>
    <style>
        body { background:#fff; font-family: sans-serif; font-size:13px; color:#000; margin:0; }
        .phpinfo-wrap { max-width: 960px; margin: 0 auto; padding: 20px; }
        h1.php-title { background: linear-gradient(135deg,#8892bf 0%,#4f5b93 100%); color:#fff; padding:20px 30px; margin:0 0 20px; border-radius:6px; font-size:28px; font-weight:400; }
        h1.php-title span { display:block; font-size:13px; opacity:0.8; margin-top:4px; font-weight:400; }
        .section-header { background:#4f5b93; color:#fff; font-size:14px; font-weight:700; padding:8px 14px; margin:18px 0 0; border-radius:4px 4px 0 0; }
        table { width:100%; border-collapse:collapse; margin-bottom:2px; }
        table td { padding:6px 12px; border:1px solid #ccc; vertical-align:top; font-size:12px; }
        tr.h td { background:#9999cc; color:#fff; font-weight:700; }
        tr:nth-child(even) td { background:#f8f8f8; }
        td:first-child { background:#e8e8f0; font-weight:600; width:280px; }
        .enabled  { color:#090; font-weight:700; }
        .disabled { color:#900; font-weight:700; }
        .info-bar { background:#f0f0f0; border:1px solid #ccc; border-radius:4px; padding:10px 14px; margin-bottom:14px; font-size:12px; }
        .ext-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:4px; margin-bottom:10px; }
        .ext-box { background:#eef; border:1px solid #cce; border-radius:3px; padding:4px 8px; font-size:11px; text-align:center; }
        .nav-back { display:inline-block; margin-bottom:16px; background:#4f5b93; color:#fff; padding:6px 16px; border-radius:4px; text-decoration:none; font-size:12px; }
        .nav-back:hover { background:#3d4a7a; }
    </style>
</head>
<body>
<div class="phpinfo-wrap">
    <a href="dashboard.php" class="nav-back">← Back to Dashboard</a>

    <h1 class="php-title">
        PHP Version 8.2.12
        <span>System: Windows NT DESKTOP-XAMPP 10.0 AMD64 &nbsp;|&nbsp; Build Date: Oct 24 2023</span>
    </h1>

    <div class="info-bar">
        <strong>Configuration File (php.ini):</strong> C:\xampp\php\php.ini &nbsp;|&nbsp;
        <strong>Loaded:</strong> C:\xampp\php\php.ini &nbsp;|&nbsp;
        <strong>Scan dir:</strong> (none)
    </div>

    <div class="section-header">PHP Core</div>
    <table>
        <tr class="h"><td>Directive</td><td>Local Value</td><td>Master Value</td></tr>
        <tr><td>allow_url_fopen</td><td><span class="enabled">On</span></td><td><span class="enabled">On</span></td></tr>
        <tr><td>allow_url_include</td><td><span class="disabled">Off</span></td><td><span class="disabled">Off</span></td></tr>
        <tr><td>display_errors</td><td><span class="enabled">On</span></td><td><span class="enabled">On</span></td></tr>
        <tr><td>error_reporting</td><td>32767</td><td>32767</td></tr>
        <tr><td>file_uploads</td><td><span class="enabled">On</span></td><td><span class="enabled">On</span></td></tr>
        <tr><td>max_execution_time</td><td>30</td><td>30</td></tr>
        <tr><td>max_input_time</td><td>60</td><td>60</td></tr>
        <tr><td>memory_limit</td><td>512M</td><td>512M</td></tr>
        <tr><td>post_max_size</td><td>40M</td><td>40M</td></tr>
        <tr><td>upload_max_filesize</td><td>40M</td><td>40M</td></tr>
        <tr><td>session.save_path</td><td>C:\xampp\tmp</td><td>C:\xampp\tmp</td></tr>
        <tr><td>date.timezone</td><td>UTC</td><td>UTC</td></tr>
        <tr><td>default_charset</td><td>UTF-8</td><td>UTF-8</td></tr>
        <tr><td>short_open_tag</td><td><span class="disabled">Off</span></td><td><span class="disabled">Off</span></td></tr>
        <tr><td>zend.assertions</td><td>1</td><td>1</td></tr>
    </table>

    <div class="section-header">PDO</div>
    <table>
        <tr class="h"><td>Feature</td><td>Support</td></tr>
        <tr><td>PDO drivers</td><td>mysql, sqlite, pgsql</td></tr>
        <tr><td>PDO MySQL driver version</td><td>mysqlnd 8.2.12</td></tr>
    </table>

    <div class="section-header">mysqli</div>
    <table>
        <tr class="h"><td>Feature</td><td>Support</td></tr>
        <tr><td>MySQLi Support</td><td><span class="enabled">enabled</span></td></tr>
        <tr><td>Client API version</td><td>mysqlnd 8.2.12</td></tr>
        <tr><td>Client API library version</td><td>mysqlnd 8.2.12</td></tr>
        <tr><td>MYSQLI_SOCKET</td><td>/tmp/mysql.sock</td></tr>
    </table>

    <div class="section-header">curl</div>
    <table>
        <tr class="h"><td>Feature</td><td>Support</td></tr>
        <tr><td>cURL support</td><td><span class="enabled">enabled</span></td></tr>
        <tr><td>cURL Information</td><td>7.87.0</td></tr>
        <tr><td>HTTP support</td><td><span class="enabled">Yes</span></td></tr>
        <tr><td>HTTPS support</td><td><span class="enabled">Yes</span></td></tr>
    </table>

    <div class="section-header">OpenSSL</div>
    <table>
        <tr class="h"><td>Feature</td><td>Support</td></tr>
        <tr><td>OpenSSL support</td><td><span class="enabled">enabled</span></td></tr>
        <tr><td>OpenSSL Library Version</td><td>OpenSSL 3.1.3 19 Sep 2023</td></tr>
        <tr><td>OpenSSL Header Version</td><td>OpenSSL 3.1.3 19 Sep 2023</td></tr>
        <tr><td>Openssl default config</td><td>C:\xampp\apache\bin\openssl.cnf</td></tr>
    </table>

    <div class="section-header">GD</div>
    <table>
        <tr class="h"><td>Feature</td><td>Support</td></tr>
        <tr><td>GD Support</td><td><span class="enabled">enabled</span></td></tr>
        <tr><td>GD Version</td><td>bundled (2.1.0 compatible)</td></tr>
        <tr><td>JPEG Support</td><td><span class="enabled">enabled</span></td></tr>
        <tr><td>PNG Support</td><td><span class="enabled">enabled</span></td></tr>
        <tr><td>WebP Support</td><td><span class="enabled">enabled</span></td></tr>
        <tr><td>GIF Support</td><td><span class="enabled">enabled</span></td></tr>
    </table>

    <div class="section-header">Zend Engine</div>
    <table>
        <tr class="h"><td>Feature</td><td>Value</td></tr>
        <tr><td>Zend Engine Version</td><td>4.2.12</td></tr>
        <tr><td>JIT enabled</td><td><span class="enabled">On</span></td></tr>
        <tr><td>Zend Signal Handling</td><td><span class="disabled">disabled</span></td></tr>
        <tr><td>Zend Memory Manager</td><td><span class="enabled">enabled</span></td></tr>
        <tr><td>Zend Multibyte Support</td><td>provided by mbstring</td></tr>
    </table>

    <div class="section-header">Loaded Extensions</div>
    <div class="ext-grid">
        <?php
        $exts = ['bcmath','calendar','com_dotnet','ctype','curl','date','dom','exif','fileinfo',
                 'filter','ftp','gd','gettext','gmp','hash','iconv','imap','intl','json','ldap',
                 'libxml','mbstring','mysqli','mysqlnd','openssl','pcre','pdo','pdo_mysql',
                 'pdo_sqlite','phar','posix','readline','reflection','session','simplexml',
                 'soap','sodium','spl','sqlite3','standard','tokenizer','xml','xmlreader',
                 'xmlwriter','xsl','zip','zlib'];
        foreach($exts as $ext) echo "<div class='ext-box'>$ext</div>";
        ?>
    </div>

    <div class="section-header">Environment Variables</div>
    <table>
        <tr class="h"><td>Variable</td><td>Value</td></tr>
        <?php
        $env = ['DOCUMENT_ROOT'=>'C:/xampp/htdocs','SERVER_SOFTWARE'=>'Apache/2.4.58 (Win64) OpenSSL/3.1.3 PHP/8.2.12','SERVER_NAME'=>'localhost','SERVER_PORT'=>'80','GATEWAY_INTERFACE'=>'CGI/1.1','REQUEST_TIME'=>time(),'PHP_SELF'=>'/simulators/db/phpinfo.php'];
        foreach($env as $k=>$v) echo "<tr><td>$k</td><td>$v</td></tr>";
        ?>
    </table>

    <p style="text-align:center; color:#888; font-size:12px; margin-top:30px;">
        This page was generated by PHP 8.2.12 &nbsp;·&nbsp; XAMPP Simulator &nbsp;·&nbsp; <?= date('Y-m-d H:i:s') ?>
    </p>
</div>
</body>
</html>
