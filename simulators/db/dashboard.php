<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome to XAMPP — Apache Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <header class="top-nav">
        <div class="nav-container">
            <div class="logo">
                <div class="logo-x">X</div>
                <div>
                    <span class="xampp-text">XAMPP</span>
                    <span class="apache-friends">Apache + MariaDB + PHP + Perl</span>
                </div>
            </div>
            <nav class="nav-links">
                <a href="index.php">🏠 Control Panel</a>
                <a href="phpinfo.php" target="_blank">📋 PHPInfo</a>
                <a href="phpmyadmin.php" target="_blank">🗄 phpMyAdmin</a>
                <a href="https://apachefriends.org" target="_blank">❓ Help</a>
            </nav>
        </div>
    </header>

    <div class="hero-banner">
        <div class="hero-content">
            <h1>Welcome to XAMPP for Windows</h1>
            <p class="hero-sub">Version 8.2.12 &nbsp;·&nbsp; Apache 2.4.58 &nbsp;·&nbsp; MariaDB 10.4.32 &nbsp;·&nbsp; PHP 8.2.12</p>
            <div class="hero-badges">
                <span class="badge badge-green">✅ Apache Running</span>
                <span class="badge badge-green" id="mysql-badge">✅ MySQL Running</span>
                <span class="badge badge-blue">PHP 8.2.12</span>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="dashboard-grid">

            <!-- SERVER STATUS -->
            <div class="card card-wide">
                <div class="card-header">🖥 Server Status</div>
                <div class="card-body">
                    <div class="stat-grid">
                        <div class="stat"><div class="stat-val" id="stat-uptime">00:00:00</div><div class="stat-lbl">Uptime</div></div>
                        <div class="stat"><div class="stat-val">80, 443</div><div class="stat-lbl">Apache Ports</div></div>
                        <div class="stat"><div class="stat-val">3306</div><div class="stat-lbl">MySQL Port</div></div>
                        <div class="stat"><div class="stat-val" id="stat-requests">0</div><div class="stat-lbl">Requests/min</div></div>
                    </div>
                </div>
            </div>

            <!-- INSTALLED APPS -->
            <div class="card">
                <div class="card-header">📦 Installed Applications</div>
                <div class="card-body">
                    <div class="app-list">
                        <div class="app-item">
                            <span class="app-icon">🗄</span>
                            <div>
                                <strong>phpMyAdmin</strong>
                                <div class="app-desc">Database administration tool</div>
                            </div>
                            <a href="phpmyadmin.php" target="_blank" class="app-link">Open →</a>
                        </div>
                        <div class="app-item">
                            <span class="app-icon">📋</span>
                            <div>
                                <strong>phpinfo()</strong>
                                <div class="app-desc">PHP configuration overview</div>
                            </div>
                            <a href="phpinfo.php" target="_blank" class="app-link">Open →</a>
                        </div>
                        <div class="app-item">
                            <span class="app-icon">⚙️</span>
                            <div>
                                <strong>XAMPP Control Panel</strong>
                                <div class="app-desc">Module management interface</div>
                            </div>
                            <a href="index.php" class="app-link">Open →</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PHP INFO CARD -->
            <div class="card">
                <div class="card-header">🐘 PHP &amp; Components</div>
                <div class="card-body">
                    <table class="info-table">
                        <tr><td>PHP Version</td><td><strong>8.2.12</strong></td></tr>
                        <tr><td>Zend Engine</td><td>4.2.12</td></tr>
                        <tr><td>Extension dir</td><td>C:/xampp/php/ext</td></tr>
                        <tr><td>Memory limit</td><td>512M</td></tr>
                        <tr><td>Max exec time</td><td>30s</td></tr>
                        <tr><td>Upload max</td><td>40M</td></tr>
                        <tr><td>Date timezone</td><td>UTC</td></tr>
                    </table>
                </div>
            </div>

            <!-- GETTING STARTED -->
            <div class="card">
                <div class="card-header">🚀 Getting Started</div>
                <div class="card-body">
                    <div class="info-alert-box">
                        <strong>⚠️ Development Only</strong>
                        <p>XAMPP is meant only for local development. Do not expose it to the internet without proper security configuration.</p>
                    </div>
                    <p class="muted-text">Place your project files in:</p>
                    <code class="path-code">C:/xampp/htdocs/your-project/</code>
                    <p class="muted-text" style="margin-top:12px">Then visit:</p>
                    <code class="path-code">http://localhost/your-project/</code>
                </div>
            </div>

            <!-- COMMUNITY -->
            <div class="card card-wide">
                <div class="card-header">🌐 Community &amp; Resources</div>
                <div class="card-body">
                    <div class="resource-grid">
                        <a class="resource-item" href="https://apachefriends.org" target="_blank">
                            <span>📘</span><span>Apache Friends Forums</span>
                        </a>
                        <a class="resource-item" href="https://php.net/docs.php" target="_blank">
                            <span>📗</span><span>PHP Documentation</span>
                        </a>
                        <a class="resource-item" href="https://mariadb.com/kb/" target="_blank">
                            <span>📙</span><span>MariaDB Knowledge Base</span>
                        </a>
                        <a class="resource-item" href="https://httpd.apache.org/docs/" target="_blank">
                            <span>📕</span><span>Apache HTTP Server Docs</span>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <footer class="dashboard-footer">
        <span>XAMPP for Windows 8.2.12 — Simulator &copy; 2026</span>
        <span id="footer-time"></span>
    </footer>

    <script>
        // Live clock
        setInterval(() => {
            document.getElementById('footer-time').textContent = new Date().toLocaleString();
        }, 1000);

        // Simulated uptime counter
        let sec = 0;
        setInterval(() => {
            sec++;
            const h=String(Math.floor(sec/3600)).padStart(2,'0');
            const m=String(Math.floor((sec%3600)/60)).padStart(2,'0');
            const s=String(sec%60).padStart(2,'0');
            document.getElementById('stat-uptime').textContent = `${h}:${m}:${s}`;
            document.getElementById('stat-requests').textContent = Math.floor(sec * 0.8 + Math.random() * 3);
        }, 1000);
    </script>
</body>
</html>
