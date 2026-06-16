// File System Wrapper

const fs = {
    async request(action, params = {}) {
        const formData = new FormData();
        formData.append('action', action);
        for (const [key, value] of Object.entries(params)) {
            formData.append(key, value);
        }
        
        try {
            const response = await fetch('api/fs.php', {
                method: 'POST',
                body: formData
            });
            return await response.json();
        } catch (err) {
            console.error('FS Error:', err);
            return { success: false, error: err.message };
        }
    },
    
    async list(path = '') {
        return await this.request('list', { path });
    },
    
    async read(path) {
        return await this.request('read', { path });
    },
    
    async write(path, content) {
        return await this.request('write', { path, content });
    },
    
    async delete(path, permanent = false) {
        return await this.request('delete', { path, permanent });
    },

    async restoreBin(uuid, drive) {
        return await this.request('restore_bin', { uuid, drive });
    },

    async emptyBin(drive = '') {
        return await this.request('empty_bin', { drive });
    },

    async getBinConfig() {
        return await this.request('bin_config');
    },

    async setBinConfig(limitMB, bypassRecycleBin) {
        return await this.request('bin_config', { limitMB, bypassRecycleBin });
    },

    async getBinItems(drive = '') {
        return await this.request('get_bin_items', { drive });
    }
};

window.fs = fs;
