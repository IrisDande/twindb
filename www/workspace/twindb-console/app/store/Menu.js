Ext.define('TwinDB.store.Menu', {
    extend: 'Ext.data.Store',
    requires: [
        'TwinDB.model.menu.Root',
        'TwinDB.util.Util'
    ],
    model: 'TwinDB.model.menu.Root',
    proxy: {
        type: 'ajax',
        url: 'php/menu.php',
        reader: {
            type: 'json',
            root: 'items'
        },
        listeners : {
            exception : function(proxy, response, operation, eOpts) {
                TwinDB.util.Util.showStoreException(proxy, response, operation, eOpts);
            }
        }
    }
});
