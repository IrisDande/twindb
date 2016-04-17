Ext.define('TwinDB.store.JobGrid',{
    extend : 'Ext.data.Store',

    requires: [
        'TwinDB.model.JobGrid'
    ],

    model : 'TwinDB.model.JobGrid',
    autoLoad: false,
    proxy : {
        type : 'ajax',
        url : 'php/getJobGrid.php',
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
