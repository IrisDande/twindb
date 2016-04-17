Ext.define('TwinDB.view.OrderGrid',{
    extend : 'Ext.grid.Panel',
    alias : 'widget.ordergrid',

    requires: [
        'Ext.grid.column.Action',
        'Ext.grid.column.Date'
    ],

    title: 'Orders',
    store: 'OrderGrid',
    viewConfig: {
        loadMask: false
    },

    columns:[
        { 
            text: 'Order Number',
            dataIndex: 'order_id',
            flex: 1
        },
        {
            text: 'Package',
            dataIndex: 'package',
            flex: 1
        },
        {
            text: 'Price, $/month',
            dataIndex: 'price',
            flex: 1
        },
        {
            text: 'Start Date',
            dataIndex: 'start_date',
            xtype: 'datecolumn',
            flex: 1
        },
        {
            text: 'End Date',
            dataIndex: 'stop_date',
            xtype: 'datecolumn',
            flex: 1
        },
        { 
            xtype: 'actioncolumn',
            items:[
                {
                    tooltip: 'Cancel subscription',
                    iconCls: 'cancel',
                    handler: function(grid, rowIndex, colIndex, item , e, record){
                        var store = grid.getStore();
                        console.log(record);
                        Ext.Msg.show({
                            title: 'Confirm subscription cancellation',
                            msg: 'Are you sure you want to cancel package ' + record.get('package') + ' started on ' + record.get('start_date')+'?',
                            buttons: Ext.Msg.OKCANCEL,
                            fn: function(btn){
                                if (btn == 'ok' ) {
                                    store.remove(record);
                                    store.removedRecord = record;
                                }
                            },
                            icon: Ext.MessageBox.QUESTION
                        });
                    }
                }
            ]
        }
    ]
});
