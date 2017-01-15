YUI.add('netgen-tags-serverside-view-service', function (Y) {
    "use strict";

    Y.namespace('Netgen.Tags.Service');

    Y.Netgen.Tags.Service.ServerSideViewService = Y.Base.create('NetgenTagsServerSideViewService', Y.eZ.ServerSideViewService, [], {
        _parseResponse: function (response) {
            Y.eZ.ServerSideViewService.prototype._parseResponse.call(this, response);

            var head = Y.one('head');

            var metaTag = head.one('meta[name="ng-tags-app-base-path"]');
            if (!metaTag) {
                metaTag = head.create('<meta />').appendTo(head);
            }

            metaTag.setAttribute("name", 'ng-tags-app-base-path');
            metaTag.setAttribute("content", this._getAdminRouteUri('/'));
        },

        _getAdminRouteUri: function (uri) {
            var app = this.get('app');
            var regexp = new RegExp('^' + app.get('apiRoot'));

            return app.routeUri('NetgenTagsGenericRoute', {uri: uri.replace(regexp, '')});
        },
    });
});
