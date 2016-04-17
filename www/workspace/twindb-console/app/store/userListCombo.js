Ext.define('TwinDB.store.userListCombo',{
    extend : 'Ext.data.Store',
    model : 'TwinDB.model.userListCombo',
    autoLoad: false,
    proxy : {
        type : 'ajax',
        url : 'php/getListUsers.php',
        reader : {
            type : 'json',
            root : 'data'
            }
        },
        listeners : {
            exception : function(proxy, response, operation, eOpts) {
                TwinDB.util.Util.showStoreException(proxy, response, operation, eOpts);
            }
        }
});
