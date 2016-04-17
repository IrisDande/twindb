Ext.define('TwinDB.controller.Main', {
    extend: 'Ext.app.Controller',
    views:[
        'Viewport',
        'Schedule',
        'Login',
        'RetentionPolicy',
        'Storage',
        'UpgradeStorageStep1',
        'UpgradeStorageStep2',
        'Welcome',
        'Dashboard',
        'JobGrid',
        'AlertsGrid',
        'BackupKPI',
        'JobGridServer',
        'charts.VolumeUsageDate',
        'charts.MaxBackupTime_Time',
        'charts.MaxRestoreTime_Time',
        'charts.BackupTime_Hist',
        'MaxDataLossGrid',
        'RegCode',
        'Server',
        'ServerGeneral',
        'ServerEncrypionKeys',
        'ServerBackupCopies',
        'WelcomeUnregistered',
        'InstructionsRedhat',
        'InstructionsDebian'
    ],
    stores: [
        'timezoneCombo',
        'userListCombo',
        'attributeCombo',
        'attributeValueCombo',
        'scheduleCombo',
        'Schedule',
        'ScheduleList',
        'RetentionPolicy',
        'BackupConfig',
        'Storage',
        'GeneralProfile',
        'SecurityProfile',
        'JobGrid',
        'AlertsGrid',
        'JobGridServer',
        'charts.VolumeUsageDate',
        'charts.MaxBackupTime_Time',
        'charts.MaxRestoreTime_Time',
        'charts.BackupTime_Hist',
        'MaxDataLossGrid',
        'RegCode',
        'ServerGeneral',
        'ServerEncrypionKeys',
        'ServerBackupCopies'
    ],
    requires:[
        'Ext.chart.axis.Category',
        'Ext.data.Store',
        'Ext.util.Cookies',
        'TwinDB.util.Util',
        'TwinDB.view.Signup'
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

    autoUpdateStoreUntilViewVisible: function (store, view, interval) {
        var me = this;
        if (!interval) {
            interval = 1000;
        }
        if (view.isVisible()) {

            var newStore = Ext.create('Ext.data.Store', {
                model: store.model,
                autoLoad: false,
                proxy: store.getProxy()
            });

            newStore.load({
                params: store.lastOptions,
                callback: function (records, operation, success) {

                    if (success) {

                        Ext.each(records, function (record) {

                            var index = store.indexOf(record);

                            if (index != -1) {
                                // compare two records

                                var old_record = store.getAt(index);

                                var model = store.model;

                                Ext.each(model.getFields(), function (field) {


                                    if (JSON.stringify(record.get(field.name))
                                        !== JSON.stringify(old_record.get(field.name))) {

                                        console.log('two fields  are different');
                                        console.log(record.get(field.name) + ' != ' + old_record.get(field.name));

                                        console.log('index = ' + index);
                                        console.log('field = ' + field.name);
                                        console.log('value = ' + old_record.get(field.name));

                                        store.getAt(index).set(field.name, record.get(field.name));

                                        store.commitChanges();

                                    }
                                });

                            } else {
                                console.log('new record');
                                console.log(record);
                                store.insert(0, record);
                            }
                        });

                        Ext.each(store.getRange(), function (old_record) {

                            if (newStore.indexOf(old_record) == -1) {
                                console.log('record has gone');
                                console.log(old_record);
                                store.remove(old_record);
                            }
                        });

                    }

                    setTimeout(function () {
                        me.autoUpdateStoreUntilViewVisible(store, view);
                    }, interval);
                }
            });
        }
    },

    init: function() {
        var me = this;
        this.control({
            "appheader": {
                afterrender: function() {
                    TwinDB.util.Util.updateAuthControls();
                }
            },
            "welcome": {
                afterrender: function (panel) {
                    var registeredUser = TwinDB.app.isRegisteredUser();
                    var authenticated = Ext.util.Cookies.get('authenticated') == 1;
                    console.log("User is registered: " + registeredUser);
                    console.log("User is authenticated: " + authenticated);
                    TwinDB.util.Util.showWelcomeInstructions(panel);
                    if (authenticated) {
                        panel.down('#signup-container').hide();
                        panel.down('#step1-label').hide();
                    }
                }
            },
            "welcome-unregistered button#signup": {
                click: function () {
                    var login_window = Ext.ComponentQuery.query('signup')[0];
                    if (login_window) {
                        login_window.show();
                    } else {
                        Ext.create('TwinDB.view.Signup');
                    }
                }
            },
            "welcome-unregistered button#instructions-redhat": {
                click: function () {
                    var instruction_window = Ext.ComponentQuery.query('instructions-redhat')[0];
                    if (instruction_window) {
                        instruction_window.show();
                    } else {
                        Ext.create('TwinDB.view.InstructionsRedhat');
                    }
                }
            },
            "welcome-unregistered button#instructions-debian": {
                click: function () {
                    var instruction_window = Ext.ComponentQuery.query('instructions-debian')[0];
                    if (instruction_window) {
                        instruction_window.show();
                    } else {
                        Ext.create('TwinDB.view.InstructionsDebian');
                    }
                }
            },
            "dashboard": {
                activate: function () {
                    var mainpanel = me.getMainPanel();
                    Ext.get(mainpanel.getEl()).unmask();
                }
            },
            "generalprofile": {
                activate: this.loadGeneralProfile
            },
            "generalprofile button#save": {
                click: function (btn, e, eOpts) {
                    console.log('Pressed save button');
                    var formPanel = btn.up('form');
                    if (formPanel.getForm().isValid()) {
                        Ext.Msg.wait('Saving...');
                        formPanel.getForm().submit({// #5
                            clientValidation: true,
                            url: 'php/setGeneralProfile.php', // #6
                            success: function (form, action) {
                                Ext.Msg.hide();
                            },
                            failure: function (form, action) {
                                TwinDB.util.Util.showFormException(action);
                            }
                        });
                    }
                }
            },
            "generalprofile button#cancel": {
                click: function (btn, e, eOpts) {
                    console.log('Pressed cancel button');
                    var tab = btn.up('generalprofile');
                    var mainpanel = btn.up('generalprofile').up('panel');
                    mainpanel.remove(tab);
                }
            },
            "securityprofile": {
                activate: this.loadSecurityProfile
            },
            "securityprofile button#save": {
                click: function (btn, e, eOpts) {
                    console.log('Pressed save button');
                    var formPanel = btn.up('form');
                    var mask = Ext.Msg.wait('Saving...');
                    var gpg_pub_key = formPanel.down('#gpg_pub_key').getValue();
                    Ext.Ajax.request({
                        url: 'php/setSecurityProfile.php',
                        params: {
                            gpg_pub_key: gpg_pub_key
                        },
                        success: function (conn, response, options, eOpts) {
                            mask.hide();
                            var result = Ext.JSON.decode(conn.responseText, true); // #1
                            if (!result) { // #2
                                result = {};
                                result.success = false;
                                result.msg = conn.responseText;
                            }
                            if (!result.success) { // #3
                                TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
                            }
                        },
                        failure: function (conn, response, options, eOpts) {
                            TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
                        }
                    });
                }
            },
            "securityprofile button#changepass": {
                click: function (btn, e, eOpts) {
                    console.log('Pressed ' + btn.id + ' button');
                    var formPanel = btn.up('form');
                    var mask = Ext.Msg.wait('Saving...');
                    var newpassword = formPanel.down('#password1').getValue();
                    Ext.Ajax.request({
                        url: 'php/changePassword.php',
                        params: {
                            newpassword: newpassword
                        },
                        success: function (conn, response, options, eOpts) {
                            mask.hide();
                            var result = Ext.JSON.decode(conn.responseText, true); // #1
                            if (!result) { // #2
                                result = {};
                                result.success = false;
                                result.msg = conn.responseText;
                            }
                            if (!result.success) { // #3
                                TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
                            }
                        },
                        failure: function (conn, response, options, eOpts) {
                            TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
                        }
                    });
                }
            },
            "securityprofile button#cancel": {
                click: function (btn, e, eOpts) {
                    console.log('Pressed cancel button');
                    var tab = btn.up('securityprofile');
                    var mainpanel = btn.up('securityprofile').up('panel');
                    mainpanel.remove(tab);
                }
            },
            "ordergrid": {
                activate: function (grid, eOpts) {
                    var st = grid.getStore();
                    st.load({
                        callback: function () {
                            var mainpanel = Ext.ComponentQuery.query('mainpanel')[0];
                            Ext.get(mainpanel.getEl()).unmask();
                        }
                    });
                }
            },
            "servergrid": {
                activate: function (grid, eOpts) {
                    var st = grid.getStore();
                    /*
                     st.load({
                     params:{
                     server_filter: Ext.encode(st.server_filter)
                     },
                     callback: function(){
                     var mainpanel = Ext.ComponentQuery.query('mainpanel')[0];
                     Ext.get(mainpanel.getEl()).unmask();
                     }
                     });
                     */
                },
                itemdblclick: this.openServerTab
            },
            "servergrid #attributeComboAction": {
                change: function (cmb, newValue, oldValue, eOpts) {
                    console.log('change event on:');
                    console.log(cmb);
                    var cmbValue = Ext.ComponentQuery.query('servergrid #attributeValueCombo')[0];
                    var cmbAttribute = Ext.ComponentQuery.query('servergrid #attributeCombo')[0];
                    var cmbConfig = Ext.ComponentQuery.query('servergrid #backupConfigCombo')[0];
                    switch (newValue) {
                        case 'remove attribute':
                            cmbValue.hide();
                            cmbAttribute.show();
                            cmbConfig.hide();
                            break;
                        case 'set attribute':
                            cmbValue.show();
                            cmbAttribute.show();
                            cmbConfig.hide();
                            break;
                        case 'set backup configuration':
                            cmbValue.hide();
                            cmbAttribute.hide();
                            cmbConfig.show();
                            break;
                        default:
                            cmbValue.hide();
                            cmbAttribute.hide();
                            cmbConfig.hide();
                    }
                }
            },
            "servergrid #attributeCombo": {
                change: function (cmb, newValue, oldValue, eOpts) {
                    console.log('change event on:');
                    console.log(cmb);
                    var st = Ext.getStore('attributeValueCombo');
                    st.load({
                        params: {
                            attribute_id: newValue,
                            include_empty: false
                        }
                    });
                }
            },
            "servergrid #attributeValueCombo": {
                specialkey: function (field, e, eOpts) {
                    console.log('specialkey event, pressed ' + e.getKey());
                    if (e.getKey() == e.ENTER) {
                        var submitBtn = field.up('servergrid').down('button#go');
                        submitBtn.fireEvent('click', submitBtn, e, eOpts);
                    }
                }
            },
            "servergrid #go": {
                click: function (btn, e, eOpts) {

                    console.log('click event on:');
                    console.log(btn);

                    var panel = btn.up('servergrid');
                    var attribute = panel.down('#attributeCombo');
                    var action = panel.down('#attributeComboAction');
                    var attribute_value = panel.down('#attributeValueCombo');
                    var backupConfig = panel.down('#backupConfigCombo');
                    var st = panel.getStore();
                    var records = st.getRange(0, st.count());
                    var model = panel.getStore().model;
                    var fieldNames = [];
                    var rawData = [];
                    var server_count = 0;
                    var selModel = panel.getSelectionModel();

                    st.each(function (rec) {
                        //console.log(rec.getId(), selModel.isSelected(rec));
                        rec.set('selected', selModel.isSelected(rec));
                        server_count += selModel.isSelected(rec) ? 1 : 0;
                    });
                    var servers = ( server_count == 1) ? 'server' : 'servers';

                    Ext.each(model.getFields(), function (field) {
                        fieldNames.push(field.name);
                    });
                    Ext.each(records, function (rec) {
                        var row = [];
                        Ext.each(fieldNames, function (fld) {
                            row.push(rec.get(fld));
                        });
                        rawData.push(row);
                    });
                    console.log('Action:');
                    console.log(action);
                    switch (action.getValue()) {
                        case 'set attribute':
                            if (!attribute_value.getValue()) {
                                console.log('attribute_value = ');
                                console.log(attribute_value);
                                TwinDB.util.Util.showErrorMsg('Please select or type attribute value');
                            }
                            var msg = '<p>You are about to '
                                + action.getValue() + ' <strong>'
                                + attribute.getRawValue() + '</strong> '
                                + ' to <strong>' + attribute_value.getValue() + '</strong>'
                                + ' for ' + server_count + ' ' + servers
                                + '<p>Do you want to proceed?';
                            Ext.Msg.confirm({
                                title: 'Please confirm action',
                                msg: msg,
                                buttons: Ext.Msg.YESNO,
                                icon: Ext.Msg.QUESTION,
                                fn: function (buttonId) {
                                    if (buttonId == 'yes') {
                                        var params = {};
                                        params.attribute = attribute;
                                        params.attribute_value = attribute_value;
                                        me.doServerGridAction(action, params, rawData);
                                    }
                                }
                            });
                            break;
                        case 'remove attribute':
                            if (!attribute.getValue()) {
                                TwinDB.util.Util.showErrorMsg('Please select attribute');
                            }
                            var msg = '<p>You are about to '
                                + action.getValue() + ' <strong>'
                                + attribute.getValue() + '</strong> '
                                + ' from ' + server_count + ' ' + servers
                                + '<p>Do you want to proceed?';
                            Ext.Msg.confirm({
                                title: 'Please confirm action',
                                msg: msg,
                                buttons: Ext.Msg.YESNO,
                                icon: Ext.Msg.QUESTION,
                                fn: function (buttonId) {
                                    if (buttonId == 'yes') {
                                        var params = {};
                                        params.attribute = attribute;
                                        me.doServerGridAction(action, params, rawData);
                                    }
                                }
                            });
                            break;
                        case 'set backup configuration':
                            if (!backupConfig.getValue()) {
                                TwinDB.util.Util.showErrorMsg('Please select backup configuration');
                            }
                            var msg = '<p>You are about to ' + action.getValue();
                            msg += ' to <strong>' + backupConfig.getRawValue() + '</strong>';
                            msg += ' for ' + server_count + ' ' + servers + '<p>Do you want to proceed?';
                            Ext.Msg.confirm({
                                title: 'Please confirm action',
                                msg: msg,
                                buttons: Ext.Msg.YESNO,
                                icon: Ext.Msg.QUESTION,
                                fn: function (buttonId) {
                                    if (buttonId == 'yes') {
                                        var params = {};
                                        params.backupConfig = backupConfig;
                                        me.doServerGridAction(action, params, rawData);
                                    }
                                }
                            });
                            break;
                        default:
                            TwinDB.util.Util.showErrorMsg('Unknown action ' + action.getValue());
                    }
                }
            },
            "schedule": {
                activate: this.loadSchedule
            },
            "retentionpolicy": {
                activate: this.loadRetentionPolicy
            },
            "storage": {
                activate: this.loadStorage
            },
            "schedule radiogroup#run_once_day": {
                change: this.toggleRunOnce
            },
            "schedule button#save": {
                click: this.saveSchedule
            },
            "schedule button#cancel": {
                click: this.closeSchedule
            },
            "retentionpolicy button#save": {
                click: this.saveRetentionPolicy
            },
            "retentionpolicy button#cancel": {
                click: this.closeRetentionPolicy
            },
            "backupconfig": {
                activate: this.loadBackupConfig
            },
            "backupconfig button#cancel": {
                click: function (btn, e, eOpts) {
                    console.log('Pressed cancel button');
                    var tab = btn.up('backupconfig');
                    var mainpanel = btn.up('backupconfig').up('panel');
                    mainpanel.remove(tab);
                }
            },
            "backupconfig button#save": {
                click: this.saveBackupConfig
            },
            "storage button#save": {
                click: this.saveStorage
            },
            "storage button#cancel": {
                click: this.closeStorage
            },
            "storage button#addmore": {
                click: this.openUpgradeStorageStep1
            },
            "upgradestoragestep1 button#cancel": {
                click: function (btn, e, eOpts) {
                    btn.up('upgradestoragestep1').close();
                }
            },
            "upgradestoragestep1 button#next": {
                click: function (btn, e, eOpts) {
                    btn.up('upgradestoragestep1').hide();
                    var step = Ext.ComponentQuery.query('upgradestoragestep2')[0];
                    if (step) {
                        var f = Ext.ComponentQuery.query('upgradestoragestep2')[0].down('#package_id');
                        var val = btn.up('form').down('radiogroup').getValue().storage_package;
                        f.setValue(val);
                        step.show();
                    } else {
                        Ext.create('widget.upgradestoragestep2');
                        var f = Ext.ComponentQuery.query('upgradestoragestep2')[0].down('#package_id');
                        var val = btn.up('form').down('radiogroup').getValue().storage_package;
                        f.setValue(val);
                    }
                }
            },
            "upgradestoragestep2 button#cancel": {
                click: function (btn, e, eOpts) {
                    btn.up('upgradestoragestep2').close();
                }
            },
            "upgradestoragestep2 button#prev": {
                click: function (btn, e, eOpts) {
                    btn.up('upgradestoragestep2').hide();
                    var step = Ext.ComponentQuery.query('upgradestoragestep1')[0];
                    if (step) {
                        step.show();
                    }
                }
            },
            "upgradestoragestep2 button#next": {
                click: function (btn, e, eOpts) {
                    btn.up('upgradestoragestep2').hide();
                    var space = btn.up('form').down('#package_id').getValue();
                    Ext.Msg.confirm({
                        title: 'Please confirm purchase',
                        msg: '<p>You are about to order ' + space + ' GB of disk space' +
                        '<p>We will charge your card $' + space / 5 + ' each month' +
                        '<p>You can cancel subscription any time' +
                        '<p>Do you want to proceed?',
                        buttons: Ext.Msg.YESNO,
                        icon: Ext.Msg.QUESTION,
                        fn: function (buttonId) {
                            if (buttonId == 'yes') {
                                Ext.Msg.show({
                                    title: 'Thank you!',
                                    msg: 'Your order is placed. The disk space is available immediately',
                                    buttons: Ext.Msg.OK,
                                    icon: Ext.Msg.INFO
                                });
                            }
                        }
                    });
                    //Ext.create('widget.upgradestoragestep3');
                }
            },
            "regcode button#ok": {
                click: function (btn, e, eOpts) {
                    btn.up('regcode').close();
                }
            },
            "appheader combo#viewas": {
                change: function (cmb, newValue, oldValue, eOpts) {
                    console.log('Switching to user ' + newValue);
                    this.switchUser(newValue);
                }
            },
            "server server-backupcopies": {
                afterrender: function (grid, eOpts) {
                    Ext.Function.defer(function () {
                        var store = Ext.getStore('ServerBackupCopies');
                        if (store.isLoading()) {
                            Ext.get(grid.getEl()).mask('Loading...');
                        }
                    }, 100);
                }
            },
            "server jobgrid-server": {
                afterrender: function (grid, eOpts) {
                    Ext.Function.defer(function () {
                        var store = Ext.getStore('JobGridServer');
                        if (store.isLoading()) {
                            Ext.get(grid.getEl()).mask('Loading...');
                        }
                    }, 100);
                }
            },
            "server-general": {
                activate: this.loadServerGeneral,
                afterrender: function (cont, eOpts) {
                    console.log('render triggered on');
                    console.log(cont);
                    var repl_container = cont.down('#replicationTopology');
                    var tab = cont.up('server');
                    var server_id = tab.server_id;
                    console.log('Replication topology container');
                    console.log(repl_container);
                    var repl_div = repl_container.add({
                        xtype: 'container',
                        html: '<div id="replication-topology-' + server_id + '"></div>'
                    });
                    repl_div.addListener('render', function () {
                        var req = Ext.Ajax.request({
                            url: 'php/getClusterNodes.php',
                            params: {
                                server_id: server_id
                            },
                            success: function (conn, response, options, eOpts) {
                                var result = Ext.JSON.decode(conn.responseText, true); // #1
                                if (!result) { // #2
                                    result = {};
                                    result.success = false;
                                    result.msg = conn.responseText;
                                }
                                var nodes = [];
                                var edges = [];
                                if (result.success) {
                                    Ext.each(result.data.nodes, function (node) {
                                        console.log('node:');
                                        console.log(node);
                                        n = {id: node.server_id, label: node.name};
                                        if (node.server_id == server_id) {
                                            n.color = {
                                                background: 'lightgreen',
                                                border: 'black',
                                                highlight: {
                                                    background: 'cyan',
                                                    border: 'black'
                                                }
                                            };
                                        } else {
                                            n.color = {
                                                background: 'lightgrey',
                                                border: 'black',
                                                highlight: {
                                                    background: 'cyan',
                                                    border: 'black'
                                                }
                                            };
                                        }
                                        nodes.push(n);
                                    });
                                    Ext.each(result.data.edges, function (edge) {
                                        console.log('edge:');
                                        console.log(edge);
                                        edges.push({
                                            from: edge.from,
                                            to: edge.to
                                        });
                                    });
                                    var container = Ext.getElementById('replication-topology-' + server_id);
                                    console.log('VIS: replication topology container');
                                    console.log(container);
                                    console.log('VIS: nodes:');
                                    console.log(nodes);
                                    console.log('VIS: edges');
                                    console.log(edges);
                                    var data = {
                                        nodes: nodes,
                                        edges: edges
                                    };
                                    var options = {
                                        width: '400px',
                                        height: '400px',
                                        nodes: {
                                            shape: 'database',
                                            fontSize: '8'
                                        },
                                        edges: {
                                            style: 'arrow'
                                        }
                                    };
                                    var network = new vis.Network(container, data, options);
                                    Ext.get(repl_container.getEl()).unmask();
                                } else {
                                    TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
                                }
                            },
                            failure: function (conn, response, options, eOpts) {
                                TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
                            }
                        });
                        Ext.Function.defer(function () {
                            if (Ext.Ajax.isLoading(req)) {
                                Ext.get(repl_container.getEl()).mask('Loading replication topology...');
                            }
                        }, 100);
                    });
                }
            },
            "server-general button#save": {
                click: function (btn, e, eOpts) {
                    console.log('Pressed save button');
                    var formPanel = btn.up('form');
                    if (formPanel.getForm().isValid()) {
                        Ext.Msg.wait('Saving...');
                        formPanel.getForm().submit({// #5
                            clientValidation: true,
                            url: 'php/setServerGeneral.php', // #6
                            success: function (form, action) {
                                Ext.Msg.hide();
                            },
                            failure: function (form, action) {
                                TwinDB.util.Util.showFormException(action);
                            }
                        });
                    }
                }
            },
            "server-encryption button#save": {
                click: function (btn, e, eOpts) {
                    console.log('Pressed save button');
                    var formPanel = btn.up('form');
                    if (formPanel.getForm().isValid()) {
                        Ext.Msg.wait('Saving...');
                        formPanel.getForm().submit({// #5
                            clientValidation: true,
                            url: 'php/setServerEncrypionKeys.php', // #6
                            success: function (form, action) {
                                Ext.Msg.hide();
                            },
                            failure: function (form, action) {
                                TwinDB.util.Util.showFormException(action);
                            }
                        });
                    }
                }
            },
            "server-encryption": {
                activate: function (me, eOpts) {
                    var store = Ext.getStore('ServerEncrypionKeys');
                    var tab = me.up('server');
                    var server_id = tab.server_id;
                    Ext.Function.defer(function () {
                        if (store.isLoading()) {
                            Ext.get(me.getEl()).mask('Loading...');
                        }
                    }, 100);
                    store.load({
                        params: {
                            server_id: server_id
                        },
                        callback: function (records, operation, success) {
                            var form = me.down('form');
                            if (records) {
                                var record = records[0];
                                form.loadRecord(record);
                            }
                            var mainpanel = Ext.ComponentQuery.query('mainpanel')[0];
                            Ext.get(me.getEl()).unmask();
                        }
                    });
                }
            },
            "jobgrid": {
                afterrender: function (panel, eOpts) {
                    console.log('Loading JobGrid store');
                    var store = Ext.getStore('JobGrid');
                    Ext.get(panel.getEl()).mask('Loading...');
                    store.load({
                        callback: function (records, operation, success) {
                            Ext.get(panel.getEl()).unmask();
                        }
                    });
                    this.autoUpdateStoreUntilViewVisible(store, panel);
                }
            },
            "alerts-grid": {
                afterrender: function (me, eOpts) {
                    console.log('Loading AlertsGrid store');
                    var store = Ext.getStore('AlertsGrid');
                    store.load({
                        callback: function (records, operation, success) {
                            var mainpanel = Ext.ComponentQuery.query('mainpanel')[0];
                            Ext.get(mainpanel.getEl()).unmask();
                        }
                    });
                }
            },
            "backup-kpi": {
                afterrender: function (me, eOpts) {
                    var mainpanel = Ext.ComponentQuery.query('mainpanel')[0];
                    Ext.get(mainpanel.getEl()).unmask();
                }
            },
            "restore-server-step1 #next": {
                click: function (btn, e, eOpts) {
                    var win = btn.up('restore-server-step1');
                    win.hide();
                    bc_view = win.down('server-backupcopies');
                    var backup_copy_id = bc_view.getSelectionModel().getSelection()[0].data.backup_copy_id;
                    console.log('Selected backup copy id ' + backup_copy_id);
                    var win_step2;
                    if (win.next) {
                        win_step2 = win.next;
                        win.next.show();
                    } else {
                        win_step2 = Ext.create('TwinDB.view.RestoreServerStep2', {
                            previous: win
                        });
                        win.next = win_step2;
                    }
                    var field = win_step2.down('#backup_copy_id');
                    field.setValue(backup_copy_id);
                    field = win_step2.down('#server_id');
                    field.setValue(win.server_id);
                }
            },
            "restore-server-step1 server-backupcopies": {
                itemclick: function (view, record, item, index, e, eOpts) {
                    var parent_view = view.up('restore-server-step1');
                    var btn = parent_view.down('#next');
                    btn.enable();
                }
            },
            "restore-server-step2 textfield": {
                specialkey: function (field, e, eOpts) {
                    console.log('specialkey event, pressed ' + e.getKey());
                    if (e.getKey() == e.ENTER) {
                        var submitBtn = field.up('restore-server-step2').down('button#go');
                        submitBtn.fireEvent('click', submitBtn, e, eOpts);
                    }
                }
            },
            "restore-server-step2 #go": {
                click: function(btn, e, eOpts) {
                    var win = btn.up('restore-server-step2');
                    var form = win.down('form').getForm();
                    if (form.isValid()) {
                        Ext.Msg.wait('Scheduling...');
                        form.submit({
                            clientValidation: true,
                            url: 'php/scheduleRestoreJob.php',
                            success : function(form,action){
                                win.close();
                                Ext.Msg.show({
                                    title: 'Success',
                                    msg: 'Restore job is successfully scheduled',
                                    buttons: Ext.Msg.OK,
                                    icon: Ext.Msg.INFO
                                });
                            },
                            failure : function(form,action){
                                TwinDB.util.Util.showFormException(action);
                            }
                        });
                    }
                }
            }
        });
        var uri = location.search;
        var authenticated = Ext.util.Cookies.get('authenticated') == 1;
        if ( uri == '?get_code' ) {
            if ( authenticated ) {
                var regcode_win = Ext.ComponentQuery.query('regcode')[0];
                    var store = Ext.getStore('RegCode');
                    console.log('Reg code store:');
                    console.log(store);
                    if(regcode_win){
                        regcode_win.show();
                    } else {
                        regcode_win = Ext.create('TwinDB.view.RegCode');
                    }
                    store.load(
                        {
                            callback: function(){
                                var rec = store.getAt(0);
                                console.log('Reg code records:');
                                console.log(rec);
                                if (rec) {
                                    var f = regcode_win.down('#reg_code');
                                    if (f) { f.setValue('twindb-agent --register ' + rec.get('reg_code')); }
                                }
                            }
                        }
                    );
            } else {
                Ext.create('TwinDB.view.Login');
            }
        }
    },
    loadGeneralProfile: function(me, eOpts){
        console.log('generalprofile render event ' + me.id);
        var store = Ext.getStore('GeneralProfile');
        store.load({
            callback: function(records, operation, success) {
                var form = Ext.ComponentQuery.query('generalprofile#' + me.id + ' form')[0];
                if(records){
                    var record = records[0];
                    form.loadRecord(record);
                }
                var mainpanel = Ext.ComponentQuery.query('mainpanel')[0];
                Ext.get(mainpanel.getEl()).unmask();
            }
        });
    },
    loadSecurityProfile: function(me, eOpts){
        console.log('securityprofile render event ' + me.id);
        var store = Ext.getStore('SecurityProfile')
        store.load({
            callback: function(records, operation, success) {
                var form = Ext.ComponentQuery.query('securityprofile#' + me.id + ' form')[1];
                var record = records[0];
                form.loadRecord(record);
                var mainpanel = Ext.ComponentQuery.query('mainpanel')[0];
                Ext.get(mainpanel.getEl()).unmask();
            }
        });
    },
    loadSchedule: function(view, eOpts){
        var me = this;
        console.log('event activate on ');
        console.log(view);
        var schedule_id = view.params.schedule_id;
        var store = Ext.getStore('Schedule')
        store.load({
            params: {
                schedule_id: schedule_id
            },
            callback: function(records, operation, success) {
                var form = Ext.ComponentQuery.query('schedule#' + view.id + ' form')[0];
                var record = records[0];
                var mainpanel = me.getMainPanel();
                if(record){
                    form.loadRecord(record);
                } else {
                    mainpanel.remove(view);
                    TwinDB.util.Util.showErrorMsg('Could not load schedule ' + schedule_id);
                }
                Ext.get(mainpanel.getEl()).unmask();
            }
        });
    },
    loadRetentionPolicy: function(view, eOpts){
        var me = this;

        console.log('retentionpolicy render event ' + view.id);
        var retention_policy_id = view.params.retention_policy_id;
        var store = Ext.getStore('RetentionPolicy')
        store.load({
            params: {
                retention_policy_id: retention_policy_id
            },
            callback: function(records, operation, success) {
                var form = Ext.ComponentQuery.query('retentionpolicy#' + view.id + ' form')[0];
                var record = records[0];
                var mainpanel = me.getMainPanel();
                if (record) {
                    form.loadRecord(record);
                } else {
                    mainpanel.remove(view);
                    TwinDB.util.Util.showErrorMsg('Could not load retention policy ' + retention_policy_id);
                }
                Ext.get(mainpanel.getEl()).unmask();
            }
        });
    },
    loadBackupConfig: function(view, eOpts){
        var me = this;
        var mainpanel = me.getMainPanel();
        var config_id = view.params.config_id;
        var store = Ext.getStore('BackupConfig');

        console.log('event activate on ');
        console.log(view);
        store.load({
            params: {
                config_id: config_id
            },
            callback: function(records, operation, success) {
                var form = Ext.ComponentQuery.query('backupconfig#' + view.id + ' form')[0];
                var record = records[0];
                if(record){
                    form.loadRecord(record);
                } else {
                    mainpanel.remove(view);
                    Ext.get(mainpanel.getEl()).unmask();
                    TwinDB.util.Util.showErrorMsg('Could not load backup configuration ' + config_id);
                }
                Ext.get(mainpanel.getEl()).unmask();
            }
        });
    },
    loadStorage: function(view, eOpts){
        var me = this;

        console.log('storage render event ' + view.id);
        var volume_id = view.params.storage_id;
        var store = Ext.getStore('Storage');
        store.load({
            params: {
                volume_id: volume_id
            },
            callback: function(records, operation, success) {
                var form = Ext.ComponentQuery.query('storage#' + view.id + ' form')[0];
                var record = records[0];
                var mainpanel = me.getMainPanel();
                if (record) {
                    form.loadRecord(record);
                    var f = form.down('progressbar');
                    f.updateProgress(record.get('used'));
                    var used_size = null;
                    if (record.get('used') > 0.01) {
                        used_size = (record.get('used')*100.0).toFixed(0);
                    } else {
                        used_size = (record.get('used')*100.0).toFixed(2);
                    }
                    f.updateText(used_size + '%');
                } else {
                    mainpanel.remove(view);
                    TwinDB.util.Util.showErrorMsg('Could not load storage ' + volume_id);
                }
                Ext.get(mainpanel.getEl()).unmask();
            }
        });
    },
    loadServerGeneral: function(me, eOpts){
        console.log('server-general render event ' + me.id);
        var store = Ext.getStore('ServerGeneral');
        var tab = me.up('server');
        var server_id = tab.server_id;
        
        Ext.Function.defer(function() {
            if (store.isLoading()) {
                Ext.get(me.getEl()).mask('Loading...');
            }
        }, 100);
        store.load({
            params: {
                server_id: server_id
            },
            callback: function(records, operation, success) {
                var form = me.down('form');
                if(records){
                    var record = records[0];
                    form.loadRecord(record);
                }
                var mainpanel = Ext.ComponentQuery.query('mainpanel')[0];
                Ext.get(me.getEl()).unmask();
            }
        });
    },
    toggleRunOnce: function(view, newValue, oldValue){
        console.log('Toggle value in #' + view.id);
        console.log('newValue = ' + newValue);
        console.log('oldValue = ' + oldValue);
        if(newValue.run_once_day == 'Y'){
            var f = view.up('form').down('#period');
            f.disable();
            f.setValue('');
            f = view.up('form').down('#ntimes');
            f.setValue('');
            f.disable();
        }
        else{
            var f = view.up('form').down('#period');
            f.setValue(1);
            f.enable();
            f = view.up('form').down('#ntimes');
            f.setValue(24);
            f.enable();
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

    findStorageTab: function(volume_id){
        var tabs = Ext.ComponentQuery.query('storage');
        var i = 0;
        for(i = 0; i < tabs.length; i++){
            if(!tabs[i].params) return null;
            if (tabs[i].params.storage_id == volume_id) {
                return tabs[i];
            }
        }
        return null;
    },

    saveSchedule: function(btn, e, eOpts){
        var me = this;

        console.log('Pressed save button');
        var formPanel = btn.up('form');
        if(formPanel.getForm().isValid()){
            Ext.Msg.wait('Saving...');
            formPanel.getForm().submit({// #5
                clientValidation: true,
                url: 'php/setSchedule.php', // #6
                success : function(form,action){
                    var menu = Ext.ComponentQuery.query('mainmenuitem[title=Schedule]')[0];
                    var id = formPanel.getForm().getValues().schedule_id;
                    var newname = formPanel.getForm().getValues().name;
                    var node = menu.getRootNode().findChild('id', 'schedule_id_' + id);
                    node.set('text', newname);
                    node.save();
                    var tab = me.findScheduleTab(id);
                    if (tab) tab.tab.setText(newname);
                    //node.text = 'aaa';
                    //console.log(node);
                    Ext.Msg.hide();
                },
                failure : function(form,action){
                    TwinDB.util.Util.showFormException(action);
                }
            });
        }
    },
    saveRetentionPolicy: function(btn, e, eOpts){
        var me = this;

        console.log('Pressed save button');
        var formPanel = btn.up('form');
        if(formPanel.getForm().isValid()){
            Ext.Msg.wait('Saving...');
            formPanel.getForm().submit({// #5
                clientValidation: true,
                url: 'php/setRetentionPolicy.php', // #6
                success : function(form,action){
                    var menu = Ext.ComponentQuery.query('mainmenuitem[title=Retention policy]')[0];
                    var id = formPanel.getForm().getValues().retention_policy_id;
                    var newname = formPanel.getForm().getValues().name;
                    var node = menu.getRootNode().findChild('id', 'retention_policy_id_' + id);
                    node.set('text', newname);
                    node.save();
                    var tab = me.findRetentionTab(id);
                    tab.tab.setText(newname);
                    Ext.Msg.hide();
                },
                failure : function(form,action){
                    TwinDB.util.Util.showFormException(action);
                }
            });
        }
    },
    saveBackupConfig: function(btn, e, eOpts){
        var me = this;

        console.log('Pressed save button');
        var formPanel = btn.up('form');
        if(formPanel.getForm().isValid()){
            Ext.Msg.wait('Saving...');
            formPanel.getForm().submit({// #5
                clientValidation: true,
                url: 'php/setBackupConfig.php', // #6
                success : function(form,action){
                    var menu = Ext.ComponentQuery.query('mainmenuitem[title=Backup configuration]')[0];
                    var id = formPanel.getForm().getValues().config_id;
                    var newname = formPanel.getForm().getValues().name;
                    var node = menu.getRootNode().findChild('id', 'config_id_' + id);
                    node.set('text', newname);
                    node.save();
                    var tab = me.findBackupTab(id);
                    tab.tab.setText(newname);
                    Ext.Msg.hide();
                },
                failure : function(form,action){
                    TwinDB.util.Util.showFormException(action);
                }
            });
        }
    },
    saveStorage: function(btn, e, eOpts){
        var me = this;

        console.log('Pressed save button');
        var formPanel = btn.up('form');
        if(formPanel.getForm().isValid()){
            Ext.Msg.wait('Saving...');
            formPanel.getForm().submit({// #5
                clientValidation: true,
                url: 'php/setStorage.php', // #6
                success : function(form,action){
                    var menu = Ext.ComponentQuery.query('mainmenuitem[title=Storage]')[0];
                    var id = formPanel.getForm().getValues().volume_id;
                    var newname = formPanel.getForm().getValues().name;
                    var node = menu.getRootNode().findChild('id', 'storage_id_' + id);
                    node.set('text', newname);
                    node.save();
                    var tab = me.findStorageTab(id);
                    if (tab) {
                        tab.tab.setText(newname);
                    }
                    Ext.Msg.hide();
                    var store = Ext.getStore('Storage');
                    if (store) {
                        store.reload();
                    }

                },
                failure : function(form,action){
                    TwinDB.util.Util.showFormException(action);
                }
            });
        }
    },
    closeSchedule: function(btn, e, eOpts){
        console.log('Pressed cancel button');
        var tab = btn.up('schedule');
        var mainpanel = btn.up('schedule').up('panel');
        mainpanel.remove(tab);
    },
    closeRetentionPolicy: function(btn, e, eOpts){
        console.log('Pressed cancel button');
        var tab = btn.up('retentionpolicy');
        var mainpanel = btn.up('retentionpolicy').up('panel');
        mainpanel.remove(tab);
    },
    closeStorage: function(btn, e, eOpts){
        console.log('Pressed cancel button');
        var tab = btn.up('storage');
        var mainpanel = btn.up('storage').up('panel');
        mainpanel.remove(tab);
    },
    openUpgradeStorageStep1: function(btn, e, eOpts){
        console.log('Pressed add storage button');
        Ext.create('widget.upgradestoragestep1');
    },
    updateAttributeValues: function(attribute_id) {
        var st = Ext.getStore('attributeValueCombo');
        st.load({
            params: {
                attribute_id: attribute_id
            }
        });
    },
    doServerGridAction: function(action, params, rawData){
        var me = this;
        var p = {};
        
        console.log('Performing action:');
        console.log(action);
        console.log('With parameters:');
        console.log(params);

        switch(action.getValue()){
            case 'set attribute':
                p = {
                    action: action.getValue(),
                    attribute_id: params.attribute.getValue(),
                    attribute_value: params.attribute_value.getValue(),
                    data: Ext.encode(rawData)
                };
                break;
            case 'remove attribute':
                p = {
                    action: action.getValue(),
                    attribute_id: params.attribute.getValue(),
                    data: Ext.encode(rawData)
                };
                break;
            case 'set backup configuration':
                p = {
                    action: action.getValue(),
                    config_id: params.backupConfig.getValue(),
                    data: Ext.encode(rawData)
                };
                break;
            default: 
                TwinDB.util.Util.showErrorMsg('Unknown action "' + action.getValue() + '"');
                return;
        }
        Ext.Msg.wait('Saving...');
        Ext.Ajax.request({
            url: 'php/setServerAttributes.php',
            params: p,
            success: function(conn, response, options, eOpts) {
                var result = Ext.JSON.decode(conn.responseText, true); // #1
                if (!result) {
                    result = {};
                    result.success = false;
                    result.msg = conn.responseText;
                }
                if (result.success) {
                    var menu = Ext.ComponentQuery.query('mainmenuitem[title=Server farm]')[0];
                    var root = menu.getRootNode();
                    if (action.getValue() == 'remove attribute') {
                        me.updateAttributeValues(params.attribute.getValue());
                    }
                    if (action.getValue() == 'set attribute' || action.getValue() == 'remove attribute') {
                        var child = root.findChild('id', 'attribute_id_' + params.attribute.getValue());
                        if(child){
                            me.application.fireEvent("attrvalueupdate", child);
                        }
                    }
                    me.application.fireEvent("serverfilterupdate");
                    Ext.Msg.show({
                        title: 'Success',
                        msg: 'Action <strong>' + action.getValue() + '</strong>'
                                + ' successfully done',
                        buttons: Ext.Msg.OK,
                        icon: Ext.Msg.INFO
                    });
                    Ext.Function.defer(function(){ Ext.Msg.close(); }, 1000);
                } else {
                    TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
                }
            },
            failure: function(conn, response, options, eOpts) {
                TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
            }
        });
    },
    switchUser: function(new_email) {
        Ext.Ajax.request({
            url: 'php/switchUser.php',
            params: {
                new_email: new_email
            },
            success: function(conn, response, options, eOpts) {
                var result = Ext.JSON.decode(conn.responseText, true); // #1
                if (!result){ // #2
                    result = {};
                    result.success = false;
                    result.msg = conn.responseText;
                }
                if (result.success) {
                    TwinDB.util.Util.updateInterface();
                } else {
                    TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
                }
            },
            failure: function(conn, response, options, eOpts) {
                TwinDB.util.Util.showAjaxException(conn, response, options, eOpts);
            }
        });
    },
    openServerTab: function(grid, record, item, index, e, eOpts ) {
        var mainPanel = this.getMainPanel();
        console.log('Selected record:');
        console.log(record);
        var newTab = mainPanel.items.findBy( // #2
            function (tab){
                console.log('Comparing with:');
                console.log(tab);
                if(tab.xtype == 'server' && tab.data){
                    return tab.data.server_id === record.data.server_id;
                } else {
                    return tab.title === record.data.name;
                }
            }
        );
        console.log('newTab = ');
        console.log(newTab);
        if ( !newTab ) { 
            var title = record.data.name;
            Ext.get(mainPanel.getEl()).mask('Loading...');
            console.log('Adding new tab');
            var params = null; 
            if (record.raw.params != '') {
                console.log('Decoding ' + record.raw.params);
                params = Ext.JSON.decode(record.raw.params);
            }
            newTab = mainPanel.add({ // #5
                xtype: 'server', // #6
                closable: true, // #7
                iconCls: 'database_gear', // #8
                params: params,
                title: title,
                server_id: record.data.server_id
            });
            var store = Ext.getStore('ServerBackupCopies');
            store.load({
                params: {
                    server_id: record.data.server_id
                },
                callback: function() {
                    var grid = newTab.down('server-backupcopies');
                    if (grid) {
                        var grid_el = Ext.get(grid.getEl());
                        if (grid_el) grid_el.unmask();
                    }
                }
            });
            store = Ext.getStore('JobGridServer');
            store.load({
                params: {
                    server_id: record.data.server_id
                },
                callback: function() {
                    var grid = newTab.down('jobgrid-server');
                    if (grid) {
                        var grid_el = Ext.get(grid.getEl());
                        if (grid_el) grid_el.unmask();
                    }
                }
            });
            Ext.get(mainPanel.getEl()).unmask();
        }
        mainPanel.setActiveTab(newTab); // #10
    },
    
    getClusterNodes: function(server_id) {
        
    }
});
