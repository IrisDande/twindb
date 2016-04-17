Ext.require([
    'TwinDB.Config'
]);


Ext.application({
    name: 'TwinDB',

    extend: 'TwinDB.Application',

    config: null,

    autoCreateViewport: false,
    authenticated: false,
    demo: true,

    statics: {
        updateAuthControls: function(){
            var me = this;
            if (me.authenticated) {
                var btn = Ext.ComponentQuery.query('appheader button#signup')[0];
                if (btn) btn.hide();
                btn = Ext.ComponentQuery.query('appheader button#login')[0];
                if (btn) btn.hide();
                btn = Ext.ComponentQuery.query('appheader button#logout')[0];
                if (btn) btn.show();
            } else {
                btn = Ext.ComponentQuery.query('appheader button#signup')[0];
                if (btn) btn.show();
                btn = Ext.ComponentQuery.query('appheader button#login')[0];
                if (btn) btn.show();
                btn = Ext.ComponentQuery.query('appheader button#logout')[0];
                if (btn) btn.hide();
            }
        }
    },
    init: function() {
        this.authenticated = Ext.util.Cookies.get('authenticated') == 1;
        this.config = new TwinDB.Config();
    },
    launch: function(){
        var me = this;
        var splashscreen = Ext.get('page-loader');
        var fadeDuration = 5;
        splashscreen.fadeOut({
            duration: fadeDuration,
            remove:true
        });
        splashscreen.next().fadeOut({
            duration: fadeDuration,
            remove:true,
            listeners: {
                afteranimate: function(){
                    console.log('Stage: ' + me.config.stage);
                    console.log('Authenticated: ' + me.authenticated);
                    console.log('Registration open: ' + me.config.registration_open);
                    me.showViewport();
                }
            }
        });
        
        console.log('TwinDB launched');
    },
    createViewport: function(){
        Ext.create('TwinDB.view.Viewport');
    },
    showViewport: function(){
        var me = this;
        if(me.isRegisteredUser()) {
            me.createViewport();
        } else {
            me.showDemoNotice(me.createViewport);
        }
    },
    showDemoNotice: function(callback){
        var config = new TwinDB.Config();
        Ext.Msg.show({
            title: 'TwinDB demo',
            msg: 'Welcome to TwinDB!'
                + '<p>This is a read-only demo mode.</p>'
                + '<p>Here you can see how we backup TwinDB itself.</p>'
                + '<p>You can backup your MySQL servers too.</p>'
                + '<p>Just sign up and get ' + config.free_storage + ' GB free!</p>',
            iconCls: 'film',
            icon: Ext.MessageBox.INFO,
            buttons: Ext.MessageBox.OK,
            fn: callback
        });
    },
    isRegisteredUser: function(){
        var me = this;
        if(me.authenticated) {
            return true;
        }
        return localStorage ? (localStorage.getItem('registeredUser') || false) : false;
    }
});
