Ext.define('TwinDB.model.Storage', {
    extend: 'Ext.data.Model',
    idProperty: 'volume_id',
    fields: [
        { name: 'volume_id' },
        { name: 'name' },
        { name: 'start_time' },
        { name: 'size' },
        { name: 'used' }
    ]
});
