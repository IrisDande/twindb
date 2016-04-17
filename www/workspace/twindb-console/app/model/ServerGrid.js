Ext.define('TwinDB.model.ServerGrid', {
    extend: 'Ext.data.Model',
    idProperty: 'server_id',
    fields: [
        { name: 'selected' },
        { name: 'cluster_id' },
        { name: 'server_id' },
        { name: 'online' },
        { name: 'name' },
        { name: 'config' },
        { name: 'time_zone' },
        { name: 'created_at' },
        { name: 'updated_at' },
        { name: 'last_seen_at' },
        { name: 'mysql_server_id' },
        { name: 'mysql_master_server_id' },
        { name: 'mysql_master_host' },
        { name: 'mysql_seconds_behind_master' },
        { name: 'mysql_slave_io_running' },
        { name: 'mysql_slave_sql_running' },
        { name: 'role' }
    ]
});
