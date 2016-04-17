Ext.define('TwinDB.model.AlertsGrid', {
    extend: 'Ext.data.Model',
    idProperty: 'notification_id',
    fields: [
        { name: 'notification_id' },
        { name: 'check_id' },
        { name: 'message' },
        { name: 'acknowledged', type: 'boolean' }
    ]
});
