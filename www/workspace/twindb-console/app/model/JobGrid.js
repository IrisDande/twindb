Ext.define('TwinDB.model.JobGrid', {
    extend: 'Ext.data.Model',
    idProperty: 'job_id',
    fields: [
        { name: 'job_id' },
        { name: 'name' },
        { name: 'type' },
        { name: 'status' },
        { 
            name: 'start_scheduled',
            type: 'date',
            dateFormat: 'Y-m-d H:i:s'
        },
        { 
            name: 'start_actual',
            type: 'date',
            dateFormat: 'Y-m-d H:i:s'
        },
        { 
            name: 'finish_actual',
            type: 'date',
            dateFormat: 'Y-m-d H:i:s' 
        }
    ]
});
