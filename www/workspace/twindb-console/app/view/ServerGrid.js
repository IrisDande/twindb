Ext.define('TwinDB.view.ServerGrid',{
    extend : 'Ext.grid.Panel',
    //extend : 'Ext.tree.Panel',
    alias : 'widget.servergrid',

    requires: [
        'Ext.button.Button',
        'Ext.form.field.ComboBox',
        'Ext.form.field.Display',
        'Ext.grid.feature.Grouping',
        'Ext.toolbar.Paging',
        'Ext.ux.grid.FiltersFeature',
        'Ext.grid.column.CheckColumn'
    ],

    title: 'Servers',
    store: 'ServerGrid',
    selType: 'checkboxmodel',
    viewConfig: {
        loadMask: false
    },
    cls: 'vertical-header-grid',
    emptyText: 'No registered servers',
    features: [
        {
            ftype: 'grouping',
            groupHeaderTpl: '<strong>Cluster</strong> {name} ({children.length} server{[values.children.length > 1 ? "s" : ""]})',
            hideGroupedHeader: true,
            startCollapsed: false,
            id: 'clusterGrouping'
        },{
            ftype: 'filters',
            encode: true,
            local: false
        }
    ],
    tbar:[
        {
            xtype: 'displayfield',
            value: 'On selected servers'
        },
        {
            itemId: 'attributeComboAction',
            xtype: 'combo',
            name : 'action',
            width: 200,
            store : [
                'set attribute',
                'remove attribute',
                'set backup configuration'
            ],
            value: 'set attribute'
        },
        {
            itemId: 'attributeCombo',
            xtype: 'combo',
            name : 'attribute',
            store : 'attributeCombo',
            displayField: 'attribute',
            queryMode: 'local',
            valueField : 'attribute_id'
        },
        {
            itemId: 'backupConfigCombo',
            xtype: 'combo',
            queryMode: 'local',
            hidden: true,
            name : 'config',
            store : 'BackupConfig',
            displayField: 'name',
            valueField : 'config_id'
        },
        {
            itemId: 'attributeValueCombo',
            xtype: 'combo',
            hidden: false,
            fieldLabel: 'to',
            labelAlign: 'right',
            labelWidth: 25,
            name : 'attribute_value',
            store : 'attributeValueCombo',
            displayField: 'attribute_value',
            valueField : 'attribute_value',
            queryMode: 'local'
        },
        {
            itemId: 'go',
            xtype: 'button',
            text: 'Go'
        }
    ],
    // Columns are defined in the store
    columns:[],
    dockedItems: [{
        xtype: 'pagingtoolbar',
        store: 'ServerGrid',   // same store GridPanel is using
        dock: 'bottom',
        displayInfo: true,
        items: [
            {
                text: 'Clear filters',
                handler: function () {
                    console.log('This:');
                    console.log(this);
                    this.up('servergrid').filters.clearFilters();
                }
            },
            {
                itemId: 'hide-offline-button',
                text: 'Hide offline servers',
                handler: function () {
                    console.log('Hide offline servers:');
                    console.log(this);
                    var grid = this.up('servergrid');
                    var store = grid.getStore();
                    store.filterBy(function(record, id) {
                        console.log('record:');
                        console.log(record);
                        return record.get('online') == 'Y';
                        }
                    );
                    this.hide();
                    var show_button = grid.down('#show-offline-button');
                    show_button.show();
                }
            },
            {
                itemId: 'show-offline-button',
                text: 'Show offline servers',
                hidden: true,
                handler: function () {
                    console.log('Hide offline servers:');
                    console.log(this);
                    var grid = this.up('servergrid');
                    var store = grid.getStore();
                    store.filterBy(function(record, id) {
                        return true;
                        }
                    );
                    var hide_button = grid.down('#hide-offline-button');
                    this.hide();
                    hide_button.show();
                }
            }
        ]
    }]
});
