//vu_alter_user_group_taxonomy submit
$(document).ready(function(){
    $("#vu_augt_button").click(function(){
		var clickBtnValue = $(this).val();
        var ajaxurl ="' . get_template_directory_uri() . '/vu-users-permissions-ajax.php",
		data =  {"action": clickBtnValue,
				 "group": $("vu_augt_group_value").val(),
				 "role": $("#vu_augt_role_select").val()};
        $.post(ajaxurl, data, function (response) {
			// Response div
			$("#vu_augt_return").html(response["vu_augt_return"]);
			alert("response came thru");
        });
    });
});