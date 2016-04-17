Ext.define('TwinDB.view.menu.contextMenuJobs', {
    extend: 'Ext.menu.Menu',
    alias: 'widget.contextmenu-job',
    twindb_menu_job_id: 0,
    items: [
        {
            itemId: 'viewLog',
            text: 'View error log',
            iconCls: 'report'
        },
        {
            itemId: 'restartJob',
            text: 'Restart job',
            iconCls: 'control_repeat'
        },
        {
            itemId: 'cancelJob',
            text: 'Cancel job',
            iconCls: 'cancel'
        }
    ]
});
