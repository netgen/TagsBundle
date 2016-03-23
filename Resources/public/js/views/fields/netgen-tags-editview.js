YUI.add('netgen-tags-editview', function (Y) {
    'use strict';

    /**
     * Provides the content edit view for tags field
     *
     * @module netgen-tags-editview
     */
    Y.namespace('Netgen');

    /**
     * Tags content edit view
     *
     * @namespace Netgen
     * @class TagsEditView
     * @constructor
     * @extends eZ.FieldEditView
     */
    Y.Netgen.TagsEditView = Y.Base.create('netgen-tags-editview', Y.eZ.FieldEditView, [], {
        /**
         * Defines the variables to import in the field edit template
         *
         * @protected
         * @method _variables
         * @return {Object} holding the variables for the template
         */
        _variables: function () {
            var def = this.get('fieldDefinition'),
                fieldValue = this.get('field').fieldValue;

            // @todo Use current language code if possible
            var tagKeywords = fieldValue.map(function(a) {
                return a.keywords[a.main_language_code];
            });

            var tagParentIds = fieldValue.map(function(a) {
                return a.parent_id;
            });

            var tagIds = fieldValue.map(function(a) {
                return a.id;
            });

            // @todo Use current language code if possible
            var tagLocales = fieldValue.map(function(a) {
                return a.main_language_code;
            });

            return {
                "tagKeywords": tagKeywords.length > 0 ? tagKeywords.join('|#') : '',
                "tagParentIds": tagParentIds.length > 0 ? tagParentIds.join('|#') : '',
                "tagIds": tagIds.length > 0 ? tagIds.join('|#') : '',
                "tagLocales": tagLocales.length > 0 ? tagLocales.join('|#') : ''
            };
        },

        /**
         * Returns the currently filled tags
         *
         * @protected
         * @method _getFieldValue
         * @return {Object}
         */
        _getFieldValue: function () {
            return this.get('field').fieldValue;
        }
    });

    Y.eZ.FieldEditView.registerFieldEditView('eztags', Y.Netgen.TagsEditView);
});
