(function (global, doc, ibexa) {
    const SELECTOR_FIELD = '.ibexa-field-edit--eztags.ibexa-field-edit--required';

    class TagsValidator extends ibexa.BaseFieldValidator {
            validateTags(event) {
            const isEmpty = !event.target.value;
            const isError = isEmpty;
            const label = event.target.closest(SELECTOR_FIELD).querySelector('.ibexa-field-edit__label').innerHTML;
            const result = {isError};
            if (isEmpty) {
                result.errorMessage = ibexa.errors.emptyField.replace('{fieldName}', label);
            }

            return result;
        }

        init() {
            ibexa.BaseFieldValidator.prototype.init.call(this);
            this.eventsMap.forEach((eventConfig) => {
                // Workaround for missing change event on hidden input.
                // Watch hidden field for changes and re-validate it
                MutationObserver = window.MutationObserver || window.WebKitMutationObserver;
                var trackChange = function (element) {
                    var oldVal = '';
                    var observer = new MutationObserver(function (record, x) {
                        var newVal = record[0].target.value;
                        if (oldVal != newVal) {
                            eventConfig.validateField({target: record[0].target});
                            oldVal = newVal;
                        }
                    });
                    observer.observe(element, {
                        attributes: true
                    });
                }

                doc.querySelectorAll('.ibexa-field-edit--eztags input.tagids').forEach((element) => {
                    trackChange(element);
                });
            });
        }
    }

    const validator = new TagsValidator({
        classInvalid: 'is-invalid',
        fieldSelector: SELECTOR_FIELD,
        eventsMap: [
            {
                selector: '.ibexa-field-edit--eztags input.tagids',
                eventName: 'change',
                callback: 'validateTags',
                errorNodeSelectors: ['.ibexa-field-edit__label-wrapper'],
            },
        ],
    });

    if (jQuery(SELECTOR_FIELD).length) {
        validator.init();
    }

    global.ibexa.fieldTypeValidators = global.ibexa.fieldTypeValidators ?
        [...global.ibexa.fieldTypeValidators, validator] :
        [validator];
})(window, window.document, window.ibexa);
