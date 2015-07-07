$(function()
{
    var files;

    $('#torrent_upload_form_files').on('change', prepareUpload);
    $('#torrent_upload_form').on('submit', uploadFiles);


    function prepareUpload(event)
    {
        files = event.target.files;
    }

    function uploadFiles(event)
    {
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
                        $('#torrent_res_div').delay(200).fadeIn(200);
                        $('#t_name').text(data.name);
                        $('#torrent_file_name').val(data.file_name);
                        $('#t_submit_name').val(data.name);
                        $('#t_size').text(data.size);
                        $('#t_comment').text(data.comment);
                        $('#t_submit_comment').val(data.comment);
                        $('#t_hash').text(data.hash);
                        $('#t_pl').text(data.piece_length);
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
});