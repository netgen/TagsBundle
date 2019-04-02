/*global $*/

(function() {
  'use strict';

  var $ = jQuery;

  var initTagsTranslations = function () {
    $.EzTags.Base.defaults.translations = {
      selectedTags: 'Selected tags',
      loading: 'Loading...',
      noSelectedTags: 'There are no selected tags',
      suggestedTags: 'Suggested tags',
      noSuggestedTags: 'There are no tags to suggest',
      addNew: 'Add new',
      clickAddThisTag: 'Click to add this tag',
      removeTag: 'Remove tag',
      translateTag: 'Translate tag',
      existingTranslations: 'Existing translations',
      noExistingTranslations: 'No existing translations',
      addTranslation: 'Add translation',
      cancel: 'Cancel',
      ok: 'OK',
      browse: 'Browse',
    };
  };

  initTagsTranslations();
  $('.tagssuggest').EzTags();
  $('.parent-selector-tree').find('.tags-modal-tree').tagsTree({'modal': true});

})();
