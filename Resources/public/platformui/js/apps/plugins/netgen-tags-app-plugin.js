YUI.add('netgen-tags-app-plugin', function (Y) {
    "use strict";

    Y.namespace('Netgen.Tags.Plugin');

    Y.Netgen.Tags.Plugin.AppPlugin = Y.Base.create('NetgenTagsAppPlugin', Y.Plugin.Base, [], {
        initializer: function () {
            var app = this.get('host');

            app.views.NetgenTagsServerSideView = {
                type: Y.Netgen.Tags.View.ServerSideView
            };

            this.addRoutes(app);
        },

        addRoutes: function (app) {
            app.route({
                name: "NetgenTagsGenericRoute",
                path: "/tags/:uri",
                sideViews: {'navigationHub': true, 'discoveryBar': false},
                service: Y.Netgen.Tags.Service.ServerSideViewService,
                view: "NetgenTagsServerSideView",
                callbacks: ['open', 'checkUser', 'handleSideViews', 'handleMainView']
            });
        },
    }, {
        NS: 'NetgenTagsApp'
    });

    Y.eZ.PluginRegistry.registerPlugin(
        Y.Netgen.Tags.Plugin.AppPlugin, ['platformuiApp']
    );
});
