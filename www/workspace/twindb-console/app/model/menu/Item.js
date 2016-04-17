Ext.define('TwinDB.model.menu.Item', {
    extend: 'Ext.data.Model',
    uses: [
        'TwinDB.model.menu.Root'
    ],
    idProperty: 'id',
    fields: [
        { name: 'text' },
        { name: 'leaf' },
        { name: 'checked' },
        { name: 'params' },
        { name: 'iconCls' },
        { name: 'className' },
        { name: 'id' },
        { name: 'attribute_id' },
        { name: 'parent_id' }
    ],
    belongsTo: {
        model: 'TwinDB.model.menu.Root',
        foreignKey: 'parent_id'
    }
});
