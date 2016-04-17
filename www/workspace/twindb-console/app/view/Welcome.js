Ext.define('TwinDB.view.Welcome', {
    extend: 'Ext.container.Container',
    alias: 'widget.welcome',

    requires: [
        'Ext.layout.container.Anchor'
    ],

    defaults: {
        margin: 10
        },
    layout: 'anchor',
    items:[]
    /*
    items:[
        {
            xtype: 'container',
            anchor: '100% 100%',
            html: '<iframe src="https://docs.google.com/document/d/1_EBQKVA1Te3nbqajUKqoC9_ADHPfwEp5xUxQ-8GjXjM/pub?embedded=true" \
                frameborder="0" style="overflow:hidden;height:100%;width:100%" height="100%" width="100%"></iframe>'
        }
    ]
    */
});
