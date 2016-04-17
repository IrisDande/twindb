Ext.define('TwinDB.model.menu.Root', {
    extend: 'Ext.data.Model',
    uses: [
        'TwinDB.model.menu.Item'
    ],
    idProperty: 'id',
    fields: [
        { name: 'text' },
        { name: 'iconCls' },
        { name: 'id' }
    ],
    hasMany: {
        model: 'TwinDB.model.menu.Item',
        foreignKey: 'parent_id',
        name: 'items'
    }
});
