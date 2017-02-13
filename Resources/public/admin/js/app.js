$.noConflict();

function ngTagsTreeInit(){
    'use strict';

    var $ = jQuery;
    $.each($('div.tags-tree'), function(index, value) {
        if ($(value).hasClass('jstree')) {
            return;
        }

        $(value).jstree({
            'plugins': ["sort", "contextmenu", "ui"],
            'contextmenu': {
                'select_node': false,
                'items': $(value).closest('div.modal-tree').length ? '' : tagTreeContextMenu
            },
            'core': {
                'data': {
                    'url': function (node) {
                        var route = $(value).data('path');
                        var rootTagId = $(value).data('roottagid');

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
            var selectedTagPath = $(value).data('selectedtagpath');
            if (selectedTagPath === '') {
                selectedTagPath = '/0/';
            }

            selectedTagPath = selectedTagPath.replace(/^\//, '').replace(/\/$/, '').split('/');
            $(value).jstree(true).load_node(selectedTagPath, function () {
                var selectedNodeId = selectedTagPath[selectedTagPath.length - 1];
                this.select_node(selectedNodeId);
                if (!$(value).closest('div.modal-tree').length) {
                    $(value).find('a#' + selectedNodeId + '_anchor').addClass('selected');
                }
            });
        }).on("ready.jstree", function (event, data) {
            var disableSubtree = $(value).data('disablesubtree');
            if (disableSubtree !== '') {
                $.each(disableSubtree.toString().split(','), function (index, element) {
                    disableNode(value, element);
                });
            }
        }).on("load_node.jstree", function (event, data) {
            var disableSubtree = $(value).data('disablesubtree');
            if (disableSubtree !== '') {
                if (disableSubtree.toString().split(',').indexOf(data.node.id) !== -1) {
                    disableNode(value, data.node.id);
                }
            }
        }).on('click', '.jstree-anchor', function (event) {
            var selectedNode = $(this).jstree(true).get_node($(this)),
                modalTreeDiv = $(event.target).closest('div.modal-tree'),
                treeDiv = $(event.target).closest('div.tags-tree');

            var disableSubtree = treeDiv.data('disablesubtree');
            if (disableSubtree !== '') {
                disableSubtree = disableSubtree.toString().split(',');

                if (disableSubtree.indexOf(selectedNode.id) !== -1) {
                    return;
                }

                var filteredDisableSubtree = disableSubtree.filter(function(el) {
                    return selectedNode.parents.indexOf(el) !== -1
                });

                if (filteredDisableSubtree.length > 0) {
                    return;
                }
            }

            if (!modalTreeDiv.length) {
                document.location.href = selectedNode.a_attr.href;
            } else {
                $(modalTreeDiv).children('input.tag-id').val(selectedNode.id);

                if (selectedNode.text === undefined || selectedNode.id == '0') {
                    $(modalTreeDiv).children('span.tag-keyword').html($(modalTreeDiv).data('novaluetext'));
                } else {
                    $(modalTreeDiv).children('span.tag-keyword').html(selectedNode.text);
                }

                $(modalTreeDiv).children('div.ng-modal').hide();
            }
        });
    });

    /**
     * Disables the provided node.
     *
     * @param tree
     * @param nodeId
     */
    function disableNode(tree, nodeId) {
        $(tree).find('li#' + nodeId).addClass('disabled');
    }

    /**
     * Builds context menu for right click on a tag in tags tree.
     *
     * @param node
     * */
    function tagTreeContextMenu(node) {
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
    }

    /**
     * Opens modal when modal open button is clicked.
     */
    $('.modal-tree-button').on('click', function(){
        $(this).parent('div.modal-tree').children('div.ng-modal').show();
    });

    /**
     * It closes modal when Close span inside modal is clicked.
     */
    $('.ng-modal').on('click', '.close', function(){
        $(this).closest('div.ng-modal').hide();
    });

    /**
     * It closes modal when user clicks anywhere outside modal window.
     */
    $(window).on('click', function(e) {
        if(e.target.className == 'modal') {
            $(e.target).hide();
        }
    });
}

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


    /* input enabled/disabled buttons */
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
    ngTagsTreeInit();
    ngTagsInit();
});
