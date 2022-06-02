var updateFeat = false;
var originalFeatName = "";
var hiddenEnabled = [];
var highlightEnabled = [];
var skipEnabled = [];
var trainingNames = [];
var featNames = [];
var itemNames = [];
var totalWeight = 0;
var allocatingAttributePts = false;
var attributeVals = [];
var trainingVals = [];
var trainings = [];
var feats = [];
var attributes = [
	'strength',
	'fortitude',
	'speed',
	'agility',
	'precision_',
	'awareness',
	'allure',
	'deception',
	'intellect',
	'innovation',
	'intuition',
	'vitality'
];

function toggleMenu() {
  $(".nav-menu").toggleClass("active");
  $(".glyphicon-menu-hamburger").toggleClass("active");
}

function allocateAttributePts() {
	// check if we have points to allocate
	if (parseInt($("#attribute_pts").val()) == 0) {
		alert("No attribute points to allocate.");
		return;
	}
	// show hidden attribute icons
	$(".attribute-col").find(".hidden-icon").each(function(){
		// don't show remove buttons
		if (!$(this).hasClass("glyphicon-remove")) {
			$(this).show();
		} else {
			$(this).hide();
		}
	});
	// hide edit buttons
	$(".attribute-col").unbind("mouseenter mouseleave");
	if (is_mobile) {
		$(".attribute-col").each(function(){
			$(this).find(".glyphicon-edit").hide();
		});
	}
	// hide hamburger menu
	$(".glyphicon-menu-hamburger").hide().toggleClass("active");
	// dismiss menu
  $(".nav-menu").toggleClass("active");
  // set attribute count value
  $(".attribute-count").html($("#attribute_pts").val()+" Points");
	// show attribute point counter
  $(".attribute-pts").toggleClass("active");
  // hide edit/remove feat buttons
  $(".feat").unbind("mouseenter mouseleave");
	if (is_mobile) {
		$(".feat").each(function(){
			$(this).find(".glyphicon-edit").hide();
			$(this).find(".glyphicon-remove").hide();
		});
	}
  allocatingAttributePts = true;
  trainings = [];
  feats = [];
  // save attribute values
	for (var i in attributes) {
		attributeVals.push($("#"+attributes[i]+"_val").val());
	}
  // save training values
	$(".training-row").each(function(){
		var key = $(this).find(".with-hidden").attr("for");
		var value = $("#"+key+"_val").val();
		trainingVals[key] = value;
	});
}

function endEditAttributes(accept) {
  allocatingAttributePts = false;
	$(".attribute-pts").toggleClass("active");
	$(".glyphicon-menu-hamburger").show();
	$(".attribute-col").find(".hidden-icon").hide();
	if (!is_mobile) {
		// restore attribute-col hover functions
		$(".attribute-col").each(function(){
			$(this).hover(function(){
				$(this).find('.hover-hide').show();
			},
			function(){
				$(this).find('.hover-hide').hide();
			});
		});
		// restore feat hover functions
	  $(".feat").hover(function () {
			$(this).find(".hover-hide").show();
		}, 
		function () {
			$(this).find(".hover-hide").hide();
		});
	} else {
		$(".attribute-col").each(function(){
			$(this).find(".glyphicon-edit").show();
		});
	  $(".feat").each(function () {
			$(this).find(".hover-hide").show();
		});
	}
	if (accept) {
		// update #attribute_pts input val from .attribute-count
		$("#attribute_pts").val($(".attribute-count").html().split(" Points")[0]);
	} else {
		// restore attribute values
		for (var i in attributes) {
			$("#"+attributes[i]+"_val").val(attributeVals[i]);
			$("#"+attributes[i]+"_text").html(attributeVals[i] >= 0 ? "+"+attributeVals[i] : attributeVals[i]);
		}
		// restore training values
		for (var key in trainingVals) {
			$("#"+key+"_val").val(trainingVals[key]);
			$("#"+key+"_text").html(trainingVals[key] >= 0 ? "+"+trainingVals[key] : trainingVals[key]);
		}
		// remove training names and remove training elements
		for (var i in trainings) {
			var index = trainingNames.indexOf(trainings[i].attr("id").split("_row")[0]);
			if (index !== -1) {
			  trainingNames.splice(index, 1);
			}
			trainings[i].remove();
		}
		// remove feat names and remove feat elements
		for (var i in feats) {
			var index = featNames.indexOf(feats[i].attr("id"));
			if (index !== -1) {
			  trainingNames.splice(index, 1);
			}
			feats[i].remove();
		}
	}
}

// detect if we're on a touchscreen
var is_mobile = $('#is_mobile').css('display')=='none';

// on xp change, adjust level
$("#xp").change(function(){
	var levels = [];
	var xp_total = 0;
	for (var i = 1; i <= 25; i++) {
		xp_total += 20 * i;
		levels.push(xp_total);
	}
	var level = 1;
	var lvl = 2;
	for (var i in levels) {
		if ($(this).val() >= levels[i]) {
			level = lvl++;
		}
	}
	// get old level
	var current = $("#level").val();
	// update if changed
	if (level != current) {
		$("#level").val(level);
		// alert if increased
		if (level > current) {
			alert("Huzzah! You made it to level "+level+"!");
			// increase attribute points
			var innovation_val = parseInt($("#innovation_val").val());
			var innovation_mod = innovation_val > 0 ? Math.floor(innovation_val/2) : 0;
			var attribute_pts = $("#attribute_pts").val() == undefined || $("#attribute_pts").val() == "" ? 
				0 : parseInt($("#attribute_pts").val());
			$("#attribute_pts").val(attribute_pts+12+innovation_mod);
		}
	}
});

// on morale change, set morale effect
$("#morale").change(function(){
	setMoraleEffect(parseInt($(this).val()));
});

// set max damage to resilience
$("#damage").attr("max", $("#resilience").val());
// on damage change, modify wounds
$("#damage").change(function(){
	if ($(this).val() == $(this).attr("max")) {
		$(this).val(0);
		$("#wounds").val( parseInt($("#wounds").val())+1 >= 3 ? 3 : parseInt($("#wounds").val())+1 );
	}
});

// enable size edit button
if (!is_mobile) {
	$("#size").hover(function () {
		$(this).find(".hover-hide").show();
	}, 
	function () {
		$(this).find(".hover-hide").hide();
	});
}

function editSize() {
	// set size text
	var size = $("#character_size_select").val();
	$("#character_size_text").html(size);
	$("#character_size_val").val(size);
	$("#move").val(size == "Small" ? 1 : (size == "Large" ? 3 : 2));
	setDodge();
	setDefend();
	// TODO check if stealth training exists; +2?
}

function setDodge() {
	// get size value and agility value
	var size = $("#character_size_select").val();
	var agility = parseInt($("#agility_val").val());
	var dodge = (agility >= 0 ? Math.floor(agility/2) : Math.ceil(agility/3)) + (size == "Small" ? 2 : (size == "Large" ? -2 : 0));
	$("#dodge").val(dodge);
}

function setDefend() {
	// get size value and agility value
	var size = $("#character_size_select").val();
	var agility = parseInt($("#agility_val").val());
	var defend = 10 + agility + (size == "Small" ? 2 : (size == "Large" ? -2 : 0));
	$("#defend").val(defend);
}

// penalty inputs - if val is zero, clear input
$(".penalty-val").on("change", function(){
	if ($(this).val() == 0) {
		$(this).val("");
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
		// window.location.href = "/lostcity/index.php";
	} else {
		window.location.href = "/?user="+$(this).val();
		// window.location.href = "/lostcity/index.php?user="+$(this).val();
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
	// if allocating attribute points, make sure we have enough points for a new feat
	if (allocatingAttributePts) {
		if (parseInt($(".attribute-count").html().split(" Points")[0]) - 4 < 0) {
			alert("Not enough attribute points to allocate for a new feat.");
			$("#new_feat_modal").modal("hide");
			return;
		}
	}

	$("#feat_name").focus();
	$("#feat_modal_title").html(updateFeat ? "Update Feat" : "New Feat");
	if (!updateFeat) {
		$("#feat_name").val("");
		$("#feat_description").val("");
	}
	updateFeat = false;
});

$("#new_training_modal").on('shown.bs.modal', function(){
	if (allocatingAttributePts) {
		$("#skill_type").show();
	} else {
		$("#skill_type").hide();
	}
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

// set attribute values on page load
function setAttributes(user) {
	for (var i in attributes) {
		if (user[attributes[i]] != undefined) {
			$("#"+attributes[i]+"_text").html(user[attributes[i]] >= 0 ? "+"+user[attributes[i]] : user[attributes[i]]);
			$("#"+attributes[i]+"_val").val(user[[attributes[i]]]);
		} else {
			$("#"+attributes[i]+"_text").html("+0");
			$("#"+attributes[i]+"_val").val(0);
		}
	}
	// set morale effect from morale
	if (user['morale'] != null) {
		setMoraleEffect(parseInt(user['morale']));
	}
}

function setMoraleEffect(morale) {
	var moraleEffects = {
		2: "Once per Encounter you can re-roll a Fate",
		4: "Once per Encounter you can re-roll a d20",
		6: "Once per Session you can declare a Fate 6",
		8: "You gain 1 additional Motivator Bonus per Session",
		10: "You gain a Benefit on a Fate 5 or 6"
	};
	var moraleEffect = "No Effect";
	for (var key in moraleEffects) {
		if (morale >= key) {
			moraleEffect = moraleEffects[key];
		}
	}
	$("#morale_effect").val(moraleEffect);
}

// adjust attribute value
function adjustAttribute(attribute, val) {
	var originalVal = parseInt($("#"+attribute+"_val").val());
	var newVal = originalVal+parseInt(val);
	$("#"+attribute+"_text").html(newVal >= 0 ? "+"+newVal : newVal);
	$("#"+attribute+"_val").val(newVal);
	// check if we are allocating attribute points
	if (allocatingAttributePts) {
		var pts = parseInt($(".attribute-count").html().split(" Points")[0]);
		// increasing attribute; reduce points by newVal, if newVal > 0, else reduce by originalVal
		if (val == 1) {
			var newPts = pts-Math.abs(newVal > 0 ? newVal : originalVal);
			if (newPts >= 0) {
				$(".attribute-count").html(newPts+" Points");
			} else {
				$("#"+attribute+"_text").html(originalVal >= 0 ? "+"+originalVal : originalVal);
				$("#"+attribute+"_val").val(originalVal);
			}
			// decreasing attribute; increase points by originalVal, if original > 0, else increase by newVal
		} else {
			$(".attribute-count").html(pts+Math.abs(originalVal > 0 ? originalVal : newVal)+" Points");
		}
	}
	// adjust stats based on attribute
	switch(attribute) {
		case 'strength':
			// adjust toughness
			var toughness = newVal >= 0 ? Math.floor(newVal/2) : Math.ceil(newVal/3);
			$("#toughness").val(toughness);
			// adjust weight capcity
			var base = 100 + 20 * newVal;
			$("#unhindered").val(base/4);
			$("#encumbered").val(base/2);
			$("#burdened").val(base/4*3);
			$("#overburdened").val(base);
			// TODO adjust melee weapon damage
			break;
		case 'fortitude':
			// adjust resilience
			var resilience = newVal >= 0 ? 3 + Math.floor(newVal/2) : 3 + Math.ceil(newVal/3);
			$("#resilience").val(resilience);
			// adjust max damage
			$("#damage").attr("max", resilience);
			break;
		case 'speed':
			// adjust standard and quick actions
			var standard = newVal >=0 ? 2 + Math.floor(newVal/4) : 2 - Math.round(-1*newVal/6);
			$("#standard").val(standard);
			var quick = newVal >= 0 ? (Math.floor(newVal/2) % 2 == 0 ? 0 : 1) : (Math.ceil(newVal/3) % 2 == 0 ? 0 : 1);
			$("#quick").val(quick);
			break;
		case 'agility':
			// adjust dodge and defend
			setDodge();
			setDefend();
			break;
		case 'precision_':
			// TODO adjust ranged weapon damage
			break;
		case 'awareness':
			// adjust initiative
			var initiative = newVal >= 0 ? 10 - Math.floor(newVal/2) : 10 - Math.ceil(newVal/3);
			$("#initiative").val(initiative);
			break;
	}
}

// enable attribute edit btn hide / show on hover; don't hide on mobile
if (!is_mobile) {
	$(".attribute-col").each(function(){
		$(this).hover(function(){
			// show glyphicon-plus, glyphicon-minus
			$(this).find('.hover-hide').show();
			// toggleHidden(this.id);
		},
		function(){
			// hide glyphicon-plus, glyphicon-minus
			$(this).find('.hover-hide').hide();
			// toggleHidden(this.id);
		});
	});
}

// show all icons for editing attribute values
function toggleHidden(col) {
	// if mobile, toggle all other edit buttons hidden
  if (is_mobile) {
  	$(".attribute-col").each(function(){
  		if (this.id != col) {
  			$(this).find(".glyphicon-edit").toggle();
  		}
  	});
  } else {
  	// if desktop, make sure all other hidden icons are hidden
  	$(".attribute-col").each(function(){
  		if (this.id != col) {
  			$(this).find(".hidden-icon").hide();
  		}
  	});
  }
	$("#"+col).find('.hidden-icon').toggle();
	// adjust padding on .with-hidden labels
	$("#"+col).find(".with-hidden").css("padding-right", $("#"+col).find(".glyphicon-remove").is(":visible") ? "0" : "23px");
}

// hide / show weapon inputs
function toggleWeapon(weapon, element) {
	var visible = $(element).hasClass('glyphicon-chevron-up');
	$(element).removeClass(visible ? "glyphicon-chevron-up" : "glyphicon-chevron-down");
	$(element).addClass(visible ? "glyphicon-chevron-down" : "glyphicon-chevron-up");
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

		// append edit/remove buttons
		createElement('span', 'glyphicon glyphicon-edit hover-hide', "#"+originalFeatName.replaceAll(" ", "_")+"_descrip", feat_name+"_edit");
		createElement('span', 'glyphicon glyphicon-remove hover-hide', "#"+originalFeatName.replaceAll(" ", "_")+"_descrip", feat_name+"_remove");

    // add click functions
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
		// if allocating attribute points, decrease points
		if (allocatingAttributePts) {
			var pts = parseInt($(".attribute-count").html().split(" Points")[0]);
			$(".attribute-count").html(pts - 4+" Points");
		}

		// make sure we're not adding a duplicate training name
		if (featNames.includes(feat_name.toLowerCase())) {
			alert("Feat name already in use");
			return;
		}

		featNames.push(feat_name.toLowerCase());
		var top = createElement('div', 'feat', '#feats', feat_name);
		if (allocatingAttributePts) {
			feats.push(top);
		}

    $('<p />', {
    	'id': feat_name+"_name",
    	'class': 'feat-title',
    	'text': featName+" : "
    }).appendTo(top);

    var descrip = $('<p />', {
    	'id': feat_name+"_descrip",
      'text': featDescription
    }).appendTo(top);

		createElement('span', 'glyphicon glyphicon-edit hover-hide', descrip, feat_name+"_edit");
		createElement('span', 'glyphicon glyphicon-remove hover-hide', descrip, feat_name+"_remove");

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
	    	// if allocating attribute points, increase points
	    	if (allocatingAttributePts) {
					var pts = parseInt($(".attribute-count").html().split(" Points")[0]);
					$(".attribute-count").html(pts + 4+" Points");
	    	}
    	}
    });

    // enable hover function - don't enable on mobile
    if (!is_mobile) {
    	top.hover(function () {
				$(this).find(".hover-hide").show();
			}, 
			function () {
				$(this).find(".hover-hide").hide();
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

	// allocating attribute points, make sure we have enough points
	if (allocatingAttributePts) {
		var pts = parseInt($(".attribute-count").html().split(" Points")[0]);
		var skill_pts = $('input[name=skill_type]:checked').val();
		if (skill_pts == undefined) {
			alert("Please select a skill type");
			return;
		} else if (pts - skill_pts < 0) {
			alert("Not enough attribute points to allocate for a new skill training.");
			return;
		}
		skill_pts = parseInt(skill_pts);
		// decrease attribute points
		$(".attribute-count").html(pts - skill_pts +" Points");
	}

	trainingNames.push(training_name.toLowerCase());
	var row = createElement('div', 'row training-row', '#'+attribute, training_name.toLowerCase()+"_row");
	if (allocatingAttributePts) {
		trainings.push(row);
	}
	var div_left = createElement('div', 'col-md-7 col-xs-8', row);

	var label_left = $('<label />', {
	  'class': 'control-label with-hidden',
	  'for': training_name,
	  'text': trainingName,
	}).appendTo(div_left);

	// add remove button
	// if allocating points, make sure remove button is visible
	var removeBtn = createElement('span', 'glyphicon glyphicon-remove hidden-icon', label_left, training_name+"_text"+"_remove");
	if (allocatingAttributePts) {
		removeBtn.show();
	}
	removeBtn.on("click", function(){
		var conf = confirm("Remove training '"+trainingName+"'?");
		if (conf) {
			// if allocating points, increase point count
			if (allocatingAttributePts) {
				var pts = parseInt($(".attribute-count").html().split(" Points")[0]);
				$(".attribute-count").html(pts + skill_pts +" Points");
			}
			row.remove();
		}
	});

	var div_right = createElement('div', 'col-md-5 col-xs-4', row);

	var label_right = $('<label />', {
	  'class': 'control-label'
	}).appendTo(div_right);

	$('<span />', {
		'id': training_name+"_text",
	  'class': 'attribute-val',
	  'html': value == '' ? '+0' : (value >= 0 ? "+"+value : value),
	}).appendTo(label_right);

	createInput('', 'hidden', 'training[]', trainingName+":"+attribute, label_right);
	createInput('', 'hidden', 'training_val[]', value == '' ? 0 : value, label_right, training_name+"_val");

	createElement('span', 'glyphicon glyphicon-plus hidden-icon', label_right, training_name+"_up");
	$("#"+training_name+"_up").on("click", function(){
		adjustAttribute(training_name, 1);
	});

	createElement('span', 'glyphicon glyphicon-minus hidden-icon', label_right, training_name+"_down");
	$("#"+training_name+"_down").on("click", function(){
		adjustAttribute(training_name, -1);
	});

	// enable label highlighting
	enableHighlighting();
	enableHiddenNumbers();

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
	weight = weight == "" ? 0 : weight;
	totalWeight += parseFloat(weight);
	$("#total_weight").val(totalWeight);
	itemNames.push(name.toLowerCase());
	// replace all characters not allowed in id
	var name_val = name.replace(/[^a-zA-Z0-9\-_:]+/g, "_");

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
	createInput('', 'number', 'weapon_weight[]', weight, div5);

	// add remove button
	createElement('span', 'glyphicon glyphicon-remove', div6, name_val+"_remove");
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
	weight = weight == "" ? 0 : weight;
	totalWeight += parseFloat(weight);
	$("#total_weight").val(totalWeight);
	itemNames.push(name.toLowerCase());
	// replace all characters not allowed in id
	var name_val = name.replace(/[^a-zA-Z0-9\-_:]+/g, "_");

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
	createInput('', 'number', 'protection_weight[]', weight, div4);

	// add remove button
	createElement('span', 'glyphicon glyphicon-remove', div5, name_val+"_remove");
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
	weight = weight == "" ? 0 : weight;
	totalWeight += parseFloat(weight);
	$("#total_weight").val(totalWeight);
	itemNames.push(name.toLowerCase());
	// replace all characters not allowed in id
	var name_val = name.replace(/[^a-zA-Z0-9\-_:]+/g, "_");

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
	createInput('', 'number', 'healing_weight[]', weight, div4);

	// add remove button
	createElement('span', 'glyphicon glyphicon-remove', div5, name_val+"_remove");
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
	weight = weight == "" ? 0 : weight;
	totalWeight += parseFloat(weight);
	$("#total_weight").val(totalWeight);
	itemNames.push(name.toLowerCase());
	// replace all characters not allowed in id
	var name_val = name.replace(/[^a-zA-Z0-9\-_:]+/g, "_");

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
	createInput('', 'number', 'misc_weight[]', weight, div4);

	// add remove button
	createElement('span', 'glyphicon glyphicon-remove', div5, name_val+"_remove");
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
			// on mobile, highlight label on focus; on desktop, highlight label on hover
			if (is_mobile) {
				inputElement.on("focus", function(){
					$("label[for='"+labelTrigger+"']").addClass("highlight");
				});
				inputElement.on("focusout", function(){
					$("label[for='"+labelTrigger+"']").removeClass("highlight");
				});
			} else {
				inputElement.hover(function (){
					$("label[for='"+labelTrigger+"']").addClass("highlight");
				},
				function(){
					$("label[for='"+labelTrigger+"']").removeClass("highlight");
				});
			}
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

// hidden number inputs - to trigger the number keypad for text inputs (mobile)
function enableHiddenNumbers() {
	$(".hidden-number").each(function(){
		if (!hiddenEnabled.includes(this.id)) {
			hiddenEnabled.push(this.id);
			$(this).on("focus", function(){
				// remove type='number' attribute to allow input of non-numeric input
				$(this).removeAttr("type");
				// get current input val of text field
				var input = $("#"+this.id+"_text");
				$(this).val(input.val());
				// empty text field
				input.val("");
			});
			$(this).on("focusout", function(){
				// copy value from number input to text input
				var input = $("#"+this.id+"_text");
				input.val($(this).val());
				// restore type='number' attribute to trigger number keyboard on next focus
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