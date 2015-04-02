<?php

if(is_admin() !== false) {

?>

 <div class="modal fade bs-example-modal-sm" id="create-group" tabindex="-1" role="dialog" aria-labelledby="Create" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h5 class="modal-title" id="Create">Alert Group</h5>
        </div>
        <div class="modal-body">
            <form method="post" role="form" id="groups" class="form-horizontal group-form">
            <input type="hidden" name="group_id" id="group_id" value="">
            <input type="hidden" name="type" id="type" value="create-alert-group">
            <div class="form-group">
                <label for='name' class='col-sm-3 control-label'>Name: </label>
                <div class="col-sm-5">
                        <input type='text' name='name' id='name' class='form-control'/>
                </div>
            </div>
            <div class="form-group">
                <label for='desc' class='col-sm-3 control-label'>Description: </label>
                <div class="col-sm-5">
                        <input type='text' id='desc' name='desc' class='form-control'/>
                </div>
            </div>
            <div class="form-group">
                <label for='add-dev' class='col-sm-3 control-label'>Add Device: </label>
                <div class="col-sm-5">
                        <input type='text' id='add-dev' class='form-control'/>
                </div>
                <div class="col-sm-3">
                        <button class="btn btn-primary btn-sm" type="button" name="add-device" id="add-device" value="Add">Add</button>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <span id="response"></span>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-3">
                        <button class="btn btn-default btn-sm" type="submit" name="group-submit" id="group-submit" value="save">Save Group</button>
                </div>
            </div>
            </form>
        </div>
      </div>
    </div>
</div>

<script>

$('#create-group').on('hide.bs.modal', function (event) {
    $('#response').data('tagmanager').empty();
});

$('#add-device').click('',function (event) {
	$('#response').data('tagmanager').populate([ $('#add-dev').val() ]);
	$('#add-dev').val('');
});

$('#create-group').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var group_id = button.data('group_id');
    var modal = $(this)
    $('#group_id').val(group_id);
    $('#tagmanager').tagmanager();
    $('#response').tagmanager({
           strategy: 'array',
           tagFieldName: 'devices[]',
           initialCap: false
    });
    $.ajax({
        type: "POST",
        url: "/ajax_form.php",
        data: { type: "parse-alert-group", group_id: group_id },
        dataType: "json",
        success: function(output) {
            $('#response').data('tagmanager').populate(output['devices']);
            $('#name').val(output['name']);
            $('#desc').val(output['desc']);
        }
    });
});
var cache = {};
$('#add-dev').typeahead([
    {
      name: 'suggestion',
      remote : '/ajax_search.php?search=%QUERY&type=device',
      template: '{{name}}',
      valueKey:"name",
      engine: Hogan
    }
]);

$('#group-submit').click('', function(e) {
    e.preventDefault();
    $.ajax({
        type: "POST",
        url: "/ajax_form.php",
        data: $('form.group-form').serialize(),
        success: function(msg){
            $("#message").html('<div class="alert alert-info">'+msg+'</div>');
            $("#create-group").modal('hide');
            if(msg.indexOf("ERROR:") <= -1) {
                $('#response').data('tagmanager').empty();
                setTimeout(function() {
                    location.reload(1);
                }, 2000);
            }
        },
        error: function(){
            $("#message").html('<div class="alert alert-info">An error occurred creating this group.</div>');
            $("#create-group").modal('hide');
        }
    });
});

</script>

<?php

}

?>
