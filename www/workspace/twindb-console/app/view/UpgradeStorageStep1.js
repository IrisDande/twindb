Ext.define('TwinDB.view.UpgradeStorageStep1', { // #1
    extend: 'Ext.window.Window', // #2
    requires: [
        'Ext.button.Button',
        'Ext.form.Panel',
        'Ext.form.RadioGroup',
        'Ext.form.field.Text',
        'Ext.layout.container.Fit',
        'Ext.toolbar.Fill'
    ],
    alias: 'widget.upgradestoragestep1', // #3
    autoShow: true, // #4
    //height: 170, // #5
    width: 360, // #6
    layout: {
        type: 'fit' // #7
    },
    iconCls: 'drive_add', // #8
    title: 'Add more space: Storage package', // #9
    closeAction: 'hide', // #10
    closable: true, // #11
    defaultFocus: 'name',
    items: [
        {
            xtype: 'form', // #12
            frame: false, // #13
            bodyPadding: 15, // #14
            defaults: { // #15
                xtype: 'textfield', // #16
                anchor: '100%'
            },
            items: [
                {
                    itemId: 'package_selector',
                    xtype: 'radiogroup',
                    vertical: true,
                    columns: 1,
                    fieldLabel: 'Please choose package',
                    allowBlank: false,
                    items: [
                        {
                            boxLabel: '50 GB: $TBA per month', name: 'storage_package', inputValue: '50'
                        },
                        {
                            boxLabel: '100 GB: $TBA per month', name: 'storage_package', inputValue: '100'
                        },
                        {
                            boxLabel: '500 GB: $TBA per month', name: 'storage_package', inputValue: '500'
                        },
                        {
                            boxLabel: '1 TB: $TBA per month', name: 'storage_package', inputValue: '1000'
                        }
                    ]
                }
            ],
            dockedItems: [
                {
                    xtype: 'toolbar',
                    dock: 'bottom',
                    items: [
                        {
                            xtype: 'tbfill' //#24
                        },
                        {
                            xtype: 'button', // #25
                            itemId: 'cancel',
                            iconCls: 'cancel',
                            text: 'Cancel'
                        },
                        {
                            xtype: 'button', // #26
                            itemId: 'prev',
                            iconCls: 'resultset_previous',
                            text: 'Back',
                            disabled: true
                        },
                        {
                            xtype: 'button', // #26
                            itemId: 'next',
                            formBind: true, // #27
                            iconCls: 'resultset_next',
                            text: 'Next'
                        }
                    ]
                }       
            ]
        }
    ]
});
