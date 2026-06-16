class ProcessManager {
    constructor() {
        this.processes = [];
        this.nextPid = 1000;
        
        // Mock data for other tabs
        this.startupItems = [
            { name: 'OneDrive', status: 'Enabled', impact: 'High' },
            { name: 'Spotify', status: 'Disabled', impact: 'Medium' },
            { name: 'Windows Security', status: 'Enabled', impact: 'Low' },
            { name: 'Cortana', status: 'Disabled', impact: 'None' }
        ];
        
        this.services = [
            { name: 'Appinfo', description: 'Application Information', status: 'Running', group: 'netsvcs' },
            { name: 'AudioSrv', description: 'Windows Audio', status: 'Running', group: 'LocalServiceNetworkRestricted' },
            { name: 'Bits', description: 'Background Intelligent Transfer Service', status: 'Stopped', group: 'netsvcs' },
            { name: 'Dnscache', description: 'DNS Client', status: 'Running', group: 'NetworkService' },
            { name: 'Spooler', description: 'Print Spooler', status: 'Running', group: 'spooler' }
        ];

        // System performance stats (global)
        this.stats = {
            cpu: 0,
            mem: 0,
            disk: 0,
            net: 0,
            history: { cpu: [], mem: [], disk: [], net: [] }
        };

        // Update loop for stats
        setInterval(() => this.updateStats(), 1000);
        
        // Listen for system events
        window.addEventListener('os:killProcess', (e) => this.killProcess(e.detail.pid));
    }

    updateStats() {
        let totalCpu = 0;
        let totalMem = 0;
        let totalDisk = 0;
        let totalNet = 0;

        this.processes.forEach(p => {
            // Randomize per-process usage
            p.cpu = Math.floor(Math.random() * 5); // 0-5%
            p.disk = (Math.random() * 0.5).toFixed(1); // 0-0.5 MB/s
            p.net = (Math.random() * 0.1).toFixed(1); // 0-0.1 Mbps
            
            // Random fluctuations in RAM
            p.ram += (Math.random() * 2 - 1); 
            if (p.ram < 5) p.ram = 5;

            totalCpu += p.cpu;
            totalMem += p.ram;
            totalDisk += parseFloat(p.disk);
            totalNet += parseFloat(p.net);
        });

        this.stats.cpu = Math.min(99, Math.max(5, totalCpu + Math.floor(Math.random() * 5)));
        this.stats.mem = Math.floor((totalMem / 8192) * 100); // Assume 8GB total
        this.stats.disk = Math.min(100, Math.max(0, Math.floor(totalDisk * 10)));
        this.stats.net = Math.min(100, Math.max(0, Math.floor(totalNet * 5)));

        // Keep history for charts (last 60 seconds)
        ['cpu', 'mem', 'disk', 'net'].forEach(key => {
            this.stats.history[key].push(this.stats[key]);
            if (this.stats.history[key].length > 60) this.stats.history[key].shift();
        });
    }
    
    // Launch a new process (Application)
    launchProcess(appData) {
        const pid = this.nextPid++;
        const process = {
            pid: pid,
            name: appData.name || 'Unknown App',
            icon: appData.icon || 'fas fa-window-maximize',
            ram: Math.floor(Math.random() * 50) + 20, 
            cpu: 0,
            disk: 0,
            net: 0,
            status: 'Running',
            user: 'Administrator',
            startTime: new Date(),
            DOMelement: null 
        };
        
        this.processes.push(process);
        window.dispatchEvent(new CustomEvent('os:processStarted', { detail: process }));
        return process;
    }
    
    killProcess(pid) {
        const index = this.processes.findIndex(p => p.pid === pid);
        if (index > -1) {
            const process = this.processes[index];
            if (process.DOMelement && process.DOMelement.parentNode) {
                process.DOMelement.parentNode.removeChild(process.DOMelement);
            }
            this.processes.splice(index, 1);
            window.dispatchEvent(new CustomEvent('os:processKilled', { detail: { pid: pid }}));
        }
    }
    
    getProcesses() { return this.processes; }
    getStats() { return this.stats; }
    getStartupItems() { return this.startupItems; }
    getServices() { return this.services; }
}

window.processManager = new ProcessManager();
