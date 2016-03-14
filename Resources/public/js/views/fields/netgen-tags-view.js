YUI.add('netgen-tags-view', function (Y) {
    'use strict';

    /**
     * Provides the content view for tags field
     *
     * @module netgen-tags-view
     */
    Y.namespace('Netgen');

    /**
     * Tags content view
     *
     * @namespace Netgen
     * @class TagsView
     * @constructor
     * @extends eZ.FieldView
     */
    Y.Netgen.TagsView = Y.Base.create('netgen-tags-view', Y.eZ.FieldView, [], {
        /**
         * Returns the value to display
         *
         * @method _getFieldValue
         * @protected
         * @return {String}
         *
         * @todo Use current language code if possible
         */
        _getFieldValue: function () {
            var tagKeywords = this.get('field').fieldValue.map(function(a) {
                return a.keywords[a.main_language_code];
            });

            return tagKeywords.join(', ');
        },

        /**
         * Checks whether the field value is empty
         *
         * @method _isFieldEmpty
         * @protected
         * @return {Boolean}
         */
        _isFieldEmpty: function () {
            return this.get('field').fieldValue.length == 0;
        }
    });

    Y.eZ.FieldView.registerFieldView('eztags', Y.Netgen.TagsView);
});
