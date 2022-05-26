var updateFeat = false;
var originalFeatName = "";
var hiddenEnabled = [];
var highlightEnabled = [];
var skipEnabled = [];
var trainingNames = [];
var featNames = [];
var itemNames = [];

// detect if we're on a touchscreen
var is_mobile = $('#is_mobile').css('display')=='none';

// disable form submit on 'enter' key
$('#user_form').on('keyup keypress', function(e) {
  var keyCode = e.keyCode || e.which;
  if (keyCode === 13) { 
    e.preventDefault();
    return false;
  }
});

// anchor link dropdown
$("#anchor_links").on("change", function(){
	if ($(this).val() != "") {
		$('html,body').animate({scrollTop: $($(this).val()).offset().top},'slow');
		$(this).val("");
	}
});

// enable hidden number input functions
enableHiddenNumbers();

// user navigation
$("#user_select").on("change", function(){
	if ($(this).val() == "") {
		window.location.href = "/";
	} else {
		window.location.href = "/?user="+$(this).val();
	}
});

// highlight label on input click
enableHighlighting();

// don't allow ':' character in training input - used to parse value on backend
$("#training_name").on('keypress', function (event) {
	var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
	if (key == ":") {
		event.preventDefault();
	return false;
	}
});

// focus on inputs on modal open
$("#new_training_modal").on('shown.bs.modal', function(){
	$("#training_name").focus();
});
$("#new_weapon_modal").on('shown.bs.modal', function(){
	$("#weapon_name").focus();
});
$("#new_protection_modal").on('shown.bs.modal', function(){
	$("#protection_name").focus();
});
$("#new_healing_modal").on('shown.bs.modal', function(){
	$("#healing_name").focus();
});
$("#new_misc_modal").on('shown.bs.modal', function(){
	$("#misc_name").focus();
});
$("#new_password_modal").on('shown.bs.modal', function(){
	$("#new_password").focus();
});
$("#password_modal").on('shown.bs.modal', function(){
	$("#password").focus();
});

// on modal shown, update modal title and clear inputs
$("#new_feat_modal").on('shown.bs.modal', function(){
	$("#feat_name").focus();
	$("#feat_modal_title").html(updateFeat ? "Update Feat" : "New Feat");
	if (!updateFeat) {
		$("#feat_name").val("");
		$("#feat_description").val("");
	}
	updateFeat = false;
});

// enable / disable password submit btn
$("#new_password").on("keypress", function(){
	$("#password_btn").attr("disabled", $("#new_password").val() == "" || $("#password_conf").val() == "");
});
$("#password_conf").on("keypress", function(){
	$("#password_btn").attr("disabled", $("#new_password").val() == "" || $("#password_conf").val() == "");
});

$("input[name='slacker']").on("change", function(){
	$("#password_btn_2").attr("disabled", false);
});

// on weapon-name input focus, show other weapon inputs
$(".weapon-name").each(function(){
	$(this).on("focus", function(){
		if ($("#"+$(this).attr("name")).is(":visible") && $("#"+$(this).attr("name")).hasClass("glyphicon-chevron-down")) {
			$("#"+$(this).attr("name")).trigger("click");
		}
	});
});

// hide / show weapon inputs
function toggleWeapon(weapon, element) {
	var visible = $(element).hasClass('glyphicon-chevron-up');
	$(element).removeClass(visible ? "glyphicon-chevron-up" : "glyphicon-chevron-down");
	$(element).addClass(visible ? "glyphicon-chevron-down" : "glyphicon-chevron-up");
	// TODO mobile - on chevron click - followed by input click - screen doesn't scroll to input
	// $(element).blur();
	$("#"+weapon+"_container").slideToggle();
}

function formSubmit() {
	// make sure name isn't blank
	if ($("#character_name").val() == "") {
		alert("Please provide a name for your character. A character without a name is like a crab without a carapace.");
		return;
	}
	if ($("#user_id").val() == "") {
		// new user, set password
		$("#new_password_modal").modal("show");
	} else {
		// prompt for password
		$("#password_modal").modal("show");
	}
}

// validate password via ajax
function validatePassword() {
	$.ajax({
	  url: 'check_password.php',
	  data: { 'password' : $("#password").val(), 'user_id' : $("#user_id").val() },
	  ContentType: "application/json",
	  type: 'POST',
	  success: function(response){
	  	// on response success, submit form
	  	if (response == 1) {
	  		// close modal
	  		$("#password_modal").modal("hide");
	  		// submit form
				$("#user_form").submit();
	  	} else {
	  		alert("Password does not match our records");
	  	}
	  }
	});
}

// validate the form
function setPassword() {
	// make sure passwords match
	if ($("#new_password").val() != $("#password_conf").val()) {
		alert("Passwords must match");
	// make sure decoy input hasn't been filled
	} else if ($("#duckdacoy").val() != "") {
		alert("You look more like a robot than a flesh sack");
	// make sure human response is correct
	} else if ($("#nerd_test").val().toLowerCase() != "gygax") {
		alert("You're either a robot or a sorry excuse for a nerd. Either way, bugger off.");
	} else {
		// show submitting message
		$("#submit_load_modal").modal("show");
		// get recaptcha token before submit
		grecaptcha.ready(function () {
			grecaptcha.execute('6Lc_NB8gAAAAAF4AG63WRUpkeci_CWPoX75cS8Yi', { action: 'new_user' }).then(function (token) {
				$("#recaptcha_response").val(token);
				// $("#new_password_modal").modal("hide");
				// $("#new_password_modal_2").modal("show");
				$("#password_val").val($("#new_password").val());
				$("#user_form").submit();
			});
		});
	}
}

// check user selected value
function setPassword2() {
	var selected = $('input[name="slacker"]:checked').val();
	if (selected == 1) {
		alert("Yeah ok well don't come crying to me when you can't update your bag of holding. Nerd.");
	} else if (selected == 2) {
		alert("Oh yeah, so what's your password then? Crab? It's crab isn't it? That's fine I guess.");
	} else if (selected == 3) {
		alert("Ok you seem pretty responsible so I'll give you the benefit of the doubt on this one.")
	} else {
		alert("I can't tell if you're being saracastic, but whatever.");
	}
	$("#password_val").val($("#new_password").val());
	$("#user_form").submit();
}

function forgotPassword(){
	alert("That's a shame. You probably should've written it down or something.");
}

// add a new feat from modal values
function newFeat() {
	var featName = $("#feat_name").val();
	$("#feat_name").val("");
	var featDescription = $("#feat_description").val()+" ";
	$("#feat_description").val("");
	if (featName != "" && featDescription != " ") {
		addFeatElements(featName, featDescription);
	}
}

// create html elements for feat
function addFeatElements(featName, featDescription) {
	var feat_name = featName.replaceAll(' ', '_');

	// new or updating?
	if ($("#feat_modal_title").html() == "Update Feat") {

		// update feat name and description
		$("#"+originalFeatName.replaceAll(" ", "_")+"_name").html(featName+" : ");
		$("#"+originalFeatName.replaceAll(" ", "_")+"_descrip").html(featDescription);

		// append edit button
	    $('<span />', {
	    	'id': feat_name+"_edit",
	      'class': 'glyphicon glyphicon-edit hover-hide',
	    }).appendTo("#"+originalFeatName.replaceAll(" ", "_")+"_descrip");

	    $('<span />', {
	    	'id': feat_name+"_remove",
	      'class': 'glyphicon glyphicon-remove hover-hide',
	    }).appendTo("#"+originalFeatName.replaceAll(" ", "_")+"_descrip");

	    // add click function
	    $("#"+feat_name+"_edit").on("click", function(){
	    	var name = $("#"+feat_name+"_name").html();
	    	var descrip = $("#"+feat_name+"_descrip").html();
	    	$("#feat_name").val(name.split(" : ")[0]);
	    	$("#feat_description").val(descrip.split(" <span")[0]);
	    	updateFeat = true;
	    	originalFeatName = name.split(" : ")[0];
	    	$("#new_feat_modal").modal("show");
	    });

	    $("#"+feat_name+"_remove").on("click", function(){
	    	var name = $("#"+feat_name+"_name").html();
	    	// confirm delete
	    	var conf = confirm("Remove feat '"+name.split(" : ")[0]+"'?");
	    	if (conf) {
	    		$("#"+feat_name).remove();
	    	}
	    });

		// update hidden input values
		$("#"+originalFeatName.replaceAll(" ", "_")+"_name_val").val(featName);
		$("#"+originalFeatName.replaceAll(" ", "_")+"_descrip_val").val(featDescription);

		// update IDs
		$("#"+originalFeatName.replaceAll(" ", "_")+"_name").attr("id", feat_name+"_name");
		$("#"+originalFeatName.replaceAll(" ", "_")+"_descrip").attr("id", feat_name+"_descrip");
		$("#"+originalFeatName.replaceAll(" ", "_")+"_name_val").attr("id", feat_name+"_name_val");
		$("#"+originalFeatName.replaceAll(" ", "_")+"_descrip_val").attr("id", feat_name+"_descrip_val");

	} else {
		// make sure we're not adding a duplicate training name
		if (featNames.includes(feat_name.toLowerCase())) {
			alert("Feat name already in use");
			return;
		}
		featNames.push(feat_name.toLowerCase());

		var top = $('<div />', {
    		'id': feat_name,
	      	'class': 'feat',
	    }).appendTo('#feats');

	    $('<p />', {
	    	'id': feat_name+"_name",
	      	'class': 'feat-title',
	      	'text': featName+" : "
	    }).appendTo(top);

	    var descrip = $('<p />', {
	    	'id': feat_name+"_descrip",
	      	'text': featDescription
	    }).appendTo(top);

	    $('<span />', {
	    	'id': feat_name+"_edit",
	      	'class': 'glyphicon glyphicon-edit hover-hide',
	    }).appendTo(descrip);

	    $('<span />', {
	    	'id': feat_name+"_remove",
	      	'class': 'glyphicon glyphicon-remove hover-hide',
	    }).appendTo(descrip);

	    // add click function to edit button
	    $("#"+feat_name+"_edit").on("click", function(){
	    	var name = $("#"+feat_name+"_name").html();
	    	var descrip = $("#"+feat_name+"_descrip").html();
	    	$("#feat_name").val(name.split(" : ")[0]);
	    	$("#feat_description").val(descrip.split(" <span")[0]);
	    	updateFeat = true;
	    	originalFeatName = name.split(" : ")[0];
	    	$("#new_feat_modal").modal("show");
	    });

	    $("#"+feat_name+"_remove").on("click", function(){
	    	var name = $("#"+feat_name+"_name").html();
	    	// confirm delete
	    	var conf = confirm("Remove feat '"+name.split(" : ")[0]+"'?");
	    	if (conf) {
	    		$("#"+feat_name).remove();
	    	}
	    });

	    // enable hover function - don't enable on mobile
	    if (!is_mobile) {
			  $(".feat").hover(function () {
					$(this).find(".glyphicon-edit").show();
					$(this).find(".glyphicon-remove").show();
				}, 
				function () {
					$(this).find(".glyphicon-edit").hide();
					$(this).find(".glyphicon-remove").hide();
				});
	    }

			// add hidden inputs
	    createInput('', 'hidden', 'feat_names[]', featName, top, feat_name+"_name_val");
	    createInput('', 'hidden', 'feat_descriptions[]', featDescription, top, feat_name+"_name_descrip");
	}

}

function newTrainingModal(attribute) {
	// launch modal
	$("#training_modal_title").html("New "+attribute+" Training");
	$("#attribute_type").val(attribute);
	$("#training_name").val("");
	$("#new_training_modal").modal("show");
}

// add a new training from modal values
function newTraining() {
	var trainingName = $("#training_name").val();
	var attribute = $("#attribute_type").val();
	if (trainingName != "") {
		addTrainingElements(trainingName, attribute);
	}
}

// create html elements for training
function addTrainingElements(trainingName, attribute, value='') {
	var training_name = trainingName.replaceAll(' ', '_');
	// make sure we're not adding a duplicate training name
	if (trainingNames.includes(training_name.toLowerCase())) {
		alert("Training name already in use");
		return;
	}
	trainingNames.push(training_name.toLowerCase());

	var row = $('<div />', {
	  'class': 'row training-row',
	}).appendTo('#'+attribute);

	var div_left = $('<div />', {
	  'class': 'col-md-9 col-xs-4',
	}).appendTo(row);

	var label = $('<label />', {
	  'class': 'control-label',
	  'for': training_name,
	  'text': trainingName
	}).appendTo(div_left);

	// add remove button
	var remove = $('<span />', {
		'id': training_name+"_text"+"_remove",
	  'class': 'glyphicon glyphicon-remove hover-hide',
	}).appendTo(label);

	var div_right = $('<div />', {
	  'class': 'col-md-3 col-xs-8 no-pad',
	}).appendTo(row);

	createInput('', 'text', 'training_val[]', value, div_right, training_name+"_text");
	createInput('hidden-number', 'number', '', '', div_right, training_name);
	createInput('', 'hidden', 'training[]', trainingName+":"+attribute, div_right);

	// enable label highlighting
	enableHighlighting();
	enableHiddenNumbers();

	// enable hover function - don't enable on mobile
	if (!is_mobile) {
	  	$(div_left).hover(function () {
			$(this).find(".glyphicon-remove").show();
		}, 
		function () {
			$(this).find(".glyphicon-remove").hide();
		});
	}

	// enable remove function
	$("#"+training_name+"_text"+"_remove").on("click", function(){
		var conf = confirm("Remove training '"+trainingName+"'?");
		if (conf) {
			$(div_left).remove();
			$(div_right).remove();
		}
	});

}

// add a new weapon from modal values
function newWeapon() {
	var name = $("#weapon_name").val();
	$("#weapon_name").val("");
	var damage = $("#weapon_damage").val();
	$("#weapon_damage").val("");
	var notes = $("#weapon_notes").val();
	$("#weapon_notes").val("");
	var weight = $("#weapon_weight").val();
	$("#weapon_weight").val("");
	if (name != "") {
		addWeaponElements(name, 1, damage, notes, weight);
	}
}

// create html elements for weapon
function addWeaponElements(name, qty, damage, notes, weight) {
	if (itemNames.includes(name.toLowerCase())) {
		alert("Item name already in use");
		return;
	}
	itemNames.push(name.toLowerCase());
	var name_val = name.replaceAll(" ", "_");

	var div = createElement('div', 'form-group', '#weapons', name_val);
	var div1 = createElement('div', 'col-xs-3 no-pad-mobile', div);
	var div2 = createElement('div', 'col-xs-1 no-pad-mobile', div);
	var div3 = createElement('div', 'col-xs-1 no-pad-mobile', div);
	var div4 = createElement('div', 'col-xs-5 no-pad-mobile', div);
	var div5 = createElement('div', 'col-xs-1 no-pad-mobile', div);
	var div6 = createElement('div', 'col-xs-1 no-pad-mobile center', div);

	createInput('', 'text', 'weapons[]', name, div1, name_val+"_name");
	createInput('', 'text', 'weapon_qty[]', qty, div2, name_val+"_qty_text");
	createInput('hidden-number', 'number', '', '', div2, name_val+"_qty");
	createInput('', 'text', 'weapon_damage[]', damage, div3, name_val+"_damage_text");
	createInput('hidden-number', 'number', '', '', div3, name_val+"_damage");
	createInput('', 'text', 'weapon_notes[]', notes, div4);
	createInput('', 'text', 'weapon_weight[]', weight, div5, name_val+"_weight_text");
	createInput('hidden-number', 'number', '', '', div5, name_val+"_weight");

	// add remove button
	$('<span />', {
		'id': name_val+"_remove",
	  'class': 'glyphicon glyphicon-remove',
	}).appendTo(div6);

	// enable remove button
	$("#"+name_val+"_remove").on("click", function(){
		var item = $("#"+name_val+"_name").val();
		var conf = confirm("Remove item '"+item+"'?");
		if (conf) {
			$("#"+name_val).remove();
		}
	});

	// enable label highlighting
	enableHighlighting();
	enableHiddenNumbers();

}

// add a new protection from modal values
function newProtection() {
	var name = $("#protection_name").val();
	$("#protection_name").val("");
	var bonus = $("#protection_bonus").val();
	$("#protection_bonus").val("");
	var notes = $("#protection_notes").val();
	$("#protection_notes").val("");
	var weight = $("#protection_weight").val();
	$("#protection_weight").val("");
	if (name != "") {
		addProtectionElements(name, bonus, notes, weight);
	}
}

// create html elements for protection
function addProtectionElements(name, bonus, notes, weight) {
	if (itemNames.includes(name.toLowerCase())) {
		alert("Item name already in use");
		return;
	}
	itemNames.push(name.toLowerCase());
	var name_val = name.replaceAll(" ", "_");

	var div = createElement('div', 'form-group', '#protections', name_val);
	var div1 = createElement('div', 'col-xs-3 no-pad-mobile', div);
	var div2 = createElement('div', 'col-xs-2 no-pad-mobile', div);
	var div3 = createElement('div', 'col-xs-5 no-pad-mobile', div);
	var div4 = createElement('div', 'col-xs-1 no-pad-mobile', div);
	var div5 = createElement('div', 'col-xs-1 no-pad-mobile center', div);

	createInput('', 'text', 'protections[]', name, div1, name_val+"_name");
	createInput('', 'text', 'protection_bonus[]', bonus, div2, name_val+"_bonus_text");
	createInput('hidden-number', 'number', '', '', div2, name_val+"_bonus");
	createInput('', 'text', 'protection_notes[]', notes, div3);
	createInput('', 'text', 'protection_weight[]', weight, div4, name_val+"_weight_text");
	createInput('hidden-number', 'number', '', '', div4, name_val+"_weight");

	// add remove button
	$('<span />', {
		'id': name_val+"_remove",
	  'class': 'glyphicon glyphicon-remove',
	}).appendTo(div5);

	// enable remove button
	$("#"+name_val+"_remove").on("click", function(){
		var item = $("#"+name_val+"_name").val();
		var conf = confirm("Remove item '"+item+"'?");
		if (conf) {
			$("#"+name_val).remove();
		}
	});

	// enable label highlighting
	enableHighlighting();
	enableHiddenNumbers();

}

// add a new healing/potion/drug from modal values
function newHealing() {
	var name = $("#healing_name").val();
	$("#healing_name").val("");
	var quantity = $("#healing_quantity").val();
	$("#healing_quantity").val("");
	var effect = $("#healing_effect").val();
	$("#healing_effect").val("");
	var weight = $("#healing_weight").val();
	$("#healing_weight").val("");
	if (name != "") {
		addHealingElements(name, quantity, effect, weight);
	}
}

// create html elements for healing
function addHealingElements(name, quantity, effect, weight) {
	if (itemNames.includes(name.toLowerCase())) {
		alert("Item name already in use");
		return;
	}
	itemNames.push(name.toLowerCase());
	var name_val = name.replaceAll(" ", "_");

	var div = createElement('div', 'form-group', '#healings', name_val);
	var div1 = createElement('div', 'col-xs-3 no-pad-mobile', div);
	var div2 = createElement('div', 'col-xs-2 no-pad-mobile', div);
	var div3 = createElement('div', 'col-xs-5 no-pad-mobile', div);
	var div4 = createElement('div', 'col-xs-1 no-pad-mobile', div);
	var div5 = createElement('div', 'col-xs-1 no-pad-mobile center', div);

	createInput('', 'text', 'healings[]', name, div1, name_val+"_name");
	createInput('', 'text', 'healing_quantity[]', quantity, div2, name_val+"_quantity_text");
	createInput('hidden-number', 'number', '', '', div2, name_val+"_quantity");
	createInput('', 'text', 'healing_effect[]', effect, div3);
	createInput('', 'text', 'healing_weight[]', weight, div4, name_val+"_weight_text");
	createInput('hidden-number', 'number', '', '', div4, name_val+"_weight");

	// add remove button
	$('<span />', {
		'id': name_val+"_remove",
	  'class': 'glyphicon glyphicon-remove',
	}).appendTo(div5);

	// enable remove button
	$("#"+name_val+"_remove").on("click", function(){
		var item = $("#"+name_val+"_name").val();
		var conf = confirm("Remove item '"+item+"'?");
		if (conf) {
			$("#"+name_val).remove();
		}
	});

	// enable label highlighting
	enableHighlighting();
	enableHiddenNumbers();

}

// add a new misc item from modal values
function newMisc() {
	var name = $("#misc_name").val();
	$("#misc_name").val("");
	var quantity = $("#misc_quantity").val();
	$("#misc_quantity").val("");
	var notes = $("#misc_notes").val();
	$("#misc_notes").val("");
	var weight = $("#misc_weight").val();
	$("#misc_weight").val("");
	if (name != "") {
		addMiscElements(name, quantity, notes, weight);
	}
}

// create html elements for misc item
function addMiscElements(name, quantity, notes, weight) {
	if (itemNames.includes(name.toLowerCase())) {
		alert("Item name already in use");
		return;
	}
	itemNames.push(name.toLowerCase());
	var name_val = name.replaceAll(" ", "_");

	var div = createElement('div', 'form-group', '#misc', name_val);
	var div1 = createElement('div', 'col-xs-3 no-pad-mobile', div);
	var div2 = createElement('div', 'col-xs-2 no-pad-mobile', div);
	var div3 = createElement('div', 'col-xs-5 no-pad-mobile', div);
	var div4 = createElement('div', 'col-xs-1 no-pad-mobile', div);
	var div5 = createElement('div', 'col-xs-1 no-pad-mobile center', div);

	createInput('', 'text', 'misc[]', name, div1, name_val+"_name");
	createInput('', 'text', 'misc_quantity[]', quantity, div2, name_val+"_quantity_text");
	createInput('hidden-number', 'number', '', '', div2, name_val+"_quantity");
	createInput('', 'text', 'misc_notes[]', notes, div3);
	createInput('', 'text', 'misc_weight[]', weight, div4, name_val+"_weight_text");
	createInput('hidden-number', 'number', '', '', div4, name_val+"_weight");

	// add remove button
	$('<span />', {
		'id': name_val+"_remove",
	  	'class': 'glyphicon glyphicon-remove',
	}).appendTo(div5);

	// enable remove button
	$("#"+name_val+"_remove").on("click", function(){
		var item = $("#"+name_val+"_name").val();
		var conf = confirm("Remove item '"+item+"'?");
		if (conf) {
			$("#"+name_val).remove();
		}
	});

	// enable label highlighting
	enableHighlighting();
	enableHiddenNumbers();

}

function enableHighlighting() {
	$("input").each(function(){
		if (!highlightEnabled.includes($(this).attr("name")+":"+this.id)) {
			highlightEnabled.push($(this).attr("name")+":"+this.id);
			// if input ID is type '_text', grab the 'hidden-number' input instead
			var inputElement = this.id.includes("_text") ? $("#"+this.id.split("_text")[0]) : $(this);
			// if input is for an attribute training, use the ID value instead
			var labelTrigger = $(this).attr("name") == 'training_val[]' ? this.id.split("_text")[0] : $(this).attr("name");
			inputElement.on("focus", function(){
				$("label[for='"+labelTrigger+"']").addClass("highlight");
			});
			inputElement.on("focusout", function(){
				$("label[for='"+labelTrigger+"']").removeClass("highlight");
			});
		}
		// find '_text' inputs and add focus function to skip over inputs on tab nav
		if (this.id.includes("_text")) {
			if (!skipEnabled.includes(this.id)) {
				skipEnabled.push(this.id);
				$(this).on("focus", function(){
					$("#"+this.id.split("_text")[0]).focus();
				});
			}
		}
	});
}

function enableHiddenNumbers() {
	$(".hidden-number").each(function(){
		if (!hiddenEnabled.includes(this.id)) {
			hiddenEnabled.push(this.id);
			$(this).on("focus", function(){
				$(this).removeAttr("type");
				var input = $("#"+this.id+"_text");
				$(this).val(input.val());
				input.val("");
			});
			$(this).on("focusout", function(){
				var input = $("#"+this.id+"_text");
				input.val($(this).val());
				$(this).attr("type", "number");
			});
		}
	});
}

function createElement(type, className, appendTo, id=null) {
	return $('<'+type+' />', {
		'id': id,
	  	'class': className,
	}).appendTo(appendTo);
}

function createInput(additionalClass, type, name, value, appendTo, id=null) {
	$('<input />', {
		'id': id,
		'class': 'form-control '+additionalClass,
		'type': type,
	  	'name': name,
	  	'value': value
	}).appendTo(appendTo);
}