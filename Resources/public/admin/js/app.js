$.noConflict();

function ngTagsInit(){
    'use strict';
    /**
     * This method creates jsTree object for each DIV element with appropriate ID prefix.
     */
    var $ = jQuery;
    var tagsTreeContainers = $('div.tags-tree');

    $.each(tagsTreeContainers, function(index, value) {
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
        }).bind("ready.jstree", function (event, data) {
            var selectedTagId = $(value).data('selectedtagid');
            if (selectedTagId !== undefined) {
                if (selectedTagId === 0 || selectedTagId === '') {
                    $(value).jstree(true).select_node(0);
                } else {
                    $.getJSON('/tags/admin/tree/parents/' + selectedTagId, {}, function (data) {
                        $(value).jstree(true).load_node(data, function () {
                            this.select_node(selectedTagId);
                        });
                    });
                }
            }
        });
    });

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
     * This method is called when user clicks on a node in tree.
     * If it's a main tree, it redirects user to a route provided in selected node.
     * Else, it puts selected node's ID in a form field with provided ID.
     * And also it puts selected node's text in a span field with provided ID:
     */
    tagsTreeContainers.on('click', '.jstree-anchor', function (event) {
        var selectedNode = $(this).jstree(true).get_node($(this)),
            modalTreeDiv = $(event.target).closest('div.modal-tree');

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
    ngTagsInit();
});
