YUI.add('netgen-tags-serverside-view-service', function (Y) {
    "use strict";

    Y.namespace('Netgen.Tags.Service');

    var PJAX_DONE_REDIRECT = 205,
        PJAX_LOCATION_HEADER = 'PJAX-Location';

    Y.Netgen.Tags.Service.ServerSideViewService = Y.Base.create('NetgenTagsServerSideViewService', Y.eZ.ServerSideViewService, [], {
        _parseResponse: function (response) {
            var app = this.get('app'),
                pjaxLocation = response.getResponseHeader(PJAX_LOCATION_HEADER);

            if ( response.status === PJAX_DONE_REDIRECT && pjaxLocation ) {
                app.navigate(this._getAdminRouteUri(pjaxLocation));
            }

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
