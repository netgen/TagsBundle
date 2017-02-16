YUI.add('netgen-tags-serverside-view', function (Y) {
    "use strict";

    Y.namespace('Netgen.Tags.View');

    Y.Netgen.Tags.View.ServerSideView = Y.Base.create('NetgenTagsServerSideView', Y.eZ.ServerSideView, [], {
        initializer: function () {
            this.after('activeChange', function() {
                if (this.get('active')) {
                    this._initializeTagTree();
                    ngTagsInit();
                }
            });
        },

        _initializeTagTree: function () {
            var view = this;
            var $ = jQuery;

            $.jstree.defaults.core.data = {};
            $.jstree.defaults.core.data.dataFilter = function (data, type) {
                data = JSON.parse(data);
                var prefix = $('meta[name="ng-tags-app-base-path"]').attr('content');

                data.forEach(function(node) {
                    node.a_attr.href = view._generateTagTreeUrl(prefix, node.a_attr.href);

                    node.data.context_menu.forEach(function(item) {
                        item.url = view._generateTagTreeUrl(prefix, item.url);
                    });
                });

                return JSON.stringify(data);
            };
        },

        _generateTagTreeUrl: function (prefix, uri) {
            return prefix + encodeURIComponent(uri.replace(/^\//, ''));
        },

        _serializeForm: function (form, focusedNode) {
            var data = [];

            if ( this._isSubmitButton(focusedNode, form) ) {
                data.push(focusedNode.getAttribute('name') + "=");
            }

            form.get('elements').each(function (field) {
                var name = field.getAttribute('name'),
                    type = field.get('type');

                if ( !name ) {
                    return;
                }

                /* jshint -W015 */
                switch (type) {
                    case 'button':
                    case 'reset':
                    case 'submit':
                        break;
                    case 'radio':
                    case 'checkbox':
                        if ( field.get('checked') ) {
                            data.push(name + "=" + field.get('value'));
                        }
                        break;
                    case 'select-multiple':
                        if ( field.get('selectedIndex') >= 0 ) {
                            field.get('options').each(function (opt) {
                                if ( opt.get('selected') ) {
                                    data.push(name + "=" + opt.get('value'));
                                }
                            });
                        }
                        break;
                    default:
                        // `.get('value')` returns the expected field value for
                        // inputs, select-one and even textarea.
                        data.push(name + "=" + field.get('value'));
                }
                /* jshint +W015 */
            });

            return data.join('&');
        },
    });
});
