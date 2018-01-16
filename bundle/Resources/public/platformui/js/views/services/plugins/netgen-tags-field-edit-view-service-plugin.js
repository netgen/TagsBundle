YUI.add('netgen-tags-field-edit-view-service-plugin', function (Y) {
    "use strict";

    Y.namespace('Netgen.Tags.Plugin');

    Y.Netgen.Tags.Plugin.NetgenTagsFieldEditViewServicePlugin = Y.Base.create('NetgenTagsFieldEditViewServicePlugin', Y.Plugin.Base, [], {

        initializer: function () {
            this.onHostEvent('*:fetchFlag', this._fetchAddPermissionFlag, this);
        },

        /**
         * Fetches hasAddAccess flag for "add new" tag button visibility.
         *
         * @method _fetchHasAddAccessFlag
         * @protected
         *
         */
        _fetchAddPermissionFlag: function (event) {

            Y.io(event.addTagButtonVisibility, {
                method: 'GET',
                on: {
                    success: function (id, xhr) {
                        event.target.set('addPermissionFlag', JSON.parse(xhr.response));
                    },
                    failure: Y.bind(function (id, xhr) {
                        this.get('host').fire('notify', {
                            notification: {
                                text: 'Fetching add tag permission failed',
                                identifier: 'fetching-add-tag-permission-error',
                                state: 'error',
                                timeout: 0
                            }
                        });
                    }, this)
                }
            });
        }

    }, {
        NS: 'NetgenTagsFieldEditViewServicePlugin'
    });

    Y.eZ.PluginRegistry.registerPlugin(
        Y.Netgen.Tags.Plugin.NetgenTagsFieldEditViewServicePlugin, ['NetgenTagsFieldEditView']
    );

});
