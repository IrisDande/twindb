Ext.define('TwinDB.model.ServerBackupCopies', {
    extend: 'Ext.data.Model',
    idProperty: 'backup_copy_id',
    fields: [
        { name: 'backup_copy_id' },
        { name: 'name' },
        { name: 'size' },
        { name: 'finish_actual' },
        { name: 'backup_type' }
    ]
});
