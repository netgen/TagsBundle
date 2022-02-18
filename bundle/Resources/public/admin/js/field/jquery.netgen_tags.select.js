/*global $*/

(function() {
  'use strict';

  var $ = jQuery;

  $.NetgenTags.Select = $.NetgenTags.Base.extend({
    templates: {
      option: ['<option value="<%= tag.id %>" <%= selected %> ><%= tag.name %></option>'],
      select: ['<select class="js-tag-select form-control"></select>'],
      skeleton: [
      '<div class="selects"></div>'
      ]
    },

    /**
     * Initializes Netgen Tags Select plugin. Calls fetch tags function with callback which appends
     * fetched tags to select dropdowns. Also, registers 'onChange' listener on
     * all select dropdowns.
     */
    initialize: function(){
      var self = this;
      this.fetch_available_tags(function(){
        if(this.tags.length()){
          $.each(this.tags.items, function(i, tag){
            self.append_select(tag);
          });
          self.$selects = self.$('.selects');
          self.should_append_new_select();
          self.update_selects();
        }else{
          self.append_select();
        }
      });
      this.$selects = this.$('.selects');
      this.on('change', '.js-tag-select', $.proxy(this.on_select, this));
    },

    /**
     * On select removes previously selected Tag from this.tags collection and adds
     * new Tag into it. Takes care if blank value is selected or was previously selected.
     * @param  {Object} e jQuery event object.
     */
    on_select: function(e){
      var $select = $(e.target),
          id = $select.val(),
          tag = $select.data('linked_tag'),
          new_tag;

      if(id){
        new_tag = this.available_tags.find(id);
        tag && this.remove(tag.id);
        this.add(new_tag);
        !tag && this.should_append_new_select();
        this.link_tag_and_select(new_tag, $select);
      }else{
        if(tag){
          $select.siblings().filter(function() {
            return $(this).val() === '';
          }).length && tag.select.remove();
          this.remove(tag.id);
          this.unlink_tag_and_select(tag, $select);
        }
      }
      this.update_selects();
    },

    link_tag_and_select: function(tag, select){
      tag.select = select;
      select.data('linked_tag', tag);
    },

    unlink_tag_and_select: function(tag, select){
      tag.select = null;
      select.data('linked_tag', null);
    },

    /**
     * Updates dropdowns depending on current tags status. If tag is selected
     * in another dropdown its disabled by current one. Also, if tag is linked with
     * select dropdown, it is marked as selected.
     */
    update_selects: function(){
      var self = this, $option, linked_tag;
      this.$('option').prop('disabled', false).prop('selected', false);
      this.$('select').each(function(i , select){
        linked_tag = $(this).data('linked_tag');
        $.each(self.tags.items, function(i, tag){
          $option = $('option[value="'+tag.id+'"]', select);
          if(linked_tag && tag.id === linked_tag.id){
            $option.prop('selected', true);
          }else{
            $option.prop('disabled', true);
          }
        });
      });
    },

    /**
     * Append new select dropdown on 'div.selects' if defined maximum is not reached.
     */
    should_append_new_select: function(){
      if(this.max_tags_limit_reached()){return;}
      this.append_select();
    },


    fetch_available_tags: function(done){
      var self = this;

      $.get(this.opts.childrenUrl, {
        subTreeLimit: this.opts.subtreeLimit,
        hideRootTag: this.opts.hideRootTag,
        locale: this.opts.locale
      }, $.proxy(function (data) {
           self.available_tags = self.parse_remote_tags(data);
           done.call(self);
         },
         this
        )
      );
    },

    /**
     * Sets up select dropdown so it has all available tags and one blank value.
     * Dropdown is linked with a Tag recieved as a parameter 'unlinked_tag'.
     * @param  {DOM element} $select      New dropdown element.
     * @param  {Tag object} unlinked_tag Tag to link with dropdown.
     */
    setup_select: function($select, unlinked_tag){
      var self = this, selected;
      var dummy_tag = new this.TagKlass({id: '', name:''});
      $select.append(self.render_template('option', {tag: dummy_tag, selected: null }));

      unlinked_tag && this.link_tag_and_select(unlinked_tag, $select);

      $.each(this.available_tags.items, function(i, tag){
        selected = unlinked_tag && tag.id === unlinked_tag.id ? 'selected="selected"' : '';
        $select.append(self.render_template('option', {tag: tag, selected: selected }));
      });
    },

    /**
     * Appends dropdown to 'div.selects' and links it with Tag parameter recieved.
     * @param  {Tag object} unlinked_tag Tag that will be linked with newly appended dropdown.
     */
    append_select: function(unlinked_tag){
      var $select = $(this.render_template('select'));
      this.setup_select($select, unlinked_tag);
      this.$selects.append($select);
    }
  });



})();
