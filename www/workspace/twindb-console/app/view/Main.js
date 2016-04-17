Ext.define('TwinDB.view.Main', {
    extend: 'Ext.container.Container',
    requires:[
        'Ext.container.Container',
        'Ext.layout.container.Border',
        'TwinDB.view.Header',
        'TwinDB.view.MainPanel',
        'TwinDB.view.menu.Accordion'
    ],
    
    xtype: 'app-main',

    layout: {
        type: 'border'
    },

    items: [
        {
            xtype: 'mainmenu',
            width: 300,
            collapsible: true,
            region: 'west'
        },
        {
            xtype: 'appheader',
            region: 'north'
        },
        {
            region: 'center',
            xtype: 'mainpanel'
        },
        {
            region: 'south',
            xtype: 'container',
            html: '<div class="x-panel-header-text-container-default">' +
            '<center><span>Online backup service for MySQL ' +
            '<a href="https://twindb.com">https://twindb.com</a> ' +
            '| Help us to improve TwinDB, ' +
            '<a href="https://twindb.atlassian.net/secure/CreateIssue!default.jspa" target="_new">' +
            'file a bug!</a></span></center></div>'
        }
    ]
});
