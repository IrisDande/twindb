Ext.define('TwinDB.view.AlertsGrid',{
    extend : 'Ext.grid.Panel',
    alias : 'widget.alerts-grid',

    requires: [
        'Ext.grid.column.Action',
        'Ext.grid.column.RowNumberer',
        'TwinDB.util.Util',
        'Ext.ux.grid.FiltersFeature'
    ],

    store: 'AlertsGrid',

    border: true,

    features: [{
        ftype: 'filters',
        // encode and local configuration options defined previously for easier reuse
        encode: true, // json encode the filter query
        local: false

    }],

    viewConfig: {
        loadMask: false,
        deferEmptyText: false,
        emptyText: 'Hooray! No alerts!',
        getRowClass: function(record){
            return record.get("acknowledged") ? "acknowledged" : "non_acknowledged";
        }
    },
    columns: [
        { 
            text: 'notification_id',
            dataIndex: 'notification_id',
            hidden: true
        },
        { xtype: 'rownumberer' },
        {
            text: 'Check',
            dataIndex: 'check_id',
            width: 200,
            filterable: true,
            filter: {
                type: 'string'
            }
        },
        {
            text: 'Message',
            dataIndex: 'message',
            width: 300,
            filter: {
                type: 'string'
            }
        },
        {
            text: 'Action',
            xtype: 'actioncolumn',
            width: 50,
            items:[
                {
                    iconCls: 'accept',
                    tooltip: 'Acknowledge',
                    handler: function(grid, rowIndex) {
                        var rec = grid.getStore().getAt(rowIndex);
                        Ext.Ajax.request({
                            url: 'php/ackNotification.php',
                            params: {
                                notification_id: rec.get('notification_id')
                            },
                            success: function(conn, response, options, eOpts) {
                                var result = Ext.JSON.decode(conn.responseText, true); // #1
                                if (!result) {
                                    result = {};
                                    result.success = false;
                                    result.msg = conn.responseText;
                                }
                                if (result.success) {
                                    grid.removeRowCls(rowIndex, 'non_acknowledged');
                                    grid.addRowCls(rowIndex, 'acknowledged');
                                    Ext.Msg.show({
                                        title: 'Success',
                                        msg: 'Alert <strong>' + rec.get('check_id') + '</strong>'
                                            + ' acknowledged',
                                        buttons: Ext.Msg.OK,
                                        icon: Ext.Msg.INFO
                                    });
                                } else {
                                    TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
                                }
                            },
                            failure: function(conn, response, options, eOpts) {
                                TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
                            }
                        });
                    }
                }
                /*
                {
                    iconCls: 'accept',
                    tooltip: 'Resolve',
                    handler: function(grid, rowIndex, colIndex) {
                        var rec = grid.getStore().getAt(rowIndex);
                        alert("Resolved " + rec.get('notification_id'));
                    }
                }
                */
            ]
        }
    ]
});
