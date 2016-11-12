/**
 * Drag and Drop Ajax file upload from css-tricks
 */

jQuery(function ($) {

    // click listener for back button in error and result pages
    $(".back").click(function(e) {
        e.preventDefault();

        $(".back").fadeOut(100);
        if($("body").hasClass("success")) {
            $(".result").fadeOut(100);
        }
        else {
            $("#error").fadeOut(100);
        }

        $("body").removeClass("success").removeClass("fail").addClass("upload");
        $("section.upload").delay(100).fadeIn(200);
    });

    // feature detection for drag&drop upload
    var isAdvancedUpload = function () {
        var div = document.createElement('div');
        return ( ( 'draggable' in div ) || ( 'ondragstart' in div && 'ondrop' in div ) ) && 'FormData' in window && 'FileReader' in window;
    }();


    // callback for successfull ajax upload
    var success_callback = function (data) {

        console.log(data);

        if (data.status == "error") {
            // Show error message
            errorPage(data.message);
        }
        else {        	
            // Parse data and show result
            var d = data.data;

            $("#loading").fadeOut();
            $(".result").show();
            $(".result a").attr("href", d.file);
            $("body").removeClass("upload").addClass("success");
            $(".back").fadeIn(2000);

            var initial;
            var elements = [];
            elements.push({'data': {'id': 'start'}});
            $.each(d.states, function (k, v) {

                // Find initial state
                if (v.initial) {
                    initial = v.name;
                }

                var elem = {data: {'id': v.name}};
                if (v.final) {
                    elem["classes"] = 'final';
                }
                elements.push(elem);
            });

            var groups = {};
            $.each(d.transitions, function (k, v) {
                var group = JSON.stringify([v.from, v.to]);
                groups[group] = groups[group] || {source: v.from, target: v.to, elements: []};
                groups[group].elements.push(v);
            });

            elements.push({'data': {'name': '', 'source': 'start', 'target': initial}, 'classes': 'start_edge'});
            $.each(groups, function (gk, gv) {
                var alphabet = undefined;
                $.each(gv.elements, function (v, k) {
                    alphabet = (alphabet == undefined ? k.alphabet : alphabet + ',' + k.alphabet);
                });
                elements.push({'data': {'alphabet': alphabet, 'source': gv.source, 'target': gv.target}});
            });

            window.elems = elements;

            var cy = window.cy = cytoscape(
                {
                    container: $("#cy"),

                    style: [
                        {
                            selector: 'node',
                            style: {
                                'height': 60,
                                'width': 60,
                                'background-color': '#264d36',
                                'color': '#fff',
                                'label': 'data(id)',
                                'text-valign': 'center',
                                'text-halign': 'center'
                            }
                        },

                        {
                            selector: 'edge',
                            style: {
                                'width': 5,
                                'opacity': 0.9,
                                'label': 'data(alphabet)',
                                'line-color': '#1b5132',
                                'color': '#fff',
                                'control-point-step-size': 70,
                                'target-arrow-color': '#1b5132',
                                'target-arrow-shape': 'triangle'
                            }
                        },

                        {
                            selector: '#start',
                            style: {
                                'color': '#163121',
                                'label': 'Start',
                                'background-opacity': 0
                            }
                        },
                        {
                            selector: '.final',
                            style: {
                                'border-width': 7,
                                'border-style': 'double',
                                'border-color': '#163121'
                            }
                        }

                    ],
                    elements: elements,

                    layout: {
                        name: 'grid'
                    }
                }
            );
        }
    };

    function errorPage(message) {
        $("body").removeClass("upload").addClass("fail");
        $("#loading").fadeOut(20);
        $("#error").fadeIn(300);
        $(".back").fadeIn(2000);
        $("#error p.message").text(message);
    }
    
    $('#upload').each(function () {
        var $form = $(this),
            $input = $form.find('input[type="file"]'),
            $label = $form.find('label'),
            droppedFiles = false;

        // letting the server side to know we are going to make an Ajax request
        $form.append('<input type="hidden" name="ajax" value="1" />');

        // automatically submit the form on file select
        $input.on('change', function (e) {
            $form.trigger('submit');
        });


        // drag&drop files if the feature is available
        if (isAdvancedUpload) {
            $form
                .addClass('has-advanced-upload') // letting the CSS part to know drag&drop is supported by the browser
                .on('drag dragstart dragend dragover dragenter dragleave drop', function (e) {
                    // preventing the unwanted behaviours
                    e.preventDefault();
                    e.stopPropagation();
                })
                .on('dragover dragenter', function () //
                {
                    $form.addClass('is-dragover');
                })
                .on('dragleave dragend drop', function () {
                    $form.removeClass('is-dragover');
                })
                .on('drop', function (e) {
                    droppedFiles = e.originalEvent.dataTransfer.files; // the files that were dropped

                    $form.trigger('submit'); // automatically submit the form on file drop
                });
        }


        // if the form was submitted

        $form.on('submit', function (e) {
            // preventing the duplicate submissions if the current one is in progress
            if ($form.hasClass('is-uploading')) return false;

            $("section.upload").fadeOut(300, function () {
                $("#loading").fadeIn(20);
                if (isAdvancedUpload) // ajax file upload for modern browsers
                {
                    e.preventDefault();

                    // gathering the form data
                    var ajaxData = new FormData($form.get(0));
                    if (droppedFiles) {
                        $.each(droppedFiles, function (i, file) {
                            ajaxData.append($input.attr('name'), file);
                        });
                    }

                    // ajax request
                    $.ajax(
                        {
                            url: $form.attr('action'),
                            type: $form.attr('method'),
                            data: ajaxData,
                            dataType: 'json',
                            cache: false,
                            contentType: false,
                            processData: false,
                            complete: function () {
                            },
                            success: function (data) {
                                success_callback(data);
                            },
                            error: function () {
                                errorPage("Error in Contacting Server!");
                            }
                        });
                }
                else // fallback Ajax solution upload for older browsers
                {
                    var iframeName = 'uploadiframe' + new Date().getTime(),
                        $iframe = $('<iframe name="' + iframeName + '" style="display: none;"></iframe>');

                    $('body').append($iframe);
                    $form.attr('target', iframeName);

                    $iframe.one('load', function () {
                        var data = $.parseJSON($iframe.contents().find('body').text());
                        success_callback(data);
                        $form.removeClass('is-uploading').addClass(data.success == true ? 'is-success' : 'is-error').removeAttr('target');
                        if (!data.success) errorPage("Error in Contacting Server!")
                        $iframe.remove();
                    });
                }
            });
        });
    });
});