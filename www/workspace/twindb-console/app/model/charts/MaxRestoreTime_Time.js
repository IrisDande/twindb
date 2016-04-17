Ext.define('TwinDB.model.charts.MaxRestoreTime_Time', {
    extend: 'Ext.data.Model',
    fields: [
        { name: 'date' },
        {
            name: 'restore_time',
            type: 'int'
        }
    ]
});
