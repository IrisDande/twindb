Ext.define('TwinDB.model.OrderGrid', {
    extend: 'Ext.data.Model',
    idProperty: 'order_id',
    fields: [
        { name: 'order_id' },
        { name: 'package' },
        { name: 'price' },
        { name: 'start_date' },
        { name: 'stop_date' }
    ]
});
