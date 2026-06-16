<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>phpMyAdmin Simulator</title>
    <link rel="stylesheet" href="phpmyadmin.css">
</head>
<body>
    <div class="sidebar">
        <div class="logo-area">
            <div class="logo-wrap" onclick="showServerHome()" style="cursor: pointer;">
                <div class="pma-logo">
                    <span class="pma-p">p</span>
                    <span class="pma-m">m</span>
                    <span class="pma-a">a</span>
                </div>
                <div class="logo-text">phpMyAdmin</div>
            </div>
            <button class="theme-toggle-btn" id="pma-theme-toggle" onclick="togglePmaTheme()" title="Toggle Dark Mode">🌙</button>
        </div>
        <div class="sidebar-controls">
            <button onclick="loadDatabases()" title="Reload Databases">🔄 Refresh</button>
            <button onclick="showServerHome()" title="Server Home">🏠 Home</button>
        </div>
        <div class="db-tree" id="db-tree-container">
            <div class="loading-indicator">Loading databases...</div>
        </div>
    </div>
    
    <div class="main-panel">
        <header class="top-nav-bar">
            <div class="breadcrumbs" id="breadcrumbs">
                🏠 Server: 127.0.0.1
            </div>
            <ul class="nav-tabs" id="nav-tabs-list">
                <!-- Tabs will be populated dynamically depending on context (Server vs DB vs Table) -->
            </ul>
        </header>
        
        <div class="content-container">
            <!-- TAB: SERVER HOME -->
            <div id="tab-server-home" class="tab-content">
                <h2>Database Server Dashboard</h2>
                
                <div class="dashboard-metrics">
                    <div class="metric-card">
                        <div class="metric-title">Queries per Second</div>
                        <div class="metric-value" id="dash-qps">0</div>
                        <div class="chart-container" id="chart-qps">
                            <!-- Dynamic bars injected via JS -->
                        </div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-title">Active Connections</div>
                        <div class="metric-value" id="dash-conn">0</div>
                        <div class="chart-container" id="chart-conn"></div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-title">Network Traffic</div>
                        <div class="metric-value" id="dash-traffic">0 KB/s</div>
                        <div class="chart-container" id="chart-traffic"></div>
                    </div>
                </div>

                <div class="info-blocks">
                    <div class="block">
                        <h3>General settings</h3>
                        <ul>
                            <li><strong>Collation:</strong> utf8mb4_unicode_ci</li>
                            <li><strong>Connection:</strong> PDO MySQL Link</li>
                            <li><button class="btn-primary" onclick="createNewDatabasePrompt()">➕ Create New Database</button></li>
                        </ul>
                    </div>
                    
                    <div class="block">
                        <h3>Database server</h3>
                        <ul>
                            <li><strong>Server:</strong> 127.0.0.1 via TCP/IP</li>
                            <li><strong>Server type:</strong> MariaDB (Simulated)</li>
                            <li><strong>Server version:</strong> 10.4.32-MariaDB</li>
                            <li><strong>User:</strong> root@localhost</li>
                        </ul>
                    </div>
                    
                    <div class="block">
                        <h3>Web server</h3>
                        <ul>
                            <li><strong>Software:</strong> Apache/2.4.58 (Win64) PHP/8.2.12</li>
                            <li><strong>Database client:</strong> libmysql - mysqlnd 8.2.12</li>
                            <li><strong>PHP extension:</strong> mysqli, pdo_mysql</li>
                        </ul>
                    </div>
                    
                    <div class="block">
                        <h3>Simulated Database Contents</h3>
                        <p style="padding: 0 15px; font-size: 13px;">This simulator includes seeded tables for testing database interactions:
                            <br><code>users</code>, <code>products</code>, and <code>orders</code> inside the <strong>xampp_sim_db</strong> database.
                        </p>
                    </div>
                </div>
            </div>

            <!-- TAB: DB STRUCTURE -->
            <div id="tab-db-structure" class="tab-content" style="display:none;">
                <div class="table-actions-header">
                    <span class="active-table-name" id="db-structure-title">Database: </span>
                    <div class="browse-controls">
                        <button class="btn-sm btn-primary" onclick="openCreateTableTab()">➕ Create New Table</button>
                        <button class="btn-sm" onclick="loadDbStructure()">🔄 Reload</button>
                    </div>
                </div>
                <div id="db-structure-container">
                    <!-- Table list populated dynamically -->
                </div>
            </div>

            <!-- TAB: CREATE TABLE -->
            <div id="tab-create-table" class="tab-content" style="display:none;">
                <div class="table-actions-header" style="justify-content: flex-start; gap: 20px;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <label>Table name:</label>
                        <input type="text" id="create-table-name" class="form-control" style="width:200px" />
                    </div>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <label>Add</label>
                        <input type="number" id="create-table-add-cols" class="form-control" style="width:60px" value="1" min="1" />
                        <label>column(s)</label>
                        <button class="btn-sm" onclick="addCreateTableColumns()">Go</button>
                    </div>
                </div>
                
                <div class="scrollable-table-container" style="margin-top:15px; overflow-x: auto;">
                    <table class="grid-table" id="create-table-grid" style="min-width: 900px;">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Length/Values</th>
                                <th>Default</th>
                                <th>Null</th>
                                <th>Index</th>
                                <th>A_I</th>
                            </tr>
                        </thead>
                        <tbody id="create-table-tbody">
                            <!-- Populated by JS -->
                        </tbody>
                    </table>
                </div>

                <div style="margin-top:20px; display:flex; gap:10px;">
                    <button class="btn-primary" onclick="submitCreateTable()">Save</button>
                    <button class="btn-secondary" onclick="switchTab('db-structure')">Cancel</button>
                </div>
                <div id="create-table-status" style="margin-top:15px;"></div>
            </div>

            <!-- TAB: BROWSE TABLE -->
            <div id="tab-browse" class="tab-content" style="display:none;">
                <div class="table-actions-header">
                    <span class="active-table-name" id="browse-table-title">Table Name</span>
                    <div class="browse-controls">
                        <input type="text" id="browse-search-inline" placeholder="Filter rows…" oninput="filterBrowseRows(this.value)" class="inline-search" />
                        <button class="btn-sm btn-primary" onclick="switchTab('insert')">➕ Insert</button>
                        <button class="btn-sm" onclick="loadTableData()">🔄 Reload</button>
                    </div>
                </div>
                <div id="browse-table-container" class="scrollable-table-container">
                    <!-- Grid populated dynamically -->
                </div>
                <div id="browse-pagination" class="browse-pagination"></div>
            </div>

            <!-- TAB: STRUCTURE -->
            <div id="tab-structure" class="tab-content" style="display:none;">
                <h3 id="structure-table-title">Table Columns Structure</h3>
                <div id="structure-container">
                    <!-- Structure table populated dynamically -->
                </div>
            </div>

            <!-- TAB: SQL RUNNER -->
            <div id="tab-sql" class="tab-content" style="display:none;">
                <h3>Run SQL Query/Queries on Database</h3>
                <div class="sql-layout">
                    <div class="sql-main">
                        <div class="sql-box">
                            <textarea id="sql-query-area" rows="12" placeholder="Enter query here..."></textarea>
                            <div class="sql-buttons">
                                <button class="btn-primary" onclick="submitSqlQuery()">Go</button>
                                <button class="btn-secondary" onclick="saveCurrentQuery()">Save Query</button>
                                <button class="btn-secondary" onclick="clearSqlQuery()">Clear</button>
                            </div>
                        </div>
                        <div id="sql-results-container" style="margin-top: 15px;">
                            <!-- Query results shown here -->
                        </div>
                    </div>
                    <div class="sql-sidebar">
                        <div class="sql-sidebar-header">Saved Queries</div>
                        <ul class="saved-queries-list" id="saved-queries-list">
                            <!-- Saved queries populated here -->
                        </ul>
                    </div>
                </div>
            </div>

            <!-- TAB: DESIGNER -->
            <div id="tab-designer" class="tab-content" style="display:none;">
                <h3>Database Designer (ERD)</h3>
                <div class="designer-canvas-wrapper" id="designer-wrapper">
                    <div class="designer-canvas" id="designer-canvas">
                        <svg id="designer-svg" style="position:absolute; top:0; left:0; width:100%; height:100%; pointer-events:none; z-index:5;"></svg>
                        <!-- Table cards rendered here -->
                    </div>
                </div>
            </div>

            <!-- TAB: INSERT ROW -->
            <div id="tab-insert" class="tab-content" style="display:none;">
                <h3 id="insert-table-title">Insert New Row</h3>
                <div id="insert-status-msg"></div>
                <form id="insert-row-form" onsubmit="submitInsertForm(event)">
                    <div id="insert-fields-container">
                        <!-- Inputs generated dynamically based on structure -->
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Insert Record</button>
                        <button type="button" class="btn-secondary" onclick="switchTab('browse')">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- TAB: OPERATIONS -->
            <div id="tab-operations" class="tab-content" style="display:none;">
                <h3>Table Operations</h3>
                <div class="ops-grid">
                    <div class="ops-card">
                        <h4>Move / Copy Table</h4>
                        <div class="form-group">
                            <label>Rename table to:</label>
                            <input type="text" id="op-rename-val" class="form-control" />
                            <button class="btn-primary" style="margin-top:5px" onclick="doTableOp('rename')">Go</button>
                        </div>
                        <div class="form-group">
                            <label>Copy table to (structure & data):</label>
                            <input type="text" id="op-copy-val" class="form-control" />
                            <button class="btn-primary" style="margin-top:5px" onclick="doTableOp('copy')">Go</button>
                        </div>
                    </div>
                    <div class="ops-card">
                        <h4>Table Maintenance</h4>
                        <ul class="op-list">
                            <li><a href="#" onclick="doTableOp('optimize')">Optimize table</a></li>
                        </ul>
                    </div>
                    <div class="ops-card danger">
                        <h4>Delete Data or Table</h4>
                        <ul class="op-list">
                            <li><a href="#" class="danger-link" onclick="doTableOp('truncate')">Empty the table (TRUNCATE)</a></li>
                            <li><a href="#" class="danger-link" onclick="doTableOp('drop')">Delete the table (DROP)</a></li>
                        </ul>
                    </div>
                    <div class="ops-card">
                        <h4>Testing & Development</h4>
                        <p style="font-size:12px; color:var(--text-muted); margin-top:0;">Generate random data to fill the table for testing purposes.</p>
                        <div class="form-group">
                            <label>Rows to insert:</label>
                            <select id="op-dummy-rows" class="search-select" style="max-width:120px;">
                                <option value="10">10 Rows</option>
                                <option value="50">50 Rows</option>
                                <option value="100">100 Rows</option>
                            </select>
                            <button class="btn-primary" style="margin-top:10px" onclick="generateDummyData()">🚀 Generate Data</button>
                        </div>
                    </div>
                </div>
                <div id="ops-status-msg" style="margin-top:15px"></div>
            </div>

            <!-- TAB: SEARCH -->
            <div id="tab-search" class="tab-content" style="display:none;">
                <h3 id="search-table-title">Search in Table</h3>
                <div class="search-form-box">
                    <div class="search-row">
                        <select id="search-column" class="search-select"><option>Loading columns…</option></select>
                        <select id="search-operator" class="search-select">
                            <option value="LIKE">LIKE</option>
                            <option value="=">= (equals)</option>
                            <option value="!=">!= (not equals)</option>
                            <option value=">">&gt; (greater than)</option>
                            <option value="<">&lt; (less than)</option>
                            <option value="REGEXP">REGEXP</option>
                        </select>
                        <input type="text" id="search-value" class="search-input" placeholder="Search value…" />
                        <button class="btn-primary" onclick="performSearch()">🔍 Search</button>
                    </div>
                </div>
                <div id="search-results-container" style="margin-top:14px;"></div>
            </div>

            <!-- TAB: USER ACCOUNTS -->
            <div id="tab-users" class="tab-content" style="display:none;">
                <h3>MySQL User Accounts</h3>
                <div id="user-accounts-container"></div>
                <div style="margin-top:20px;">
                    <h4>Add New User</h4>
                    <div class="user-form">
                        <input type="text" id="new-user-name" class="form-control" placeholder="Username" style="max-width:160px" />
                        <input type="text" id="new-user-host" class="form-control" value="localhost" style="max-width:120px" />
                        <input type="password" id="new-user-pass" class="form-control" placeholder="Password" style="max-width:160px" />
                        <select id="new-user-role" class="search-select">
                            <option value="ALL">All Privileges</option>
                            <option value="SELECT">SELECT only</option>
                            <option value="SELECT,INSERT,UPDATE">Read-Write</option>
                        </select>
                        <button class="btn-primary" onclick="addSimulatedUser()">➕ Add User</button>
                    </div>
                    <div id="user-status-msg" style="margin-top:10px;"></div>
                </div>
            </div>

            <!-- TAB: EXPORT -->
            <div id="tab-export" class="tab-content" style="display:none;">
                <h3>Export Database / Table</h3>
                <div class="export-box block" style="padding: 20px;">
                    <p>Export tables from database <strong id="export-db-name"></strong> in SQL format:</p>
                    <div class="form-group">
                        <label>Export Method:</label>
                        <label><input type="radio" name="export-method" value="quick" checked> Quick - display only the minimal options</label>
                    </div>
                    <div class="form-group">
                        <label>Format:</label>
                        <select id="export-format">
                            <option value="sql">SQL (*.sql)</option>
                        </select>
                    </div>
                    <button class="btn-primary" onclick="performExport()">Export Database</button>
                </div>
            </div>

            <!-- TAB: IMPORT -->
            <div id="tab-import" class="tab-content" style="display:none;">
                <h3>Import SQL File</h3>
                <div class="import-box block" style="padding: 20px;">
                    <p>File to import (Max: 40MiB):</p>
                    <div class="form-group">
                        <input type="file" id="import-file-input" accept=".sql" />
                    </div>
                    <p class="help-text">Choose a `.sql` text file to run commands sequentially against database <strong id="import-db-name"></strong>.</p>
                    <button class="btn-primary" onclick="performImport()">Import / Run Script</button>
                </div>
                <div id="import-results-container" style="margin-top: 15px;"></div>
            </div>

        </div>
    </div>

    <script>
        let currentDb = '';
        let currentTable = '';
        let databaseTreeData = {};
        
        // Dashboard state
        let dashInterval = null;
        let qpsHistory = Array(15).fill(0);
        let connHistory = Array(15).fill(0);
        let trafficHistory = Array(15).fill(0);

        document.addEventListener('DOMContentLoaded', () => {
            initPmaTheme();
            loadDatabases();
            showServerHome();
            initDashboardCharts();
        });

        // Theme management
        function initPmaTheme() {
            const dark = localStorage.getItem('pma_theme') === 'dark';
            document.body.classList.toggle('dark-theme', dark);
            document.getElementById('pma-theme-toggle').textContent = dark ? '☀️' : '🌙';
        }

        function togglePmaTheme() {
            const dark = !document.body.classList.contains('dark-theme');
            document.body.classList.toggle('dark-theme', dark);
            localStorage.setItem('pma_theme', dark ? 'dark' : 'light');
            document.getElementById('pma-theme-toggle').textContent = dark ? '☀️' : '🌙';
        }

        // Dashboard Charts
        function initDashboardCharts() {
            if (dashInterval) clearInterval(dashInterval);
            dashInterval = setInterval(updateDashboard, 2000);
            updateDashboard(); // Initial call
        }

        function updateDashboard() {
            if (document.getElementById('tab-server-home').style.display === 'none') return;

            // Generate some plausible fake metric data
            const qps = Math.floor(Math.random() * 45) + 5;
            const conn = Math.floor(Math.random() * 10) + 12;
            const traffic = (Math.random() * 20 + 2).toFixed(1);

            qpsHistory.push(qps); qpsHistory.shift();
            connHistory.push(conn); connHistory.shift();
            trafficHistory.push(parseFloat(traffic)); trafficHistory.shift();

            document.getElementById('dash-qps').textContent = qps;
            document.getElementById('dash-conn').textContent = conn;
            document.getElementById('dash-traffic').textContent = traffic + ' KB/s';

            renderChart('chart-qps', qpsHistory, 60);
            renderChart('chart-conn', connHistory, 30);
            renderChart('chart-traffic', trafficHistory, 30);
        }

        function renderChart(containerId, dataArray, maxVal) {
            const container = document.getElementById(containerId);
            if (!container) return;
            container.innerHTML = '';
            dataArray.forEach(val => {
                const bar = document.createElement('div');
                bar.className = 'chart-bar';
                let pct = (val / maxVal) * 100;
                if (pct > 100) pct = 100;
                bar.style.height = Math.max(pct, 2) + '%';
                container.appendChild(bar);
            });
        }

        // Load Databases list from api
        function loadDatabases() {
            const container = document.getElementById('db-tree-container');
            container.innerHTML = '<div class="loading-indicator">Loading databases...</div>';

            fetch('api.php?action=databases')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        renderDatabaseTree(data.databases);
                    } else {
                        container.innerHTML = `<div class="error-msg">Error: ${data.error}</div>`;
                    }
                })
                .catch(err => {
                    container.innerHTML = '<div class="error-msg">Failed to connect to database.</div>';
                });
        }

        function renderDatabaseTree(databases) {
            const container = document.getElementById('db-tree-container');
            container.innerHTML = '';
            
            const ul = document.createElement('ul');
            ul.className = 'db-list';

            databases.forEach(db => {
                const li = document.createElement('li');
                li.className = 'db-node';
                li.id = `db-node-${db}`;
                
                const dbLink = document.createElement('a');
                dbLink.href = '#';
                dbLink.className = 'db-link';
                dbLink.innerHTML = `📁 ${db}`;
                dbLink.onclick = (e) => {
                    e.preventDefault();
                    toggleDatabaseNode(db);
                };

                li.appendChild(dbLink);
                ul.appendChild(li);
            });

            container.appendChild(ul);
        }

        function toggleDatabaseNode(db) {
            const node = document.getElementById(`db-node-${db}`);
            const isExpanded = node.classList.contains('expanded');

            if (isExpanded) {
                node.classList.remove('expanded');
                const existingSubUl = node.querySelector('ul');
                if (existingSubUl) node.removeChild(existingSubUl);
            } else {
                node.classList.add('expanded');
                fetchTables(db, (tables) => {
                    const subUl = document.createElement('ul');
                    subUl.className = 'table-list';
                    
                    tables.forEach(tbl => {
                        const subLi = document.createElement('li');
                        subLi.className = 'table-node';
                        
                        const tblLink = document.createElement('a');
                        tblLink.href = '#';
                        tblLink.className = 'table-link';
                        tblLink.innerHTML = `📊 ${tbl}`;
                        tblLink.onclick = (e) => {
                            e.preventDefault();
                            document.querySelectorAll('.table-link').forEach(el => el.classList.remove('active'));
                            tblLink.classList.add('active');
                            selectDatabaseTable(db, tbl);
                        };
                        
                        subLi.appendChild(tblLink);
                        subUl.appendChild(subLi);
                    });
                    
                    if (tables.length === 0) {
                        const emptyLi = document.createElement('li');
                        emptyLi.className = 'empty-node';
                        emptyLi.textContent = '(No tables)';
                        subUl.appendChild(emptyLi);
                    }
                    
                    node.appendChild(subUl);
                });
            }
            selectDatabase(db);
        }

        function fetchTables(db, callback) {
            fetch(`api.php?action=tables&db=${db}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        databaseTreeData[db] = data.tables;
                        callback(data.tables);
                    } else {
                        alert('Error loading tables: ' + data.error);
                    }
                })
                .catch(err => console.error(err));
        }

        function selectDatabase(db) {
            currentDb = db;
            currentTable = '';
            
            // Update breadcrumbs
            document.getElementById('breadcrumbs').innerHTML = `🏠 Server: 127.0.0.1 » 📁 <a href="#" onclick="selectDatabase('${db}')">${db}</a>`;
            
            // Build Database-level tabs
            const tabsList = document.getElementById('nav-tabs-list');
            tabsList.innerHTML = `
                <li><a href="#" class="tab-link active" onclick="switchTab('db-structure'); loadDbStructure();">Structure</a></li>
                <li><a href="#" class="tab-link" onclick="switchTab('sql')">SQL</a></li>
                <li><a href="#" class="tab-link" onclick="switchTab('designer'); loadDesigner();">Designer</a></li>
                <li><a href="#" class="tab-link" onclick="switchTab('export')">Export</a></li>
                <li><a href="#" class="tab-link" onclick="switchTab('import')">Import</a></li>
            `;
            
            // Set text values
            document.getElementById('export-db-name').textContent = db;
            document.getElementById('import-db-name').textContent = db;

            // Load Structure tab by default
            switchTab('db-structure');
            loadDbStructure();
            document.getElementById('sql-query-area').value = `-- Write SQL queries for database ${db}\nSELECT * FROM modules;`;
        }

        function selectDatabaseTable(db, table) {
            currentDb = db;
            currentTable = table;
            
            // Update breadcrumbs
            document.getElementById('breadcrumbs').innerHTML = `
                🏠 Server: 127.0.0.1 » 
                📁 <a href="#" onclick="selectDatabase('${db}')">${db}</a> » 
                📊 <a href="#" onclick="selectDatabaseTable('${db}', '${table}')">${table}</a>
            `;
            
            // Build Table-level tabs
            const tabsList = document.getElementById('nav-tabs-list');
            tabsList.innerHTML = `
                <li><a href="#" class="tab-link active" id="tab-btn-browse" onclick="switchTab('browse')">Browse</a></li>
                <li><a href="#" class="tab-link" id="tab-btn-structure" onclick="switchTab('structure')">Structure</a></li>
                <li><a href="#" class="tab-link" id="tab-btn-sql" onclick="switchTab('sql')">SQL</a></li>
                <li><a href="#" class="tab-link" id="tab-btn-search" onclick="switchTab('search')">Search</a></li>
                <li><a href="#" class="tab-link" id="tab-btn-insert" onclick="switchTab('insert')">Insert</a></li>
                <li><a href="#" class="tab-link" id="tab-btn-export" onclick="switchTab('export')">Export</a></li>
                <li><a href="#" class="tab-link" id="tab-btn-operations" onclick="switchTab('operations')">Operations</a></li>
            `;
            
            document.getElementById('export-db-name').textContent = db;
            document.getElementById('import-db-name').textContent = db;

            loadTableData();
            loadTableStructure();
            
            // Default to Browse
            switchTab('browse');
        }

        function showServerHome() {
            currentDb = '';
            currentTable = '';
            document.getElementById('breadcrumbs').innerHTML = `🏠 Server: 127.0.0.1`;
            
            const tabsList = document.getElementById('nav-tabs-list');
            tabsList.innerHTML = `
                <li><a href="#" class="tab-link active" onclick="switchTab('server-home')">Databases</a></li>
                <li><a href="#" class="tab-link" onclick="switchTab('users'); loadUserAccounts();">User accounts</a></li>
                <li><a href="#" class="tab-link" onclick="switchTab('sql')">SQL</a></li>
            `;
            switchTab('server-home');
        }

        function switchTab(tabId) {
            // Hide all tab containers
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => content.style.display = 'none');
            
            // Show target container
            const targetTab = document.getElementById(`tab-${tabId}`);
            if (targetTab) targetTab.style.display = 'block';

            // Mark active class on nav link
            document.querySelectorAll('.tab-link').forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('onclick').includes(tabId)) {
                    link.classList.add('active');
                }
            });

            // Focus textarea if SQL tab
            if (tabId === 'sql') {
                setTimeout(() => {
                    document.getElementById('sql-query-area').focus();
                }, 100);
            }
        }

        // --- BROWSE TAB ENGINE ---
        function loadTableData() {
            const container = document.getElementById('browse-table-container');
            container.innerHTML = '<div class="loading-indicator">Loading records...</div>';
            document.getElementById('browse-table-title').innerHTML = `Table records: <code>${currentTable}</code>`;

            fetch(`api.php?action=table_data&db=${currentDb}&table=${currentTable}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        renderTableGrid(data.columns, data.rows);
                    } else {
                        container.innerHTML = `<div class="error-msg">Error: ${data.error}</div>`;
                    }
                })
                .catch(err => {
                    container.innerHTML = '<div class="error-msg">Failed to query records.</div>';
                });
        }

        function renderTableGrid(columns, rows) {
            const container = document.getElementById('browse-table-container');
            container.innerHTML = '';

            if (rows.length === 0) {
                container.innerHTML = `<div class="info-alert">Table is empty. 0 rows returned.</div>`;
                return;
            }

            const table = document.createElement('table');
            table.className = 'grid-table';

            // Find primary key column if any
            let pkColumn = '';
            const pkField = columns.find(c => c.key === 'PRI');
            if (pkField) pkColumn = pkField.name;
            else if (columns.length > 0) pkColumn = columns[0].name; // fallback

            // store for filtering
            table._allRows = rows;
            table._columns = columns;
            table._pkColumn = pkColumn;

            // Header row with sortable columns
            const thead = document.createElement('thead');
            const htr = document.createElement('tr');
            const hactions = document.createElement('th');
            hactions.textContent = 'Actions'; hactions.style.width = '110px';
            htr.appendChild(hactions);
            columns.forEach(col => {
                const th = document.createElement('th');
                th.style.cursor = 'pointer';
                th.innerHTML = `${col.name} ${col.key === 'PRI' ? '🔑' : ''} <span class="sort-arrow">⇅</span>`;
                th.onclick = () => sortTableBy(col.name, th);
                htr.appendChild(th);
            });
            thead.appendChild(htr);
            table.appendChild(thead);

            // Body rows
            const tbody = document.createElement('tbody');
            table.id = 'main-browse-table';
            rows.forEach((row) => {
                tbody.appendChild(buildBrowseRow(row, columns, pkColumn));
            });
            table.appendChild(tbody);
            container.appendChild(table);

            const pag = document.getElementById('browse-pagination');
            if (pag) pag.textContent = `${rows.length} row(s) in set`;
        }

        function deleteTableRow(pkCol, pkVal) {
            const formData = new FormData();
            formData.append('db', currentDb);
            formData.append('table', currentTable);
            formData.append('pk_col', pkCol);
            formData.append('pk_val', pkVal);

            fetch('api.php?action=delete_row', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    loadTableData(); // reload
                } else {
                    alert('Failed to delete row: ' + data.error);
                }
            })
            .catch(err => alert('Network error: ' + err));
        }

        // --- STRUCTURE TAB ENGINE ---
        function loadTableStructure() {
            const container = document.getElementById('structure-container');
            container.innerHTML = '<div class="loading-indicator">Loading schema structure...</div>';
            document.getElementById('structure-table-title').innerHTML = `Table Structure: <code>${currentTable}</code>`;

            fetch(`api.php?action=table_structure&db=${currentDb}&table=${currentTable}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        renderStructureGrid(data.structure);
                    } else {
                        container.innerHTML = `<div class="error-msg">Error: ${data.error}</div>`;
                    }
                })
                .catch(err => {
                    container.innerHTML = '<div class="error-msg">Failed to query columns.</div>';
                });
        }

        function renderStructureGrid(columns) {
            const container = document.getElementById('structure-container');
            container.innerHTML = '';

            const table = document.createElement('table');
            table.className = 'grid-table';

            // Headers
            const thead = document.createElement('thead');
            const htr = document.createElement('tr');
            ['Name', 'Type', 'Null', 'Key', 'Default', 'Extra'].forEach(h => {
                const th = document.createElement('th');
                th.textContent = h;
                htr.appendChild(th);
            });
            thead.appendChild(htr);
            table.appendChild(thead);

            // Rows
            const tbody = document.createElement('tbody');
            columns.forEach(col => {
                const tr = document.createElement('tr');
                const tdName = document.createElement('td');
                tdName.innerHTML = `<strong>${col.Field}</strong> ${col.Key === 'PRI' ? '🔑' : ''}`;
                tr.appendChild(tdName);
                const tdType = document.createElement('td'); tdType.textContent = col.Type; tr.appendChild(tdType);
                const tdNull = document.createElement('td'); tdNull.textContent = col.Null; tr.appendChild(tdNull);
                const tdKey = document.createElement('td'); tdKey.textContent = col.Key || '-'; tr.appendChild(tdKey);
                const tdDefault = document.createElement('td');
                tdDefault.textContent = col.Default === null ? 'NULL' : col.Default;
                if (col.Default === null) tdDefault.className = 'null-cell';
                tr.appendChild(tdDefault);
                const tdExtra = document.createElement('td'); tdExtra.textContent = col.Extra || '-'; tr.appendChild(tdExtra);
                tbody.appendChild(tr);
            });
            table.appendChild(tbody);
            container.appendChild(table);

            // Fetch Indexes & Stats
            const indexesDiv = document.createElement('div'); indexesDiv.id = 'structure-indexes';
            const statsDiv = document.createElement('div'); statsDiv.id = 'structure-stats';
            container.appendChild(indexesDiv); container.appendChild(statsDiv);
            
            fetch(`api.php?action=table_indexes&db=${currentDb}&table=${currentTable}`).then(r=>r.json()).then(d=>{
                if(d.success && d.indexes.length) {
                    let html = `<h4 style="margin-top:20px;margin-bottom:8px">Indexes</h4><table class="grid-table" style="max-width:600px"><thead><tr><th>Keyname</th><th>Type</th><th>Unique</th><th>Column</th></tr></thead><tbody>`;
                    d.indexes.forEach(i => { html += `<tr><td><strong>${i.Key_name}</strong></td><td>${i.Index_type}</td><td>${i.Non_unique==0?'Yes':'No'}</td><td>${i.Column_name}</td></tr>`; });
                    indexesDiv.innerHTML = html + `</tbody></table>`;
                }
            });
            fetch(`api.php?action=table_stats&db=${currentDb}&table=${currentTable}`).then(r=>r.json()).then(d=>{
                if(d.success && d.stats) {
                    const s = d.stats;
                    const formatBytes = (bytes) => (bytes/1024).toFixed(1)+' KiB';
                    statsDiv.innerHTML = `
                    <div style="margin-top:20px; display:flex; gap:20px;">
                        <table class="grid-table" style="max-width:300px">
                            <thead><tr><th colspan="2">Information</th></tr></thead>
                            <tbody>
                                <tr><td>Format</td><td>${s.ENGINE}</td></tr>
                                <tr><td>Collation</td><td>${s.TABLE_COLLATION}</td></tr>
                                <tr><td>Rows</td><td>${s.ACCURATE_ROWS}</td></tr>
                                <tr><td>Next autoindex</td><td>${s.AUTO_INCREMENT||'—'}</td></tr>
                            </tbody>
                        </table>
                        <table class="grid-table" style="max-width:300px">
                            <thead><tr><th colspan="2">Space usage</th></tr></thead>
                            <tbody>
                                <tr><td>Data</td><td>${formatBytes(s.DATA_LENGTH)}</td></tr>
                                <tr><td>Index</td><td>${formatBytes(s.INDEX_LENGTH)}</td></tr>
                                <tr><td><strong>Total</strong></td><td><strong>${formatBytes(Number(s.DATA_LENGTH)+Number(s.INDEX_LENGTH))}</strong></td></tr>
                            </tbody>
                        </table>
                    </div>`;
                }
            });
        }

        // --- SQL CONSOLE TAB ENGINE ---
        function submitSqlQuery() {
            const query = document.getElementById('sql-query-area').value;
            const container = document.getElementById('sql-results-container');
            container.innerHTML = '<div class="loading-indicator">Running SQL statement(s)...</div>';

            const formData = new FormData();
            formData.append('db', currentDb || 'xampp_sim_db');
            formData.append('query', query);

            fetch('api.php?action=run_multi_query', {
                method: 'POST', body: formData
            })
            .then(res => res.json())
            .then(data => {
                container.innerHTML = '';
                if (data.success) {
                    data.results.forEach((res, idx) => {
                        const block = document.createElement('div');
                        block.style.marginBottom = '20px';
                        if (data.results.length > 1) {
                            block.innerHTML = `<div class="query-header">-- Query ${idx+1}: ${res.query.substring(0, 80)}${res.query.length>80?'...':''}</div>`;
                        }
                        
                        if (res.type === 'select') {
                            const info = document.createElement('div');
                            info.className = 'success-alert';
                            info.textContent = `Query OK. Returned ${res.rows.length} rows.`;
                            block.appendChild(info);
                            if (res.rows.length > 0) {
                                const tbl = renderRawGrid(res.columns, res.rows);
                                block.appendChild(tbl);
                            }
                        } else if (res.type === 'dml') {
                            const info = document.createElement('div');
                            info.className = 'success-alert';
                            info.textContent = res.message;
                            block.appendChild(info);
                        } else if (res.type === 'error') {
                            const err = document.createElement('div');
                            err.className = 'error-alert';
                            err.innerHTML = `<strong>Error:</strong><br>${res.error}`;
                            block.appendChild(err);
                        }
                        container.appendChild(block);
                    });
                    loadDatabases(); // Reload tree in case of CREATE/DROP
                } else {
                    container.innerHTML = `<div class="error-alert"><strong>MySQL Error:</strong><br>${data.error}</div>`;
                }
            })
            .catch(err => {
                container.innerHTML = `<div class="error-alert">Execution failed: Connection error.</div>`;
            });
        }
        
        function renderRawGrid(columns, rows) {
            const table = document.createElement('table');
            table.className = 'grid-table';
            table.style.marginTop = '10px';
            const thead = document.createElement('thead'), htr = document.createElement('tr');
            columns.forEach(col => { const th = document.createElement('th'); th.textContent = col; htr.appendChild(th); });
            thead.appendChild(htr); table.appendChild(thead);
            const tbody = document.createElement('tbody');
            rows.forEach(row => {
                const tr = document.createElement('tr');
                columns.forEach(col => {
                    const td = document.createElement('td');
                    td.textContent = row[col] === null ? 'NULL' : row[col];
                    if (row[col] === null) td.className = 'null-cell';
                    tr.appendChild(td);
                });
                tbody.appendChild(tr);
            });
            table.appendChild(tbody);
            return table;
        }

        // Removed renderSqlResultGrid inline as it's merged above

        function clearSqlQuery() {
            document.getElementById('sql-query-area').value = '';
            document.getElementById('sql-query-area').focus();
        }

        // --- INSERT TAB ENGINE ---
        function loadTableStructureForInsert() {
            const container = document.getElementById('insert-fields-container');
            container.innerHTML = '<div class="loading-indicator">Loading schema fields...</div>';
            document.getElementById('insert-table-title').innerHTML = `Insert Row: <code>${currentTable}</code>`;

            fetch(`api.php?action=table_structure&db=${currentDb}&table=${currentTable}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        renderInsertFormFields(data.structure);
                    } else {
                        container.innerHTML = `<div class="error-msg">Error: ${data.error}</div>`;
                    }
                })
                .catch(err => {
                    container.innerHTML = '<div class="error-msg">Failed to query columns schema.</div>';
                });
        }

        function renderInsertFormFields(structure) {
            const container = document.getElementById('insert-fields-container');
            container.innerHTML = '';

            structure.forEach(field => {
                const isAuto = field.Extra.includes('auto_increment');
                
                const formRow = document.createElement('div');
                formRow.className = 'insert-form-row';

                const label = document.createElement('label');
                label.innerHTML = `<strong>${field.Field}</strong> <span class="field-type">${field.Type}</span>`;
                formRow.appendChild(label);

                const inputContainer = document.createElement('div');
                inputContainer.className = 'input-container';

                if (isAuto) {
                    const info = document.createElement('input');
                    info.type = 'text';
                    info.disabled = true;
                    info.value = 'Auto Increment (Generated)';
                    info.className = 'disabled-input';
                    inputContainer.appendChild(info);
                } else {
                    let input;
                    const typeLower = field.Type.toLowerCase();

                    if (typeLower.includes('text')) {
                        input = document.createElement('textarea');
                        input.rows = 3;
                    } else if (typeLower.includes('int') || typeLower.includes('decimal') || typeLower.includes('float') || typeLower.includes('double')) {
                        input = document.createElement('input');
                        input.type = 'number';
                        if (typeLower.includes('decimal') || typeLower.includes('float')) {
                            input.step = '0.01';
                        }
                    } else if (typeLower.includes('datetime') || typeLower.includes('timestamp')) {
                        input = document.createElement('input');
                        input.type = 'text';
                        input.placeholder = 'YYYY-MM-DD HH:MM:SS';
                        input.value = getFormattedCurrentDatetime();
                    } else if (typeLower.includes('date')) {
                        input = document.createElement('input');
                        input.type = 'date';
                    } else {
                        input = document.createElement('input');
                        input.type = 'text';
                    }

                    input.name = `field_${field.Field}`;
                    input.className = 'form-control';
                    if (field.Null === 'NO' && field.Default === null) {
                        input.required = true;
                    }
                    inputContainer.appendChild(input);
                }

                formRow.appendChild(inputContainer);
                container.appendChild(formRow);
            });
        }

        function getFormattedCurrentDatetime() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
        }

        // Trigger fields rendering when switching to insert tab
        const originalSwitchTab = switchTab;
        switchTab = function(tabId) {
            originalSwitchTab(tabId);
            if (tabId === 'insert' && currentTable) {
                loadTableStructureForInsert();
                document.getElementById('insert-status-msg').innerHTML = '';
            }
        };

        function submitInsertForm(e) {
            e.preventDefault();
            const form = document.getElementById('insert-row-form');
            const statusMsg = document.getElementById('insert-status-msg');

            statusMsg.innerHTML = '<div class="loading-indicator">Inserting row...</div>';

            // Gather inputs
            const data = {};
            const formDataInputs = new FormData(form);
            for (let [name, val] of formDataInputs.entries()) {
                const colName = name.replace('field_', '');
                data[colName] = val;
            }

            const formData = new FormData();
            formData.append('db', currentDb);
            formData.append('table', currentTable);
            formData.append('data', JSON.stringify(data));

            fetch('api.php?action=insert_row', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    statusMsg.innerHTML = `<div class="success-alert">${data.message} Primary key ID inserted: <strong>${data.insert_id}</strong>.</div>`;
                    form.reset();
                    // Auto switch back to Browse after 1.5 seconds
                    setTimeout(() => {
                        selectDatabaseTable(currentDb, currentTable);
                    }, 1500);
                } else {
                    statusMsg.innerHTML = `<div class="error-alert">Error: ${data.error}</div>`;
                }
            })
            .catch(err => {
                statusMsg.innerHTML = '<div class="error-alert">Connection error. Failed to insert.</div>';
            });
        }

        // --- EXPORT DATABASE UTILITY ---
        function performExport() {
            if (!currentDb) {
                alert('Please select a database to export.');
                return;
            }

            const format = document.getElementById('export-format').value;
            let sqlOutput = `-- phpMyAdmin SQL Dump Simulator\n`;
            sqlOutput += `-- Database: ${currentDb}\n`;
            sqlOutput += `-- Generated: ${new Date().toISOString()}\n\n`;
            sqlOutput += `CREATE DATABASE IF NOT EXISTS \`${currentDb}\`;\n`;
            sqlOutput += `USE \`${currentDb}\`;\n\n`;

            const tables = databaseTreeData[currentDb] || [];
            if (tables.length === 0) {
                alert('No tables found in this database to export.');
                return;
            }

            let completedTables = 0;

            tables.forEach(table => {
                // Fetch structure first
                fetch(`api.php?action=table_structure&db=${currentDb}&table=${table}`)
                    .then(res => res.json())
                    .then(structData => {
                        if (structData.success) {
                            let tableSql = `DROP TABLE IF EXISTS \`${table}\`;\nCREATE TABLE \`${table}\` (\n`;
                            const colsSql = [];
                            let primaryKey = '';
                            
                            structData.structure.forEach(col => {
                                let colDef = `  \`${col.Field}\` ${col.Type}`;
                                if (col.Null === 'NO') colDef += ' NOT NULL';
                                if (col.Default !== null) {
                                    colDef += ` DEFAULT '${col.Default}'`;
                                }
                                if (col.Extra) colDef += ` ${col.Extra.toUpperCase()}`;
                                
                                colsSql.push(colDef);
                                if (col.Key === 'PRI') {
                                    primaryKey = col.Field;
                                }
                            });
                            
                            if (primaryKey) {
                                colsSql.push(`  PRIMARY KEY (\`${primaryKey}\`)`);
                            }
                            
                            tableSql += colsSql.join(',\n') + '\n);\n\n';
                            sqlOutput += tableSql;

                            // Fetch data
                            fetch(`api.php?action=table_data&db=${currentDb}&table=${table}`)
                                .then(res => res.json())
                                .then(dataData => {
                                    if (dataData.success && dataData.rows.length > 0) {
                                        let insertsSql = '';
                                        dataData.rows.forEach(row => {
                                            const keys = [];
                                            const vals = [];
                                            
                                            dataData.columns.forEach(col => {
                                                keys.push(`\`${col.name}\``);
                                                const val = row[col.name];
                                                if (val === null) {
                                                    vals.push('NULL');
                                                } else {
                                                    vals.push(`'${String(val).replace(/'/g, "\\'")}'`);
                                                }
                                            });
                                            
                                            insertsSql += `INSERT INTO \`${table}\` (${keys.join(', ')}) VALUES (${vals.join(', ')});\n`;
                                        });
                                        sqlOutput += insertsSql + '\n';
                                    }
                                    
                                    completedTables++;
                                    if (completedTables === tables.length) {
                                        downloadSqlFile(currentDb + '_dump.sql', sqlOutput);
                                    }
                                })
                                .catch(err => console.error(err));
                        }
                    })
                    .catch(err => console.error(err));
            });
        }

        function downloadSqlFile(filename, text) {
            const element = document.createElement('a');
            element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
            element.setAttribute('download', filename);
            element.style.display = 'none';
            document.body.appendChild(element);
            element.click();
            document.body.removeChild(element);
        }

        // --- IMPORT DATABASE UTILITY ---
        function performImport() {
            if (!currentDb) {
                alert('Please select a database context in the sidebar first.');
                return;
            }

            const fileInput = document.getElementById('import-file-input');
            const container = document.getElementById('import-results-container');
            container.innerHTML = '';

            if (fileInput.files.length === 0) {
                alert('Please select a .sql script file.');
                return;
            }

            const file = fileInput.files[0];
            const reader = new FileReader();

            container.innerHTML = '<div class="loading-indicator">Reading script file...</div>';

            reader.onload = function(e) {
                const text = e.target.result;
                // Split queries (basic parsing by semicolon)
                // Filter out comments and empty lines
                const lines = text.split('\n');
                let sqlText = '';
                lines.forEach(l => {
                    const cleanLine = l.trim();
                    if (cleanLine !== '' && !cleanLine.startsWith('--') && !cleanLine.startsWith('#')) {
                        sqlText += l + '\n';
                    }
                });

                const queries = sqlText.split(';').map(q => q.trim()).filter(q => q !== '');
                if (queries.length === 0) {
                    container.innerHTML = '<div class="error-alert">No queries found in the imported file.</div>';
                    return;
                }

                container.innerHTML = `<div class="loading-indicator">Executing ${queries.length} query statements...</div>`;
                executeImportQueriesSequentially(queries, 0, 0, 0, []);
            };

            reader.readAsText(file);
        }

        function executeImportQueriesSequentially(queries, index, successCount, errorCount, errorDetails) {
            const container = document.getElementById('import-results-container');
            if (index >= queries.length) {
                // Done
                let summary = `<div class="success-alert">Import completed successfully! ${successCount} statement(s) executed.</div>`;
                if (errorCount > 0) {
                    summary = `<div class="error-alert">Import completed with ${errorCount} errors. ${successCount} succeeded. See details below.</div>`;
                    let detailsList = '<ul style="margin:5px 0; padding-left:20px; font-size:12px;">';
                    errorDetails.forEach(d => {
                        detailsList += `<li><code>${escapeHtml(d.query)}</code><br><span style="color:red;">Error: ${d.error}</span></li>`;
                    });
                    detailsList += '</ul>';
                    summary += detailsList;
                }
                container.innerHTML = summary;
                loadDatabases();
                return;
            }

            const query = queries[index];
            const formData = new FormData();
            formData.append('db', currentDb);
            formData.append('query', query);

            fetch('api.php?action=run_query', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    executeImportQueriesSequentially(queries, index + 1, successCount + 1, errorCount, errorDetails);
                } else {
                    errorDetails.push({ query: query.substring(0, 100) + '...', error: data.error });
                    executeImportQueriesSequentially(queries, index + 1, successCount, errorCount + 1, errorDetails);
                }
            })
            .catch(err => {
                errorDetails.push({ query: query.substring(0, 100) + '...', error: 'Connection error' });
                executeImportQueriesSequentially(queries, index + 1, successCount, errorCount + 1, errorDetails);
            });
        }

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }

        function createNewDatabasePrompt() {
            const dbName = prompt('Enter name for the new database:');
            if (dbName) {
                const cleanName = dbName.trim().replace(/[^a-zA-Z0-9_]/g, '');
                if (cleanName === '') {
                    alert('Invalid database name.');
                    return;
                }
                
                const formData = new FormData();
                formData.append('db', 'xampp_sim_db');
                formData.append('query', `CREATE DATABASE \`${cleanName}\``);
                
                fetch('api.php?action=run_query', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert(`Database \`${cleanName}\` created successfully.`);
                        loadDatabases();
                    } else {
                        alert('Error creating database: ' + data.error);
                    }
                })
                .catch(err => alert('Failed to create database. Connection error.'));
            }
        }

        function loadDbStructure() {
            if (!currentDb) return;
            const container = document.getElementById('db-structure-container');
            container.innerHTML = '<div class="loading-indicator">Loading tables...</div>';
            document.getElementById('db-structure-title').innerHTML = `Database: <code>${currentDb}</code>`;

            // Since fetchTables populates the sidebar, it updates global state too.
            // Using direct fetch so we don't accidentally recurse or interfere if not needed.
            fetch(`api.php?action=tables&db=${currentDb}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (data.tables.length === 0) {
                            container.innerHTML = '<div class="info-alert">No tables found in database.</div>';
                            return;
                        }
                        const table = document.createElement('table');
                        table.className = 'grid-table';
                        table.innerHTML = '<thead><tr><th>Table Name</th><th>Actions</th></tr></thead>';
                        const tbody = document.createElement('tbody');
                        data.tables.forEach(tbl => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td><strong>${tbl}</strong></td>
                                <td class="actions-cell">
                                    <button class="btn-sm btn-primary" onclick="selectDatabaseTable('${currentDb}', '${tbl}')">Browse</button>
                                    <button class="btn-grid-delete" onclick="dropTableFromDb('${tbl}')">🗑 Drop</button>
                                </td>
                            `;
                            tbody.appendChild(tr);
                        });
                        table.appendChild(tbody);
                        container.innerHTML = '';
                        container.appendChild(table);
                    } else {
                        container.innerHTML = `<div class="error-msg">Error: ${data.error}</div>`;
                    }
                })
                .catch(err => {
                    container.innerHTML = '<div class="error-msg">Failed to load tables.</div>';
                });
        }

        function dropTableFromDb(tbl) {
            if (!confirm(`Are you sure you want to DROP the table \`${tbl}\`?`)) return;
            const fd = new FormData();
            fd.append('db', currentDb);
            fd.append('table', tbl);
            fd.append('operation', 'drop');
            fetch('api.php?action=table_operation', {method: 'POST', body: fd})
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    loadDatabases();
                    setTimeout(loadDbStructure, 500);
                } else {
                    alert('Error dropping table: ' + data.error);
                }
            })
            .catch(err => alert('Failed to drop table. Connection error.'));
        }

        function openCreateTableTab() {
            if (!currentDb) {
                alert('Select a database first.');
                return;
            }
            document.getElementById('create-table-name').value = '';
            document.getElementById('create-table-status').innerHTML = '';
            const tbody = document.getElementById('create-table-tbody');
            tbody.innerHTML = '';
            // Add 4 default columns
            for (let i = 0; i < 4; i++) {
                tbody.appendChild(buildCreateTableColumnRow());
            }
            switchTab('create-table');
        }

        function buildCreateTableColumnRow() {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><input type="text" class="form-control" name="col_name" placeholder="Column name"></td>
                <td>
                    <select class="search-select" name="col_type">
                        <option value="INT">INT</option>
                        <option value="VARCHAR">VARCHAR</option>
                        <option value="TEXT">TEXT</option>
                        <option value="DATE">DATE</option>
                        <optgroup label="Numeric">
                            <option value="TINYINT">TINYINT</option>
                            <option value="SMALLINT">SMALLINT</option>
                            <option value="MEDIUMINT">MEDIUMINT</option>
                            <option value="BIGINT">BIGINT</option>
                            <option value="DECIMAL">DECIMAL</option>
                            <option value="FLOAT">FLOAT</option>
                            <option value="DOUBLE">DOUBLE</option>
                            <option value="BOOLEAN">BOOLEAN</option>
                        </optgroup>
                        <optgroup label="Date and time">
                            <option value="DATETIME">DATETIME</option>
                            <option value="TIMESTAMP">TIMESTAMP</option>
                            <option value="TIME">TIME</option>
                        </optgroup>
                    </select>
                </td>
                <td><input type="text" class="form-control" name="col_length" placeholder="e.g. 255"></td>
                <td>
                    <select class="search-select" name="col_default_type" style="width:110px;" onchange="this.nextElementSibling.style.display = this.value === 'USER_DEFINED' ? 'inline-block' : 'none'">
                        <option value="NONE">None</option>
                        <option value="USER_DEFINED">As defined:</option>
                        <option value="NULL">NULL</option>
                        <option value="CURRENT_TIMESTAMP">CURRENT_TIMESTAMP</option>
                    </select>
                    <input type="text" class="form-control" name="col_default_val" style="display:none; width:80px; margin-top:4px;" placeholder="value">
                </td>
                <td style="text-align:center;"><input type="checkbox" name="col_null"></td>
                <td>
                    <select class="search-select" name="col_index">
                        <option value="">---</option>
                        <option value="PRIMARY KEY">PRIMARY</option>
                        <option value="UNIQUE">UNIQUE</option>
                        <option value="INDEX">INDEX</option>
                    </select>
                </td>
                <td style="text-align:center;"><input type="checkbox" name="col_ai" title="Auto Increment"></td>
            `;
            return tr;
        }

        function addCreateTableColumns() {
            const count = parseInt(document.getElementById('create-table-add-cols').value, 10);
            if (isNaN(count) || count < 1) return;
            const tbody = document.getElementById('create-table-tbody');
            for (let i = 0; i < count; i++) {
                tbody.appendChild(buildCreateTableColumnRow());
            }
        }

        function submitCreateTable() {
            const tableName = document.getElementById('create-table-name').value.trim();
            const statusBox = document.getElementById('create-table-status');
            
            if (!tableName) {
                statusBox.innerHTML = '<div class="error-alert">Please enter a table name.</div>';
                return;
            }

            const cleanName = tableName.replace(/[^a-zA-Z0-9_]/g, '');
            if (cleanName === '') {
                statusBox.innerHTML = '<div class="error-alert">Invalid table name.</div>';
                return;
            }

            const tbody = document.getElementById('create-table-tbody');
            const rows = tbody.querySelectorAll('tr');
            const columnsSql = [];
            let primaryKeySet = false;

            for (let i = 0; i < rows.length; i++) {
                const tr = rows[i];
                const name = tr.querySelector('[name="col_name"]').value.trim().replace(/[^a-zA-Z0-9_]/g, '');
                if (!name) continue;

                const type = tr.querySelector('[name="col_type"]').value;
                const length = tr.querySelector('[name="col_length"]').value.trim();
                const defaultType = tr.querySelector('[name="col_default_type"]').value;
                const defaultVal = tr.querySelector('[name="col_default_val"]').value.replace(/'/g, "''");
                const isNull = tr.querySelector('[name="col_null"]').checked;
                const index = tr.querySelector('[name="col_index"]').value;
                const ai = tr.querySelector('[name="col_ai"]').checked;

                let colDef = `\`${name}\` ${type}`;
                
                if (length) {
                    colDef += `(${length})`;
                }

                if (ai) {
                    colDef += ` AUTO_INCREMENT`;
                }

                if (!isNull && !ai) {
                    colDef += ` NOT NULL`;
                }

                if (defaultType === 'NULL') {
                    colDef += ` DEFAULT NULL`;
                } else if (defaultType === 'CURRENT_TIMESTAMP') {
                    colDef += ` DEFAULT CURRENT_TIMESTAMP`;
                } else if (defaultType === 'USER_DEFINED') {
                    colDef += ` DEFAULT '${defaultVal}'`;
                }

                if (index === 'PRIMARY KEY') {
                    if (!primaryKeySet) {
                        colDef += ` PRIMARY KEY`;
                        primaryKeySet = true;
                    }
                } else if (index) {
                    colDef += ` ${index}`;
                }

                columnsSql.push(colDef);
            }

            if (columnsSql.length === 0) {
                statusBox.innerHTML = '<div class="error-alert">Please define at least one column.</div>';
                return;
            }

            const sql = `CREATE TABLE \`${cleanName}\` (\n  ${columnsSql.join(',\n  ')}\n)`;
            
            const formData = new FormData();
            formData.append('db', currentDb);
            formData.append('query', sql);
            
            statusBox.innerHTML = '<div class="loading-indicator">Creating table...</div>';

            fetch('api.php?action=run_query', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    statusBox.innerHTML = `<div class="success-alert">Table \`${cleanName}\` created successfully!</div>`;
                    loadDatabases();
                    setTimeout(() => {
                        selectDatabaseTable(currentDb, cleanName);
                        switchTab('structure');
                    }, 1000);
                } else {
                    statusBox.innerHTML = `<div class="error-alert">Error creating table: ${data.error}</div>`;
                }
            })
            .catch(err => {
                statusBox.innerHTML = `<div class="error-alert">Failed to create table. Connection error.</div>`;
            });
        }

        // ── BUILD A SINGLE BROWSE ROW ────────────────────────────────────
        function buildBrowseRow(row, columns, pkColumn) {
            const tr = document.createElement('tr');
            const tdA = document.createElement('td');
            tdA.className = 'actions-cell';

            const editBtn = document.createElement('button');
            editBtn.className = 'btn-grid-edit';
            editBtn.textContent = '✏️ Edit';
            editBtn.onclick = () => openEditModal(row, columns, pkColumn);

            const delBtn = document.createElement('button');
            delBtn.className = 'btn-grid-delete';
            delBtn.textContent = '🗑';
            delBtn.onclick = () => {
                if (confirm(`Delete row where ${pkColumn} = ${row[pkColumn]}?`)) deleteTableRow(pkColumn, row[pkColumn]);
            };
            tdA.appendChild(editBtn);
            tdA.appendChild(delBtn);
            tr.appendChild(tdA);

            columns.forEach(col => {
                const td = document.createElement('td');
                const val = row[col.name];
                td.textContent = val === null ? 'NULL' : val;
                if (val === null) td.className = 'null-cell';
                tr.appendChild(td);
            });
            return tr;
        }

        // ── SORT TABLE ──────────────────────────────────────────────────
        let _sortCol = '', _sortAsc = true;
        function sortTableBy(colName, th) {
            _sortAsc = (_sortCol === colName) ? !_sortAsc : true;
            _sortCol = colName;
            document.querySelectorAll('.sort-arrow').forEach(s => s.textContent = '⇅');
            th.querySelector('.sort-arrow').textContent = _sortAsc ? '▲' : '▼';

            const tbody = document.querySelector('#main-browse-table tbody');
            const tbl   = document.getElementById('main-browse-table');
            if (!tbl || !tbl._allRows) return;
            const sorted = [...tbl._allRows].sort((a, b) => {
                const av = a[colName] ?? '', bv = b[colName] ?? '';
                return _sortAsc ? String(av).localeCompare(String(bv), undefined, {numeric:true})
                                : String(bv).localeCompare(String(av), undefined, {numeric:true});
            });
            tbody.innerHTML = '';
            sorted.forEach(row => tbody.appendChild(buildBrowseRow(row, tbl._columns, tbl._pkColumn)));
        }

        // ── INLINE FILTER ───────────────────────────────────────────────
        function filterBrowseRows(query) {
            const tbl = document.getElementById('main-browse-table');
            if (!tbl) return;
            const tbody = tbl.querySelector('tbody');
            tbody.innerHTML = '';
            const q = query.toLowerCase();
            const filtered = q ? tbl._allRows.filter(r => Object.values(r).some(v => String(v??'').toLowerCase().includes(q))) : tbl._allRows;
            filtered.forEach(row => tbody.appendChild(buildBrowseRow(row, tbl._columns, tbl._pkColumn)));
            const pag = document.getElementById('browse-pagination');
            if (pag) pag.textContent = `${filtered.length} of ${tbl._allRows.length} row(s) shown`;
        }

        // ── EDIT ROW MODAL ──────────────────────────────────────────────
        let _editPkCol = '', _editPkVal = '', _editColumns = [];
        function openEditModal(row, columns, pkColumn) {
            _editPkCol = pkColumn; _editPkVal = row[pkColumn]; _editColumns = columns;
            let existing = document.getElementById('edit-row-modal');
            if (existing) existing.remove();

            const overlay = document.createElement('div');
            overlay.id = 'edit-row-modal';
            overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:999;display:flex;align-items:center;justify-content:center;';

            const box = document.createElement('div');
            box.style.cssText = 'background:#fff;border-radius:8px;width:520px;max-height:80vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,0.3);';

            let fieldsHtml = columns.map(col => {
                const isAuto = col.name === pkColumn;
                const val = row[col.name] === null ? '' : row[col.name];
                return `<div class="insert-form-row">
                    <label><strong>${col.name}</strong> <span class="field-type">${col.type||''}</span></label>
                    <div class="input-container">
                        ${isAuto
                            ? `<input class="disabled-input" value="${val} (PK)" disabled>`
                            : `<input class="form-control" id="edit-field-${col.name}" value="${String(val).replace(/"/g,'&quot;')}">`
                        }
                    </div></div>`;
            }).join('');

            box.innerHTML = `
                <div style="background:#eee;padding:12px 16px;font-weight:700;border-bottom:1px solid #ddd;display:flex;justify-content:space-between;align-items:center;">
                    ✏️ Edit Row &mdash; ${currentTable}
                    <span style="cursor:pointer;font-size:20px;" onclick="document.getElementById('edit-row-modal').remove()">&times;</span>
                </div>
                <div style="padding:16px;">
                    <div id="edit-status"></div>
                    ${fieldsHtml}
                    <div class="form-actions">
                        <button class="btn-primary" onclick="submitEditRow()">💾 Save Changes</button>
                        <button class="btn-secondary" onclick="document.getElementById('edit-row-modal').remove()">Cancel</button>
                    </div>
                </div>`;
            overlay.appendChild(box);
            document.body.appendChild(overlay);
            overlay.addEventListener('click', e => { if(e.target===overlay) overlay.remove(); });
        }

        function submitEditRow() {
            const data = {};
            _editColumns.forEach(col => {
                if (col.name === _editPkCol) return;
                const inp = document.getElementById('edit-field-'+col.name);
                if (inp) data[col.name] = inp.value;
            });
            const setClauses = Object.entries(data).map(([k,v]) => `\`${k}\` = '${v.replace(/'/g,"\\'")}'`).join(', ');
            const query = `UPDATE \`${currentTable}\` SET ${setClauses} WHERE \`${_editPkCol}\` = '${_editPkVal}'`;

            const fd = new FormData();
            fd.append('db', currentDb); fd.append('query', query);
            fetch('api.php?action=run_query', {method:'POST', body:fd})
                .then(r => r.json())
                .then(d => {
                    const st = document.getElementById('edit-status');
                    if (d.success) {
                        st.innerHTML = '<div class="success-alert" style="margin-bottom:10px">✅ Row updated!</div>';
                        setTimeout(() => { document.getElementById('edit-row-modal')?.remove(); loadTableData(); }, 800);
                    } else {
                        st.innerHTML = `<div class="error-alert" style="margin-bottom:10px">Error: ${d.error}</div>`;
                    }
                });
        }

        // ── SEARCH TAB ──────────────────────────────────────────────────
        function loadSearchColumns() {
            if (!currentTable) return;
            fetch(`api.php?action=table_structure&db=${currentDb}&table=${currentTable}`)
                .then(r=>r.json()).then(d => {
                    if (!d.success) return;
                    const sel = document.getElementById('search-column');
                    sel.innerHTML = d.structure.map(c=>`<option value="${c.Field}">${c.Field}</option>`).join('');
                    document.getElementById('search-table-title').innerHTML = `Search in: <code>${currentTable}</code>`;
                });
        }

        function performSearch() {
            const col = document.getElementById('search-column').value;
            const op  = document.getElementById('search-operator').value;
            const val = document.getElementById('search-value').value;
            const container = document.getElementById('search-results-container');
            if (!val) { container.innerHTML = '<div class="info-alert">Enter a value to search.</div>'; return; }
            container.innerHTML = '<div class="loading-indicator">Searching…</div>';

            const escaped = val.replace(/'/g, "\\'");
            const query = op === 'LIKE'
                ? `SELECT * FROM \`${currentTable}\` WHERE \`${col}\` LIKE '%${escaped}%'`
                : `SELECT * FROM \`${currentTable}\` WHERE \`${col}\` ${op} '${escaped}'`;

            const fd = new FormData();
            fd.append('db', currentDb); fd.append('query', query);
            fetch('api.php?action=run_query',{method:'POST',body:fd}).then(r=>r.json()).then(d=>{
                container.innerHTML = '';
                if (d.success) {
                    const info = document.createElement('div');
                    info.className = 'success-alert';
                    info.textContent = `Found ${d.rows.length} result(s) for "${val}" in ${col}.`;
                    container.appendChild(info);
                    if (d.rows.length > 0) {
                        const t = document.createElement('table');
                        t.className = 'grid-table'; t.style.marginTop='10px';
                        const h = document.createElement('thead');
                        const hr = document.createElement('tr');
                        d.columns.forEach(c=>{const th=document.createElement('th');th.textContent=c;hr.appendChild(th);});
                        h.appendChild(hr); t.appendChild(h);
                        const b = document.createElement('tbody');
                        d.rows.forEach(row=>{const tr=document.createElement('tr');d.columns.forEach(c=>{const td=document.createElement('td');td.textContent=row[c]??'NULL';tr.appendChild(td);});b.appendChild(tr);});
                        t.appendChild(b); container.appendChild(t);
                    }
                } else {
                    container.innerHTML = `<div class="error-alert">Error: ${d.error}</div>`;
                }
            });
        }

        // ── USER ACCOUNTS TAB ───────────────────────────────────────────
        let simulatedUsers = [
            {user:'root',host:'localhost',plugin:'mysql_native_password',privileges:'ALL PRIVILEGES',active:true},
            {user:'pma',host:'localhost',plugin:'mysql_native_password',privileges:'SELECT',active:true},
        ];

        function loadUserAccounts() {
            // Try real SHOW GRANTS query first, fallback to simulated list
            const fd = new FormData();
            fd.append('db','mysql'); fd.append('query','SELECT User, Host, plugin FROM user');
            fetch('api.php?action=run_query',{method:'POST',body:fd}).then(r=>r.json()).then(d=>{
                if (d.success && d.rows.length > 0) {
                    simulatedUsers = d.rows.map(r=>({user:r.User,host:r.Host,plugin:r.plugin||'—',privileges:'—',active:true}));
                }
                renderUserAccounts();
            }).catch(()=>renderUserAccounts());
        }

        function renderUserAccounts() {
            const c = document.getElementById('user-accounts-container');
            if (!c) return;
            const t = document.createElement('table');
            t.className = 'grid-table';
            t.innerHTML = `<thead><tr><th>Username</th><th>Host</th><th>Auth Plugin</th><th>Privileges</th><th>Actions</th></tr></thead>`;
            const tbody = document.createElement('tbody');
            simulatedUsers.forEach((u,i) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td><strong>${u.user}</strong></td><td>${u.host}</td><td><code>${u.plugin}</code></td><td>${u.privileges}</td>
                    <td class="actions-cell">
                        <button class="btn-sm" onclick="editUserPriv(${i})">✏️ Edit</button>
                        ${u.user !== 'root' ? `<button class="btn-grid-delete" onclick="removeSimulatedUser(${i})">🗑</button>` : ''}
                    </td>`;
                tbody.appendChild(tr);
            });
            t.appendChild(tbody);
            c.innerHTML = '';
            c.appendChild(t);
        }

        function addSimulatedUser() {
            const name = document.getElementById('new-user-name').value.trim();
            const host = document.getElementById('new-user-host').value.trim();
            const role = document.getElementById('new-user-role').value;
            const st   = document.getElementById('user-status-msg');
            if (!name) { st.innerHTML='<div class="error-alert">Username is required.</div>'; return; }
            if (simulatedUsers.find(u=>u.user===name&&u.host===host)) { st.innerHTML='<div class="error-alert">User already exists.</div>'; return; }
            simulatedUsers.push({user:name, host:host, plugin:'mysql_native_password', privileges:role, active:true});
            renderUserAccounts();
            st.innerHTML = `<div class="success-alert">User '${name}'@'${host}' added (simulated).</div>`;
            document.getElementById('new-user-name').value='';
        }

        function removeSimulatedUser(i) {
            if (!confirm('Remove this user?')) return;
            simulatedUsers.splice(i,1);
            renderUserAccounts();
        }

        function editUserPriv(i) {
            const newPriv = prompt('Enter new privilege string:', simulatedUsers[i].privileges);
            if (newPriv !== null) { simulatedUsers[i].privileges = newPriv; renderUserAccounts(); }
        }

        // ── TABLE OPERATIONS ───────────────────────────────────────────
        function doTableOp(op) {
            let newName = '';
            const st = document.getElementById('ops-status-msg');
            
            if (op === 'rename') {
                newName = document.getElementById('op-rename-val').value.trim();
                if (!newName) { st.innerHTML = '<div class="error-alert">Enter a new table name.</div>'; return; }
            } else if (op === 'copy') {
                newName = document.getElementById('op-copy-val').value.trim();
                if (!newName) { st.innerHTML = '<div class="error-alert">Enter a destination table name.</div>'; return; }
            } else {
                const msgs = { truncate: 'EMPTY (TRUNCATE)', drop: 'DROP (DELETE)' };
                if (msgs[op] && !confirm(`You are about to ${msgs[op]} the table \`${currentTable}\`.\nAre you sure?`)) return;
            }

            st.innerHTML = '<div class="loading-indicator">Executing operation...</div>';
            const fd = new FormData();
            fd.append('db', currentDb); fd.append('table', currentTable);
            fd.append('operation', op); fd.append('new_name', newName);

            fetch('api.php?action=table_operation', {method:'POST', body:fd}).then(r=>r.json()).then(d=>{
                if (d.success) {
                    st.innerHTML = `<div class="success-alert">✅ ${d.message}</div>`;
                    loadDatabases();
                    if (op === 'drop') {
                        setTimeout(() => showServerHome(), 1000);
                    } else if (op === 'rename') {
                        currentTable = newName;
                        setTimeout(() => switchTab('browse'), 1000);
                    } else {
                        document.getElementById('op-rename-val').value = '';
                        document.getElementById('op-copy-val').value = '';
                    }
                } else {
                    st.innerHTML = `<div class="error-alert">Error: ${d.error}</div>`;
                }
            }).catch(e=>{ st.innerHTML = `<div class="error-alert">Network error.</div>`; });
        }

        // ── OVERRIDE switchTab to load search columns ───────────────────
        const _baseSwitchTab = switchTab;
        switchTab = function(tabId) {
            _baseSwitchTab(tabId);
            if (tabId === 'search' && currentTable) loadSearchColumns();
            if (tabId === 'users') loadUserAccounts();
            if (tabId === 'server-home') updateDashboard();
        };

        // ── GENERATE DUMMY DATA ─────────────────────────────────────────
        function generateDummyData() {
            if (!currentTable) return;
            const rowsCount = parseInt(document.getElementById('op-dummy-rows').value, 10);
            const st = document.getElementById('ops-status-msg');
            st.innerHTML = '<div class="loading-indicator">Generating and inserting data...</div>';

            // We need table structure to generate appropriate data types
            fetch(`api.php?action=table_structure&db=${currentDb}&table=${currentTable}`)
                .then(r => r.json())
                .then(data => {
                    if (!data.success) {
                        st.innerHTML = `<div class="error-alert">Failed to load structure: ${data.error}</div>`;
                        return;
                    }
                    
                    const structure = data.structure;
                    let inserts = [];
                    
                    for (let i = 0; i < rowsCount; i++) {
                        let keys = [];
                        let vals = [];
                        
                        structure.forEach(col => {
                            if (col.Extra.includes('auto_increment')) return; // skip autoinc
                            keys.push(`\`${col.Field}\``);
                            
                            const type = col.Type.toLowerCase();
                            let val = "''";
                            
                            if (type.includes('int')) {
                                val = Math.floor(Math.random() * 1000);
                            } else if (type.includes('decimal') || type.includes('float')) {
                                val = (Math.random() * 1000).toFixed(2);
                            } else if (type.includes('datetime') || type.includes('timestamp')) {
                                val = `'${getFormattedCurrentDatetime()}'`;
                            } else if (col.Field.toLowerCase().includes('email')) {
                                val = `'user${Math.floor(Math.random()*9999)}@example.com'`;
                            } else if (col.Field.toLowerCase().includes('name')) {
                                const names = ["John", "Jane", "Alex", "Chris", "Sam", "Pat"];
                                val = `'${names[Math.floor(Math.random()*names.length)]} ${Math.floor(Math.random()*100)}'`;
                            } else {
                                val = `'Sample Data ${Math.floor(Math.random()*1000)}'`;
                            }
                            vals.push(val);
                        });
                        
                        inserts.push(`INSERT INTO \`${currentTable}\` (${keys.join(', ')}) VALUES (${vals.join(', ')});`);
                    }
                    
                    // Run the batch insert via multi_query
                    const fd = new FormData();
                    fd.append('db', currentDb);
                    fd.append('query', inserts.join('\n'));
                    
                    fetch('api.php?action=run_multi_query', {method: 'POST', body: fd})
                        .then(r => r.json())
                        .then(d => {
                            if (d.success) {
                                st.innerHTML = `<div class="success-alert">✅ Successfully generated and inserted ${rowsCount} rows.</div>`;
                            } else {
                                st.innerHTML = `<div class="error-alert">Error: ${d.error}</div>`;
                            }
                        });
                })
                .catch(err => {
                    st.innerHTML = '<div class="error-alert">Network error generating data.</div>';
                });
        }

        // ── SAVED QUERIES ───────────────────────────────────────────────
        function saveCurrentQuery() {
            const query = document.getElementById('sql-query-area').value.trim();
            if (!query) {
                alert('Please enter a query to save.');
                return;
            }
            const name = prompt('Enter a name for this query:');
            if (!name) return;

            let queries = JSON.parse(localStorage.getItem('pma_saved_queries') || '[]');
            queries.push({ id: Date.now(), name: name, query: query });
            localStorage.setItem('pma_saved_queries', JSON.stringify(queries));
            loadSavedQueries();
        }

        function loadSavedQueries() {
            const list = document.getElementById('saved-queries-list');
            if (!list) return;
            const queries = JSON.parse(localStorage.getItem('pma_saved_queries') || '[]');
            list.innerHTML = '';
            
            if (queries.length === 0) {
                list.innerHTML = '<li style="padding:15px; color:#888; font-style:italic; font-size:12px;">No saved queries.</li>';
                return;
            }

            queries.forEach(q => {
                const li = document.createElement('li');
                li.className = 'saved-query-item';
                li.innerHTML = `
                    <div class="saved-query-name">
                        <span>${escapeHtml(q.name)}</span>
                        <button class="btn-delete-query" onclick="event.stopPropagation(); deleteSavedQuery(${q.id})" title="Delete Query">✖</button>
                    </div>
                    <div class="saved-query-preview">${escapeHtml(q.query)}</div>
                `;
                li.onclick = () => loadQueryIntoEditor(q.query);
                list.appendChild(li);
            });
        }

        function loadQueryIntoEditor(query) {
            document.getElementById('sql-query-area').value = query;
        }

        function deleteSavedQuery(id) {
            if (!confirm('Delete this saved query?')) return;
            let queries = JSON.parse(localStorage.getItem('pma_saved_queries') || '[]');
            queries = queries.filter(q => q.id !== id);
            localStorage.setItem('pma_saved_queries', JSON.stringify(queries));
            loadSavedQueries();
        }

        // ── DATABASE DESIGNER (ERD) ─────────────────────────────────────
        let isDragging = false;
        let dragTarget = null;
        let startX, startY, initialX, initialY;
        let designerTablesData = {};

        function loadDesigner() {
            if (!currentDb) return;
            const canvas = document.getElementById('designer-canvas');
            canvas.innerHTML = '<svg id="designer-svg" style="position:absolute; top:0; left:0; width:100%; height:100%; pointer-events:none; z-index:5;"></svg><div class="loading-indicator">Loading schema...</div>';
            designerTablesData = {};
            
            // Fetch all tables
            fetch(`api.php?action=tables&db=${currentDb}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        canvas.innerHTML = `<div class="error-alert">Error: ${data.error}</div>`;
                        return;
                    }
                    if (data.tables.length === 0) {
                        canvas.innerHTML = '<div class="info-alert" style="margin:20px;">No tables found in this database.</div>';
                        return;
                    }
                    
                    canvas.innerHTML = '<svg id="designer-svg" style="position:absolute; top:0; left:0; width:100%; height:100%; pointer-events:none; z-index:5;"></svg>';
                    let loadedCount = 0;
                    
                    // Stagger initial positions
                    let posX = 50;
                    let posY = 50;
                    
                    data.tables.forEach(table => {
                        fetch(`api.php?action=table_structure&db=${currentDb}&table=${table}`)
                            .then(r => r.json())
                            .then(structData => {
                                if (structData.success) {
                                    designerTablesData[table] = {
                                        structure: structData.structure,
                                        element: null
                                    };
                                    designerTablesData[table].element = renderTableCard(canvas, table, structData.structure, posX, posY);
                                    posX += 260;
                                    if (posX > 1000) {
                                        posX = 50;
                                        posY += 300;
                                    }
                                }
                                loadedCount++;
                                if (loadedCount === data.tables.length) {
                                    drawDesignerLines();
                                }
                            });
                    });
                })
                .catch(err => {
                    canvas.innerHTML = '<div class="error-alert">Failed to load tables for designer.</div>';
                });
        }

        function renderTableCard(container, tableName, structure, x, y) {
            const card = document.createElement('div');
            card.id = 'erd-card-' + tableName;
            card.className = 'erd-table-card';
            card.dataset.table = tableName;
            card.style.left = x + 'px';
            card.style.top = y + 'px';
            
            const header = document.createElement('div');
            header.className = 'erd-table-header';
            header.textContent = tableName;
            card.appendChild(header);
            
            const list = document.createElement('ul');
            list.className = 'erd-columns-list';
            
            structure.forEach(col => {
                const li = document.createElement('li');
                li.className = 'erd-column-item';
                const isPk = col.Key === 'PRI';
                li.innerHTML = `
                    <span class="erd-column-name">
                        ${isPk ? '<span class="erd-pk" title="Primary Key">🔑</span>' : ''}
                        ${escapeHtml(col.Field)}
                    </span>
                    <span class="erd-column-type">${escapeHtml(col.Type)}</span>
                `;
                list.appendChild(li);
            });
            
            card.appendChild(list);
            
            // Drag logic
            header.addEventListener('mousedown', dragStart);
            container.appendChild(card);
            
            return card;
        }

        function drawDesignerLines() {
            const svg = document.getElementById('designer-svg');
            if (!svg) return;
            svg.innerHTML = ''; // Clear existing lines
            
            const tables = Object.keys(designerTablesData);
            
            tables.forEach(tableA => {
                const structA = designerTablesData[tableA].structure;
                const elA = designerTablesData[tableA].element;
                if (!elA) return;
                
                structA.forEach(col => {
                    let targetTable = null;
                    const colName = col.Field.toLowerCase();
                    
                    tables.forEach(tableB => {
                        if (tableA === tableB) return;
                        const tb = tableB.toLowerCase();
                        const singularTb = tb.endsWith('s') ? tb.slice(0, -1) : tb;
                        
                        if (colName === `${tb}_id` || colName === `${singularTb}_id` || colName === `id_${tb}` || colName === `id_${singularTb}`) {
                            targetTable = tableB;
                        }
                    });
                    
                    if (targetTable && designerTablesData[targetTable] && designerTablesData[targetTable].element) {
                        const elB = designerTablesData[targetTable].element;
                        
                        // Calculate centers
                        const rectA = { left: elA.offsetLeft, top: elA.offsetTop, width: elA.offsetWidth, height: elA.offsetHeight };
                        const rectB = { left: elB.offsetLeft, top: elB.offsetTop, width: elB.offsetWidth, height: elB.offsetHeight };
                        
                        const x1 = rectA.left + rectA.width / 2;
                        const y1 = rectA.top + rectA.height / 2;
                        const x2 = rectB.left + rectB.width / 2;
                        const y2 = rectB.top + rectB.height / 2;
                        
                        const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                        const d = `M ${x1} ${y1} C ${(x1+x2)/2} ${y1}, ${(x1+x2)/2} ${y2}, ${x2} ${y2}`;
                        path.setAttribute('d', d);
                        path.setAttribute('stroke', 'var(--brand-m)');
                        path.setAttribute('stroke-width', '2');
                        path.setAttribute('fill', 'none');
                        path.setAttribute('opacity', '0.6');
                        svg.appendChild(path);
                    }
                });
            });
        }

        function dragStart(e) {
            dragTarget = e.target.closest('.erd-table-card');
            if (!dragTarget) return;
            
            // Bring to front
            document.querySelectorAll('.erd-table-card').forEach(c => c.style.zIndex = 10);
            dragTarget.style.zIndex = 20;

            initialX = dragTarget.offsetLeft;
            initialY = dragTarget.offsetTop;
            startX = e.clientX;
            startY = e.clientY;
            
            isDragging = true;
            
            document.addEventListener('mousemove', drag);
            document.addEventListener('mouseup', dragEnd);
        }

        function drag(e) {
            if (!isDragging || !dragTarget) return;
            e.preventDefault();
            
            const dx = e.clientX - startX;
            const dy = e.clientY - startY;
            
            dragTarget.style.left = (initialX + dx) + 'px';
            dragTarget.style.top = (initialY + dy) + 'px';
            
            drawDesignerLines();
        }

        function dragEnd() {
            isDragging = false;
            dragTarget = null;
            document.removeEventListener('mousemove', drag);
            document.removeEventListener('mouseup', dragEnd);
        }
    </script>
</body>
</html>
