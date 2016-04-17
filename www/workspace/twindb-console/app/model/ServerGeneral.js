Ext.define('TwinDB.model.ServerGeneral', {
    extend: 'Ext.data.Model',
    idProperty: 'server_id',
    fields: [
        { name: 'server_id' },
        { name: 'name' },
        { name: 'time_zone' },
        { name: 'config_id' },
        { name: 'lastseen' },
        { name: 'replication_status' },
        { name: 'agent_permissions_status' }
    ]
});
