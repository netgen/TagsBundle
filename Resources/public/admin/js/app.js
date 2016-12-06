$('document').ready(function () {
    /**
     * This method creates jsTree object on all selected DIVs with appropriate ID.
     */
    $('div[id^=tags-tree-]').jstree({
        'plugins' : [ "sort" ],
        'sort' : function (a, b) {
            return this.get_text(a).toLowerCase() > this.get_text(b).toLowerCase() ? 1 : -1;
        },
        'core' : {
            'data' : {
                'url' : function (node) {
                    var route = $('div[id^=tags-tree-]').data('path');
                    var rootId = $('div[id^=tags-tree-]').data('rootid');

                    return route
                        .replace("_tagId_", node.id)
                        .replace("#", rootId + "/true")
                    ;
                }
            }
        }
    });

    /**
     * This method is called when user clicks on a node in tree.
     * If it's a main tree, it redirects user to a route provided in selected node.
     * Else, it puts selected node's ID in a form field with provided ID.
     * And also it puts selected node's text in a span field with provided ID:
     */
    $('div[id^=tags-tree-]').on(
        'changed.jstree',
        function (event, data) {
            var tagsTreeId = $(event.target).attr('id').replace('tags-tree-', '');

            if (tagsTreeId == 'main') {
                document.location.href = data.instance.get_node(data.selected[0]).a_attr.href;
            }

            else {
                $('#' + $(event.target).data('fieldid')).val(data.instance.get_node(data.selected[0]).id);

                if (data.instance.get_node(data.selected[0]).text == undefined || data.instance.get_node(data.selected[0]).id == '0') {
                    $('#' + $(event.target).data('spanid')).html($(event.target).data('novaluetext'));
                }

                else {
                    $('#' + $(event.target).data('spanid')).html(data.instance.get_node(data.selected[0]).text);
                }

                $('#modal-tree-' + tagsTreeId).hide();
            }
        }
    );

    /**
     * Opens modal when modal open button is clicked.
     */
    $('.modal-tree-button').click(function() {
        var modalTreeId = $(this).data("modaltreeid");

        $('#modal-tree-' + modalTreeId).show();
    });

    /**
     * It closes modal when Close span inside modal is clicked.
     */
    $('.modal .close').click(function() {
        var modalTreeId = $(this).data("modaltreeid");

        $('#modal-tree-' + modalTreeId).hide();
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
