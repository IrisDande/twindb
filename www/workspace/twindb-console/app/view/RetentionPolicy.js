Ext.define('TwinDB.view.RetentionPolicy', {
    extend: 'Ext.container.Container',
    alias: 'widget.retentionpolicy',

    requires: [
        'Ext.button.Button',
        'Ext.form.FieldSet',
        'Ext.form.Panel',
        'Ext.form.field.Hidden',
        'Ext.form.field.Number',
        'Ext.form.field.Text',
        'Ext.layout.container.HBox',
        'Ext.layout.container.VBox',
        'Ext.toolbar.Fill',
        'Ext.toolbar.Toolbar'
    ],

    defaults: {
        margin: 5
        },
    layout:{
        type: 'hbox'
    },
    items:[{
            xtype: 'form',
            header: false,
            defaults:{
                margin: 5,
                width: 300
            },
            items:[
                {
                    xtype : 'hidden',
                    name : 'retention_policy_id'
                },
                {
                    itemId: 'name',
                    xtype: 'textfield',
                    name: 'name',
                    labelWidth: 75,
                    width: 250,
                    fieldLabel: 'Policy name',
                    allowBlank: false,
                    msgTarget: 'side',
                    maxLength: 127
                },
                {
                    xtype: 'fieldset',
                    title: 'How many full sets to keep',
                    layout: {
                        type: 'vbox',
                        pack: 'start'
                        },
                    defaults: {
                        anchor: '100%',
                        margin: 5,
                        xtype: 'numberfield',
                        labelWidth: 100,
                        labelAlign: 'right',
                        width: 200,
                        fieldStyle: "text-align:right;"
                    },
                    items: [
                        {
                            itemId: 'keep_full_sets',
                            name: 'keep_full_sets',
                            fieldLabel: 'Number of full sets to keep',
                            minValue: '1',
                            maxValue: '255'
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
                            xtype: 'tbfill'
                        },
                        {
                            itemId: 'save',
                            xtype: 'button',
                            text: 'Save',
                            iconCls: 'disk'
                        },
                        {
                            itemId: 'cancel',
                            xtype: 'button',
                            text: 'Close',
                            iconCls: 'cancel'
                        }
                    ]
                }
            ]
        }
    ]
});
