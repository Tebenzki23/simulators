<?php
session_start();
$is_authenticated = isset($_SESSION['user']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Windows 10 Web OS</title>
    <!-- CSS Styles -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/desktop.css">
    <link rel="stylesheet" href="css/window.css">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="css/fontawesome/css/all.min.css">
    
    <!-- Boot Screen Styles -->
    <style>
        #boot-screen {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: #000;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            transition: opacity 0.5s ease;
        }
        #boot-screen.hidden {
            opacity: 0;
            pointer-events: none;
        }
        .win10-loader {
            position: relative;
            width: 40px;
            height: 40px;
            margin-top: 60px;
        }
        .win10-loader .circle {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0;
            transform: rotate(225deg);
            animation-iteration-count: infinite;
            animation-name: orbit;
            animation-duration: 5.5s;
        }
        .win10-loader .circle .inner {
            position: absolute;
            width: 6px;
            height: 6px;
            background: #fff;
            border-radius: 50%;
            left: 0;
            top: 0;
        }
        .win10-loader .circle:nth-child(1) { animation-delay: 0.24s; }
        .win10-loader .circle:nth-child(2) { animation-delay: 0.48s; }
        .win10-loader .circle:nth-child(3) { animation-delay: 0.72s; }
        .win10-loader .circle:nth-child(4) { animation-delay: 0.96s; }
        .win10-loader .circle:nth-child(5) { animation-delay: 1.2s; }

        @keyframes orbit {
            0% { transform: rotate(225deg); opacity: 1; animation-timing-function: ease-out; }
            7% { transform: rotate(345deg); animation-timing-function: linear; }
            30% { transform: rotate(455deg); animation-timing-function: ease-in-out; }
            39% { transform: rotate(690deg); animation-timing-function: linear; }
            70% { transform: rotate(815deg); opacity: 1; animation-timing-function: ease-out; }
            75% { transform: rotate(945deg); animation-timing-function: ease-out; }
            76% { transform: rotate(945deg); opacity: 0; }
            100% { transform: rotate(945deg); opacity: 0; }
        }
    </style>
</head>
<body>
    <div id="os-container">
        <?php if (!$is_authenticated): ?>
            <!-- Boot Loader -->
            <div id="boot-screen">
                <div class="windows-logo">
                    <i class="fab fa-windows" style="font-size: 8rem; color: #0078D7;"></i>
                </div>
                <div class="win10-loader">
                    <div class="circle"><div class="inner"></div></div>
                    <div class="circle"><div class="inner"></div></div>
                    <div class="circle"><div class="inner"></div></div>
                    <div class="circle"><div class="inner"></div></div>
                    <div class="circle"><div class="inner"></div></div>
                </div>
            </div>

            <!-- Lock / Login Screen -->
            <div id="lock-screen" class="active">
                <div class="time-container">
                    <h1 id="lock-time">10:00</h1>
                    <h2 id="lock-date">Monday, January 1</h2>
                </div>
                <div class="login-container" id="login-container">
                    <img src="storage/images/avatar.png" alt="User Avatar" class="user-avatar">
                    <h2 class="user-name">Administrator</h2>
                    <form id="login-form">
                        <div class="input-group">
                            <input type="password" id="password" placeholder="Password" autocomplete="off">
                            <button type="submit"><i class="fas fa-arrow-right"></i></button>
                        </div>
                        <p id="login-error" class="error-text"></p>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <!-- Desktop Environment -->
            <div id="desktop" class="active">
                <div id="desktop-bg"></div>
                <!-- Desktop Icons -->
                <div id="desktop-icons"></div>
                
                <!-- Windows Container -->
                <div id="windows-container"></div>
                
                <!-- Taskbar -->
                <div id="taskbar">
                    <!-- Start Button -->
                    <div id="start-btn" class="taskbar-item">
                        <i class="fab fa-windows"></i>
                    </div>
                    
                    <!-- Search Box Placeholder -->
                    <div id="taskbar-search">
                        <i class="fas fa-search"></i>
                        <span>Type here to search</span>
                    </div>

                    <!-- Open Apps in Taskbar -->
                    <div id="taskbar-apps"></div>
                    
                    <!-- System Tray -->
                    <div id="system-tray">
                        <div class="tray-icon" id="tray-show-hidden-btn"><i class="fas fa-chevron-up"></i></div>
                        <div class="tray-icon" id="network-icon"><i class="fas fa-wifi"></i></div>
                        <div class="tray-icon" id="audio-icon"><i class="fas fa-volume-up"></i></div>
                        <div class="tray-icon tray-time-date">
                            <span id="tray-time">10:00 AM</span>
                            <span id="tray-date">1/1/2026</span>
                        </div>
                        <div id="action-center-btn" class="tray-icon"><i class="far fa-comment-alt"></i></div>
                        <div id="desktop-show-btn"></div>
                    </div>
                </div>
                
                <!-- Start Menu -->
                <div id="start-menu">
                    <div class="start-sidebar">
                        <div class="start-item btn-menu"><i class="fas fa-bars"></i></div>
                        <div class="start-sidebar-bottom">
                            <div class="start-item" id="start-user"><i class="fas fa-user" style="color: #40c4ff; filter: drop-shadow(0px 2px 4px rgba(64,196,255,0.4));"></i></div>
                            <div class="start-item" id="start-documents"><i class="fas fa-file-alt" style="color: #b388ff; filter: drop-shadow(0px 2px 4px rgba(179,136,255,0.4));"></i></div>
                            <div class="start-item" id="start-pictures"><i class="fas fa-image" style="color: #b2ff59; filter: drop-shadow(0px 2px 4px rgba(178,255,89,0.4));"></i></div>
                            <div class="start-item" id="start-settings"><i class="fas fa-cog" style="color: #ff8a80; filter: drop-shadow(0px 2px 4px rgba(255,138,128,0.4));"></i></div>
                            <div class="start-item" id="start-power"><i class="fas fa-power-off" style="color: #ff5252; filter: drop-shadow(0px 2px 4px rgba(255,82,82,0.4));"></i></div>
                        </div>
                    </div>
                    <div class="start-apps-list">
                        <!-- App list populated by JS -->
                    </div>
                    <div class="start-tiles">
                        <!-- Metro Live Tiles -->
                    </div>
                </div>

                <!-- BSOD Overlay -->
                <div id="bsod-screen">
                    <div class="bsod-content">
                        <h1 class="bsod-face">:(</h1>
                        <h2>Your PC ran into a problem and needs to restart. We're just collecting some error info, and then we'll restart for you.</h2>
                        <div class="bsod-progress">0% complete</div>
                        <div class="bsod-details">
                            <img src="storage/images/qr_code.svg" alt="QR Code" class="bsod-qr">
                            <div class="bsod-info">
                                <p>For more information about this issue and possible fixes, visit https://windows.com/stopcode</p>
                                <p>If you call a support person, give them this info:</p>
                                <p>Stop code: <span id="bsod-stopcode">CRITICAL_PROCESS_DIED</span></p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        <?php endif; ?>
    </div>
    
    <!-- Scripts -->
    <!-- Define base URL for fetch commands if needed -->
    <script>window.isAuth = <?php echo $is_authenticated ? 'true' : 'false'; ?>;</script>
    <script src="js/process.js?v=<?= time() ?>"></script>
    <script src="js/filesystem.js?v=<?= time() ?>"></script>
    <script src="js/window.js?v=<?= time() ?>"></script>
    <script src="js/os.js?v=<?= time() ?>"></script>
</body>
</html>
