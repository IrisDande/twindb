Ext.define('TwinDB.view.BackupConfig', {
    extend: 'Ext.container.Container',
    alias: 'widget.backupconfig',

    requires: [
        'Ext.button.Button',
        'Ext.form.FieldSet',
        'Ext.form.Panel',
        'Ext.form.field.ComboBox',
        'Ext.form.field.Hidden',
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
    params: null,
    items:[{
            xtype: 'form',
            header: false,
            defaults:{
                margin: 5
            },
            items:[
                {
                    xtype : 'hidden',
                    name : 'config_id'
                },
                {
                    itemId: 'name',
                    xtype: 'textfield',
                    name: 'name',
                    fieldLabel: 'Backup configuration name',
                    allowBlank: false,
                    msgTarget: 'side',
                    labelWidth: 150,
                    width: 400,
                    maxLength: 127
                },
                {
                    xtype: 'fieldset',
                    title: 'Backup configuration',
                    layout: {
                        type: 'vbox',
                        pack: 'start'
                        },
                    defaults: {
                        xtype: 'combo',
                        queryMode: 'local',
                        anchor: '100%',
                        labelWidth: 200,
                        width: 500,
                        margin: 5
                    },
                    items: [{
                            itemId: 'schedule_id',
                            fieldLabel: 'Schedule',
                            name: 'schedule_id',
                            store: 'ScheduleList',
                            displayField: 'name',
                            valueField : 'schedule_id'
                        }, {
                            itemId: 'retention_policy_id',
                            fieldLabel: 'Retention policy',
                            name: 'retention_policy_id',
                            store: 'RetentionPolicyList',
                            displayField: 'name',
                            valueField : 'retention_policy_id'
                        }, {
                            itemId: 'volume_id',
                            fieldLabel: 'Volume',
                            name: 'volume_id',
                            store: 'StorageList',
                            displayField: 'name',
                            valueField : 'volume_id'
                        }

                    ]
                },
                {
                    xtype: 'fieldset',
                    title: 'MySQL credentials for TwinDB agent',
                    layout: {
                        type: 'vbox',
                        pack: 'start'
                        },
                    defaults: {
                        anchor: '100%',
                        labelWidth: 200,
                        width: 500,
                        margin: 5
                    },
                    items: [
                        {
                            xtype: 'textfield',
                            fieldLabel : 'User name',
                            name : 'mysql_user'
                        },
                        {
                            xtype: 'textfield',
                            fieldLabel : 'Password',
                            name : 'mysql_password'
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
