Ext.define('TwinDB.view.JobLog', {
    extend: 'Ext.window.Window',
    alias: 'widget.joblog',

    requires: [
        'Ext.button.Button',
        'Ext.form.field.TextArea',
        'Ext.layout.container.Fit',
        'Ext.toolbar.Fill'
    ],

    width: 540,
    height: 480,
    autoShow: true,
    maximizable: true,
    layout: {
        type: 'fit'
    },
    iconCls: 'report',
    title: 'Job error log',
    items: [
        {
            xtype: 'textareafield',
            anchor: '100%',
            name: 'msg',
            readOnly: true,
            grow: true,
            growMax: '300',
            fieldStyle: {
                'fontFamily'   : 'courier new'
                //'fontSize'     : '8px'
            }
        }
    ],
    dockedItems: [
        {
            xtype: 'toolbar',
            dock: 'bottom',
            items: [
                {
                    xtype: 'tbfill'
                },
                {
                    xtype: 'button', // #25
                    itemId: 'close',
                    iconCls: 'cancel',
                    text: 'Close'
                }
            ]
        }       
    ]
});
