Ext.define('TwinDB.view.menu.contextMenuSchedule', {
    extend: 'Ext.menu.Menu',
    alias: 'widget.contextmenu-schedule',
    items: [
        {
            itemId: 'delete',
            text: 'Delete schedule',
            iconCls: 'delete'
        }
    ]
});
