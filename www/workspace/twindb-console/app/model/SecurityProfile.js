Ext.define('TwinDB.model.SecurityProfile', {
    extend: 'Ext.data.Model',
    idProperty: 'user_id',
    fields: [
        { name: 'user_id' },
        { name: 'gpg_pub_key' }
    ]
});
