$(function()
{
    var files;

    $('#torrent_upload_form_files').on('change', prepareUpload);
    $('#torrent_upload_form').on('submit', uploadFiles);
    $('#magnet_torrent_upload_form').on('submit', sendMagnet);

    $('#frm_fetch').on('submit', fetchFiles);

    $('#fetch_filter').on('input',function(e){
        if ($('#fetch_filter').val() == ''){
            $('#empty_filter').hide();
        }else{
            $('#empty_filter').show();
            $('#f_help').text($('#fetch_filter').val());
        }
    });


    function prepareUpload(event)
    {
        files = event.target.files;
    }

    function sendMagnet(event){
        event.stopPropagation();
        event.preventDefault();

        var btn = $('#magnet_torrent_upload_form_submit').prop('disabled', true);
        btn.html('<i class="fa fa-refresh fa-spin"></i> Wait A Little...');

        $.ajax({
            url: '',
            type: 'POST',
            data: 'type=magnet&' + $('#magnet_torrent_upload_form').serialize(),
            dataType: 'json',
            success: function(data)
            {
                if(typeof data.error === 'undefined')
                {
                    if (data.result == 'error') {
                        btn.prop('disabled', false);
                        btn.html('<i class="fa fa fa-check"></i> Get Info');
                        toastr["error"](data.message, "Oh Snap!");
                    }else if (data.result == 'ok'){
                        btn.html('<i class="fa fa fa-check"></i> Done!');
                        $('#torrent_div').fadeOut(200);
                        $('#magnet').fadeOut(200);
                        $('#magnet_torrent_tab').fadeOut(200);
                        $('#torrent_res_div').delay(200).fadeIn(200);
                        $('#t_name').text(data.name);
                        $('#torrent_file_name').val(data.file_name);
                        $('#t_submit_name').val(data.name);
                        $('#t_size').text(data.size);
                        $('#t_comment').text(data.comment);
                        $('#t_submit_comment').val(data.comment);
                        $('#t_hash').text(data.hash);
                        $('#t_pl').text(data.piece_length);
                        setTimeout(function() {$('#t_submit_name').select();},200);
                        $('#jstree_demo_div').jstree($.parseJSON( data.content))
                    }else {
                        btn.prop('disabled', false);
                        btn.html('<i class="fa fa fa-check"></i> Get Info');
                        toastr["error"]("Unknown error occured. Please refresh the page.", "Oh Snap!");
                    }
                }
                else
                {
                    btn.prop('disabled', false);
                    btn.html('<i class="fa fa fa-check"></i> Get Info');
                    toastr["error"]("Unknown error occured.", "Oh Snap!");
                    console.log('ERRORS: ' + data.error);
                }
            },
            error: function(jqXHR, textStatus, errorThrown)
            {
                toastr["error"]("Could not upload your file.", "Oh Snap!");
                console.log('ERRORS: ' + textStatus);
                btn.prop('disabled', false);
                btn.html('<i class="fa fa fa-check"></i> Get Info');
            }
        });

    }

    function uploadFiles(event) {
        event.stopPropagation();
        event.preventDefault();

        var btn = $('#torrent_upload_form_submit').prop('disabled', true);
        btn.html('<i class="fa fa-refresh fa-spin"></i> Uploading...');

        var data = new FormData();
        $.each(files, function(key, value)
        {
            data.append(key, value);
        });

        var _token =  $('#torrent_upload_form input[name=_token]').val();
        data.append("_token", _token);
        data.append("type", "torrent");

        $.ajax({
            url: '?files',
            type: 'POST',
            data: data,
            cache: false,
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function(data, textStatus, jqXHR)
            {
                if(typeof data.error === 'undefined')
                {
                    if (data.result == 'error') {
                        btn.prop('disabled', false);
                        btn.html('<i class="fa fa fa-check"></i> Get Info');
                        toastr["error"](data.message, "Oh Snap!");
                    }else if (data.result == 'ok'){
                        btn.html('<i class="fa fa fa-check"></i> Uploaded!');
                        $('#torrent_div').fadeOut(200);
                        $('#magnet_torrent_tab').fadeOut(200);
                        $('#torrent_res_div').delay(200).fadeIn(200);
                        $('#t_name').text(data.name);
                        $('#torrent_file_name').val(data.file_name);
                        $('#t_submit_name').val(data.name);
                        $('#t_size').text(data.size);
                        $('#t_comment').text(data.comment);
                        $('#t_submit_comment').val(data.comment);
                        $('#t_hash').text(data.hash);
                        $('#t_pl').text(data.piece_length);
                        setTimeout(function() {$('#t_submit_name').select();},200);
                        $('#jstree_demo_div').jstree($.parseJSON( data.content))
                    }else {
                        btn.prop('disabled', false);
                        btn.html('<i class="fa fa fa-check"></i> Get Info');
                        toastr["error"]("Unknown error occured. Please refresh the page.", "Oh Snap!");
                    }
                }
                else
                {
                    btn.prop('disabled', false);
                    btn.html('<i class="fa fa fa-check"></i> Get Info');
                    toastr["error"]("Unknown error occured.", "Oh Snap!");
                    console.log('ERRORS: ' + data.error);
                }
            },
            error: function(jqXHR, textStatus, errorThrown)
            {
                toastr["error"]("Could not upload your file.", "Oh Snap!");
                console.log('ERRORS: ' + textStatus);
                btn.prop('disabled', false);
                btn.html('<i class="fa fa fa-check"></i> Get Info');
            }
        });
    }

    function fetchFiles(event) {
        event.preventDefault();

        var btnhtml = $('#fetch_submit').html();
        var btn = $('#fetch_submit').prop('disabled', true);
        btn.html('<i class="fa fa-refresh fa-spin"></i> Fetching...');

        $.ajax({
            url: "",
            type: "POST",
            data: $("#frm_fetch").serialize() + '&type=fetch',
            dataType: 'json',

            success: function (response) {
                if (response.result == 'error') {
                    toastr["error"](response.message, "Oh Snap!");
                } else {
                    var ln = $('#links').val('');
                    $.each(response.links, function(key, val) {
                        ln.val(ln.val() +  val + '\n');
                    });

                }
                btn.html(btnhtml);
                btn.prop('disabled', false);
            },

            error: function (jqXHR, textStatus, errorThrown) {
                toastr["error"]("Unknow error.", "Oh Snap!");
                btn.html(btnhtml);
                btn.prop('disabled', false);
            }
        });
    }



});