//vu_alter_user_group_taxonomy submit
function vu_alter_user_group_taxonomy_submit(){
		var spath = ajax_object.ajax_url;
		data =  {"action": "vu_alter_user_group_taxonomy_process_request",
				 "group": $("#vu_augt_group_field").val(),
				 "role": $("#vu_augt_role_select").val(),
				 "vu_augt_nonce": $("#vu_augt_nonce").val()};
		console.log(data);
		console.log("vu_alter_user_group_taxonomy_submit\n", JSON.stringify(data), "\n"+spath);
        $.post(spath, data, function (response) {
			// Response div
			$("#vu_augt_return").html(response.replace("\n","<br />"));
        });
  
};

// $(document).ready(function(){
	
//     $("#vu_augt_button").click(function(){
// 		alert ("button clicked");
// 		var clickBtnValue = $(this).val();
// 		var spath = document.location.pathname; //get current path
// 		spath = spath.split("/").slice(0,-2).join("/"); //go up two directory levels
// 		spath += "/wp-content/plugins/vu-panels/vu-users-permissions-ajax.php"; //go to target function's dir
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