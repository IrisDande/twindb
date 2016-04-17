Ext.define('TwinDB.util.Util', {
    requires: [
        'Ext.util.Cookies',
        'TwinDB.view.Welcome',
        'TwinDB.view.WelcomeUnregistered',
        'TwinDB.view.menu.Item'
    ],

    statics : { // #1
        decodeJSON : function (text) { // #2
            var result = Ext.JSON.decode(text, true);
            if (!result){
                result = {};
                result.success = false;
                result.msg = text;
            }
            return result;
        },
        showErrorMsg: function (text) { // #3
            var msg = text + '<p><a href="https://twindb.atlassian.net/secure/CreateIssue!default.jspa" target="_new">' +
                'Please file a bug</a></p>';
            Ext.Msg.show({
                title: 'Error',
                msg: msg,
                icon: Ext.Msg.ERROR,
                buttons: Ext.Msg.OK
            });
        },
        showStoreException: function(proxy, response, operation, eOpts) {
            console.log('showStoreException():');
            console.log('proxy=');
            console.log(proxy);
            console.log('response=');
            console.log(response);
            console.log('operation=');
            console.log(operation);
            console.log('eOpts=');
            console.log(eOpts);
            var mainpanel = Ext.ComponentQuery.query('mainpanel')[0];
            if(mainpanel) Ext.get(mainpanel.getEl()).unmask();
            var msg;
            if (proxy && response && operation) {
                msg = '<p>Action ' + operation.action;
                msg += '<p>Proxy: (' + proxy.type + ') ' + proxy.url;
                msg += '<p>HTTP error ' + response.status + ': ' + response.statusText;
                if(response.responseText){
                    var result = Ext.JSON.decode(response.responseText, true);
                    if(result && result.errors && result.errors.msg){
                        msg += '<p>Server replied: ' + result.errors.msg;
                    }
                }
                TwinDB.util.Util.showErrorMsg(msg);
            } else {
                msg = 'showStoreException(): Unknown error';
                TwinDB.util.Util.showErrorMsg(msg);
            }
        },
        showFormException: function(action) {
            console.log(action);
            var msg;
            if (action) {
                msg = '<p>Failure type: ' + action.failureType;
                msg += '<p>url: ' + action.url;
                msg += '<p>HTTP error ' + action.response.status + ': ' + action.response.statusText;
                if(action.response.responseText){
                    var result = Ext.JSON.decode(action.response.responseText, true);
                    if(result){
                        msg += '<p>Server replied: ' + result.errors.msg;
                    }
                }
                TwinDB.util.Util.showErrorMsg(msg);
            } else {
                msg = 'showFormException(): Unknown error';
                TwinDB.util.Util.showErrorMsg(msg);
            }
        },
        showAjaxException: function(conn, response) {
            console.log(conn);
            console.log(response);
            var msg;
            if (conn) {
                msg = '<p>url: ' + response.url;
                msg += '<p>HTTP error ' + conn.status + ': ' + conn.statusText;
                if(conn.responseText){
                    var result = Ext.JSON.decode(conn.responseText, true);
                    if(result){
                        msg += '<p>Server replied: ' + result.errors.msg;
                    }
                }
                TwinDB.util.Util.showErrorMsg(msg);
            } else {
                msg = 'showAjaxException(): Unknown error';
                TwinDB.util.Util.showErrorMsg(msg);
            }
        },
        updateInterface: function() {
            TwinDB.util.Util.updateAuthControls();
            TwinDB.util.Util.updateMainTabs();
            TwinDB.util.Util.updateMenu();
        },
        updateAuthControls: function(){
            var authenticated = Ext.util.Cookies.get('authenticated') == 1;
            console.log('updateAuthControls(): authenticated = ' + authenticated);
            var greating_field = Ext.ComponentQuery.query('appheader displayfield#greating')[0];
            if (authenticated) {
                var btn = Ext.ComponentQuery.query('appheader button#signup')[0];
                if (btn) btn.hide();
                btn = Ext.ComponentQuery.query('appheader button#login')[0];
                if (btn) btn.hide();
                btn = Ext.ComponentQuery.query('appheader box#forgotpassword')[0];
                if (btn) btn.hide();
                btn = Ext.ComponentQuery.query('appheader button#logout')[0];
                if (btn) btn.show();
                var email = Ext.util.Cookies.get('email');
                var user_name = Ext.util.Cookies.get('user_name');
                if(user_name) {
                    greating_field.setValue('Welcome, <strong>' + user_name + '</strong>');
                } else if(email){
                    greating_field.setValue('Welcome, <strong>' + email + '</strong>');
                } else {
                    greating_field.setValue('Welcome to TwinDB');
                }
                var st = Ext.getStore('userListCombo');
                st.load({
                    callback: function(records) {
                        var cmb = Ext.ComponentQuery.query('appheader combo#viewas')[0];
                        console.log('userListCombo records:');
                        if (records && records.length > 0) {
                            console.log(records);
                            cmb.show();
                        } else {
                            cmb.hide();
                        }
                    }
                });
            } else {
                btn = Ext.ComponentQuery.query('appheader button#signup')[0];
                if (btn) btn.show();
                btn = Ext.ComponentQuery.query('appheader button#login')[0];
                if (btn) btn.show();
                btn = Ext.ComponentQuery.query('appheader box#forgotpassword')[0];
                if (btn) btn.show();
                btn = Ext.ComponentQuery.query('appheader button#logout')[0];
                if (btn) btn.hide();
                greating_field.setValue('Welcome to TwinDB demo');
                var cmb = Ext.ComponentQuery.query('appheader combo#viewas')[0];
                if (cmb) {
                    cmb.hide();
                }
            }
            var mainpanel = Ext.ComponentQuery.query('mainpanel')[0];
            mainpanel.removeAll();
            mainpanel.add({
                xtype: 'welcome', // #6
                closable: false, // #7
                iconCls: 'home', // #8
                title: 'Home'
            });
        },
        updateMainTabs: function() {
            var home_tab = Ext.ComponentQuery.query('welcome')[0];
            var welcome_container = Ext.ComponentQuery.query('welcome-unregistered')[0];
            if (home_tab && welcome_container) {
                home_tab.remove(welcome_container);
            }
            this.showWelcomeInstructions(home_tab);
        },
        updateMenu: function(){
            var store = Ext.getStore('Menu');
            var menuPanel = Ext.ComponentQuery.query('mainmenu')[0];
            Ext.get(menuPanel.getEl()).mask('Loading ...');
            // remove old items
            Ext.each(Ext.ComponentQuery.query('mainmenuitem'), function(tab) {
                menuPanel.remove(tab);
                }
            );
            // add new items
            store.load(function(records){
                Ext.each(records, function(root) {
                    var menu = Ext.create('TwinDB.view.menu.Item', {
                        title: root.get('text'),
                        iconCls: root.get('iconCls')
                    });
                    if(menu.title == 'Status'){
                        menu.down('#new').hide();
                    }
                    if(menu.title == 'Profile'){
                        menu.down('#new').hide();
                    }
                    if(menu.title == 'Server farm'){
                        menu.down('#new').text = 'New attribute';
                    }
                    Ext.each(root.items(), function(items){ // #7
                        Ext.each(items.data.items, function(item){
                            console.log('Adding child:');
                            console.log(item);
                            menu.getRootNode().appendChild({ // #8
                                text: item.get('text'),
                                leaf: item.get('leaf'),
                                iconCls: item.get('iconCls'),
                                checked: item.get('checked'),
                                id: item.get('id'),
                                className: item.get('className'),
                                attribute_id: item.get('attribute_id'),
                                params: item.get('params')
                            });
                        });
                    });
                    menuPanel.add(menu);
                });
                Ext.get(menuPanel.getEl()).unmask();
            });
        },
        showWelcomeInstructions: function(panel) {
            var mainpanel = Ext.ComponentQuery.query('mainpanel')[0];
            var view = Ext.create('widget.welcome-unregistered');
            panel.add(view);
            var store = Ext.getStore('RegCode');
            store.load(
                {
                    callback: function(){
                        var rec = store.getAt(0);
                        console.log('Reg code records:');
                        console.log(rec);
                        if (rec) {
                            var f = view.down('#command_to_run');
                            if (f) { f.setValue('twindb-agent --register ' + rec.get('reg_code')); }
                        }
                        Ext.get(mainpanel.getEl()).unmask();
                    }
                }
            );
            Ext.Function.defer(function() {
                if (store.isLoading()) {
                    Ext.get(mainpanel.getEl()).mask('Loading ...');
                }
            }, 100);
        }
    }
});

