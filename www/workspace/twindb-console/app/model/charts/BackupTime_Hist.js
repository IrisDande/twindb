Ext.define('TwinDB.model.charts.BackupTime_Hist', {
    extend: 'Ext.data.Model',
    fields: [
        { name: 'backup_time' },
        { 
            name: 'n_jobs',
            type: 'int'
        }
    ]
});
