YUI.add('netgen-tags-field-view', function (Y) {
    "use strict";

    Y.namespace('Netgen.Tags.View.Field');

    Y.Netgen.Tags.View.Field.TagsView = Y.Base.create('NetgenTagsFieldView', Y.eZ.FieldView, [], {
        _isFieldEmpty: function () {
            return this._getFieldValue().length === 0;
        },
    });

    Y.eZ.FieldView.registerFieldView('eztags', Y.Netgen.Tags.View.Field.TagsView);
});
