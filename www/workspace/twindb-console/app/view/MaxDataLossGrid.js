Ext.define('TwinDB.view.MaxDataLossGrid',{
    extend : 'Ext.grid.Panel',
    alias : 'widget.maxdatalossgrid',

    requires: [
        'Ext.grid.column.RowNumberer'
    ],


    store: 'MaxDataLossGrid',
    viewConfig: {
        loadMask: false
    },
    border: true,
    emptyText: 'No Matching Records',
    columns:[
        { xtype: 'rownumberer' },
        { 
            dataIndex: 'config_id',
            hidden: true
        },
        { 
            text: 'Backup configuration',
            dataIndex: 'name',
            flex: 1
        },
        {
            text: 'Max data loss',
            dataIndex: 'max_data_loss',
            flex: 1
        }
    ]
});
