Ext.define('TwinDB.store.RetentionPolicyList',{
    extend : 'Ext.data.Store',

    requires: [
        'TwinDB.model.RetentionPolicy',
        'TwinDB.util.Util'
    ],

    model : 'TwinDB.model.RetentionPolicy',
    autoLoad: true,
    proxy : {
        type : 'ajax',
        url : 'php/getRetentionPolicy.php',
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
