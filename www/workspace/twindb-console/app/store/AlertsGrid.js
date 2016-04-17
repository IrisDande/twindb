Ext.define('TwinDB.store.AlertsGrid',{
    extend : 'Ext.data.Store',
    model : 'TwinDB.model.AlertsGrid',
    autoLoad: false,
    proxy : {
        type : 'ajax',
        url : 'php/getAlertsGrid.php',
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
