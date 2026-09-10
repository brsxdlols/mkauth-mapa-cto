(function(root) {
    'use strict';
    const defaults = () => ({ctoCom:true, ctoSem:true, clienteCom:true, clienteSem:true, online:true, offline:true});
    function listMatches(capacity, clients, filter) {
        capacity = Math.max(0, Number(capacity) || 0);
        clients = Math.max(0, Number(clients) || 0);
        if (filter === 'capacidade') return capacity > 0;
        if (filter === 'livres') return capacity > clients;
        if (filter === 'utilizadas') return clients > 0;
        if (filter === 'semclientes') return clients === 0;
        return true;
    }
    function ctoMatches(cto, filters) {
        return Number(cto.total_clientes) > 0 ? filters.ctoCom : filters.ctoSem;
    }
    function clientMatches(client, filters) {
        const linked = String(client.caixa_herm || '').trim() !== '';
        const online = String(client.status || '').toLowerCase() === 'online';
        return (linked ? filters.clienteCom : filters.clienteSem) && (online ? filters.online : filters.offline);
    }
    const api = {defaults, listMatches, ctoMatches, clientMatches};
    if (typeof module !== 'undefined' && module.exports) module.exports = api;
    else root.FtthFilters = api;
})(typeof window !== 'undefined' ? window : this);
