$.noConflict();

(function($){
    'use strict';
    /* options for tagsTree plugin
        'modal' - boolean - is the tagsTree opened in modal window (default false)
        'treeClassName' - string - class name for div on which jstree is initialized (default 'tags-tree')
        'modalClassName' - string - class name for modal div in which tagsTree are opened (default 'ng-modal')
    */
    var TagsTree = function(el, options){
        this.settings = $.extend({
            'modal': false,
            'treeClassName': 'tags-tree',
            'modalClassName': 'ng-modal'
        }, options);

        this.$el = $(el);
        this.$tree = this.$el.find('.' + this.settings.treeClassName);
        this.disableSubtree = this.$tree.data('disablesubtree');

        if (this.settings.modal){
            this.$modal = this.$el.find('.' + this.settings.modalClassName);
            this.modalInit();
        }

        this.treeInit();
    };

    TagsTree.prototype.treeInit = function(){
        var self = this;
        this.$tree.jstree({
            'plugins': ["sort", "contextmenu", "ui"],
            'contextmenu': {
                'select_node': false,
                'items': self.settings.modal ? '' : self.tagTreeContextMenu
            },
            'core': {
                'data': {
                    'url': function (node) {
                        var route = self.$tree.data('path');
                        var rootTagId = self.$tree.data('roottagid');

                        return route
                            .replace("_tagId_", node.id)
                            .replace("#", rootTagId + "/true");
                    }
                },
                'themes': {
                    'name': 'ng-tags'
                }
            }
        }).on("ready.jstree", function (event, data) {
            var selectedTagPath = self.$tree.data('selectedtagpath');
            if (selectedTagPath === '') {
                selectedTagPath = '/0/';
            }

            selectedTagPath = selectedTagPath.replace(/^\//, '').replace(/\/$/, '').split('/');
            self.$tree.jstree(true).load_node(selectedTagPath, function () {
                var selectedNodeId = selectedTagPath[selectedTagPath.length - 1];
                this.select_node(selectedNodeId);
                if (!self.settings.modal) {
                    self.$tree.find('a#' + selectedNodeId + '_anchor').addClass('selected');
                }
            });
        }).on("ready.jstree", function (event, data) {
            if (self.disableSubtree !== '') {
                $.each(self.disableSubtree.toString().split(','), function (index, element) {
                    self.disableNode(element);
                });
            }
        }).on("load_node.jstree", function (event, data) {
            if (self.disableSubtree !== '') {
                if (self.disableSubtree.toString().split(',').indexOf(data.node.id) !== -1) {
                    self.disableNode(data.node.id);
                }
            }
        }).on("open_node.jstree", function (event, data) {
            if (self.disableSubtree !== '') {
                self.disableNode(self.disableSubtree);
            }
        }).on('click', '.jstree-anchor', function (event) {
            var selectedNode = $(this).jstree(true).get_node($(this));

            if (self.disableSubtree !== '') {
                self.disableSubtree = self.disableSubtree.toString().split(',');

                if (self.disableSubtree.indexOf(selectedNode.id) !== -1) {
                    return;
                }

                var filteredDisableSubtree = self.disableSubtree.filter(function(el) {
                    return selectedNode.parents.indexOf(el) !== -1
                });

                if (filteredDisableSubtree.length > 0) {
                    return;
                }
            }

            if (!self.settings.modal) {
                document.location.href = selectedNode.a_attr.href;
            } else {
                self.$el.find('input.tag-id').val(selectedNode.id);

                if (selectedNode.text === undefined || selectedNode.id == '0') {
                    self.$el.find('span.tag-keyword').html(self.$el.data('novaluetext'));
                } else {
                    self.$el.find('span.tag-keyword').html(selectedNode.text);
                }

                self.closeModal();
            }
        });
    }

    /** Disables the provided node.
        * @param nodeId */
    TagsTree.prototype.disableNode = function(nodeId) {
        this.$tree.find('li#' + nodeId).addClass('disabled');
    };

    /** Builds context menu for right click on a tag in tags tree.
        * @param node */
    TagsTree.prototype.tagTreeContextMenu = function(node) {
        var menu = {};

        node.data.context_menu.forEach(function(item){
            menu[item.name] = {
                "label": item.text,
                "action": function () {
                    window.location.href = item.url;
                }
            };
        });

        return menu;
    };

    TagsTree.prototype.modalInit = function(){
        /** Opens modal when modal open button is clicked. */
        var self = this;
        this.$el.on('click', '.modal-tree-button', function(){
            self.openModal();
        });

        /** It closes modal when Close span inside modal is clicked. */
        this.$modal.on('click', '.close', function(){
            self.closeModal();
        });

        /** It closes modal when user clicks anywhere outside modal window. */
        $(window).on('click', function(e) {
            if(e.target.className === self.settings.modalClassName) {
                self.closeModal();
            }
        });
    };
    TagsTree.prototype.openModal = function(){
        if (!this.settings.modal) return;
        this.$modal.show();
    };
    TagsTree.prototype.closeModal = function(){
        if (!this.settings.modal) return;
        this.$modal.hide();
    };

    /* register tagsTree jQuery plugin */
    $.fn.tagsTree = function (options) {
        return this.each(function(){
            if ($(this).data('tagsTree')) return;
            var instance = new TagsTree(this, options);
            $(this).data('tagsTree', instance);
        });
    };


    /* resizable plugin */
    function TagsResize(el, options){
        this.el = el;
        this.$el = $(el);
        this.settings = $.extend({
            'connectWith': 0,
            'minWidth': 140
        }, options);
        this.$handle = $('<div class="tags-resizable-handle" />');

        this.init();
    }
    TagsResize.prototype.init = function(){
        if(this.settings.connectWith){
            this.$connected = $(this.settings.connectWith);
        }
        this.initialResize();
        this.$el.addClass('tags-resizable').append(this.$handle);
        this.setupEvents();
    };
    TagsResize.prototype.setupEvents = function(){
        this.$handle.on('mousedown', function(e){
            e.preventDefault();
            $('body').addClass('tags-resizing');
            $(window).on('mousemove.resize', function(e){
                this.resizeEl(e.pageX - this.el.offsetLeft);
            }.bind(this));
            $(window).one('mouseup', function(e){
                if(!$('body').hasClass('tags-resizing')) return;
                $(window).off('mousemove.resize');
                $('body').removeClass('tags-resizing');
                window.sessionStorage.tagsResize = this.el.offsetWidth;
            }.bind(this));
        }.bind(this));
    };
    TagsResize.prototype.initialResize = function(){
        var startWidth = window.sessionStorage.tagsResize || this.el.offsetWidth;
        if(startWidth < this.settings.minWidth) {
            this.resizeEl(this.settings.minWidth);
        } else if (window.sessionStorage.tagsResize){
            this.resizeEl(window.sessionStorage.tagsResize);
        }
    };
    TagsResize.prototype.resizeEl = function(newWidth){
        if(newWidth < this.settings.minWidth) return;
        this.$el.css('flex', '0 0 ' + newWidth + 'px');
        if(this.settings.connectWith && this.$connected.length){
            this.$connected.outerWidth(newWidth);
        }
    };
    /* register tagsResize jQuery plugin */
    $.fn.tagsResize = function (options) {
        return this.each(function(){
            if ($(this).data('tagsResize')) return;
            var instance = new TagsResize(this, options);
            $(this).data('tagsResize', instance);
        });
    };

})(jQuery);

function ngTagsInit(){
    'use strict';

    var $ = jQuery;

    /* button click effect */
    function TagsBtn(el){
        this.$el = $(el);
        this.$effect = $('<span class="tags-btn-effect">');
        this.init();
    }
    TagsBtn.prototype.init = function(){
        this.setupEvents();
    };
    TagsBtn.prototype.setupEvents = function(){
        this.$el.on('click', function(e){
            if(e.currentTarget.attributes.disabled) e.preventDefault();
        });
        this.$el.on('mousedown', function(e){
            this.$effect.detach();
            this.addEffect(e);
        }.bind(this));
        this.$effect.on('animationend', function(){
            $(this).detach();
        });
    };
    TagsBtn.prototype.addEffect = function(e){
        this.$effect.css(this.calcPos(e));
        this.$el.append(this.$effect);
    };
    TagsBtn.prototype.calcPos = function(e){
        var btnOffset = this.$el.offset(),
            elWidth = this.$el.outerWidth(),
            rel = {
                x: e.pageX - btnOffset.left,
                y: e.pageY - btnOffset.top
            },
            effectWidth = rel.x <= (elWidth/2) ? (elWidth - rel.x) * 2.4 : rel.x * 2.4;
        this.effectCss = {
            'left': rel.x,
            'top': rel.y,
            'width': effectWidth,
            'height': effectWidth
        };
        return this.effectCss;
    };
    $.fn.tagsBtn = function () {
        return $(this).each(function(){
            var $this = $(this);
            if ($this.data('tagsbtn')){
                return;
            }
            var instance = new TagsBtn(this);
            $this.data('tagsbtn', instance);
        });
    };

    $('.tags-btn').tagsBtn();

    /* tabs */
    $.fn.tagsTabs = function(){
        return this.each(function(){
            if(!$(this).data('tagsTabs')){
                var $el = $(this),
                    controls = $el.find('.tags-tab-control'),
                    $initialTab = $el.find('.tags-tab-control[href="' + localStorage.tagsTabActive + '"]'),
                    toggleActive = function(trigger, tab){
                        trigger.addClass('active').siblings().removeClass('active');
                        tab.addClass('active').siblings().removeClass('active');
                    };
                $(this).data('tagsTabs', 'true');
                $(this).on('click', '.tags-tab-control', function(e){
                    e.preventDefault();
                });
                $(this).on('mousedown', '.tags-tab-control', function(e){
                    var target = this.getAttribute('href');
                    localStorage.tagsTabActive = target;
                    toggleActive($(this).closest('li'), $('[data-tab="' + target + '"]'));
                });
                if($initialTab.length){
                    toggleActive($initialTab.closest('li'), $('[data-tab="' + localStorage.tagsTabActive + '"]'));
                } else {
                    toggleActive($(controls[0]).closest('li'), $('[data-tab="' + $(controls[0]).attr('href') + '"]'));
                }
            }
        });
    };

    $('.tags-tabs').tagsTabs();
    $('.tags-modal-tree').tagsTree({'modal': true});
    $('.tags-tree-wrapper').tagsTree();
    $('.tags-sidebar-resizable').tagsResize({connectWith: '.ng-tags-logo'});

    /* input enabled/disabled buttons */
    ngTagsInit.handleButtons($);
}

ngTagsInit.handleButtons = function ($) {
    var $enabledInputs = $('input[data-enable]'),
        enabledInputsGroups = Array.from($enabledInputs).reduce(function(arr, item) {
        var name = item.dataset.enable;
        if (arr.indexOf(name) == -1) arr.push(name);
        return arr;
    }, []);
    enabledInputsGroups.forEach(function(name){
        if($('input[type="checkbox"][data-enable="' + name + '"]:checked').length) return;
        $('[data-enabler="' + name + '"]').attr('disabled', 'disabled');
    });
    $enabledInputs.on('change', function(e){
        var name = e.currentTarget.dataset.enable;
        $('input[data-enable="' + name + '"]:checked').length ? $('[data-enabler="' + name + '"]').removeAttr('disabled') : $('[data-enabler="' + name + '"]').attr('disabled', 'disabled');
    });
}

jQuery(document).ready(function($) {
    ngTagsInit();
});

// only disable/enable buttons, as the tree/tags needs further work.
document.addEventListener('ez:app:updated', ngTagsInit.handleButtons.bind(this, jQuery));
