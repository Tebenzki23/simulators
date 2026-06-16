<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XAMPP Control Panel v3.3.0 Simulator</title>
    <meta name="description" content="A fully interactive XAMPP Control Panel simulator with Apache, MySQL, and other module management.">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- TOAST CONTAINER -->
    <div id="toast-container"></div>

    <div class="xampp-window">
        <!-- TITLE BAR -->
        <div class="title-bar">
            <div class="title-bar-buttons">
                <span class="tb-btn tb-close" onclick="handleQuit()"></span>
                <span class="tb-btn tb-min"></span>
                <span class="tb-btn tb-max"></span>
            </div>
            <div class="title-bar-text">XAMPP Control Panel v3.3.0</div>
            <div class="title-bar-right">
                <button id="theme-toggle" class="theme-btn">🌙</button>
            </div>
        </div>

        <!-- HEADER -->
        <div class="xampp-header">
            <div class="logo">
                <div class="logo-icon">X</div>
                <div class="logo-text-wrap">
                    <span class="logo-main">XAMPP Control Panel</span>
                    <span class="logo-sub">Apache + MariaDB + PHP + Perl</span>
                </div>
            </div>
            <div class="header-stats">
                <div class="stat-chip" id="uptime-chip">
                    <span class="stat-icon">⏱</span>
                    <span id="uptime-display">Uptime: 00:00:00</span>
                </div>
                <div class="stat-chip" id="connections-chip">
                    <span class="stat-icon">🔗</span>
                    <span id="connections-display">Connections: 0</span>
                </div>
                <div class="stat-chip" id="version-chip">
                    <span class="stat-icon">📦</span>
                    <span>v3.3.0</span>
                </div>
            </div>
        </div>

        <!-- TOOLBAR -->
        <div class="toolbar">
            <button class="toolbar-btn" onclick="openModal('services-modal')" id="btn-services">
                <span>⚙️</span> Services
            </button>
            <button class="toolbar-btn" onclick="openShellModal()" id="btn-shell">
                <span>🖥</span> Shell
            </button>
            <button class="toolbar-btn" onclick="openModal('explorer-modal')" id="btn-explorer">
                <span>📁</span> Explorer
            </button>
            <button class="toolbar-btn" onclick="openNetstatModal()" id="btn-netstat">
                <span>🌐</span> Netstat
            </button>
            <button class="toolbar-btn" onclick="openConfigModal()" id="btn-config">
                <span>📝</span> Config
            </button>
            <div class="toolbar-sep"></div>
            <button class="toolbar-btn" onclick="window.open('phpinfo.php','_blank')" id="btn-phpinfo">
                <span>ℹ️</span> PHPInfo
            </button>
            <button class="toolbar-btn" onclick="fetchStatus()" id="btn-refresh">
                <span>🔄</span> Refresh
            </button>
            <button class="toolbar-btn toolbar-btn-quit" onclick="handleQuit()">
                <span>✕</span> Quit
            </button>
        </div>

        <!-- BODY -->
        <div class="xampp-body">
            <div class="modules-container">
                <div class="modules-header">
                    <span class="col-status">Status</span>
                    <span class="col-module">Module</span>
                    <span class="col-pid">PID(s)</span>
                    <span class="col-port">Port(s)</span>
                    <span class="col-actions">Actions</span>
                </div>
                <div id="modules-list">
                    <!-- Modules populated by JS -->
                </div>
            </div>
        </div>

        <!-- LOG SECTION -->
        <div class="log-section">
            <div class="log-header-bar">
                <div class="log-tabs">
                    <button class="log-tab active" onclick="setLogFilter('all', this)">All</button>
                    <button class="log-tab" onclick="setLogFilter('Apache', this)">Apache</button>
                    <button class="log-tab" onclick="setLogFilter('MySQL', this)">MySQL</button>
                </div>
                <div class="log-controls">
                    <label class="autoscroll-toggle">
                        <input type="checkbox" id="autoscroll-chk" checked> Auto-scroll
                    </label>
                    <button onclick="clearLogs()" class="clear-logs-btn">🗑 Clear</button>
                </div>
            </div>
            <div class="log-window" id="log-window">
                <div class="log-placeholder">Loading logs…</div>
            </div>
        </div>

        <!-- STATUS BAR -->
        <div class="status-bar">
            <span id="status-bar-text">Ready</span>
            <span id="status-bar-time"></span>
        </div>
    </div>

    <!-- ===== MODALS ===== -->

    <!-- CONFIG MODAL -->
    <div id="config-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>📝 Configuration File Editor</h3>
                <span class="close-btn" onclick="closeModal('config-modal')">&times;</span>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="config-file-select">Select Configuration File:</label>
                    <select id="config-file-select" onchange="loadConfigFile()">
                        <option value="httpd.conf">Apache — httpd.conf</option>
                        <option value="my.ini">MySQL — my.ini</option>
                    </select>
                </div>
                <textarea id="config-text-area" rows="16" placeholder="Loading configuration file..."></textarea>
            </div>
            <div class="modal-footer">
                <span id="config-status-msg" class="status-msg"></span>
                <button onclick="saveConfigFile()" class="btn-primary">💾 Save Changes</button>
                <button onclick="closeModal('config-modal')" class="btn-secondary">Close</button>
            </div>
        </div>
    </div>

    <!-- NETSTAT MODAL -->
    <div id="netstat-modal" class="modal">
        <div class="modal-content modal-wide">
            <div class="modal-header">
                <h3>🌐 Netstat — Active Socket Connections</h3>
                <div style="display:flex; align-items:center; gap:10px;">
                    <button class="btn-sm" onclick="openNetstatModal()">🔄 Refresh</button>
                    <span class="close-btn" onclick="closeModal('netstat-modal')">&times;</span>
                </div>
            </div>
            <div class="modal-body">
                <div id="netstat-summary" class="netstat-summary"></div>
                <table class="netstat-table">
                    <thead>
                        <tr>
                            <th>Protocol</th>
                            <th>Local Address</th>
                            <th>Port</th>
                            <th>State</th>
                            <th>PID</th>
                            <th>Process / Module</th>
                        </tr>
                    </thead>
                    <tbody id="netstat-tbody"></tbody>
                </table>
            </div>
            <div class="modal-footer">
                <span class="status-msg" id="netstat-status"></span>
                <button onclick="closeModal('netstat-modal')" class="btn-secondary">Close</button>
            </div>
        </div>
    </div>

    <!-- SHELL TERMINAL MODAL -->
    <div id="shell-modal" class="modal">
        <div class="modal-content terminal-content">
            <div class="modal-header terminal-header">
                <div class="terminal-dots">
                    <span class="tdot tdot-red" onclick="closeModal('shell-modal')"></span>
                    <span class="tdot tdot-yellow"></span>
                    <span class="tdot tdot-green"></span>
                </div>
                <h3>XAMPP Shell — bash</h3>
                <span class="close-btn terminal-close" onclick="closeModal('shell-modal')">&times;</span>
            </div>
            <div class="modal-body terminal-body" id="terminal-body-wrap">
                <div id="terminal-output">Welcome to the XAMPP Shell Simulator.
Type <span style="color:#f1c40f">"help"</span> for a list of available commands.
Type <span style="color:#2ecc71">"mysql"</span> to enter the MySQL Interactive Client.
─────────────────────────────────────
</div>
                <div class="terminal-input-line">
                    <span id="terminal-prompt">shell&gt;&nbsp;</span>
                    <input type="text" id="terminal-input" autofocus autocomplete="off" spellcheck="false" />
                </div>
            </div>
        </div>
    </div>

    <!-- SERVICES MODAL -->
    <div id="services-modal" class="modal">
        <div class="modal-content modal-wide">
            <div class="modal-header">
                <h3>⚙️ Windows Services Manager</h3>
                <span class="close-btn" onclick="closeModal('services-modal')">&times;</span>
            </div>
            <div class="modal-body" style="padding:0;">
                <table class="services-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Display Name</th>
                            <th>Status</th>
                            <th>Startup Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="services-tbody">
                        <!-- Populated by JS -->
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <span class="status-msg" style="font-size:11px; color:#888;">Note: Service states are synchronized with XAMPP module states.</span>
                <button onclick="closeModal('services-modal')" class="btn-secondary">Close</button>
            </div>
        </div>
    </div>

    <!-- EXPLORER MODAL -->
    <div id="explorer-modal" class="modal">
        <div class="modal-content modal-wide">
            <div class="modal-header">
                <h3>📁 XAMPP File Explorer</h3>
                <span class="close-btn" onclick="closeModal('explorer-modal')">&times;</span>
            </div>
            <div class="modal-body explorer-body">
                <div class="explorer-sidebar">
                    <div class="explorer-tree">
                        <div class="etree-root active" onclick="showExplorerPath('htdocs')">📂 htdocs/</div>
                        <div class="etree-item" onclick="showExplorerPath('apache')">📂 apache/</div>
                        <div class="etree-item" onclick="showExplorerPath('mysql')">📂 mysql/</div>
                        <div class="etree-item" onclick="showExplorerPath('php')">📂 php/</div>
                        <div class="etree-item" onclick="showExplorerPath('phpmyadmin')">📂 phpmyadmin/</div>
                    </div>
                </div>
                <div class="explorer-main">
                    <div class="explorer-path-bar">
                        <span>📍</span> <span id="explorer-current-path">C:/xampp/htdocs</span>
                    </div>
                    <div id="explorer-file-list" class="explorer-file-list"></div>
                </div>
            </div>
            <div class="modal-footer">
                <span class="status-msg" id="explorer-status"></span>
                <button onclick="closeModal('explorer-modal')" class="btn-secondary">Close</button>
            </div>
        </div>
    </div>

    <!-- STARTUP PROGRESS OVERLAY -->
    <div id="startup-overlay" class="startup-overlay" style="display:none;">
        <div class="startup-box">
            <div class="startup-icon" id="startup-icon">⚙️</div>
            <div class="startup-module-name" id="startup-module-name">Apache</div>
            <div class="startup-action" id="startup-action">Starting...</div>
            <div class="startup-progress-track">
                <div class="startup-progress-fill" id="startup-progress-fill"></div>
            </div>
            <div class="startup-detail" id="startup-detail">Initializing service...</div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
