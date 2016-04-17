Ext.define('TwinDB.model.charts.MaxBackupTime_Time', {
    extend: 'Ext.data.Model',
    fields: [
        { name: 'date' },
        {
            name: 'backup_time',
            type: 'int'
        }
    ]
});
