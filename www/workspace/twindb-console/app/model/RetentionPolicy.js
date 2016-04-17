Ext.define('TwinDB.model.RetentionPolicy', {
    extend: 'Ext.data.Model',
    idProperty: 'retention_policy_id',
    fields: [
        { name: 'retention_policy_id' },
        { name: 'name' },
        { name: 'keep_full_sets' }
    ]
});
