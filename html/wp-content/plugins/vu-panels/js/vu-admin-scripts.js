var Debugger = function (gState, klass){
	this.debug = {}
	if (!window.console) return function(){}
	if (gState && klass.isDebug) {
	  for (var m in console)
		if (typeof console[m] == 'function')
		  this.debug[m] = console[m].bind(window.console, klass.toString()+": ")
	}else{
	  for (var m in console)
		if (typeof console[m] == 'function')
		  this.debug[m] = function(){}
	}
	return this.debug
  }
  isDebug = typeof ajax_object.IS_WP_DEBUG !== "undefined" ? ajax_object.IS_WP_DEBUG : true //global debug state

  // we instantiate with the global switch and a ref to this for the local 
  // this must have it's own isDebug defined for local control
  debug = Debugger(isDebug, this)

//vu_alter_user_group_taxonomy submit
function vu_alter_user_group_taxonomy_submit(){
	var spath = ajax_object.ajax_url;
	data =  {"action": "vu_alter_user_group_taxonomy_process_request",
				"group": $("#vu_augt_group_field").val(),
				"role": $("#vu_augt_role_select").val(),
				"vu_augt_nonce": $("#vu_augt_nonce").val()};
	debug.log(data);
	debug.log("vu_alter_user_group_taxonomy_submit\n", JSON.stringify(data), "\n"+spath);
	$.post(spath, data, function (response) {
		// Response div
		$("#vu_augt_return").html(response.replace(/\\n/g, "<br />")); //replace \n's with <br>'s so they print properly
	});
};

//spath = spath.split("/").slice(0,-2).join("/"); //go up two directory levels