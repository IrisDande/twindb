Ext.define('TwinDB.model.JobLog', {
    extend: 'Ext.data.Model',
    idProperty: 'job_id',
    fields: [
        { name: 'job_id' },
        { name: 'msg' }
    ]
});
