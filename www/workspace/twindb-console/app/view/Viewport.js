Ext.define('TwinDB.view.Viewport', {
    extend: 'Ext.container.Viewport',
    requires:[
        'Ext.layout.container.Fit',
        'TwinDB.view.Main'
    ],
    layout: {
        type: 'fit'
    },

    items: [{
        xtype: 'app-main'
    }]
});
