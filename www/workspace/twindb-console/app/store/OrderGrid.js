Ext.define('TwinDB.store.OrderGrid',{
    extend : 'Ext.data.Store',
    model : 'TwinDB.model.OrderGrid',
    autoLoad: false,
    proxy : {
        type : 'ajax',
        url : 'php/getOrderGrid.php',
        reader : {
            type : 'json',
            root : 'data'
        },
        listeners : {
            exception : function(proxy, response, operation, eOpts) {
                TwinDB.util.Util.showStoreException(proxy, response, operation, eOpts);
            }
        },
        actionMethods :{
            read : 'POST'
        }
    }
});
