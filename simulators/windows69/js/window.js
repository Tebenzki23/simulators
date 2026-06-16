// Window Management for Web OS

class WindowManager {
    constructor() {
        this.container = document.getElementById('windows-container');
        this.focusedWindow = null;
        this.zIndexCounter = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--z-window-base')) || 10;
        
        // Binding for events
        this.handleMouseUp = this.handleMouseUp.bind(this);
        this.handleMouseMove = this.handleMouseMove.bind(this);
        
        document.addEventListener('mouseup', this.handleMouseUp);
        document.addEventListener('mousemove', this.handleMouseMove);
        
        // Drag/Resize State
        this.state = {
            isDragging: false,
            isResizing: false,
            targetObj: null,
            startX: 0, startY: 0,
            startWidth: 0, startHeight: 0,
            startTop: 0, startLeft: 0,
            resizeDir: ''
        };
    }

    createWindow(appData) {
        // Register process
        const process = window.processManager.launchProcess(appData);
        
        const winEl = document.createElement('div');
        winEl.className = 'os-window';
        winEl.id = 'win-' + process.pid;
        winEl.dataset.pid = process.pid;
        
        // Default positioning cascade
        const offset = (this.zIndexCounter % 10) * 30;
        winEl.style.width = (appData.width || 600) + 'px';
        winEl.style.height = (appData.height || 400) + 'px';
        winEl.style.top = (100 + offset) + 'px';
        winEl.style.left = (150 + offset) + 'px';
        winEl.style.zIndex = ++this.zIndexCounter;
        
        // Titlebar
        const titlebar = document.createElement('div');
        titlebar.className = 'window-titlebar';
        titlebar.innerHTML = `
            <div class="titlebar-title">
                ${appData.icon.startsWith('fas') ? `<i class="${appData.icon}" style="margin-right:8px"></i>` : `<img src="${appData.icon}">`}
                <span>${appData.name}</span>
            </div>
            <div class="titlebar-controls">
                <div class="win-btn btn-minimize" title="Minimize"><i class="fas fa-window-minimize"></i></div>
                <div class="win-btn btn-maximize" title="Maximize"><i class="far fa-square"></i></div>
                <div class="win-btn btn-close" title="Close"><i class="fas fa-times"></i></div>
            </div>
        `;
        
        // Content Area
        const content = document.createElement('div');
        content.className = 'window-content';
        if (appData.url) {
            content.innerHTML = `<iframe src="${appData.url}"></iframe>`;
        } else if (appData.content) {
            content.innerHTML = appData.content;
        }

        winEl.appendChild(titlebar);
        winEl.appendChild(content);
        
        // Add resize handles if resizable
        if (appData.resizable !== false) {
            const dirs = ['n','e','s','w','ne','nw','se','sw'];
            dirs.forEach(dir => {
                const handle = document.createElement('div');
                handle.className = `resize-handle resize-${dir}`;
                handle.dataset.dir = dir;
                winEl.appendChild(handle);
                
                handle.addEventListener('mousedown', (e) => this.startResize(e, winEl, dir));
            });
        }
        
        // Events
        winEl.addEventListener('mousedown', () => this.focusWindow(winEl));
        titlebar.querySelector('.titlebar-title').addEventListener('mousedown', (e) => this.startDrag(e, winEl));
        
        // Controls
        titlebar.querySelector('.btn-close').addEventListener('click', (e) => {
            e.stopPropagation();
            window.processManager.killProcess(process.pid); // kill process removes DOM element
        });
        titlebar.querySelector('.btn-maximize').addEventListener('click', (e) => {
            e.stopPropagation();
            if (winEl.classList.contains('maximized')) {
                winEl.classList.remove('maximized');
            } else {
                winEl.classList.add('maximized');
            }
        });
        titlebar.querySelector('.btn-minimize').addEventListener('click', (e) => {
            e.stopPropagation();
            winEl.classList.add('minimized');
            // Remove focus
            if (this.focusedWindow === winEl) {
                this.focusedWindow.classList.remove('focused');
                this.focusedWindow = null;
            }
            window.dispatchEvent(new CustomEvent('os:windowMinimized', { detail: { pid: process.pid }}));
        });
        
        this.container.appendChild(winEl);
        process.DOMelement = winEl;
        
        this.focusWindow(winEl);
        return winEl;
    }

    focusWindow(winEl) {
        if (this.focusedWindow) {
            this.focusedWindow.classList.remove('focused');
        }
        winEl.style.zIndex = ++this.zIndexCounter;
        winEl.classList.add('focused');
        winEl.classList.remove('minimized');
        this.focusedWindow = winEl;
        
        window.dispatchEvent(new CustomEvent('os:windowFocused', { detail: { pid: parseInt(winEl.dataset.pid) }}));
    }
    
    // Dragging Logic
    startDrag(e, winEl) {
        if (winEl.classList.contains('maximized')) return;
        this.focusWindow(winEl);
        this.state.isDragging = true;
        this.state.targetObj = winEl;
        this.state.startX = e.clientX;
        this.state.startY = e.clientY;
        this.state.startTop = parseInt(getComputedStyle(winEl).top, 10);
        this.state.startLeft = parseInt(getComputedStyle(winEl).left, 10);
        
        // Prevent iframe absorbing mouse events
        document.querySelectorAll('iframe').forEach(ifr => ifr.style.pointerEvents = 'none');
    }
    
    // Resizing Logic
    startResize(e, winEl, dir) {
        if (winEl.classList.contains('maximized')) return;
        this.focusWindow(winEl);
        this.state.isResizing = true;
        this.state.targetObj = winEl;
        this.state.resizeDir = dir;
        this.state.startX = e.clientX;
        this.state.startY = e.clientY;
        this.state.startWidth = parseInt(getComputedStyle(winEl).width, 10);
        this.state.startHeight = parseInt(getComputedStyle(winEl).height, 10);
        this.state.startTop = parseInt(getComputedStyle(winEl).top, 10);
        this.state.startLeft = parseInt(getComputedStyle(winEl).left, 10);
        
        document.querySelectorAll('iframe').forEach(ifr => ifr.style.pointerEvents = 'none');
    }
    
    handleMouseMove(e) {
        if (!this.state.isDragging && !this.state.isResizing) return;
        
        const dx = e.clientX - this.state.startX;
        const dy = e.clientY - this.state.startY;
        const win = this.state.targetObj;
        
        if (this.state.isDragging) {
            win.style.top = (this.state.startTop + dy) + 'px';
            win.style.left = (this.state.startLeft + dx) + 'px';
        } else if (this.state.isResizing) {
            const dir = this.state.resizeDir;
            if (dir.includes('e')) win.style.width = (this.state.startWidth + dx) + 'px';
            if (dir.includes('s')) win.style.height = (this.state.startHeight + dy) + 'px';
            if (dir.includes('w')) {
                win.style.width = (this.state.startWidth - dx) + 'px';
                win.style.left = (this.state.startLeft + dx) + 'px';
            }
            if (dir.includes('n')) {
                win.style.height = (this.state.startHeight - dy) + 'px';
                win.style.top = (this.state.startTop + dy) + 'px';
            }
        }
    }
    
    handleMouseUp() {
        if (this.state.isDragging || this.state.isResizing) {
            this.state.isDragging = false;
            this.state.isResizing = false;
            this.state.targetObj = null;
            document.querySelectorAll('iframe').forEach(ifr => ifr.style.pointerEvents = 'all');
        }
    }
}

window.windowManager = new WindowManager();
