$('document').ready(function () {
    $('div[id^=tags-tree-]').jstree({
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
        },
    });

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

    $('.modal-tree-button').click(function() {
        var modalTreeId = $(this).data("modaltreeid");

        $('#modal-tree-' + modalTreeId).show();
    });

    $('.modal .close').click(function() {
        var modalTreeId = $(this).data("modaltreeid");

        $('#modal-tree-' + modalTreeId).hide();
    });

    $(window).click(function(event) {
        if($(event.target).attr('class') == 'modal') {
            $(event.target).hide();
        }
    });
});
