Ext.define('TwinDB.model.ServerEncrypionKeys', {
    extend: 'Ext.data.Model',
    idProperty: 'server_id',
    fields: [
        { name: 'server_id' },
        { name: 'ssh_public_key' },
        { name: 'enc_public_key' }
    ]
});
