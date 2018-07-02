//vu_alter_user_group_taxonomy submit
function vu_alter_user_group_taxonomy_submit(){
		var clickBtnValue = $(this).val();
		var spath = document.location.pathname;
		spath = path.split("/").slice(0,-2).join("/"); //go up two directory levels
		spath += "/wp-content/plugins/vu-panels/vu-users-permissions-ajax.php"; //go to function's dir
		data =  {"action": clickBtnValue,
				 "group": $("vu_augt_group_value").val(),
				 "role": $("#vu_augt_role_select").val()};
		console.log(data);
        $.post(spath, data, function (response) {
			// Response div
			$("#vu_augt_return").html(response["vu_augt_return"]);
			alert("response came thru");
        });
  
};

// $(document).ready(function(){
	
//     $("#vu_augt_button").click(function(){
// 		alert ("button clicked");
// 		var clickBtnValue = $(this).val();
// 		var spath = document.location.pathname;
// 		spath = path.split("/").slice(0,-2).join("/"); //go up two directory levels
// 		spath += "/wp-content/plugins/vu-panels/vu-users-permissions-ajax.php"; //go to function's dir
// 		data =  {"action": clickBtnValue,
// 				 "group": $("vu_augt_group_value").val(),
// 				 "role": $("#vu_augt_role_select").val()};
//         $.post(spath, data, function (response) {
// 			// Response div
// 			$("#vu_augt_return").html(response["vu_augt_return"]);
// 			alert("response came thru");
//         });
//     });
// });