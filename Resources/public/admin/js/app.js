$('document').ready(function () {
    /**
     * This method creates jsTree object for each DIV element with appropriate ID prefix.
     */
    var tagsTreeContainers = $('div.ng-tags-app div.tags-tree');

    $.each(tagsTreeContainers, function(index, value) {
        $(value).jstree({
            'plugins': ["sort", "contextmenu", "ui"],
            'sort': function (a, b) {
                return this.get_text(a).toLowerCase() > this.get_text(b).toLowerCase() ? 1 : -1;
            },
            'contextmenu': {
                'select_node': false,
                'items': $(value).parents('div.modal-tree').length == 0 ? tagTreeContextMenu : ''
            },
            'core': {
                'data': {
                    'url': function (node) {
                        var route = $(value).data('path');
                        var rootTagId = $(value).data('roottagid');

                        return route
                            .replace("_tagId_", node.id)
                            .replace("#", rootTagId + "/true")
                            ;
                    }
                },
                'themes': {
                    'name': 'ng-tags'
                }
            }
        }).bind("ready.jstree", function (event, data) {
            if ($(value).data('selectedtagid') !== undefined) {
                if ($(value).data('selectedtagid') === 0) {
                    $(value).jstree(true).select_node(0);
                }

                else {
                    $.getJSON('/tags/admin/tree/parents/' + $(value).data('selectedtagid'), {}, function (data) {
                        $(value).jstree(true).load_node(data, function () {
                            this.select_node($(value).data('selectedtagid'));
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
        var menu = {
            addChild: {
                "label": node.data.add_child.text,
                "action": function () {
                    window.location.href = node.data.add_child.url;
                }
            }
        };

        if(node.parent != '#') {
            menu.editTag = {
                "label": node.data.update_tag.text,
                "action": function () {
                    window.location.href = node.data.update_tag.url;
                }
            };

            menu.deleteTag = {
                "label": node.data.delete_tag.text,
                "action": function () {
                    window.location.href = node.data.delete_tag.url;
                }
            };

            menu.mergeTag = {
                "label": node.data.merge_tag.text,
                "action": function () {
                    window.location.href = node.data.merge_tag.url;
                }
            };

            menu.addSynonym = {
                "separator_before": true,
                "label": node.data.add_synonym.text,
                "action": function () {
                    window.location.href = node.data.add_synonym.url;
                }
            };

            menu.convertTag = {
                "label": node.data.convert_tag.text,
                "action": function () {
                    window.location.href = node.data.convert_tag.url;
                }
            }
        }

        return menu;
    }

    /**
     * This method is called when user clicks on a node in tree.
     * If it's a main tree, it redirects user to a route provided in selected node.
     * Else, it puts selected node's ID in a form field with provided ID.
     * And also it puts selected node's text in a span field with provided ID:
     */
    $('div.ng-tags-app div.tags-tree').on(
        'click',
        '.jstree-anchor',
        function (event) {
            var selectedNode = $(this).jstree(true).get_node($(this));

            if ($(event.target).parents('div.modal-tree').length == 0) {
                document.location.href = selectedNode.a_attr.href;
            }

            else {
                var modalTreeDiv = $(event.target).parents('div.modal-tree');

                $(modalTreeDiv).children('input[type=hidden]').val(selectedNode.id);

                if (selectedNode.text == undefined || selectedNode.id == '0') {
                    $(modalTreeDiv).children('span.tag-keyword').html($(modalTreeDiv).data('novaluetext'));
                }

                else {
                    $(modalTreeDiv).children('span.tag-keyword').html(selectedNode.text);
                }

                $(modalTreeDiv).children('div.modal').hide();
            }
        }
    );

    /**
     * Opens modal when modal open button is clicked.
     */
    $('div.ng-tags-app div.modal-tree input.modal-tree-button').click(function() {
        $(this).parent('div.modal-tree').children('div.modal').show();
    });

    /**
     * It closes modal when Close span inside modal is clicked.
     */
    $('div.ng-tags-app div.modal-tree span.close').click(function() {
        $(this).parents('div.modal-tree').children('div.modal').hide();
    });

    /**
     * It closes modal when user clicks anywhere outside modal window.
     */
    $(window).click(function(event) {
        if($(event.target).attr('class') == 'modal') {
            $(event.target).hide();
        }
    });
});
