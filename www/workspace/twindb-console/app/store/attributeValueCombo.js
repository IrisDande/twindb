Ext.define('TwinDB.store.attributeValueCombo',{
    extend : 'Ext.data.Store',
    model : 'TwinDB.model.attributeValueCombo',
    autoLoad: false,
    proxy : {
        type : 'ajax',
        url : 'php/getListAttributesValues.php',
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
