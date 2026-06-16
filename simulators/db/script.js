// ─── STATE ───────────────────────────────────────────────────────────────────
let activeModules = [], lastLogCount = 0, logFilter = 'all';
let terminalState = 'shell', mysqlDatabase = 'xampp_sim_db';
let cmdHistory = [], historyIdx = -1;
let uptimeSeconds = 0, uptimeInterval = null, connectionsCount = 0;

// ─── INIT ─────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    applyTheme();
    document.getElementById('theme-toggle').addEventListener('click', toggleTheme);
    document.getElementById('terminal-input').addEventListener('keydown', handleTerminalKey);
    document.querySelector('.terminal-body').addEventListener('click', () => document.getElementById('terminal-input').focus());
    fetchStatus();
    setInterval(fetchStatus, 3000);
    startClock();
});

// ─── THEME ────────────────────────────────────────────────────────────────────
function applyTheme() {
    const dark = localStorage.getItem('theme') === 'dark';
    document.body.classList.toggle('dark-theme', dark);
    document.getElementById('theme-toggle').textContent = dark ? '☀️' : '🌙';
}
function toggleTheme() {
    const dark = !document.body.classList.contains('dark-theme');
    document.body.classList.toggle('dark-theme', dark);
    localStorage.setItem('theme', dark ? 'dark' : 'light');
    document.getElementById('theme-toggle').textContent = dark ? '☀️' : '🌙';
}

// ─── CLOCK + UPTIME ───────────────────────────────────────────────────────────
function startClock() {
    setInterval(() => {
        const now = new Date();
        const el = document.getElementById('status-bar-time');
        if (el) el.textContent = now.toLocaleTimeString();
    }, 1000);
}
function startUptime() {
    if (uptimeInterval) return;
    uptimeSeconds = 0;
    uptimeInterval = setInterval(() => {
        uptimeSeconds++;
        const h = String(Math.floor(uptimeSeconds/3600)).padStart(2,'0');
        const m = String(Math.floor((uptimeSeconds%3600)/60)).padStart(2,'0');
        const s = String(uptimeSeconds%60).padStart(2,'0');
        const el = document.getElementById('uptime-display');
        if (el) el.textContent = `Uptime: ${h}:${m}:${s}`;
        connectionsCount = Math.floor(uptimeSeconds/10) + activeModules.filter(m=>m.status==='running').length * 3;
        const cel = document.getElementById('connections-display');
        if (cel) cel.textContent = `Connections: ${connectionsCount}`;
    }, 1000);
}
function stopUptime() {
    if (activeModules.every(m => m.status !== 'running')) {
        clearInterval(uptimeInterval); uptimeInterval = null;
        const el = document.getElementById('uptime-display');
        if (el) el.textContent = 'Uptime: 00:00:00';
        const cel = document.getElementById('connections-display');
        if (cel) cel.textContent = 'Connections: 0';
    }
}

// ─── STATUS FETCH ─────────────────────────────────────────────────────────────
function fetchStatus() {
    fetch('api.php?action=status').then(r=>r.json()).then(data => {
        if (data.error) {
            document.getElementById('log-window').innerHTML = `<div style="color:#e74c3c">${data.error} — <a href="setup.php" style="color:#79c0ff">Run setup.php</a></div>`;
            return;
        }
        activeModules = data.modules;
        renderModules(data.modules);
        renderLogs(data.logs);
        renderServicesTable(data.modules);
        const sb = document.getElementById('status-bar-text');
        const running = data.modules.filter(m=>m.status==='running').length;
        if (sb) sb.textContent = running ? `${running} module(s) running` : 'Ready';
        if (running > 0) startUptime(); else stopUptime();
    }).catch(() => {});
}

// ─── RENDER MODULES ───────────────────────────────────────────────────────────
function renderModules(modules) {
    const c = document.getElementById('modules-list');
    if (!c) return;
    c.innerHTML = '';
    modules.forEach(mod => {
        const running = mod.status === 'running';
        const row = document.createElement('div');
        row.className = 'module-row';
        row.innerHTML = `
            <div class="status-indicator"><div class="status-dot ${running?'running':''}"></div></div>
            <div class="module-name-cell ${running?'running':''}">${mod.name}</div>
            <div class="module-pid">${mod.pid||'—'}</div>
            <div class="module-port">${running?mod.port:'—'}</div>
            <div class="module-actions">
                <button class="start-btn ${running?'action-stop':'action-start'}" onclick="toggleModule('${mod.name}','${running?'stopped':'running'}')">
                    ${running?'Stop':'Start'}</button>
                <button class="admin-btn" onclick="openAdmin('${mod.name}')" ${!running?'disabled':''}>Admin</button>
                <button class="config-btn" onclick="openConfigModal('${mod.name==='Apache'?'httpd.conf':mod.name==='MySQL'?'my.ini':''}')" ${mod.name!=='Apache'&&mod.name!=='MySQL'?'disabled':''}>Config</button>
                <button class="logs-btn" onclick="showModuleLogs('${mod.name}')">Logs</button>
            </div>`;
        c.appendChild(row);
    });
}

// ─── TOGGLE MODULE (with startup animation) ───────────────────────────────────
function toggleModule(name, newStatus) {
    showStartupOverlay(name, newStatus);
    const fd = new FormData();
    fd.append('name', name); fd.append('status', newStatus);
    fetch('api.php?action=toggle', {method:'POST', body:fd})
        .then(r=>r.json()).then(d => {
            hideStartupOverlay();
            if (d.success) {
                toast(newStatus==='running'?'success':'info',
                    newStatus==='running'?`${name} Started`:`${name} Stopped`,
                    newStatus==='running'?`PID assigned. Ports now active.`:`Service stopped gracefully.`);
                fetchStatus();
            }
        }).catch(() => { hideStartupOverlay(); toast('error','Error','Could not toggle module.'); });
}

// ─── STARTUP ANIMATION ────────────────────────────────────────────────────────
const startupSteps = {
    running: ['Initializing service…','Loading configuration…','Binding ports…','Starting workers…','Service is ready!'],
    stopped: ['Sending stop signal…','Waiting for workers…','Releasing ports…','Stopped.']
};
function showStartupOverlay(name, action) {
    const overlay = document.getElementById('startup-overlay');
    const steps = startupSteps[action];
    overlay.style.display = 'flex';
    document.getElementById('startup-module-name').textContent = name;
    document.getElementById('startup-action').textContent = action==='running' ? 'Starting up…' : 'Stopping…';
    document.getElementById('startup-icon').textContent = action==='running' ? '⚙️' : '🛑';
    document.getElementById('startup-progress-fill').style.width = '0%';
    let step = 0;
    const interval = setInterval(() => {
        if (step >= steps.length) { clearInterval(interval); return; }
        document.getElementById('startup-detail').textContent = steps[step];
        document.getElementById('startup-progress-fill').style.width = `${((step+1)/steps.length)*100}%`;
        step++;
    }, 320);
    overlay._interval = interval;
}
function hideStartupOverlay() {
    const overlay = document.getElementById('startup-overlay');
    clearInterval(overlay._interval);
    document.getElementById('startup-progress-fill').style.width = '100%';
    setTimeout(() => { overlay.style.display = 'none'; }, 400);
}

// ─── ADMIN ────────────────────────────────────────────────────────────────────
function openAdmin(name) {
    const map = {Apache:'dashboard.php', MySQL:'phpmyadmin.php'};
    if (map[name]) window.open(map[name],'_blank');
    else toast('info', name+' Admin', 'Admin panel not yet implemented for '+name+'.');
}

// ─── LOGS ─────────────────────────────────────────────────────────────────────
function setLogFilter(filter, btn) {
    logFilter = filter;
    document.querySelectorAll('.log-tab').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    lastLogCount = 0; fetchStatus();
}
function renderLogs(logs) {
    const c = document.getElementById('log-window');
    if (!c) return;
    const filtered = logFilter === 'all' ? logs : logs.filter(l => l.message.toLowerCase().includes(logFilter.toLowerCase()));
    if (filtered.length === lastLogCount && c.innerHTML !== '<div class="log-placeholder">Loading logs…</div>') return;
    lastLogCount = filtered.length;
    c.innerHTML = '';
    if (filtered.length === 0) { c.innerHTML = '<div class="log-placeholder">No log entries match the filter.</div>'; return; }
    filtered.forEach(log => {
        const time = (log.timestamp||'').split(' ')[1]||'';
        const msgLower = log.message.toLowerCase();
        const cls = msgLower.includes('apache') ? 'log-msg-apache' : msgLower.includes('mysql') ? 'log-msg-mysql' : 'log-msg-system';
        const div = document.createElement('div');
        div.className = 'log-entry';
        div.innerHTML = `<span class="log-time">${time}</span><span class="${cls}">${escHtml(log.message)}</span>`;
        c.appendChild(div);
    });
    if (document.getElementById('autoscroll-chk')?.checked) c.scrollTop = c.scrollHeight;
}
function showModuleLogs(name) {
    setLogFilter(name, document.querySelector(`.log-tab[onclick*="${name}"]`) || document.querySelector('.log-tab'));
    toast('info', name+' Logs', 'Log view filtered to '+name+' entries.');
}
function clearLogs() {
    const fd = new FormData(); fd.append('db','xampp_sim_db'); fd.append('query','TRUNCATE TABLE logs');
    fetch('api.php?action=run_query',{method:'POST',body:fd}).then(()=>fetchStatus());
    toast('info','Logs Cleared','Console output has been cleared.');
}

// ─── QUIT ─────────────────────────────────────────────────────────────────────
function handleQuit() {
    if (confirm('Exit the XAMPP Control Panel Simulator?')) {
        document.querySelector('.xampp-window').style.opacity='0.3';
        document.querySelector('.xampp-window').style.pointerEvents='none';
        toast('warn','Simulator Stopped','Refresh the page to restart.');
    }
}

// ─── MODALS ───────────────────────────────────────────────────────────────────
function openModal(id) { document.getElementById(id).style.display='flex'; }
function closeModal(id) { document.getElementById(id).style.display='none'; }

// ─── CONFIG ───────────────────────────────────────────────────────────────────
function openConfigModal(file='httpd.conf') {
    openModal('config-modal');
    const sel = document.getElementById('config-file-select');
    if (file) sel.value = file;
    loadConfigFile();
}
function loadConfigFile() {
    const file = document.getElementById('config-file-select').value;
    document.getElementById('config-text-area').value = 'Loading…';
    fetch('api.php?action=get_config&file='+file).then(r=>r.json()).then(d => {
        document.getElementById('config-text-area').value = d.success ? d.content : 'Error: '+d.error;
    });
}
function saveConfigFile() {
    const file = document.getElementById('config-file-select').value;
    const content = document.getElementById('config-text-area').value;
    const sm = document.getElementById('config-status-msg');
    sm.style.color='#f39c12'; sm.textContent='Saving…';
    const fd = new FormData(); fd.append('file',file); fd.append('content',content);
    fetch('api.php?action=save_config',{method:'POST',body:fd}).then(r=>r.json()).then(d => {
        if (d.success) { sm.style.color='#2ecc71'; sm.textContent='Saved!'; toast('success','Config Saved',file+' updated successfully.'); fetchStatus(); setTimeout(()=>sm.textContent='',3000); }
        else { sm.style.color='#e74c3c'; sm.textContent='Error: '+d.error; }
    });
}

// ─── NETSTAT ──────────────────────────────────────────────────────────────────
function openNetstatModal() {
    openModal('netstat-modal');
    const tbody = document.getElementById('netstat-tbody');
    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:20px;color:#888">Scanning ports…</td></tr>';
    const sum = document.getElementById('netstat-summary');
    setTimeout(() => {
        tbody.innerHTML = '';
        const running = activeModules.filter(m=>m.status==='running');
        sum.textContent = running.length ? `${running.length} active module(s) — ${running.reduce((a,m)=>a+m.port.split(',').length,0)} port(s) listening` : 'No active connections.';
        if (!running.length) { tbody.innerHTML='<tr><td colspan="6" style="text-align:center;padding:20px;color:#888">No modules running.</td></tr>'; return; }
        running.forEach(mod => {
            mod.port.split(',').forEach(p => {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td>TCP</td><td>0.0.0.0</td><td><strong>${p.trim()}</strong></td><td><span class="badge-listen">LISTENING</span></td><td>${mod.pid}</td><td>${mod.name}.exe</td>`;
                tbody.appendChild(tr);
            });
        });
        document.getElementById('netstat-status').textContent = 'Last scanned: '+new Date().toLocaleTimeString();
    }, 500);
}

// ─── SERVICES TABLE ───────────────────────────────────────────────────────────
const servicesMeta = {
    Apache:   {display:'Apache HTTP Server',  startup:'Automatic'},
    MySQL:    {display:'MySQL/MariaDB Server', startup:'Automatic'},
    FileZilla:{display:'FileZilla FTP Server', startup:'Manual'},
    Mercury:  {display:'Mercury Mail Server',  startup:'Manual'},
    Tomcat:   {display:'Apache Tomcat',        startup:'Manual'},
};
function renderServicesTable(modules) {
    const tbody = document.getElementById('services-tbody');
    if (!tbody) return;
    tbody.innerHTML = '';
    modules.forEach(mod => {
        const running = mod.status==='running';
        const meta = servicesMeta[mod.name]||{display:mod.name,startup:'Manual'};
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><code style="font-size:11px">${mod.name.toLowerCase()}d</code></td>
            <td>${meta.display}</td>
            <td class="${running?'svc-status-running':'svc-status-stopped'}">${running?'▶ Running':'■ Stopped'}</td>
            <td>${meta.startup}</td>
            <td><button class="btn-sm" onclick="toggleModule('${mod.name}','${running?'stopped':'running'}')">${running?'Stop':'Start'}</button></td>`;
        tbody.appendChild(tr);
    });
}

// ─── EXPLORER ─────────────────────────────────────────────────────────────────
const explorerData = {
    htdocs:    [{icon:'📄',name:'index.php',size:'5.7 KB'},{icon:'📄',name:'api.php',size:'12.2 KB'},{icon:'📄',name:'dashboard.php',size:'4.1 KB'},{icon:'📄',name:'phpmyadmin.php',size:'44.9 KB'},{icon:'📄',name:'setup.php',size:'4.2 KB'},{icon:'📄',name:'style.css',size:'9.8 KB'},{icon:'📄',name:'script.js',size:'19.3 KB'}],
    apache:    [{icon:'📄',name:'conf/httpd.conf',size:'20.1 KB'},{icon:'📁',name:'logs/',size:'—'},{icon:'📁',name:'modules/',size:'—'}],
    mysql:     [{icon:'📁',name:'data/',size:'—'},{icon:'📄',name:'bin/mysql.exe',size:'5.2 MB'},{icon:'📄',name:'bin/mysqld.exe',size:'12.7 MB'}],
    php:       [{icon:'📄',name:'php.ini',size:'73.4 KB'},{icon:'📄',name:'php.exe',size:'618 KB'},{icon:'📁',name:'ext/',size:'—'}],
    phpmyadmin:[{icon:'📄',name:'index.php',size:'3.3 KB'},{icon:'📁',name:'libraries/',size:'—'},{icon:'📁',name:'themes/',size:'—'}],
};
const explorerPaths = {
    htdocs:'C:/xampp/htdocs', apache:'C:/xampp/apache', mysql:'C:/xampp/mysql', php:'C:/xampp/php', phpmyadmin:'C:/xampp/phpMyAdmin'
};
function showExplorerPath(key) {
    document.querySelectorAll('.etree-root,.etree-item').forEach(el=>el.classList.remove('active'));
    event.target.classList.add('active');
    document.getElementById('explorer-current-path').textContent = explorerPaths[key]||'C:/xampp/'+key;
    const list = document.getElementById('explorer-file-list');
    list.innerHTML = '';
    (explorerData[key]||[]).forEach(f => {
        const div = document.createElement('div');
        div.className = 'explorer-file-item';
        div.innerHTML = `<span>${f.icon}</span><span>${f.name}</span><span class="explorer-file-size">${f.size}</span>`;
        list.appendChild(div);
    });
    document.getElementById('explorer-status').textContent = (explorerData[key]||[]).length+' item(s)';
}
function openExplorerModal() { openModal('explorer-modal'); showExplorerPath('htdocs'); }
document.getElementById && document.addEventListener('DOMContentLoaded', ()=>{
    const btn = document.getElementById('btn-explorer');
    if (btn) btn.setAttribute('onclick','openExplorerModal()');
});

// ─── SHELL ────────────────────────────────────────────────────────────────────
function openShellModal() { openModal('shell-modal'); setTimeout(()=>document.getElementById('terminal-input').focus(),100); }
function handleTerminalKey(e) {
    const inp = e.target;
    if (e.key==='Enter') {
        const cmd = inp.value.trim(); inp.value='';
        if (cmd) { cmdHistory.push(cmd); historyIdx=cmdHistory.length; }
        executeTerminalCommand(cmd);
    } else if (e.key==='ArrowUp') { if(historyIdx>0){historyIdx--;inp.value=cmdHistory[historyIdx];} e.preventDefault(); }
    else if (e.key==='ArrowDown') { if(historyIdx<cmdHistory.length-1){historyIdx++;inp.value=cmdHistory[historyIdx];}else{historyIdx=cmdHistory.length;inp.value='';} e.preventDefault(); }
}
function termPrint(html, isCmd=false) {
    const out = document.getElementById('terminal-output');
    const prompt = terminalState==='mysql'?`<span style="color:#f1c40f">mysql [${mysqlDatabase}]&gt;&nbsp;</span>` : '<span style="color:#3fb950">shell&gt;&nbsp;</span>';
    const div = document.createElement('div');
    div.innerHTML = isCmd ? prompt+escHtml(html) : html;
    out.appendChild(div);
    while(out.childNodes.length>400) out.removeChild(out.firstChild);
    document.getElementById('terminal-body-wrap').scrollTop = 99999;
}
function executeTerminalCommand(cmd) {
    termPrint(cmd, true);
    if (terminalState==='shell') processShell(cmd);
    else processMysql(cmd);
}
function processShell(cmd) {
    const c = cmd.trim().toLowerCase(), parts = cmd.trim().split(' ');
    if (!c) return;
    if (c==='clear'||c==='cls') { document.getElementById('terminal-output').innerHTML=''; return; }
    if (c==='exit') { closeModal('shell-modal'); return; }
    if (c==='help') { termPrint(`<span style="color:#8b949e">Available commands:</span>\n  <span style="color:#79c0ff">help</span>     — show this help\n  <span style="color:#79c0ff">status</span>   — module status\n  <span style="color:#79c0ff">netstat</span>  — show active ports\n  <span style="color:#79c0ff">mysql</span>    — enter MySQL client\n  <span style="color:#79c0ff">php -v</span>   — PHP version\n  <span style="color:#79c0ff">pwd</span>      — current directory\n  <span style="color:#79c0ff">ls</span>       — list files\n  <span style="color:#79c0ff">clear</span>    — clear screen\n  <span style="color:#79c0ff">exit</span>     — close shell`); return; }
    if (c==='pwd') { termPrint('C:\\xampp\\htdocs'); return; }
    if (c==='ls'||c==='dir') { termPrint(explorerData.htdocs.map(f=>`${f.icon} ${f.name}`).join('\n')); return; }
    if (c==='php -v'||c==='php --version') { termPrint('PHP 8.2.12 (cli)\nCopyright (c) The PHP Group\nZend Engine v4.2.12'); return; }
    if (c==='status') { termPrint(activeModules.map(m=>`<span style="color:${m.status==='running'?'#3fb950':'#e74c3c'}">[${m.status.toUpperCase().padEnd(7)}]</span> ${m.name} ${m.pid?'PID:'+m.pid:''}`).join('\n')); return; }
    if (c==='netstat') {
        const rows = activeModules.filter(m=>m.status==='running').flatMap(m=>m.port.split(',').map(p=>`  TCP  0.0.0.0:${p.trim().padEnd(6)} LISTENING  PID:${m.pid}  (${m.name})`));
        termPrint(rows.length?rows.join('\n'):'No active connections.'); return;
    }
    if (c==='mysql'||c.startsWith('mysql ')) {
        const mysqlMod = activeModules.find(m=>m.name==='MySQL');
        if (!mysqlMod||mysqlMod.status!=='running') { termPrint('<span style="color:#e74c3c">ERROR 2002: MySQL service is not running.</span>'); return; }
        terminalState='mysql';
        document.getElementById('terminal-prompt').innerHTML='mysql ['+mysqlDatabase+']&gt;&nbsp;';
        termPrint(`Welcome to the MariaDB monitor.\nYour MariaDB connection id is ${Math.floor(Math.random()*50)+10}\nServer version: 10.4.32-MariaDB\n\nType 'help;' or '\\h' for help. Type '\\c' to clear.\n`); return;
    }
    termPrint(`<span style="color:#e74c3c">'${escHtml(cmd)}': command not found. Type 'help' for commands.</span>`);
}
function processMysql(cmd) {
    const raw = cmd.trim(), lo = raw.toLowerCase().replace(/;\s*$/, '').trim();
    if (!raw) return;

    // Exit
    if (lo === 'exit' || lo === 'quit' || lo === '\\q') {
        terminalState = 'shell';
        document.getElementById('terminal-prompt').innerHTML = 'shell&gt;&nbsp;';
        termPrint('Bye'); return;
    }
    // Clear
    if (lo === 'clear' || lo === '\\c') { document.getElementById('terminal-output').innerHTML = ''; return; }
    // Help
    if (lo === 'help' || lo === '\\h' || lo === '\\?') {
        termPrint([
            '<span style="color:#f1c40f">MySQL Commands:</span>',
            '  <span style="color:#79c0ff">SHOW DATABASES;</span>             List all databases',
            '  <span style="color:#79c0ff">USE &lt;db&gt;;</span>                    Select a database',
            '  <span style="color:#79c0ff">SHOW TABLES;</span>                 List tables in current db',
            '  <span style="color:#79c0ff">DESCRIBE &lt;table&gt;;</span>            Show table columns',
            '  <span style="color:#79c0ff">SHOW INDEX FROM &lt;table&gt;;</span>     Show indexes',
            '  <span style="color:#79c0ff">SHOW CREATE TABLE &lt;table&gt;;</span>   Show CREATE statement',
            '  <span style="color:#79c0ff">SHOW STATUS;</span>                 Server status',
            '  <span style="color:#79c0ff">SHOW VARIABLES;</span>              Server variables',
            '  <span style="color:#79c0ff">SHOW PROCESSLIST;</span>            Active connections',
            '  <span style="color:#79c0ff">SHOW GRANTS;</span>                 Grants for current user',
            '  <span style="color:#79c0ff">SELECT / INSERT / UPDATE / DELETE</span>',
            '  <span style="color:#79c0ff">CREATE DATABASE &lt;name&gt;;</span>',
            '  <span style="color:#79c0ff">CREATE TABLE &lt;name&gt; (...);</span>',
            '  <span style="color:#79c0ff">DROP TABLE / DROP DATABASE</span>',
            '  <span style="color:#79c0ff">TRUNCATE TABLE &lt;name&gt;;</span>',
            '  <span style="color:#79c0ff">exit / quit</span>                  Leave MySQL client',
        ].join('\n')); return;
    }
    // STATUS command
    if (lo === 'status') {
        termPrint([
            '--------------',
            `mysql  Ver 15.1 Distrib 10.4.32-MariaDB, for Win64 (AMD64)`,
            `Connection id:     ${Math.floor(Math.random()*99)+1}`,
            `Current database:  ${mysqlDatabase}`,
            `Current user:      root@localhost`,
            `Server version:    10.4.32-MariaDB MariaDB Server`,
            `Protocol version:  10`,
            `Connection:        127.0.0.1 via TCP/IP`,
            `Server charset:    UTF-8 Unicode (utf8mb4)`,
            `Uptime:            ${uptimeSeconds}s`,
            '--------------',
        ].join('\n')); return;
    }
    // USE database (client-side first then verify)
    if (lo.startsWith('use ')) {
        const db = lo.substring(4).replace(/[`'";\s]/g, '');
        fetch('api.php?action=tables&db=' + db).then(r => r.json()).then(d => {
            if (d.success) {
                mysqlDatabase = db;
                document.getElementById('terminal-prompt').innerHTML = `mysql [${db}]&gt;&nbsp;`;
                termPrint(`Database changed`);
            } else {
                termPrint(`<span style="color:#e74c3c">ERROR 1049 (42000): Unknown database '${escHtml(db)}'</span>`);
            }
        }); return;
    }
    // SHOW GRANTS (simulated)
    if (lo === 'show grants' || lo.startsWith('show grants for')) {
        termPrint([
            '+-----------------------------------------------------------------------+',
            '| Grants for root@localhost                                             |',
            '+-----------------------------------------------------------------------+',
            '| GRANT ALL PRIVILEGES ON *.* TO `root`@`localhost` WITH GRANT OPTION  |',
            '+-----------------------------------------------------------------------+',
            '1 row in set (0.000 sec)',
        ].join('\n')); return;
    }

    // All other queries go through run_multi_query (handles multiple ; separated statements)
    const t0 = performance.now();
    const fd = new FormData();
    fd.append('db', mysqlDatabase);
    fd.append('query', raw);
    fetch('api.php?action=run_multi_query', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            const elapsed = ((performance.now() - t0) / 1000).toFixed(3);
            if (!d.success) {
                termPrint(`<span style="color:#e74c3c">ERROR: ${escHtml(d.error)}</span>`); return;
            }
            d.results.forEach((res, idx) => {
                if (d.results.length > 1) {
                    termPrint(`<span style="color:#8b949e">-- Statement ${idx+1}: ${escHtml((res.query||'').substring(0,60))} --</span>`);
                }
                if (res.type === 'select') {
                    if (!res.rows || res.rows.length === 0) {
                        termPrint(`<span style="color:#8b949e">Empty set (${elapsed} sec)</span>`);
                    } else {
                        termPrint(`<pre style="font-family:Consolas,monospace;font-size:12px;margin:2px 0;color:#e6edf3">${escHtml(formatAscii(res.columns, res.rows))}\n${res.rows.length} row(s) in set (${elapsed} sec)</pre>`);
                    }
                } else if (res.type === 'dml') {
                    termPrint(`<span style="color:#3fb950">Query OK, ${res.affected_rows||0} row(s) affected (${elapsed} sec)</span>`);
                } else if (res.type === 'error') {
                    termPrint(`<span style="color:#e74c3c">ERROR: ${escHtml(res.error)}</span>`);
                }
            });
        })
        .catch(() => termPrint('<span style="color:#e74c3c">ERROR 2002 (HY000): Can\'t connect to MySQL server.</span>'));
}
function formatAscii(cols, rows) {
    if(!cols||!cols.length) return '(0 rows)';
    const w={};
    cols.forEach(c=>w[c]=c.length);
    rows.forEach(r=>cols.forEach(c=>{const v=String(r[c]??'NULL');if(v.length>w[c])w[c]=v.length;}));
    const sep='+'+cols.map(c=>'-'.repeat(w[c]+2)).join('+')+'+';
    const hdr='|'+cols.map(c=>' '+c.padEnd(w[c])+' ').join('|')+'|';
    const dataRows=rows.map(r=>'|'+cols.map(c=>' '+String(r[c]??'NULL').padEnd(w[c])+' ').join('|')+'|');
    return [sep,hdr,sep,...dataRows,sep,rows.length+' row(s) in set'].join('\n');
}

// ─── TOAST ────────────────────────────────────────────────────────────────────
const toastIcons = {success:'✅',error:'❌',info:'ℹ️',warn:'⚠️'};
function toast(type, title, msg, duration=4000) {
    const c = document.getElementById('toast-container');
    const t = document.createElement('div');
    t.className = `toast toast-${type}`;
    t.innerHTML = `<span class="toast-icon">${toastIcons[type]||'📢'}</span><div class="toast-body"><div class="toast-title">${title}</div><div class="toast-msg">${msg}</div></div>`;
    t.onclick = () => removeToast(t);
    c.appendChild(t);
    setTimeout(() => removeToast(t), duration);
}
function removeToast(t) {
    t.classList.add('toast-out');
    setTimeout(() => t.remove(), 300);
}

// ─── HELPERS ──────────────────────────────────────────────────────────────────
function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
