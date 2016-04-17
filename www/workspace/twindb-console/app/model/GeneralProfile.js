Ext.define('TwinDB.model.GeneralProfile', {
    extend: 'Ext.data.Model',
    idProperty: 'user_id',
    fields: [
        { name: 'user_id' },
        { name: 'email' },
        { name: 'first_name' },
        { name: 'last_name' },
        { name: 'phone' },
        { name: 'skype' }
    ]
});
