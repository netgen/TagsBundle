/**
 *  class that overides default behaviour for eztag fields.
 *
 */
YUI.add('netgen-tags-field-editview', function (Y) {
    "use strict";
    /**
     * Provides the field edit view for the ezTag fields
     *
     * @module netgen-tags-editview
     */

    Y.namespace('Netgen.Tags.Edit.Field');

    // fieldidentifier to handle..
    var FIELDTYPE_IDENTIFIER = 'eztags';

    /**
     * ez-tags edit view
     *
     * @namespace eZ
     * @class tagsEditView
     * @constructor
     * @extends eZ.SelectionEditView
     */
    Y.Netgen.Tags.Edit.Field.TagsEdit = Y.Base.create('NetgenTagsFieldEditView', Y.eZ.SelectionEditView, [], {

        _tagList: [],
        _tagNames: [],

        initializer: function () {
            this._useStandardFieldDefinitionDescription = false;

            this.containerTemplate = '<div class="' +
                this._generateViewClassName(this._getName()) + ' ' +
                this._generateViewClassName(Y.eZ.SelectionEditView.NAME) + '"/>';

            var fieldDefinition = this.get('fieldDefinition');

            // AJAX call in order to get the tagList if not available
            var path = '/api/ezp/v2/tags-all/'+fieldDefinition.fieldSettings.subTreeLimit +'?hideRootTag='+fieldDefinition.fieldSettings.hideRootTag;
            Y.io(path, {
                method: 'GET',
                on: {
                    success: Y.bind(this._handleResponse, this),
                    failure: this._handleLoadFailure,
                },
                context: this
            });
        },


        /**
         * Handles the Ajax response and stores the returned list of all available tags..
         *
         * @method _handleFormSubmitResponse
         * @param {XMLHttpRequest} response
         */
        _handleResponse: function (transactionId, ajax) {
            var tags = JSON.parse(ajax.response);

            this._tagList = tags.tagTree;

            // save the tagsname of each tag ..
            var tagNames = [];
            for (var tagname in tags.tagTree){
                tagNames.push(tagname);
            }

            // sort the list..
            tagNames.sort(function(strA, strB) {
                // .. upper - and lowercases are not correctly ordered.. so lowercase all of them during sorting..
                return strA.toLowerCase().localeCompare(strB.toLowerCase());
            });

            // format the values in order to handle them directly by the searchbox..
            var res = [];
            for (var i = 0; i < tagNames.length; i++) {
                res.push({
                    Name: tagNames[i]
                });
            }

            // store the tagnames in config..
            this._tagNames = res;
        },


        /**
         * Handles the loading error.
         *
         * @method _handleLoadFailure
         * @param {String} tId
         * @param {XMLHttpRequest} response
         * @protected
         */
        _handleLoadFailure: function (tId, response) {
            var frag = Y.Node.create(response.responseText),
                notificationCount,
                errorMsg = '';

            this.get('app').set('loading', false);
            notificationCount = this._parseNotifications(frag);
            if ( notificationCount === 0 ) {
                errorMsg = "Failed to load '" + response.responseURL + "'";
            }
            this._error(errorMsg);
        },


        /**
         * function that gets the keyword from the tag object and returns it ..
         *
         * @param tag
         * @returns string
         * @private
         */
        _getTagName: function(tag) {
            var mainLanguageCode = tag.main_language_code;
            return tag.keywords[mainLanguageCode];
        },



        /**
         * function that initializes the selection component..
         *
         * @returns {Y.eZ.SelectionFilterView}
         * @private
         */
        _getSelectionFilter: function () {

            var container = this.get('container'),
                selectedObjectArray = this._getSelectedTextValues(),
                input = container.one('.ez-selection-filter-input'),
                source = this._tagNames,
                selected = [];

            Y.Array.each(selectedObjectArray, function (selectedObject) {
                selected.push(selectedObject.text);
            });
            return new Y.eZ.SelectionFilterView({
                container: input.get('parentNode'),
                inputNode: input,
                listNode: this.get('container').one('.ez-selection-options'),
                selected: selected,
                source: source,
                filter: true,
                resultFilters: 'startsWith',
                resultHighlighter: 'startsWith',
                isMultiple: true,
                resultTextLocator: function (sourceElement) {
                    return sourceElement.Name;
                },
                resultAttributesFormatter: function (sourceElement) {
                    return {
                        text: sourceElement.Name
                    };
                }
            });
        },


        /**
         * Validates the current input of eztag field field
         *
         * @method validate
         */
        validate: function () {

            // config of the field
            var fieldDefinition = this.get('fieldDefinition');

            // fieldvalues..
            var values = this.get('values');

            // no Tag is given, but it should...
            if ((values.length==0)&&(fieldDefinition.isRequired)){
                this.set('errorStatus', 'This field is required');
            }

            // more tags given than allowed..
            else if (  (0<fieldDefinition.fieldSettings.maxTags)
                     &&(fieldDefinition.fieldSettings.maxTags < values.length)) {
                this.set('errorStatus', 'To many tags');
            }

            // no error
            else {
                this.set('errorStatus', false);
            }
        },



        /**
         * function that returns list of keywords from selected tags.
         * the returned values are used for the display in textbox.
         *
         * @returns {Array} of objects containing  - keywords from selected tags..
         * @private
         */
        _getSelectedTextValues: function () {
            var field = this.get('field'),
                fieldTagsObjs = [],
                res = [];

            // get the fieldvalues..
            if ( field && field.fieldValue ) {
                fieldTagsObjs = field.fieldValue;
            }

            // get the keywords from the tag object and create a key/value pair..
            for (var i = 0; i < fieldTagsObjs.length; i++) {
                res.push({
                    text: this._getTagName(fieldTagsObjs[i])
                });
            }
            return res;
        },


        /**
         *  function that translates the selected values into tag objects.
         *  this function is called just before saving the dataset of the content object.
         * @returns {Array} of tagobjects
         * @private
         */
        _getFieldValue: function () {

            // get config in order to access to the tagobjects list
            var config = this.get('config');

            // get the fieldvalues..
            var values = this.get('values');

            // get the tag objects corresponding
            var res = [];
            for (var i = 0; i < values.length; i++) {
                // value is issued from initial value
                if (values[i].text){
                    res.push(this._tagList[values[i].text]);

                // value has been added trough cumbobox..
                } else if (typeof values[i] == 'string'){
                    res.push(this._tagList[values[i]]);
                }
            }
            // array of tag objects..
            return res;
        }
    });


    // link js class to ez field. ...
    Y.eZ.FieldEditView.registerFieldEditView(FIELDTYPE_IDENTIFIER, Y.Netgen.Tags.Edit.Field.TagsEdit);
});
