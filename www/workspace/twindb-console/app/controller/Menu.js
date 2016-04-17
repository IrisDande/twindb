var getKeysFromJson = function (obj) {
    var keys = [];
    for (var key in obj) {
        if (obj.hasOwnProperty(key)) {
            keys.push(key);
        }
    }
    return keys;
};

Ext.define('TwinDB.controller.Menu', {
    extend: 'Ext.app.Controller',
    requires:[
        'TwinDB.util.Util',
        'TwinDB.view.menu.contextMenu',
        'TwinDB.view.menu.contextMenuJobs',
        'TwinDB.view.menu.contextMenuServerGrid',
        'TwinDB.view.JobLog'
    ],
    models: [
        'menu.Root',
        'menu.Item'
    ],
    stores: [
        'Menu',
        'OrderGrid',
        'ServerGrid',
        'JobLog',
        'RegCode',
        'ScheduleList',
        'RetentionPolicyList',
        'StorageList'
//        'attributeCombo'
    ],
    views: [
        'menu.Accordion',
        'menu.Item',
        'NewAttribute',
        'NewBackupConfig',
        'NewSchedule',
        'NewRetentionPolicy',
        'NewStorage',
        'GeneralProfile',
        'SecurityProfile',
        'ServerGrid',
        'OrderGrid',
        'BackupConfig'
    ],
    refs: [
        {
            ref: 'mainPanel',
            selector: 'mainpanel'
        },
        {
            ref: 'mainMenu',
            selector: 'mainmenu'
        }
    ],
    init: function(application) {
        var me = this;

        this.application.on("attrvalueupdate", this.updateAttributeValues, this);
        this.application.on("serverfilterupdate", this.updateServerFilter, this);
        me.control({
            "mainmenu": {
                render: TwinDB.util.Util.updateMenu
            },
            "mainmenuitem[title!=Server farm]": {
                itemclick: me.onMenuItemClick,
                itemcontextmenu: me.showContextMenu
            },
            "mainmenuitem[title=Server farm]": {
                itemclick: function(view, record, item, index, e, eOpts ){
                    var me = this;

                    console.log('Event itemclick on record:');
                    console.log(record);
                    if(record.get('id') == 'attribute_id_0'){
                        me.resetNodeStatus(record);
                    } else {
                        me.toggleNodeStatus(record);
                        if(record.isLeaf()){
                            me.updateParentStatus(record);
                        } else {
                            record.expand();
                            me.updateChildrenStatus(record);
                        }
                    }
                    me.onMenuItemClick(view, record, item, index, e, eOpts);
                    me.application.fireEvent("serverfilterupdate");
                },
                //checkchange: me.openServerGrid,
                itemexpand: me.updateAttributeValues,
                itemcontextmenu: me.showContextMenu
            },
            "mainmenuitem[title=Server farm] button#new": {
                click: function(btn, e, eOpts){
                    Ext.create('widget.newattribute');
                }
            },
            "mainmenuitem[title=Backup configuration] button#new": {
                click: function(btn, e, eOpts){
                    Ext.create('widget.newbackupconfig');
                },
                itemcontextmenu: me.showContextMenu
            },
            "mainmenuitem[title=Schedule] button#new": {
                click: function(btn, e, eOpts){
                    Ext.create('widget.newschedule');
                }
            },
            "mainmenuitem[title=Retention policy] button#new": {
                click: function(btn, e, eOpts){
                    Ext.create('widget.newretentionpolicy');
                }
            },
            "mainmenuitem[title=Storage] button#new": {
                click: me.openNewStorageWindow
            },
            "newattribute form button#submit": {
                click: this.onNewAttributeButtonClickSubmit 
            },
            "newattribute form textfield": {
                specialkey: this.onTextfieldSpecialKey
            },
            "newattribute form button#cancel": {
                click: function(button, e, options) {
                    button.up('window').close();
                }
            },
            "newbackupconfig form button#submit": {
                click: this.onNewBackupConfigButtonClickSubmit 
            },
            "newbackupconfig form textfield": {
                specialkey: this.onTextfieldSpecialKey
            },
            "newbackupconfig form button#cancel": {
                click: function(button, e, options) {
                    button.up('window').close();
                }
            },
            "newschedule form button#submit": {
                click: this.onButtonClickSubmit 
            },
            "newschedule form button#cancel": {
                click: function(button, e, options) {
                    button.up('window').close();
                }
            },
            "newschedule form textfield": {
                specialkey: this.onTextfieldSpecialKey
            },
            "newretentionpolicy form button#submit": {
                click: this.onNewRetentioPolicyButtonClickSubmit
            },
            "newretentionpolicy form button#cancel": {
                click: function(button, e, options) {
                    button.up('window').close();
                }
            },
            "newretentionpolicy form textfield": {
                specialkey: this.onTextfieldSpecialKey
            },
            "contextmenu": {
                click: me.doContextMenuAction
            },
            "jobgrid": {
                itemcontextmenu: function(grid, record, item, index, e, eOpts) {
                    var me = this;
                    e.stopEvent();
                    Ext.each(Ext.ComponentQuery.query('contextmenu-job'), function(cm) {
                        cm.destroy();
                    });
                    var cm = Ext.create('widget.contextmenu-job',{
                        twindb_menu_job_id: record.get('job_id')
                    });
                    var job_status = record.get('status');
                    if ( job_status == 'Failed' || job_status == 'Finished' ) {
                        var item = Ext.ComponentQuery.query('contextmenu-job #cancelJob')[0];
                        item.disable();
                    }
                    cm.showAt(e.getXY());
                }
            },
            "jobgrid-server": {
                itemcontextmenu: function(grid, record, item, index, e, eOpts) {
                    e.stopEvent();
                    Ext.each(Ext.ComponentQuery.query('contextmenu-job'), function(cm) {
                        cm.destroy();
                    });
                    var cm = Ext.create('widget.contextmenu-job',{
                        twindb_menu_job_id: record.get('job_id')
                    });
                    var job_status = record.get('status');
                    if ( job_status == 'Failed' || job_status == 'Finished' ) {
                        var item = Ext.ComponentQuery.query('contextmenu-job #cancelJob')[0];
                        item.disable();
                    }
                    cm.showAt(e.getXY());
                }
            },
            "contextmenu-job": {
                click: me.doContextMenuJobAction
            },
            "joblog button#close": {
                click: function(button, e, options) {
                    button.up('window').close();
                }
            },
            "servergrid": {
                itemcontextmenu: function(grid, record, item, index, e, eOpts) {
                    var me = this;
                    e.stopEvent();
                    Ext.each(Ext.ComponentQuery.query('contextmenu-server-grid'), function(cm) {
                        cm.destroy();
                    });
                    var cm = Ext.create('widget.contextmenu-server-grid',{
                        twindb_menu_server_id: record.get('server_id'),
                        twindb_menu_server_name: record.get('name')
                    });
                    cm.showAt(e.getXY());
                }
            },
            "contextmenu-server-grid": {
                click: function(menu, item, e, eOpts){
                    var me = this;
                    console.log('Context menu:');
                    console.log(menu);
                    console.log('Clicked on item:');
                    console.log(item);
                    switch ( item.itemId ) {
                        case 'viewLog':
                            var store = Ext.getStore('JobLog');
                            var win = Ext.create('widget.joblog');
                            Ext.get(win.getEl()).mask('Loading error log');
                            store.load({
                                params: {
                                    job_id: menu.twindb_menu_job_id
                                },
                                callback: function(records, operation, success){
                                    var f = win.down('textareafield[name=msg]');
                                    console.log(records);
                                    var msg = records[0].data.msg;
                                    if ( msg == "" ) {
                                        msg = "Log is empty";
                                    }
                                    f.setValue(msg);
                                    Ext.get(win.getEl()).unmask();
                                }
                            });
                            break;
                        case 'scheduleRestoreJob':
                            var store = Ext.getStore('ServerBackupCopies');
                            var mainpanel = Ext.ComponentQuery.query('mainpanel')[0];
                            Ext.get(mainpanel.getEl()).mask('Loading...');
                            store.load({
                                params: {
                                    server_id: menu.twindb_menu_server_id
                                },
                                callback: function(records, operation, success) {
                                    Ext.create('TwinDB.view.RestoreServerStep1',{
                                        server_id: menu.twindb_menu_server_id
                                    });
                                    Ext.get(mainpanel.getEl()).unmask();
                                }
                            });
                            break;
                        case 'unregisterServer':
                            Ext.create('TwinDB.view.unregisterServer', {
                                server_id: menu.twindb_menu_server_id,
                                server_name: menu.twindb_menu_server_name
                            });
                            break;
                        case 'scheduleBackupJob':
                            Ext.Msg.show({
                                title: 'Schedule job confirmation',
                                msg: 'Are you sure you want to schedule a backup job on <strong>' + menu.twindb_menu_server_name + '</strong>?',
                                icon: Ext.Msg.QUESTION,
                                buttons: Ext.Msg.OKCANCEL,
                                fn: function(btn){
                                    if (btn == 'ok' ) {
                                        console.log('Will schedule job on server ' + menu.twindb_menu_server_id);
                                        me.scheduleBackupJob(menu.twindb_menu_server_id, menu.twindb_menu_server_name);
                                    }
                                }
                            });
                            break;
                    }
                    menu.destroy();
                }
            },
            "unregister-server": {
                afterrender: function(win) {
                    var f = win.down('#confirm-unregister');
                    f.setBoxLabel('Yes, I am sure I want to unregister server <strong>' + win.server_name +'</strong>');
                    Ext.get(win.getEl()).mask('Loading ...');
                    Ext.Ajax.request({
                        url: 'php/getBackupsCountSize.php',
                        params: {
                            server_id: win.server_id
                        },
                        success: function(conn, response, options, eOpts) {
                            var result = Ext.JSON.decode(conn.responseText, true); // #1
                            if (!result){ // #2
                                result = {};
                                result.success = false;
                                result.msg = conn.responseText;
                            }
                            if (result.success) {
                                var f = win.down('#delete-backups');
                                f.setBoxLabel('Also delete all backup copies taken from this server(<strong>' + result.data.count + '</strong> copies, total size - <strong>' + result.data.size + '</strong>)');
                                Ext.get(win.getEl()).unmask();
                            } else {
                                TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
                            }
                        },
                        failure: function(conn, response, options, eOpts) {
                            TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
                        }
                    });
                }
            },
            "unregister-server button#cancel": {
                click: function(button, e, options) {
                    button.up('window').close();
                }
            },
            "unregister-server button#submit": {
                click: function(button, e, options) {
                    var me = this;
                    var win = button.up('window');
                    var formPanel = button.up('form');
                    var delete_backups = formPanel.down('#delete-backups').getValue();
                    if (formPanel.getForm().isValid()) {
                        var win_wait = Ext.Msg.wait('Unregistering server. Please wait...');
                        Ext.Ajax.request({
                            url: 'php/doUnregisterServer.php',
                            params: {
                                server_id: win.server_id,
                                delete_backups: delete_backups
                            },
                            success: function(conn, response, options, eOpts) {
                                var result = Ext.JSON.decode(conn.responseText, true); // #1
                                if (!result){ // #2
                                    result = {};
                                    result.success = false;
                                    result.msg = conn.responseText;
                                }
                                if (result.success) {
                                        var store = Ext.getStore('ServerGrid');
                                        var rec = store.getById( win.server_id );
                                        if ( rec ) {
                                            console.log('Removing record');
                                            console.log(rec)
                                            store.remove(rec);
                                        }
                                        win_wait.close();
                                } else {
                                    TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
                                }
                            },
                            failure: function(conn, response, options, eOpts) {
                                TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
                            }
                        });
                        win.close();
                    }
                }
            },
            "unregister-server #confirm-unregister": {
                change: function(f, newValue, oldValue, eOpts ) {
                    var form = f.up('form');
                    var btn = form.down('button#submit');
                    if ( newValue == true ) {
                        btn.enable();
                    } else {
                        btn.disable();
                    }
                }
            }
        });
    },
    onMenuItemClick: function(view, record, item, index, e, options){
        var mainPanel = this.getMainPanel(); // #1
        console.log('Selected record:');
        console.log(record);
        var newTab = mainPanel.items.findBy( // #2
            function (tab){
                console.log('Comparing with:');
                console.log(tab);
                if(record.raw.className == 'servergrid'){
                    return tab.xtype === record.raw.className;
                } else if (record.raw.className == 'schedule') {
                    console.log('Decoding ' + record.raw.params);
                    var params = Ext.JSON.decode(record.raw.params);
                    var schedule_id = params.schedule_id;
                    return (tab.xtype == record.raw.className) && (tab.params.schedule_id == schedule_id);
                } else {
                    return tab.title === record.raw.text;
                }
            }
        );
        console.log('newTab = ');
        console.log(newTab);
        if (!newTab){ // #4
            var title = record.raw.className == 'servergrid' ? 'Servers' : record.get('text');
            Ext.get(mainPanel.getEl()).mask('Loading ...');
            console.log('Adding new tab');
            var params = null; 
            if (record.raw.params != '') {
                console.log('Decoding ' + record.raw.params);
                params = Ext.JSON.decode(record.raw.params);
            }
            newTab = mainPanel.add({
                xtype: record.raw.className,
                closable: true,
                iconCls: record.get('iconCls'),
                params: params,
                title: title
            });
        }
        mainPanel.setActiveTab(newTab); // #10
    },
    openNewStorageWindow: function(btn, e, eOpts){
        console.log('New Storage button click');
        var store = Ext.getStore('RegCode');
        store.load(
            {
                callback: function(){
                    var rec = store.getAt(0);
                    console.log('Reg code records:');
                    console.log(rec);
                    var reg_code = 'hexadecimal_registation_code';
                    if (rec) {
                        reg_code = rec.get('reg_code');
                    }
                    var authenticated = Ext.util.Cookies.get('authenticated') == 1;
                    var msg = '<p>To add new storage you have to install package <strong>twindb-server-storage</strong> from ' +
                        '<a href="https://repo.twindb.com/">TwinDB repository</a>' +
                        '<p>Then execute command to register the storage server in TwinDB' +
                        '<pre>twindb-register-storage ' + reg_code + '</pre>';
                    if (!authenticated) {
                        msg += '<p>You must login to see the actual registration code';
                    }
                    Ext.Msg.show({
                        title: 'Add new storage',
                        msg: msg,
                        icon: Ext.Msg.INFO,
                        buttons: Ext.Msg.OK
                    });
                }
            }
        );

    },
    onNewAttributeButtonClickSubmit: function(button, e, options) {
        var me = this;
        console.log('newattribute submit'); // #5
        var formPanel = button.up('form'),
            newattribute = button.up('newattribute'),
            name = formPanel.down('textfield[name=name]').getValue();
        if (formPanel.getForm().isValid()) {
            var win_wait = Ext.Msg.wait('Please wait...');
            Ext.Ajax.request({
                url: 'php/addNewAttribute.php',
                params: {
                    name: name
                    },
                success: function(conn, response, options, eOpts) {
                    Ext.get(newattribute.getEl()).unmask();
                    var result = Ext.JSON.decode(conn.responseText, true); // #1
                    if (!result){ // #2
                        result = {};
                        result.success = false;
                        result.msg = conn.responseText;
                    }
                    if (result.success) { // #3
                        var attribute_id = 'attribute_id_' + result.data.attribute_id;
                        var menu = Ext.ComponentQuery.query('mainmenuitem[title=Server farm]')[0];
                        menu.getRootNode().appendChild({ // #8
                            text: name,
                            leaf: false,
                            attribute_id: result.data.attribute_id,
                            id: attribute_id,
                            iconCls: 'tag_blue',
                            checked: false,
                            params: '{"attribute_id":"' + result.data.attribute_id +'"}',
                            className: 'servergrid' // xtype of form with schedule details
                        });
                        newattribute.close(); // #4
                        var st = Ext.getStore('attributeCombo');
                        st.reload({
                            callback: function(records, operation, success) {
                                win_wait.close();
                            }
                        });
                    } else {
                        TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
                    }
                },
                failure: function(conn, response, options, eOpts) {
                    TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
                }
            });
        }
    },
    onNewBackupConfigButtonClickSubmit: function(button, e, options) {
        var me = this;
        console.log('newbackupconfig submit'); // #5
        var formPanel = button.up('form'),
            newbackupconfig = button.up('newbackupconfig'),
            name = formPanel.down('textfield[name=name]').getValue();
        if (formPanel.getForm().isValid()) {
            var win_wait = Ext.Msg.wait('Please wait...');
            Ext.Ajax.request({
                url: 'php/addBackupConfig.php',
                params: {
                    name: name
                    },
                success: function(conn, response, options, eOpts) {
                    Ext.get(newbackupconfig.getEl()).unmask();
                    var result = Ext.JSON.decode(conn.responseText, true); // #1
                    if (!result){ // #2
                        result = {};
                        result.success = false;
                        result.msg = conn.responseText;
                    }
                    if (result.success) { // #3
                        var config_id = 'config_id_' + result.data.config_id;
                        var menu = Ext.ComponentQuery.query('mainmenuitem[title=Backup configuration]')[0];
                        menu.getRootNode().appendChild({ // #8
                            text: name,
                            leaf: true,
                            id: config_id,
                            iconCls: 'cog',
                            params: '{"config_id":"' + result.data.config_id +'"}',
                            className: 'backupconfig' // xtype of form with schedule details
                        });
                        newbackupconfig.close(); // #4
                        // add new node to left menu
                        win_wait.close();
                    } else {
                        TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
                    }
                },
                failure: function(conn, response, options, eOpts) {
                    TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
                }
            });
        }
    },
    onButtonClickSubmit: function(button, e, options) {
        var me = this;
        console.log('newschedule submit'); // #5
        var formPanel = button.up('form'),
            newschedule = button.up('newschedule'),
            name = formPanel.down('textfield[name=name]').getValue();
        if (formPanel.getForm().isValid()) {
            var win_wait = Ext.Msg.wait('Please wait...');
            Ext.Ajax.request({
                url: 'php/addSchedule.php',
                params: {
                    name: name
                    },
                success: function(conn, response, options, eOpts) {
                    Ext.get(newschedule.getEl()).unmask();
                    var result = Ext.JSON.decode(conn.responseText, true); // #1
                    if (!result){ // #2
                        result = {};
                        result.success = false;
                        result.msg = conn.responseText;
                    }
                    if (result.success) { // #3
                        var schedule_id = 'schedule_id_' + result.data.schedule_id;
                        var menu = Ext.ComponentQuery.query('mainmenuitem[title=Schedule]')[0];
                        menu.getRootNode().appendChild({ // #8
                            text: name,
                            leaf: true,
                            id: schedule_id,
                            iconCls: 'calendar_view_day',
                            params: '{"schedule_id":"' + result.data.schedule_id +'"}',
                            className: 'schedule' // xtype of form with schedule details
                        });
                        newschedule.close(); // #4
                        // add new node to left menu
                        var store = Ext.getStore('ScheduleList');
                        store.reload();
                        win_wait.close();
                    } else {
                        TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
                    }
                },
                failure: function(conn, response, options, eOpts) {
                    TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
                }
            });
        }
    },
    onNewRetentioPolicyButtonClickSubmit: function(button, e, options) {
        var me = this;
        console.log('newretentionpolicy submit'); // #5
        var formPanel = button.up('form'),
            newretentionpolicy = button.up('newretentionpolicy'),
            name = formPanel.down('textfield[name=name]').getValue();
        if (formPanel.getForm().isValid()) {
            var win_wait = Ext.Msg.wait('Please wait...');
            Ext.Ajax.request({
                url: 'php/addRetentionPolicy.php',
                params: {
                    name: name
                    },
                success: function(conn, response, options, eOpts) {
                    Ext.get(newretentionpolicy.getEl()).unmask();
                    var result = Ext.JSON.decode(conn.responseText, true); // #1
                    if (!result){ // #2
                        result = {};
                        result.success = false;
                        result.msg = conn.responseText;
                    }
                    if (result.success) { // #3
                        var retention_policy_id = 'retention_policy_id_' + result.data.retention_policy_id;
                        var menu = Ext.ComponentQuery.query('mainmenuitem[title=Retention policy]')[0];
                        menu.getRootNode().appendChild({ // #8
                            text: name,
                            leaf: true,
                            id: retention_policy_id,
                            iconCls: 'page_white_stack',
                            params: '{"retention_policy_id":"' + result.data.retention_policy_id +'"}',
                            className: 'retentionpolicy' // xtype of form with schedule details
                        });
                        newretentionpolicy.close(); // #4
                        // add new node to left menu
                        var store = Ext.getStore('RetentionPolicyList');
                        store.reload();
                        win_wait.close();
                    } else {
                        TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
                    }
                },
                failure: function(conn, response, options, eOpts) {
                    TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
                }
            });
        }
    },
    onTextfieldSpecialKey: function(field, e, options) {
        console.log('Special key event handler after key #' + e.getKey() + ' pressed'); // #6
        if (e.getKey() == e.ENTER){
            var submitBtn = field.up('form').down('button#submit');
            submitBtn.fireEvent('click', submitBtn, e, options);
        }
        if (e.getKey() == e.ESC){
            field.up('window').close();
        }
    },
    showContextMenu: function(view, record, item, index, e){
        var me = this;
        e.stopEvent();
        console.log('click on id = ' + record.get('id'));
        var menu = view.up('panel');
        console.log(menu);
        console.log(record);

        if(menu.title == 'Schedule' ||
                menu.title == 'Backup configuration' ||
                menu.title == 'Retention policy' ||
                menu.title == 'Storage' ||
                (menu.title == 'Server farm' && record.get('leaf') == false)){
            var cm = Ext.create('widget.contextmenu', {
                twindb_menu_item_id: record.get('id'),
                twindb_menu_name: record.get('text')
            });
            cm.showAt(e.getXY());
        }
    },
    doContextMenuJobAction: function(menu, item, e, eOpts){
        var me = this;
        console.log('Context menu:');
        console.log(menu);
        console.log('Clicked on item:');
        console.log(item);
        switch ( item.itemId ) {
            case 'viewLog':
                var store = Ext.getStore('JobLog');
                var win = Ext.create('widget.joblog');
                Ext.get(win.getEl()).mask('Loading error log');
                store.load({
                    params: {
                        job_id: menu.twindb_menu_job_id
                    },
                    callback: function(records, operation, success){
                        var f = win.down('textareafield[name=msg]');
                        console.log(records);
                        var msg = records[0].data.msg;
                        if ( msg == "" ) {
                            msg = "Log is empty";
                        }
                        f.setValue(msg);
                        Ext.get(win.getEl()).unmask();
                    }
                });
                break;
            case 'restartJob':
                Ext.Msg.show({
                    title: 'Restart job confirmation',
                    msg: 'Are you sure you want to restart job "' + menu.twindb_menu_job_id + '"?',
                    icon: Ext.Msg.QUESTION,
                    buttons: Ext.Msg.OKCANCEL,
                    fn: function(btn){
                        if (btn == 'ok' ) {
                            console.log('Will restart job ' + menu.twindb_menu_job_id);
                            me.restartJob(menu.twindb_menu_job_id);
                        }
                    }
                });
                break;
            case 'cancelJob':
                Ext.Msg.show({
                    title: 'Cancel job confirmation',
                    msg: 'Are you sure you want to cancel job "' + menu.twindb_menu_job_id + '"?',
                    icon: Ext.Msg.QUESTION,
                    buttons: Ext.Msg.OKCANCEL,
                    fn: function(btn){
                        if (btn == 'ok' ) {
                            console.log('Will cancel job ' + menu.twindb_menu_job_id);
                            me.cancelJob(menu.twindb_menu_job_id);
                        }
                    }
                });
                break;
        }
        menu.destroy();
    },
    doContextMenuAction: function(menu, item, e, eOpts){
        var me = this;
        console.log('Context menu:');
        console.log(menu);
        console.log('Clicked on item:');
        console.log(item);
        var action = item.itemId;
        if(menu.twindb_menu_item_id.search('schedule_id_') == 0){
            if(action == 'delete'){
                Ext.Msg.show({
                    title: 'Delete schedule',
                    msg: 'Are you sure you want to delete schedule "' + menu.twindb_menu_name + '"?',
                    icon: Ext.Msg.QUESTION,
                    buttons: Ext.Msg.OKCANCEL,
                    fn: function(btn){
                        if (btn == 'ok' ) {
                            console.log('Will delete schedule ' + menu.twindb_menu_item_id);
                            me.deleteSchedule(menu.twindb_menu_item_id.replace('schedule_id_', ''));
                        }
                    }
                });
            }
        }
        if(menu.twindb_menu_item_id.search('retention_policy_id_') == 0){
            if(action == 'delete'){
                Ext.Msg.show({
                    title: 'Delete retention policy',
                    msg: 'Are you sure you want to delete retention policy "' + menu.twindb_menu_name + '"?',
                    icon: Ext.Msg.QUESTION,
                    buttons: Ext.Msg.OKCANCEL,
                    fn: function(btn){
                        if (btn == 'ok' ) {
                            console.log('Will delete retention policy ' + menu.twindb_menu_item_id);
                            me.deleteRetentionPolicy(menu.twindb_menu_item_id.replace('retention_policy_id_', ''));
                        }
                    }
                });
            }
        }
        if(menu.twindb_menu_item_id.search('attribute_id_') == 0){
            if(action == 'delete'){
                Ext.Msg.show({
                    title: 'Delete attribute',
                    msg: 'Are you sure you want to delete attribute "' + menu.twindb_menu_name + '"?',
                    icon: Ext.Msg.QUESTION,
                    buttons: Ext.Msg.OKCANCEL,
                    fn: function(btn){
                        if (btn == 'ok' ) {
                            console.log('Will delete attribute ' + menu.twindb_menu_item_id);
                            me.deleteAttribute(menu.twindb_menu_item_id.replace('attribute_id_', ''));
                        }
                    }
                });
            }
        }
        if(menu.twindb_menu_item_id.search('config_id_') == 0){
            if(action == 'delete'){
                Ext.Msg.show({
                    title: 'Delete backup configuration',
                    msg: 'Are you sure you want to delete backup configuration "' + menu.twindb_menu_name + '"?',
                    icon: Ext.Msg.QUESTION,
                    buttons: Ext.Msg.OKCANCEL,
                    fn: function(btn){
                        if (btn == 'ok' ) {
                            console.log('Will delete backup configuration ' + menu.twindb_menu_item_id);
                            me.deleteBackupConfig(menu.twindb_menu_item_id.replace('config_id_', ''));
                        }
                    }
                });
            }
        }
    },
    deleteSchedule: function(schedule_id){
        var me = this;

        var win_wait = Ext.Msg.wait('Please wait...');
        Ext.Ajax.request({
                url: 'php/deleteSchedule.php',
                params: {
                    schedule_id: schedule_id
                    },
                success: function(conn, response, options, eOpts) {
                    var result = Ext.JSON.decode(conn.responseText, true); // #1
                    if (!result){ // #2
                        result = {};
                        result.success = false;
                        result.msg = conn.responseText;
                    }
                    if (result.success) { // #3
                        var menu = Ext.ComponentQuery.query('mainmenuitem[title=Schedule]')[0];
                        var child = menu.getRootNode().findChild('id', 'schedule_id_' + schedule_id);
                        child.remove();
                        var tab = me.findScheduleTab(schedule_id);
                        var mainpanel = me.getMainPanel();
                        if(tab){
                            mainpanel.remove(tab);
                        }
                        win_wait.close();
                    } else {
                        TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
                    }
                },
                failure: function(conn, response, options, eOpts) {
                    TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
                }
            });
        
    },
    deleteRetentionPolicy: function(retention_policy_id){
        var me = this;

        var win_wait = Ext.Msg.wait('Please wait...');
        Ext.Ajax.request({
            url: 'php/deleteRetentionPolicy.php',
            params: {
                retention_policy_id: retention_policy_id
            },
            success: function(conn, response, options, eOpts) {
                //Ext.get(newschedule.getEl()).unmask();
                var result = Ext.JSON.decode(conn.responseText, true); // #1
                if (!result){ // #2
                    result = {};
                    result.success = false;
                    result.msg = conn.responseText;
                }
                if (result.success) { // #3
                    var menu = Ext.ComponentQuery.query('mainmenuitem[title=Retention policy]')[0];
                    var child = menu.getRootNode().findChild('id', 'retention_policy_id_' + retention_policy_id);
                    child.remove();
                    var tab = me.findRetentionTab(retention_policy_id);
                    var mainpanel = me.getMainPanel();
                    if(tab){
                        mainpanel.remove(tab);
                    }
                    win_wait.close();
                } else {
                    TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
                }
            },
            failure: function(conn, response, options, eOpts) {
                TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
            }
        });
    },
    deleteAttribute: function(attribute_id){
        var win_wait = Ext.Msg.wait('Please wait...');
        Ext.Ajax.request({
            url: 'php/deleteAttribute.php',
            params: {
                attribute_id: attribute_id
            },
            success: function(conn, response, options, eOpts) {
                //Ext.get(newschedule.getEl()).unmask();
                var result = Ext.JSON.decode(conn.responseText, true); // #1
                if (!result){ // #2
                    result = {};
                    result.success = false;
                    result.msg = conn.responseText;
                }
                if (result.success) { // #3
                    var menu = Ext.ComponentQuery.query('mainmenuitem[title=Server farm]')[0];
                    var child = menu.getRootNode().findChild('id', 'attribute_id_' + attribute_id);
                    child.remove();
                    var st = Ext.getStore('attributeCombo');
                        st.reload({
                            callback: function(records, operation, success) {
                                win_wait.close();
                            }
                    });
                    win_wait.close();
                } else {
                    TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
                }
            },
            failure: function(conn, response, options, eOpts) {
                TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
            }
        });
    },
    deleteBackupConfig: function(config_id){
        var me = this;

        var win_wait = Ext.Msg.wait('Please wait...');
        Ext.Ajax.request({
            url: 'php/deleteBackupConfig.php',
            params: {
                config_id: config_id
            },
            success: function(conn, response, options, eOpts) {
                //Ext.get(newschedule.getEl()).unmask();
                var result = Ext.JSON.decode(conn.responseText, true); // #1
                if (!result){ // #2
                    result = {};
                    result.success = false;
                    result.msg = conn.responseText;
                }
                if (result.success) { // #3
                    var menu = Ext.ComponentQuery.query('mainmenuitem[title=Backup configuration]')[0];
                    var child = menu.getRootNode().findChild('id', 'config_id_' + config_id);
                    child.remove();
                    var tab = me.findBackupTab(config_id);
                    var mainpanel = me.getMainPanel();
                    if(tab){
                        mainpanel.remove(tab);
                    }
                    win_wait.close();
                } else {
                    TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
                }
            },
            failure: function(conn, response, options, eOpts) {
                TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
            }
        });
    },
    updateAttributeValues: function(node, eOpts ){
        var me = this;

        console.log('Refreshing children of:');
        console.log(node);
        var panel = Ext.ComponentQuery.query('mainmenuitem[title=Server farm]')[0];
        if (panel) {
            Ext.get(panel.getEl()).mask('Loading attribute values...');
        }
        var attribute_id = node.data.id.replace('attribute_id_', '');
        Ext.Ajax.request({
            url: 'php/getListAttributesValues.php',
            params: {
                attribute_id: attribute_id,
                include_empty: true
            },
            success: function(conn, response, options, eOpts) {
                var result = Ext.JSON.decode(conn.responseText, true); // #1
                if (!result){ // #2
                    result = {};
                    result.success = false;
                    result.msg = conn.responseText;
                }
                if (result.success) { // #3
                    console.log('Request result:');
                    console.log(result);
                    Ext.each(result.data, function(i){
                        var attribute_value = i.attribute_value;
                        var attribute_id = i.attribute_id;
                        var value_id = 'value_id_' + attribute_value + '_' + attribute_id;
                        if(!node.findChild('id', value_id)){
                            node.appendChild({ // #8
                                text: attribute_value,
                                attribute_id: attribute_id,
                                leaf: true,
                                id: value_id,
                                checked: node.get('checked'),
                                className: 'servergrid' // xtype of form with schedule details
                            });
                        }
                    });
                    var v = Ext.ComponentQuery.query('mainmenuitem[title=Server farm]')[0];
                    me.application.fireEvent("serverfilterupdate");
                    if (panel) {
                        panel.unmask();
                    }
                } else {
                    TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
                }
            },
            failure: function(conn, response, options, eOpts) {
                TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
            }
        });
    },
    getServerFilter: function(){
        var me = this;

        var mainPanel = this.getMainPanel();
        var grid = mainPanel.down('servergrid');
        if(!grid) return;
        var st = grid.getStore();
        return st.server_filter;
    },
    updateServerFilter: function(){
        var me = this;

        var mainPanel = this.getMainPanel();
        var grid = mainPanel.down('servergrid');
        if(!grid) return;
        var st = grid.getStore();
        st.server_filter = [];
        var menu = Ext.ComponentQuery.query('mainmenuitem[title=Server farm]')[0];
        var root = menu.getRootNode();
        root.eachChild(function(chld){
            console.log('updateServerFilter(): child:');
            console.log(chld);
            if(chld.data.checked == true){
                var items = [];
                var include_null = false;
                chld.eachChild(function(chldVal){
                    if(chldVal.data.checked){
                        if(chldVal.data.text != '<i>(empty)</i>'){
                            items.push(chldVal.data.text);
                        } else {
                            if(chldVal.data.checked) include_null = true;
                        }
                    }
                });
                st.server_filter.push({ 
                    attribute: chld.data.text,
                    attribute_id: chld.raw.attribute_id,
                    checked: true,
                    include_null: include_null,
                    items: items
                });
            }
        });
        console.log('Updated server filter');
        console.log(st.server_filter);
        var mainpanel = me.getMainPanel();
        if(!Ext.Msg.isVisible()) Ext.get(mainpanel.getEl()).mask('Loading list of servers...');
        st.load({
            params: {
                server_filter: Ext.encode(st.server_filter)
            },
            callback: function(){
                //var mainpanel = Ext.ComponentQuery.query('mainpanel')[0];
                Ext.get(mainpanel.getEl()).unmask();
            }
        });
        return st.server_filter;
    },
    openServerGrid: function(node, checked, eOpts ){
        var me = this;
        
        console.log('Loading server grid');
        console.log(node);
        console.log('Checked state ' + checked);
        
        if(!node.isLeaf()){
            console.log('Children:');
            node.eachChild(function(chld){
                console.log(chld);
                chld.set('checked', checked);
            });
        }
        if(node.isLeaf()){
            var pnode = node.parentNode;
            node = pnode;
        } 
        var server_filter = me.getServerFilter();
        var mainpanel = me.getMainPanel();
        Ext.get(mainpanel.getEl()).mask('Loading list of servers...');
        st.load({
            params: {
                server_filter: Ext.encode(server_filter)
            },
            callback: function(){
                //var mainpanel = Ext.ComponentQuery.query('mainpanel')[0];
                Ext.get(mainpanel.getEl()).unmask();
            }
        });
    },
    updateParentStatus: function(valueNode){
        var attrNode = valueNode.parentNode;
        var checkedValuesPresent = false;
        attrNode.eachChild(function(child){
                if (child.data.checked ) checkedValuesPresent = true;
        });
        if(checkedValuesPresent){
            attrNode.set('checked', true);
        } else {
            attrNode.set('checked', false);
        }
    },
    updateChildrenStatus: function(attrNode){
        attrNode.eachChild(function(child){
                child.set('checked', attrNode.data.checked);
        });
    },
    toggleNodeStatus: function(node){
        console.log('Togling node:');
        console.log(node);
        if(node.get('checked') != null){
            console.log('Setting from ' + node.get('checked') + ' to ' + !node.get('checked'));
            node.set('checked', !node.get('checked'));
        }
    },
    resetNodeStatus: function(node){
        console.log('Rsetting all siblings of node:');
        console.log(node);
        while(node.nextSibling != null){
            node = node.nextSibling;
            if(node.get('checked') != null){
                node.set('checked', false);
                this.updateChildrenStatus(node);
            }
        }
    },
    findScheduleTab: function(schedule_id){
        var tabs = Ext.ComponentQuery.query('schedule');
        var i = 0;
        for(i = 0; i < tabs.length; i++){
            if(!tabs[i].params) return null;
            if (tabs[i].params.schedule_id == schedule_id) {
                return tabs[i];
            }
        }
        return null;
    },
    findRetentionTab: function(retention_policy_id){
        var tabs = Ext.ComponentQuery.query('retentionpolicy');
        var i = 0;
        for(i = 0; i < tabs.length; i++){
            if(!tabs[i].params) return null;
            if (tabs[i].params.retention_policy_id == retention_policy_id) {
                return tabs[i];
            }
        }
        return null;
    },
    findBackupTab: function(config_id){
        var tabs = Ext.ComponentQuery.query('backupconfig');
        var i = 0;
        for(i = 0; i < tabs.length; i++){
            if(!tabs[i].params) return null;
            if (tabs[i].params.config_id == config_id) {
                return tabs[i];
            }
        }
        return null;
    },
    scheduleBackupJob: function(server_id, hostname) {
        var win_wait = Ext.Msg.wait('Please wait...');
        Ext.Ajax.request({
            url: 'php/scheduleBackupJob.php',
            params: {
                server_id: server_id
            },
            success: function(conn, response, options, eOpts) {
                var result = Ext.JSON.decode(conn.responseText, true);
                if (!result){ 
                    result = {};
                    result.success = false;
                    result.msg = conn.responseText;
                }
                if (result.success) {
                    win_wait.close();
                    Ext.Msg.show({
                        title: 'Success',
                        msg: 'Backup job on <strong>' + hostname + '</strong>'
                                + ' is successfully scheduled',
                        buttons: Ext.Msg.OK,
                        icon: Ext.Msg.INFO
                    });
                } else {
                    TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
                }
            },
            failure: function(conn, response, options, eOpts) {
                TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
            }
        });
    },
    restartJob: function(job_id) {
        var win_wait = Ext.Msg.wait('Please wait...');
        Ext.Ajax.request({
            url: 'php/restartJob.php',
            params: {
                job_id: job_id
            },
            success: function(conn, response, options, eOpts) {
                var result = Ext.JSON.decode(conn.responseText, true);
                if (!result){ 
                    result = {};
                    result.success = false;
                    result.msg = conn.responseText;
                }
                if (result.success) {
                    var store = Ext.getStore('JobGrid');
                    store.reload({
                        callback: function(records, operation, success) {
                            win_wait.close();
                        }
                    });
                } else {
                    TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
                }
            },
            failure: function(conn, response, options, eOpts) {
                TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
            }
        });
    },
    cancelJob: function(job_id) {
        var win_wait = Ext.Msg.wait('Please wait...');
        Ext.Ajax.request({
            url: 'php/cancelJob.php',
            params: {
                job_id: job_id
            },
            success: function(conn, response, options, eOpts) {
                var result = Ext.JSON.decode(conn.responseText, true);
                if (!result){ 
                    result = {};
                    result.success = false;
                    result.msg = conn.responseText;
                }
                if (result.success) {
                    var store = Ext.getStore('JobGrid');
                    store.reload({
                        callback: function(records, operation, success) {
                            win_wait.close();
                        }
                    });
                } else {
                    TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
                }
            },
            failure: function(conn, response, options, eOpts) {
                TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
            }
        });
    }
});
