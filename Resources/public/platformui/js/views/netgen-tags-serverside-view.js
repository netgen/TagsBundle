YUI.add('netgen-tags-serverside-view', function (Y) {
    "use strict";

    Y.namespace('Netgen.Tags.View');

    Y.Netgen.Tags.View.ServerSideView = Y.Base.create('NetgenTagsServerSideView', Y.eZ.ServerSideView, [], {
        initializer: function () {
            this.after('activeChange', function() {
                if (this.get('active')) {
                    ngTagsInit();
                }
            });
        },
    });
});
