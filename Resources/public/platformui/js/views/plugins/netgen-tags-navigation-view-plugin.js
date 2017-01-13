YUI.add('netgen-tags-navigation-view-plugin', function (Y) {
    "use strict";

    Y.namespace('Netgen.Tags.Plugin');

    Y.Netgen.Tags.Plugin.NavigationViewPlugin = Y.Base.create('NetgenTagsNavigationViewPlugin', Y.Plugin.Base, [], {
        initializer: function () {
            var host = this.get('host');
            this._injectNetgenTagsZone(host);

            host.addAttr('ngtagsNavigationItems', {
                setter: '_buildNavigationViews',
                writeOnce: true,
            });

            // _handleSelectedItem is triggered before ngtagsNavigationItems is
            // populated, so top menu is not preselected when UI is opened on
            // one of the Tags pages. This repeats the selection process
            // once ngtagsNavigationItems is populated.
            host.after('ngtagsNavigationItemsChange', host._handleSelectedItem);
        },

        _injectNetgenTagsZone: function (host) {
            var zones = host.get('zones');
            var newZones = {};

            // We want to add ngtags zone just before the admin zone
            for (var zone in zones) {
                if (zones.hasOwnProperty(zone)) {
                    if (zone === 'admin') {
                        newZones['ngtags'] = 'Netgen Tags';
                    }

                    newZones[zone] = zones[zone];
                }
            }

            host._set('zones', newZones);
        }
    }, {
        NS: 'NetgenTagsNavigationView'
    });

    Y.eZ.PluginRegistry.registerPlugin(
        Y.Netgen.Tags.Plugin.NavigationViewPlugin, ['navigationHubView']
    );
});
