(function (global) {
    const SELECTOR_FIELD = '.ez-field-edit--eztags.ez-field-edit--required';

    class EzTagsValidator extends global.eZ.BaseFieldValidator {
        /**
         * Validates the input
         *
         * @method validateInteger
         * @param {Event} event
         * @returns {Object}
         * @memberof EzIntegerValidator
         */
        validateEzTags(event) {
            const isEmpty = !event.target.value;
            const isError = isEmpty;
            const label = event.target.closest(SELECTOR_FIELD).querySelector('.ez-field-edit__label').innerHTML;
            const result = {isError};
            if (isEmpty) {
                result.errorMessage = global.eZ.errors.emptyField.replace('{fieldName}', label);
            }

            return result;
        }

        init() {
            global.eZ.BaseFieldValidator.prototype.init.call(this);
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
                jQuery('.ez-field-edit--eztags input.tagids').each(function(index,element) {
                    trackChange(element);
                });
            });

        }
    }

    const validator = new EzTagsValidator({
        classInvalid: 'is-invalid',
        fieldSelector: SELECTOR_FIELD,
        eventsMap: [
            {
                selector: '.ez-field-edit--eztags input.tagids',
                eventName: 'change',
                callback: 'validateEzTags',
                errorNodeSelectors: ['.ez-field-edit__label-wrapper'],
            },
        ],
    });

    if (jQuery(SELECTOR_FIELD).length) {
        validator.init();
    }

    global.eZ.fieldTypeValidators = global.eZ.fieldTypeValidators ?
        [...global.eZ.fieldTypeValidators, validator] :
        [validator];


})(window);
