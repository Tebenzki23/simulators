// Main OS Logic

document.addEventListener('DOMContentLoaded', () => {
    
    // Boot Screen
    const bootScreen = document.getElementById('boot-screen');
    if (bootScreen) {
        setTimeout(() => {
            bootScreen.classList.add('hidden');
            setTimeout(() => bootScreen.style.display = 'none', 500);
        }, 2500); // 2.5s boot time
    }

    // Auth & Lock Screen
    const lockScreen = document.getElementById('lock-screen');
    const desktop = document.getElementById('desktop');
    const loginForm = document.getElementById('login-form');
    
    if (!window.isAuth) {
        // Update lock screen time
        setInterval(() => {
            const now = new Date();
            document.getElementById('lock-time').innerText = now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            document.getElementById('lock-date').innerText = now.toLocaleDateString([], {weekday: 'long', month: 'long', day: 'numeric'});
        }, 1000);
        
        // Click anywhere to show login form
        lockScreen.addEventListener('click', (e) => {
            if (e.target === lockScreen) {
                document.querySelector('.time-container').style.opacity = '0';
                document.getElementById('login-container').classList.add('active');
            }
        });
        
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const password = document.getElementById('password').value;
            const res = await fetch('api/auth.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=login&password=${encodeURIComponent(password)}`
            });
            const data = await res.json();
            if (data.success) {
                lockScreen.classList.add('dismissed');
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            } else {
                document.getElementById('login-error').innerText = data.error;
            }
        });
    } else {
        initDesktop();
    }
    
    // Initialize Desktop Environment
    function initDesktop() {
        // System Tray Timer
        setInterval(() => {
            const now = new Date();
            document.getElementById('tray-time').innerText = now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            document.getElementById('tray-date').innerText = now.toLocaleDateString([], {month: 'numeric', day: 'numeric', year: 'numeric'});
        }, 1000);
        
        // Start Menu Toggle
        const startBtn = document.getElementById('start-btn');
        const startMenu = document.getElementById('start-menu');
        startBtn.addEventListener('click', () => {
            startMenu.classList.toggle('open');
            startBtn.classList.toggle('open');
        });
        
        // Close start menu when clicking outside
        document.addEventListener('mousedown', (e) => {
            if (!startMenu.contains(e.target) && !startBtn.contains(e.target) && startMenu.classList.contains('open')) {
                startMenu.classList.remove('open');
                startBtn.classList.remove('open');
            }
        });
        
        // Populate Desktop Icons
        const icons = [
            { name: 'This PC', icon: 'fas fa-desktop', style: 'color: #00e5ff; filter: drop-shadow(0px 2px 4px rgba(0,229,255,0.4));', app: 'thispc' },
            { name: 'Recycle Bin', icon: 'fas fa-trash-alt', style: 'color: #90a4ae; filter: drop-shadow(0px 2px 4px rgba(144,164,174,0.4));', app: 'thispc', path: 'RecycleBin' },
            { name: 'Notepad', icon: 'fas fa-file-alt', style: 'color: #40c4ff; filter: drop-shadow(0px 2px 4px rgba(64,196,255,0.4));', app: 'notepad' },
            { name: 'Task Manager', icon: 'fas fa-tasks', style: 'color: #448aff; filter: drop-shadow(0px 2px 4px rgba(68,138,255,0.4));', app: 'taskmgr' },
            { name: 'Terminal', icon: 'fas fa-terminal', style: 'color: #607d8b; filter: drop-shadow(0px 2px 4px rgba(96,125,139,0.4));', app: 'cmd' },
            { name: 'Browser', icon: 'fab fa-chrome', style: 'color: #ff5252; filter: drop-shadow(0px 2px 4px rgba(255,82,82,0.4));', app: 'browser' },
            { name: 'Settings', icon: 'fas fa-cog', style: 'color: #b0bec5; filter: drop-shadow(0px 2px 4px rgba(176,190,197,0.4));', app: 'sysinfo' },
            { name: 'Calculator', icon: 'fas fa-calculator', style: 'color: #18ffff; filter: drop-shadow(0px 2px 4px rgba(24,255,255,0.4));', app: 'calculator' }
        ];
        
        const deskIconsContainer = document.getElementById('desktop-icons');
        icons.forEach(ico => {
            const el = document.createElement('div');
            el.className = 'desktop-icon';
            el.innerHTML = `<i class="${ico.icon}" style="font-size:2rem; margin-bottom:5px; ${ico.style || ''}"></i><span>${ico.name}</span>`;
            
            el.addEventListener('click', (e) => {
                e.stopPropagation();
                document.querySelectorAll('.desktop-icon').forEach(n => n.classList.remove('selected'));
                el.classList.add('selected');
            });
            
            el.addEventListener('dblclick', () => {
                launchApp(ico);
            });
            deskIconsContainer.appendChild(el);
        });
        
        // Deselect icons when clicking desktop
        deskIconsContainer.addEventListener('click', () => {
            document.querySelectorAll('.desktop-icon').forEach(n => n.classList.remove('selected'));
        });
        
        // Populate Start Menu Apps List and Tiles
        const startAppsList = document.querySelector('.start-apps-list');
        const startTiles = document.querySelector('.start-tiles');
        icons.forEach(ico => {
            // Apps list
            const appEl = document.createElement('div');
            appEl.className = 'app-list-item';
            appEl.innerHTML = `<i class="${ico.icon}" style="width:20px;text-align:center;${ico.style || ''}"></i><span>${ico.name}</span>`;
            appEl.addEventListener('click', () => {
                startMenu.classList.remove('open');
                startBtn.classList.remove('open');
                launchApp(ico);
            });
            startAppsList.appendChild(appEl);
            
            // Tiles (only for some apps)
            if (['thispc', 'explorer', 'notepad', 'browser', 'sysinfo'].includes(ico.app)) {
                const tileEl = document.createElement('div');
                tileEl.className = 'tile';
                if(ico.app === 'browser') tileEl.classList.add('wide');
                tileEl.innerHTML = `<i class="${ico.icon}" style="color:#fff"></i><span>${ico.name}</span>`;
                tileEl.addEventListener('click', () => {
                     startMenu.classList.remove('open');
                     startBtn.classList.remove('open');
                     launchApp(ico);
                });
                startTiles.appendChild(tileEl);
            }
        });
        
        // Start Menu Sidebar Buttons
        document.getElementById('start-power').addEventListener('click', async () => {
            if(confirm('Are you sure you want to shut down (logout)?')) {
                await fetch('api/auth.php', { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: 'action=logout' });
                window.location.reload();
            }
        });
        document.getElementById('start-settings').addEventListener('click', () => {
            startMenu.classList.remove('open');
            startBtn.classList.remove('open');
            launchApp({name: 'Settings', icon: 'fas fa-cog', app: 'sysinfo'});
        });
        document.getElementById('start-documents').addEventListener('click', () => {
            startMenu.classList.remove('open');
            startBtn.classList.remove('open');
            launchApp({name: 'Documents', icon: 'fas fa-folder', app: 'explorer', path: 'Documents'});
        });
        document.getElementById('start-pictures').addEventListener('click', () => {
            startMenu.classList.remove('open');
            startBtn.classList.remove('open');
            launchApp({name: 'Pictures', icon: 'fas fa-folder', app: 'explorer', path: 'Pictures'});
        });
        document.getElementById('start-user').addEventListener('click', () => {
            startMenu.classList.remove('open');
            startBtn.classList.remove('open');
            alert('Logged in as Administrator. Welcome!');
        });
        
        // Taskbar Search
        const tbSearch = document.getElementById('taskbar-search');
        tbSearch.innerHTML = '<i class="fas fa-search"></i><input type="text" placeholder="Type here to search" style="border:none;background:transparent;outline:none;width:100%;">';
        tbSearch.querySelector('input').addEventListener('keydown', (e) => {
            if(e.key === 'Enter' && e.target.value) {
                launchApp({name: 'Search', icon: 'fab fa-chrome', app: 'browser'});
                e.target.value = '';
            }
        });

        // System tray actions
        document.getElementById('tray-show-hidden-btn').addEventListener('click', () => {
            const tray = document.getElementById('system-tray');
            const hiddenMenu = document.getElementById('hidden-icons-menu');
            if(hiddenMenu) {
                hiddenMenu.remove();
            } else {
                const menu = document.createElement('div');
                menu.id = 'hidden-icons-menu';
                menu.style.position = 'absolute';
                menu.style.bottom = '40px';
                menu.style.right = '10px';
                menu.style.background = '#2b2b2b';
                menu.style.border = '1px solid #444';
                menu.style.padding = '5px';
                menu.style.color = 'white';
                menu.style.display = 'grid';
                menu.style.gridTemplateColumns = 'repeat(3, 1fr)';
                menu.style.gap = '5px';
                menu.style.borderRadius = '5px';
                menu.style.boxShadow = '0px 0px 10px rgba(0,0,0,0.5)';
                menu.innerHTML = `
                    <div class="tray-icon" title="Windows Security" style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;border-radius:3px;"><i class="fas fa-shield-alt"></i></div>
                    <div class="tray-icon" title="Bluetooth Devices" style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;border-radius:3px;"><i class="fab fa-bluetooth-b"></i></div>
                    <div class="tray-icon" title="Windows Update" style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;border-radius:3px;"><i class="fas fa-sync"></i></div>
                `;
                
                // Add hover effect
                menu.querySelectorAll('.tray-icon').forEach(icon => {
                    icon.addEventListener('mouseenter', () => icon.style.background = 'rgba(255,255,255,0.1)');
                    icon.addEventListener('mouseleave', () => icon.style.background = 'transparent');
                });
                
                document.getElementById('desktop').appendChild(menu);
                
                setTimeout(() => {
                    document.addEventListener('click', function closeMenu(e) {
                        if(e.target.closest('#tray-show-hidden-btn')) return;
                        menu.remove();
                        document.removeEventListener('click', closeMenu);
                    });
                }, 10);
            }
        });
        document.getElementById('desktop-show-btn').addEventListener('click', () => {
            document.querySelectorAll('.os-window').forEach(w => w.classList.add('minimized'));
            document.querySelectorAll('.taskbar-app').forEach(a => a.classList.remove('active'));
        });
        document.getElementById('action-center-btn').addEventListener('click', () => alert('No new notifications.'));
        document.getElementById('audio-icon').addEventListener('click', () => alert('Volume: 100%'));
        
        // Taskbar Apps sync
        window.addEventListener('os:processStarted', (e) => updateTaskbar());
        window.addEventListener('os:processKilled', (e) => updateTaskbar());
        window.addEventListener('os:windowFocused', (e) => {
            document.querySelectorAll('.taskbar-app').forEach(el => {
                if(parseInt(el.dataset.pid) === e.detail.pid) el.classList.add('active');
                else el.classList.remove('active');
            });
        });
    }
    
    // Launch Application helper
    function launchApp(ico) {
        // Will inject iframe or run html depending on app
        const urlMap = {
            'thispc': 'apps/thispc.html',
            'explorer': 'apps/explorer.html',
            'notepad': 'apps/notepad.html',
            'taskmgr': 'apps/taskmgr.html',
            'cmd': 'apps/terminal.html',
            'browser': 'apps/browser.html',
            'sysinfo': 'apps/sysinfo.html',
            'calculator': 'apps/calculator.html'
        };
        
        if (urlMap[ico.app]) {
            let url = urlMap[ico.app] + '?v=' + Date.now();
            if (ico.path) {
                url += '&path=' + encodeURIComponent(ico.path);
            }
            window.windowManager.createWindow({
                name: ico.name,
                icon: ico.icon,
                url: url,
                width: 700,
                height: 500
            });
        }
    }
    
    // Network connectivity monitoring
    const netIcon = document.getElementById('network-icon');
    function updateNetworkStatus() {
        if(navigator.onLine) {
            netIcon.innerHTML = '<i class="fas fa-wifi"></i>';
            netIcon.title = "Internet access";
        } else {
            netIcon.innerHTML = '<i class="fas fa-globe"></i><i class="fas fa-ban" style="position:absolute;font-size:0.5rem;color:red;margin-top:5px;margin-left:5px;"></i>';
            netIcon.title = "Not connected";
        }
    }
    window.addEventListener('online', updateNetworkStatus);
    window.addEventListener('offline', updateNetworkStatus);
    updateNetworkStatus();
    
    // BSOD Listener
    document.addEventListener('os:bsod', (e) => {
        const bsod = document.getElementById('bsod-screen');
        const code = document.getElementById('bsod-stopcode');
        code.innerText = e.detail || 'CRITICAL_PROCESS_DIED';
        bsod.classList.add('active');
        
        // Mock progress
        let pct = 0;
        const prog = bsod.querySelector('.bsod-progress');
        const ival = setInterval(() => {
            pct += Math.floor(Math.random() * 20);
            if(pct >= 100) {
                pct = 100;
                clearInterval(ival);
                setTimeout(() => window.location.reload(), 2000); // Reload browser
            }
            prog.innerText = pct + '% complete';
        }, 1000);
    });
    
    function updateTaskbar() {
        const tb = document.getElementById('taskbar-apps');
        tb.innerHTML = '';
        window.processManager.getProcesses().forEach(p => {
            const el = document.createElement('div');
            el.className = 'taskbar-app open';
            el.dataset.pid = p.pid;
            el.innerHTML = `<i class="${p.icon}"></i>`;
            el.title = p.name;
            
            if (p.DOMelement && p.DOMelement.classList.contains('focused')) {
                el.classList.add('active');
            }
            
            el.addEventListener('click', () => {
                if (p.DOMelement) {
                    if (p.DOMelement.classList.contains('focused')) {
                        p.DOMelement.classList.add('minimized');
                        el.classList.remove('active');
                    } else {
                        window.windowManager.focusWindow(p.DOMelement);
                    }
                }
            });
            tb.appendChild(el);
        });
    }

    // Keyboard Shortcuts
    window.addEventListener('keydown', (e) => {
        if (e.ctrlKey && e.shiftKey && e.key === 'Escape') {
            e.preventDefault();
            launchApp({ name: 'Task Manager', icon: 'fas fa-tasks', app: 'taskmgr' });
        }
    });

    // Taskbar Context Menu
    const taskbar = document.getElementById('taskbar');
    taskbar.addEventListener('contextmenu', (e) => {
        e.preventDefault();
        
        // Remove existing context menus
        document.querySelectorAll('.os-context-menu').forEach(m => m.remove());

        const menu = document.createElement('div');
        menu.className = 'os-context-menu';
        menu.style.position = 'absolute';
        menu.style.bottom = '40px';
        menu.style.left = e.clientX + 'px';
        menu.style.background = '#eee';
        menu.style.border = '1px solid #aaa';
        menu.style.boxShadow = '2px 2px 5px rgba(0,0,0,0.2)';
        menu.style.zIndex = '9999';
        menu.style.fontSize = '12px';
        menu.style.minWidth = '180px';
        menu.style.padding = '3px 0';

        const item = document.createElement('div');
        item.innerText = 'Task Manager';
        item.style.padding = '5px 25px';
        item.style.cursor = 'default';
        item.addEventListener('mouseenter', () => item.style.background = '#91c9f7');
        item.addEventListener('mouseleave', () => item.style.background = 'transparent');
        item.addEventListener('click', () => {
            launchApp({ name: 'Task Manager', icon: 'fas fa-tasks', app: 'taskmgr' });
            menu.remove();
        });

        menu.appendChild(item);
        document.body.appendChild(menu);

        document.addEventListener('mousedown', function close(e) {
            if (!menu.contains(e.target)) {
                menu.remove();
                document.removeEventListener('mousedown', close);
            }
        });
    });
});
