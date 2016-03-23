/*global $*/

YUI.add('netgen-tags-tagsedit-tree', function (Y) {
  'use strict';

  // jstree setup
  function TreeView(el){
      this.$el = $(el);
      this.rootNodeAdded = false;
      this.rootNode = {};
      this.getSetupData();
  };

  TreeView.prototype.getSetupData = function(){
      $.getJSON(this.$el.data('config-url') + '?ContentType=json', function(data) {
          this.rootNode = data.content.config.rootTag;
          this.hideRoot = data.content.config.hideRootTag;
          this.initialiseTree();
      }.bind(this));
  };

  TreeView.prototype.getTreeData = function(node, cb){
      if(this.rootNodeAdded || this.hideRoot){
          var tagId = node.id == '#' ? this.rootNode.id : node.id;
          $.getJSON(this.$el.data('base-url') + tagId + '?ContentType=json', function(data) {
              var children = data.content.children;
              var selected = this.tags.tags.items;
              for(var i=0; i<children.length; i++){
                  (children[i].parent == this.rootNode.id && this.hideRoot) && (children[i].parent = '#');
                  for(var j=0; j<selected.length; j++){
                      if(children[i].id.toString() == selected[j].id){
                          children[i].a_attr['data-selected'] = true;
                      };
                  };
              }
              cb(children)
          }.bind(this));
      } else {
          cb([this.rootNode]);
          this.rootNodeAdded = true;
      }
  };

  TreeView.prototype.initialiseTree = function(){
      this.$el.jstree({
          'core': {
              'multiple': false,
              'data': this.getTreeData.bind(this)
          }
      });
  };

  // eztags tree version setup
  $.EzTags.Tree = $.EzTags.Default.extend({
      setup_events: function(){
          $.EzTags.Default.prototype.setup_events.apply(this, arguments);
          this.$el.parent().on('click', 'a.jstree-anchor:not(.jstree-disabled)', function(e){
              this.add($(e.currentTarget).data());
          }.bind(this));
          this.on('remove:after', this.remove_data_selected.bind(this));
          this.on('add:after', function(e, data){
              this.add_data_selected(data.tag.id);
          }.bind(this));
          this.addTree();
      },

      addTree: function(){
          var tree = new TreeView(this.$el.parent().find('.ez-tags-tree-selector'));
          tree.tags = this;
      },

      // add data attribute to dom element for styling of selected tag in tree
      remove_data_selected: function(e, data){
          this.$el.parent().find('a.jstree-anchor[data-id=' + data.tag.id + ']').removeAttr('data-selected');
      },

      add_data_selected: function(id){
          this.$el.parent().find('a.jstree-anchor[data-id=' + id + ']').attr('data-selected', true);
      }
  });

});
