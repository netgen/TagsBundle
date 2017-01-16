YUI.add('netgen-tags-content-type-edit-serverside-view-plugin', function (Y) {
    "use strict";

    Y.namespace('Netgen.Tags.Plugin');

    Y.Netgen.Tags.Plugin.ContentTypeEditServerSideViewPlugin = Y.Base.create('NetgenTagsContentTypeEditServerSideViewPlugin', Y.Plugin.Base, [], {
        initializer: function () {
            Y.on('contentready', this.initTags, 'form[name="ezrepoforms_contenttype_update"]');
        },

        initTags: function () {
            ngTagsInit();
        }
    }, {
        NS: 'NetgenTagsContentTypeEditServerSideView'
    });

    Y.eZ.PluginRegistry.registerPlugin(
        Y.Netgen.Tags.Plugin.ContentTypeEditServerSideViewPlugin, ['contentTypeEditServerSideView']
    );
});
