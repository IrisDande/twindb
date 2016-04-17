Ext.define('TwinDB.model.BackupConfig', {
    extend: 'Ext.data.Model',
    idProperty: 'config_id',
    fields: [
        { name: 'config_id' },
        { name: 'name' },
        { name: 'schedule_id' },
        { name: 'retention_policy_id' },
        { name: 'volume_id' },
        { name: 'mysql_user' },
        { name: 'mysql_password' }
    ]
});
