YUI.add('netgen-tags-navigation-view-service-plugin', function (Y) {
    "use strict";

    Y.namespace('Netgen.Tags.Plugin');

    Y.Netgen.Tags.Plugin.NavigationViewServicePlugin = Y.Base.create('NetgenTagsNavigationViewServicePlugin', Y.eZ.Plugin.ViewServiceBase, [], {
        initializer: function () {
            var host = this.get('host');

            host.addAttr('ngtagsNavigationItems', {
                getter: function (val) {
                    if (val) {
                        return val;
                    }

                    val = [
                        host._getParameterItem(
                            "Dashboard", "netgen-tags-dashboard",
                            "NetgenTagsGenericRoute", {uri: "tags/admin/"}, "uri"
                        ),
                    ];

                    host._set('ngtagsNavigationItems', val);
                    return val;
                },
                readOnly: true,
            });
        },

        getViewParameters: function () {
            return {
                ngtagsNavigationItems: this.get('host').get('ngtagsNavigationItems'),
            };
        },
    }, {
        NS: 'NetgenTagsNavigationViewService'
    });

    Y.eZ.PluginRegistry.registerPlugin(
        Y.Netgen.Tags.Plugin.NavigationViewServicePlugin, ['navigationHubViewService']
    );
});
