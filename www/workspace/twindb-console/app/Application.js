Ext.define('TwinDB.Application', {
    name: 'TwinDB',

    extend: 'Ext.app.Application',

    views: [
        'Viewport'
    ],

    controllers: [
        'Login',
        'Main',
        'Menu'
    ],

    stores: [
    ]
});
