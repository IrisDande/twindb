Ext.define('TwinDB.store.ServerGrid',{
    extend : 'Ext.data.Store',

    requires: [
        'Ext.grid.column.CheckColumn',
        'TwinDB.model.ServerGrid',
        'TwinDB.util.Util'
    ],

    model : 'TwinDB.model.ServerGrid',
    autoLoad: false,
    proxy : {
        type : 'ajax',
        url : 'php/getServerGrid.php',
        reader : {
            type : 'json',
            root : 'data'
        },
        listeners : {
            exception : function(proxy, response, operation, eOpts) {
                TwinDB.util.Util.showStoreException(proxy, response, operation, eOpts);
            }
        },
        actionMethods : {
            read : 'POST'
        }
    },
    groupField: 'cluster_id',
    sorters: 'name',
    server_filter:[],
    pageSize: 25,
    listeners: {
        beforeload: function(store, operation, eOpts){
            var me = this;

            console.log('Event beforeload on:');
            console.log(store);
            var i = 1;
            var model = Ext.ModelManager.getModel(me.model);
            var fields = [
                { name: 'selected' },
                { name: 'server_id' },
                { name: 'cluster_id' },
                { name: 'online' },
                { name: 'name' },
                { name: 'role' },
                { name: 'config' },
                { name: 'time_zone' },
                { name: 'created_at' },
                { name: 'updated_at' },
                { name: 'last_seen_at' },
                { name: 'mysql_server_id' },
                { name: 'mysql_master_server_id' },
                { name: 'mysql_master_host' },
                { name: 'mysql_seconds_behind_master' },
                { name: 'mysql_slave_io_running' },
                { name: 'mysql_slave_sql_running' }
            ];
            me.server_filter.forEach(function(value, index, arr){
                fields.push({ name: 'attribute_' + i });
                fields.push({ name: 'attribute_value_' + i });
                i++;
                }
            );
            model.setFields(fields);
            var view = Ext.ComponentQuery.query('servergrid')[0];
            if (view) {
                var columns = [
                    { text: 'server_id', dataIndex: 'server_id', hidden: true },
                    { text: 'cluster_id', dataIndex: 'cluster_id', hidden: true },
                    { xtype : 'checkcolumn', dataIndex: 'selected', text: 'Selected', hidden: true },
                    { text: 'My server_id', dataIndex: 'mysql_server_id', hidden: true },
                    { 
                        dataIndex: 'online',
                        width: 25,
                        renderer: function(value, metadata, record){
                            if (value == 'Y') {
                                metadata.tdCls = 'bullet_green'
                            } else {
                                metadata.tdCls = 'bullet_black'
                            }
                        }//,
                        //filter: {
                        //    type: 'list',
                        //    options: ['Online', 'Offline']
                        //} 
                    },
                    {
                        text: 'Hostname',
                        dataIndex: 'name',
                        flex: 1,
                        filter: {
                            type: 'string'
                        }
                    },
                    { text: 'Backup config', dataIndex: 'config', flex: 1 },
                    { text: 'Master_Server_Id', dataIndex: 'mysql_master_server_id', hidden: true },
                    { text: 'Master Host', dataIndex: 'mysql_master_host', flex: 1 },
                    {
                        text: 'Slave IO Running',
                        dataIndex: 'mysql_slave_io_running',
                        flex: 1,
                        filter: {
                            type: 'list',
                            options: ['Yes', 'No']
                        } 
                    },
                    {
                        text: 'Slave SQL Running',
                        dataIndex: 'mysql_slave_sql_running',
                        flex: 1,
                        filter: {
                            type: 'list',
                            options: ['Yes', 'No']
                        } 
                    },
                    {
                        text: 'Seconds Behind Master',
                        dataIndex: 'mysql_seconds_behind_master',
                        flex: 1,
                        filter: {
                            type: 'numeric'
                        } 
                    },
                    { text: 'Created at', dataIndex: 'created_at', hidden: true },
                    { text: 'Updated at', dataIndex: 'updated_at', hidden: true },
                    { text: 'Last seen at', width: 120, dataIndex: 'last_seen_at', flex: 1 },
                    { text: 'Time zone', width: 60,dataIndex: 'time_zone', flex: 1}
                ];
                i = 1;
                var pos = 1;
                me.server_filter.forEach(function(value, index, arr){
                    columns.splice(pos + 1, 0, { text: 'Attribute', dataIndex: 'attribute_' + i});
                    columns.splice(pos + 2, 0, { text: 'Value', dataIndex: 'attribute_value_' + i});
                    i++;
                    pos += 2;
                });
                view.reconfigure(me, columns);
            }
        }
    }
});
