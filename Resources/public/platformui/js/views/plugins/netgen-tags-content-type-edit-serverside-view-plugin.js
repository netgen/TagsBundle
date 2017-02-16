YUI.add('netgen-tags-content-type-edit-serverside-view-plugin', function (Y) {
    "use strict";

    Y.namespace('Netgen.Tags.Plugin');

    Y.Netgen.Tags.Plugin.ContentTypeEditServerSideViewPlugin = Y.Base.create('NetgenTagsContentTypeEditServerSideViewPlugin', Y.Plugin.Base, [], {
        initializer: function () {
            Y.on('contentready', this.initTags, 'form[name="ezrepoforms_contenttype_update"]');

            this.on('activeChange', function () {
                this.after('htmlChange', this.initTags);
            });
        },

        initTags: function () {
            jQuery('.tags-modal-tree').tagsTree({'modal': true});
        }
    }, {
        NS: 'NetgenTagsContentTypeEditServerSideView'
    });

    Y.eZ.PluginRegistry.registerPlugin(
        Y.Netgen.Tags.Plugin.ContentTypeEditServerSideViewPlugin, ['contentTypeEditServerSideView']
    );
});
