Ext.define('TwinDB.view.menu.contextMenu', {
    extend: 'Ext.menu.Menu',
    alias: 'widget.contextmenu',
    twindb_menu_type: null,
    twindb_menu_name: null,
    twindb_menu_item_id: null,
    items: [
        {
            itemId: 'delete',
            text: 'Delete',
            iconCls: 'delete'
        }
    ]
});
