Ext.define('TwinDB.model.MaxDataLossGrid', {
    extend: 'Ext.data.Model',
    idProperty: 'config_id',
    fields: [
        { name: 'config_id' },
        { name: 'name' },
        { name: 'max_data_loss' }
    ]
});
