Ext.define('TwinDB.view.menu.Item', {
    extend: 'Ext.tree.Panel',
    alias: 'widget.mainmenuitem',
    border: 0,
    autoScroll: true,
    rootVisible: false,
    tbar: [
        {
            itemId: 'new',
            xtype: 'button', 
            text: 'New',
            iconCls: 'add'
        }
        //{
        //    itemId: 'delete',
        //    xtype: 'button', 
        //    text: 'Delete',
        //    iconCls: 'delete'
        //},
    ]
});