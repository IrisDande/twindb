Ext.define('TwinDB.view.menu.contextMenuServerGrid', {
    extend: 'Ext.menu.Menu',
    alias: 'widget.contextmenu-server-grid',
    twindb_menu_server_id: 0,
    twindb_menu_server_name: null,
    items: [
        {
            itemId: 'scheduleBackupJob',
            text: 'Schedule backup job',
            iconCls: 'database_save'
        },
        {
            itemId: 'scheduleRestoreJob',
            text: 'Restore server',
            iconCls: 'database_refresh'
        },
        {
            itemId: 'unregisterServer',
            text: 'Unregister server',
            iconCls: 'database_delete'
        }
    ]
});
