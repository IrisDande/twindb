Ext.define('TwinDB.store.MaxDataLossGrid',{
    extend : 'Ext.data.Store',
    model : 'TwinDB.model.MaxDataLossGrid',
    autoLoad: true,
    proxy : {
        type : 'ajax',
        url : 'php/getMaxDataLossGrid.php',
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
    }
});
