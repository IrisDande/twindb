Ext.define('TwinDB.model.Schedule', {
    extend: 'Ext.data.Model',
    idProperty: 'schedule_id',
    fields: [
        { name: 'schedule_id' },
        { name: 'name' },
        { name: 'start_time' },
        { name: 'day_mon' },
        { name: 'day_tue' },
        { name: 'day_wed' },
        { name: 'day_thu' },
        { name: 'day_fri' },
        { name: 'day_sat' },
        { name: 'day_sun' },
        { name: 'run_once_day' },
        { name: 'period' },
        { name: 'ntimes' },
        { name: 'full_copy' },
        { name: 'time_zone' },  
        { name: 'frequency_unit' }
    ]
});
