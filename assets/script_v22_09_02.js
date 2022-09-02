// arrays to make sure elements are not assigned redundant functions
var hiddenEnabled = [];
var highlightEnabled = [];
var skipEnabled = [];
// arrays to make sure we don't have feats/trainings with the same name
var trainingNames = [];
var featNames = [];
// bools to determine rules and element visibility for various operating modes
var allocatingAttributePts = false;
var characterCreation = false;
var adminEditMode = false;
var unsavedChanges = false;
// arrays used to restore attribute values, feats, and trainings on cancel allocate points
var attributeVals = [];
var trainingVals = [];
var trainings = [];
var feats = [];
var skills = [];
// ID of input to gain focus on modal show
var focus_id = "";
// equipped protections
var protections;
var equipped = [];
// user feat list - determines which feats are shown in the autocomplete list
var user_feats = [];
// show encumbered alert
var loadingItems = false;
var suppressAlerts = false;

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
	'vitality',
];

// detect if we're on a touchscreen
var is_mobile = $('#is_mobile').css('display')=='none';

// hover function for clearable inputs
function tog(v){ return v?'addClass':'removeClass'; }

// back to select campaign page
function back() {
	window.location.replace("/");
}

// text input clear functions
$(document).on('input', '.clearable', function() {
    $(this)[tog(this.value)]('x');
}).on('mousemove', '.x', function(e) {
    $(this)[tog(this.offsetWidth-18 < e.clientX-this.getBoundingClientRect().left)]('onX');   
}).on('click', '.onX', function(){
    $(this).removeClass('x onX').val('');
    if (this.id == "feat_name") {
    	$("#feat_description").val("").height("125px");
    }
});

// trigger 'unsaved changes' alert when leaving the page
$("input").on("change", function(){
	unsavedChanges = true;
});
$("textarea").on("input propertychange", function(){
	unsavedChanges = true;
});
$("#user_form").on("submit", function(){
	unsavedChanges = false;
});
$(window).on("beforeunload", function(e) {
	if (unsavedChanges) {
  	return "Unsaved changes will be lost."; // custom message will not be displayed; message is browser specific
	}
});

// enable size edit button
if (!is_mobile) {
	$("#size").hover(function () {
		$(this).addClass("highlight");
	}, 
	function () {
		$(this).removeClass("highlight");
	});
}

// enable attribute edit btn hide / show on hover; don't hide on mobile
if (is_mobile) {
	$(".attribute-col").each(function(){
		$(this).find('.hover-hide').hide();
	});
}

// hide nav menu on click away
$(document).mouseup(function(e) {
  var container = $(".nav-menu");
  if (!$(".glyphicon-menu-hamburger").is(e.target) && !container.is(e.target) && container.has(e.target).length === 0) {
  	if ($(".glyphicon-menu-hamburger").hasClass("active")) {
  		$(".glyphicon-menu-hamburger").removeClass("active");
  	}
  	if ($(".nav-menu").hasClass("active")) {
  		$(".nav-menu").removeClass("active");
  	}
  }
});

// resize character background textarea to fit text
$("#background").height( $("#background")[0].scrollHeight );

$("#attribute_pts").on("input", function(){
	if (parseInt($(this).val()) == 0) {
		$("#attribute_pts_span").addClass("disabled");
	} else {
		$("#attribute_pts_span").removeClass("disabled");
	}
});

$("#select_feat_type").on("change", function(){
	$(".feat-type").addClass("hidden");
	$("#"+$(this).val()).removeClass("hidden").trigger("change");
	if ($(this).val() == "feat_name") {
		$("#feat_name").val("").removeClass("x onX");
		$("#feat_name_val").val("");
		$("#feat_description").val("");
	}
});

// highlight attributes
if (!is_mobile) {
	$(".attribute-row").hover(function(){
		$(this).addClass("highlight");
	}, function(){
		$(this).removeClass("highlight");
	});
}

// launch GM modal
function settings() {
	// set modal title
	$("#gm_title").html("Admin Settings");
	$("#gm_modal").modal("show");
}

// check admin password for settings page
function adminSettings() {
	// check password
	var password = $("#gm_password").val();
	$("#gm_password").val("");
	// check admin_password
	$.ajax({
	  url: 'check_admin_password.php',
	  data: { 'password' : password, 'admin_password' : campaign['admin_password'] },
	  ContentType: "application/json",
	  type: 'POST',
	  success: function(response){
	  	if (response == 1) {
	  		// launch settings page
				window.location.href = "/admin.php?campaign="+$('#campaign_id').val();
	  	} else {
				alert("Sorry sucker, that ain't it.");
	  	}
	  }
	});
}

// enter GM edit mode
function GMEditMode() {
	// check password
	var password = $("#gm_password").val();
	$("#gm_password").val("");
	// check admin_password
	$.ajax({
	  url: 'check_admin_password.php',
	  data: { 'password' : password, 'admin_password' : campaign['admin_password'] },
	  ContentType: "application/json",
	  type: 'POST',
	  success: function(response){
	  	if (response == 1) {
	  		// enter admin edit mode
				adminEditMode = true;
				
				// show GM menu, hide hamburger menu
			  $(".gm-menu").toggleClass("active");
				$(".glyphicon-menu-hamburger").hide().toggleClass("active");

				// show hidden attribute icons
				$(".attribute-col").find(".hidden-icon").each(function(){
					$(this).show();
				});

				// show new feat button
				$("#new_feat_btn").show();

				// show hidden feat buttons and unbind hover functions
				$("#feats").find(".glyphicon").show();

				// enable edit attribute pts input, enable edit xp input
				$("#attribute_pts").attr("readonly", false).attr("type", "number");
				$("#xp").attr("readonly", false).attr("type", "number").attr("data-toggle", null);
	  	} else {
				alert("Sorry sucker, that ain't it.");
	  	}
	  }
	});
}

// exit GM edit mode
function endGMEdit(accept) {
	if (!accept) {
		// reload page
		unsavedChanges = false;
		window.location.reload();
	} else {
		// confirm and save
		var conf = confirm("Save changes?");
		if (conf) {
			$("#user_form").submit();
		}
	}
}

// function for 'add' button in xp modal
// function addXP() {
// 	if ($("#add_xp").val() != "") {
// 		var current = parseInt($("#xp_text").html());
// 		$("#xp_text").html(current + parseInt($("#add_xp").val()));
// 		$("#add_xp").val("");
// 	}
// }

// function for 'ok' button in xp modal
// function setXP() {
// 	var current = parseInt($("#xp_text").html());
// 	$("#xp").val(current).trigger("change");
// }

// show user menu
function toggleMenu() {
  $(".nav-menu").toggleClass("active");
  $(".glyphicon-menu-hamburger").toggleClass("active");
}

// select a weapon from the dropdown
function selectWeapon(id) {
	var selected = $("#weapon_select_"+id).val();
	// make sure the weapon isn't already selected
	var duplicate = false;
	// if weapon quantity is > 1, allow multiple selections
	$.each(weapons, function(i, weapon) {
		var count = 0;
		var qty = isNaN(weapon['quantity']) ? 1 : weapon['quantity'];
		$(".weapon-select").each(function(){
			if (this.id != "weapon_select_"+id && selected == $(this).val() && selected != "") {
				count += 1;
			}
			if (count >= qty) {
				$("#weapon_select_"+id).val("");
				duplicate = true;
			}
		});
	});
	if (!duplicate && selected != "") {
		for (var i in weapons) {
			if (weapons[i]['name'] == selected) {
				var damage = weapons[i]['damage'];
				$("#weapon_damage_"+id).val(damage);
				// look for crit modifiers
				var crit = 6;
				if (weapons[i]['crit'] != null && weapons[i]['crit'] != '') {
					crit -= parseInt(weapons[i]['crit']);
				}
				if (includesIgnoreCase(featNames, "improved critical hit")) {
					crit -= 1;
				}
				$("#weapon_crit_"+id).val(crit);
				$("#weapon_range_"+id).val(weapons[i]['range_'] == null || weapons[i]['range_'] == "" ? "-" : weapons[i]['range_']);
				$("#weapon_rof_"+id).val(weapons[i]['rof'] == "" ? "-" : weapons[i]['rof']);
				if (weapons[i]['type'] == "Melee") {
					// check for strength modifier for melee weapons
					var damage_mod = parseInt($("#strength_val").val()) >= 0 ? 
						Math.floor(parseInt($("#strength_val").val())/2) : Math.ceil(parseInt($("#strength_val").val())/3);
				} else {
					// check for precision modifier for ranged weapons
					var damage_mod = parseInt($("#precision__val").val()) >= 0 ? 
						Math.floor(parseInt($("#precision__val").val())/2) : Math.ceil(parseInt($("#precision__val").val())/3);
				}
				// make sure damage doesn't exceed max
				if (damage_mod > 0) {
					var max_damage = weapons[i]['max_damage'] == null || weapons[i]['max_damage'] == "" ? 0 : weapons[i]['max_damage'];
					if (max_damage != 0 && parseInt($("#weapon_damage_"+id).val())+damage_mod > weapons[i]['max_damage']) {
						damage_mod = max_damage - damage;
					}
					$("#weapon_damage_"+id).val(damage_mod != 0 ? damage+" (+"+damage_mod+")" : damage);
				}
			}
		}
	} else {
		// clear inputs
		$("#weapon_damage_"+id).val("");
		$("#weapon_crit_"+id).val("");
		$("#weapon_range_"+id).val("");
		$("#weapon_rof_"+id).val("");
	}
	// update defend value
	setDefend();
}

// start point allocation mode
function allocateAttributePts(e) {
	// return if button is disabled
	if ($(e).hasClass("disabled")) {
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
	// show new feat button
	$("#new_feat_btn").show();
	if (characterCreation) {
		// hide edit buttons
		$(".attribute-col").unbind("mouseenter mouseleave");
		if (is_mobile) {
			$(".attribute-col").each(function(){
				$(this).find(".glyphicon-edit").hide();
			});
		}
	}
	// hide hamburger menu
	$(".glyphicon-menu-hamburger").hide().toggleClass("active");
	// dismiss menu
  $(".nav-menu").toggleClass("active");
  // set attribute count value
  $(".attribute-count").html($("#attribute_pts").val()+" Points");
	// show attribute point counter
  $(".attribute-pts").toggleClass("active");
  // reset arrays
  allocatingAttributePts = true;
  trainings = [];
  feats = [];
  skills = [];
  attributeVals = [];
  trainingVals = [];
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

// finish point allocation mode
function endEditAttributes(accept) {
  allocatingAttributePts = false;
	$(".attribute-pts").toggleClass("active");
	$(".glyphicon-menu-hamburger").show();
	$(".attribute-col").find(".hidden-icon").hide();
	$(".feat").find(".glyphicon-remove").hide();
	if (!characterCreation) {
		$("#new_feat_btn").hide();
	}
	// restore attribute-col hover function for edit buttons
	if (characterCreation) {
		if (!is_mobile) {
			$(".attribute-col").each(function(){
				$(this).hover(function(){
					$(this).find('.hover-hide').show();
				},
				function(){
					$(this).find('.hover-hide').hide();
				});
			});
		} else {
			$(".attribute-col").each(function(){
				$(this).find(".hover-hide").show();
			});
		}
	}
	if (accept) {
		// update #attribute_pts input val from .attribute-count
		$("#attribute_pts").val($(".attribute-count").html().split(" Points")[0]);
		if (parseInt($("#attribute_pts").val()) == 0) {
			$("#attribute_pts_span").addClass("disabled");
		}
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
			var index = trainingNames.indexOf(trainings[i].text().split("+0")[0]);
			if (index !== -1) {
			  trainingNames.splice(index, 1);
			}
			trainings[i].remove();
		}
		for (var i in skills) {
			var index = trainingNames.indexOf(skills[i].text().split("+0")[0]);
			if (index !== -1) {
			  trainingNames.splice(index, 1);
			}
			skills[i].remove();
		}
		// remove feat names and remove feat elements
		for (var i in feats) {
			var index = featNames.indexOf(feats[i].text().split(" : ")[0]);
			if (index !== -1) {
			  featNames.splice(index, 1);
			}
			feats[i].remove();
		}
	}
}

// save motivator point value on focus
$('.motivator-pts').on('focusin', function(){
    $(this).data('val', $(this).val());
});
// on motivator pt change, adjust bonuses
$(".motivator-pts").on("input", function(){
	var pts = [];
	var pts_prev = [];
	var score = 0;
	$(".motivator-pts").each(function(){
		pts.push($(this).val() == "" ? 0 : parseInt($(this).val()));
		pts_prev.push($(this).data('val') != undefined ? parseInt($(this).data('val')) : parseInt($(this).val()));
	});
	// add the highest 3 pt values
	pts.sort(function(a, b) {
	  return b - a;
	});
	pts_prev.sort(function(a, b) {
	  return b - a;
	});
	score = pts[0] + pts[1] + pts[2];
	var bonuses = score >= 64 ? 5 : (score >= 32 ? 4 : (score >= 16 ? 3 : (score >= 8 ? 2 : (score >= 4 ? 1 : 0))));
	$("#bonuses").val(bonuses);

	// adjust xp
	var current = $(this).val();
	var prev = $(this).data('val');
	$(this).data('val', current);
	var up = current > prev;
	// increasing - add xp if least value is less than current
	if (up && pts[3] < current) {
		$("#xp").val(parseInt($("#xp").val()) + parseInt($("#level").val())).trigger("change");
	}
	// decreasing - subtract xp if prev is greater than least of the prev vals
	if (!up && prev > pts_prev[3]) {
		$("#xp").val(parseInt($("#xp").val()) - parseInt($("#level").val())).trigger("change");
	}
});

// on xp change, adjust level
$("#xp").change(function() {
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
			var exclamations = [
				"Huzzah!",
				"Shazam!",
				"Booyah!",
				"Boom!",
				"Crabcakes!",
				"Fruit on the bottom!",
				"Jumpin' Jehoshaphat!",
				"Crom!",
				"Fish on a stick!",
				"Oingo Boingo!",
				"Abracadabra!",
				"Hootie McBoobs!",
				"Poop-a-doodle-doo!",
				"Dancin' Danzig!",
				"Leapin' linguine!",
				"Dorka Dorka Dorkass!",
				"Rock'em Sock'em Robots!",
				"Toaster Pastry Pop Tarts!",
				"Testicular torsion!",
				"Potato pancakes!",
				"Yahtzee!",
				"Fart on a forklift!",
				"Blammo!",
				"Kapow!",
				"Excelsior!",
			];
			var index = Math.floor(Math.random() * (exclamations.length));
			alert(exclamations[index]+" You made it to level "+level+"!");
			// increase attribute points
			var innovation_val = parseInt($("#innovation_val").val());
			var innovation_mod = innovation_val > 0 ? Math.floor(innovation_val/2) : 0;
			var attribute_pts = $("#attribute_pts").val() == undefined || $("#attribute_pts").val() == "" ? 
				0 : parseInt($("#attribute_pts").val());
			$("#attribute_pts").val(attribute_pts + ( (12 + innovation_mod) * (level - current) ));
			$("#attribute_pts_span").removeClass("disabled");
			// update next_level in xp modal
			var current_xp = $(this).val();
			var next_level = 0;
			for (var i in levels) {
				if (current_xp < levels[i]) {
					next_level = levels[i];
					break;
				}
			}
			$("#next_level").html(next_level);
		}
	}
});

// on morale change, set morale effect
$("#morale").on("input", function() {
	setMoraleEffect(parseInt($(this).val()));
});

// set max damage to resilience
$("#damage").attr("max", $("#resilience").val());
// on damage change, modify wounds
$("#damage").on("change", function() {
	while (parseInt($(this).val()) >= parseInt($(this).attr("max"))) {
		$(this).val($(this).val() - $(this).attr("max")).trigger("input");
		$("#wounds").val( parseInt($("#wounds").val())+1 >= 3 ? 3 : parseInt($("#wounds").val())+1 );
	}
});

function editSize() {
	// set size text
	var size = $("#character_size_select").val();
	user['size'] = size;
	var size_text = size == "Small" ? "Small; +2 Defend/Dodge/Stealth, -0.5 Move" : (size == "Large" ? "Large; -2 Defend/Dodge/Stealth, +0.5 Move" : size)
	$("#character_size_text").html(size_text);
	$("#character_size_val").val(size);
	setDodge();
	setDefend();
	updateTotalWeight();
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
	// check for weapon defend mofifier
	$(".weapon-select").each(function(){
		if ($(this).val() != "") {
			for (var i in weapons) {
				if ($(this).val() == weapons[i]['name'] && weapons[i]['defend'] != ''&& weapons[i]['defend'] != undefined) {
					defend += parseInt(weapons[i]['defend']);
				}
			}
		}
	});
	$("#defend").val(defend);
}

function setToughness() {
	// get base toughness value from strength
	var strength = user['strength'] == undefined ? 0 : user['strength'];
	var toughness = strength > 0 ? Math.floor(strength/2) : Math.ceil(strength/3);
	var bonus = 0;
	for (var i in equipped) {
		for (var j in protections) {
			if (equipped[i] == protections[j]['name']) {
				bonus += parseInt(protections[j]['bonus']);
			}
		}
	}
	$("#toughness").val(toughness + bonus);
}

// penalty inputs - if val is zero, clear input
$(".penalty-val").on("input", function(){
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
		window.location.replace("/?campaign="+$('#campaign_id').val());
	} else {
		window.location.replace("/?campaign="+$('#campaign_id').val()+"&user="+$(this).val());
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

// focus inputs on modal open -  inputs on modal close
$("#new_training_modal").on('shown.bs.modal', function(){
	$("#training_name").focus();
});
$("#new_weapon_modal").on('shown.bs.modal', function(){
	$(focus_id == "" ? "#weapon_name" : focus_id).focus();
	focus_id = "";
});
$("#new_weapon_modal").on('hidden.bs.modal', function(){
	$("#weapon_modal_title").html("New Weapon");
	$("#weapon_name").val("");
	$("#weapon_damage").val("");
	$("#weapon_max_damage").val("");
	$("#weapon_range").val("");
	$("#weapon_rof").val("");
	$("#weapon_defend").val("");
	$("#weapon_crit").val("");
	$("#weapon_notes").val("");
	$("#weapon_weight").val("");
	$("#weapon_qty").val("");
});
$("#new_protection_modal").on('shown.bs.modal', function(){
	$(focus_id == "" ? "#protection_name" : focus_id).focus();
	focus_id = "";
});
$("#new_protection_modal").on('hidden.bs.modal', function(){
	$("#protection_modal_title").html("New Protection");
	$("#protection_name").val("");
	$("#protection_bonus").val("");
	$("#protection_notes").val("");
	$("#protection_weight").val("");
});
$("#new_healing_modal").on('shown.bs.modal', function(){
	$(focus_id == "" ? "#healing_name" : focus_id).focus();
	focus_id = "";
});
$("#new_healing_modal").on('hidden.bs.modal', function(){
	$("#healing_modal_title").html("New Healing/Potion/Drug");
	$("#healing_name").val("");
	$("#healing_quantity").val("");
	$("#healing_effect").val("");
	$("#healing_weight").val("");
});
$("#new_misc_modal").on('shown.bs.modal', function(){
	$(focus_id == "" ? "#misc_name" : focus_id).focus();
	focus_id = "";
});
$("#new_misc_modal").on('hidden.bs.modal', function(){
	$("#misc_modal_title").html("New Miscellaneous Item");
	$("#misc_name").val("");
	$("#misc_quantity").val("");
	$("#misc_notes").val("");
	$("#misc_weight").val("");
});
$("#new_note_modal").on('shown.bs.modal', function(){
	$("#note_title").focus();
});
$("#new_note_modal").on('hidden.bs.modal', function(){
	$("#note_modal_title").html("New Note");
	$("#note_title").val("");
	$("#note_content").val("");
});
$("#xp_modal").on('shown.bs.modal', function(){
	$("#add_xp").focus();
});
$("#new_password_modal").on('shown.bs.modal', function(){
	$("#new_password").focus();
	toggleMenu();
});
$("#password_modal").on('shown.bs.modal', function(){
	$("#password").focus();
	toggleMenu();
});
$("#gm_modal").on('shown.bs.modal', function(){
	$("#gm_password").focus();
	toggleMenu();
});
$("#gm_modal").on('hidden.bs.modal', function(){
	$("#gm_title").html("GM Edit Mode")
});
function GMModalClose() {
	// check title value
	if ($("#gm_title").html() == "GM Edit Mode") {
		GMEditMode();
	} else {
		adminSettings();
	}
}
$("#help_modal").on('shown.bs.modal', function(){
	// toggleMenu();
});

// on modal shown, update modal title and clear inputs
$("#new_feat_modal").on('shown.bs.modal', function(){
	$("#feat_name").focus();
	// gm edit mode - unhide select feat type elements
	if (adminEditMode || characterCreation) {
		$("#select_feat_type").removeClass("hidden");
		$("#select_feat_type_label").removeClass("hidden");
	} else {
		$("#select_feat_type").addClass("hidden");
		$("#select_feat_type_label").addClass("hidden");
	}
});
$("#new_feat_modal").on('hidden.bs.modal', function(){
	$("#feat_modal_title").html("New Feat");
	$("#feat_name").val("").removeClass("x onX").attr("disabled", false);
	$("#feat_description").val("").height("125px");
	$("#feat_id").val("");
	// reset all dropdowns
	$("#select_feat_type").val("feat_name").trigger("change");
	$("#social_trait_name").val("").attr("disabled", false);
	$("#physical_trait_pos_name").val("").attr("disabled", false);
	$("#physical_trait_neg_name").val("").attr("disabled", false);
	$("#compelling_action_name").val("").attr("disabled", false);
	$("#profession_name").val("").attr("disabled", false);
	$("#morale_trait_name").val("").attr("disabled", false);
});

$("#new_training_modal").on('shown.bs.modal', function(){
	// if (allocatingAttributePts) {
	// 	$("#skill_type").show();
	// } else {
	// 	$("#skill_type").hide();
	// }
});

$("#encumbered_modal").on('hidden.bs.modal', function(){
	// check if we should suppress encumbered alerts
	if ($("#suppress_alert").is(":checked")) {
		suppressAlerts = true;
	}
});

// enable / disable password submit btn
$("#new_password").on("keypress", function(){
	$("#password_btn").attr("disabled", $("#new_password").val() == "" || $("#password_conf").val() == "" || $("#email").val() == "");
});
$("#password_conf").on("keypress", function(){
	$("#password_btn").attr("disabled", $("#new_password").val() == "" || $("#password_conf").val() == "" || $("#email").val() == "");
});
$("#email").on("keypress", function(){
	$("#password_btn").attr("disabled", $("#new_password").val() == "" || $("#password_conf").val() == "" || $("#email").val() == "");
});

// on weapon-name input focus, show other weapon inputs
$(".weapon-name").each(function(){
	$(this).on("focus", function(){
		if ($("#"+$(this).attr("name")).is(":visible") && $("#"+$(this).attr("name")).hasClass("glyphicon-chevron-down")) {
			$("#"+$(this).attr("name")).trigger("click");
		}
	});
});

// autofill feat description on option select
$(".feat-select").on("change", function(){
	var description = "";
	var cost = "";
	for (var i in feat_list) {
		if (feat_list[i]['name'].replaceAll("'","") == $(this).val()) {
			description = feat_list[i]['description'];
			// if physical trait, also show attribute point cost/bonus
			if (feat_list[i]['type'] == "physical_trait") {
				cost = feat_list[i]['cost'];
			}
			// if morale trait, add \n between positive/negative states
			if (feat_list[i]['type'] == "morale_trait") {
				description = description.replace('; ', '.\n');
			}
		}
	}
	description += cost == "" ? "" : "\n\n"+(cost > 0 ? "Attribute Point Cost: "+cost : "Attribute Point Bonus: "+(cost*-1));
	$("#feat_description").val(description);
	$("#feat_name_val").val($(this).val());
});

// set feats as eligible/ineligible and set autocomplete list
function setFeatList() {
	var eligible_feats = [];
	var ineligible_feats = [];
	// set autocomplete list for feats
	$.each(feat_list, function(i, feat) {
		if (feat['type'] == 'feat') {
			var is_eligible = true;
			// feat[requirements] is an array of arrays - each array must return true
			$.each(feat['requirements'], function(j, requirements) {
				var satisfied = false;
				// requirements is an array of dictionaries (req) - one req within requirements needs to return true
				$.each(requirements, function(k, req) {
					for (key in req) {
						switch (key) {
							case 'feat':
							satisfied = satisfied ? true : includesIgnoreCase(featNames, req[key]);
							break;
							case 'training':
							satisfied = satisfied ? true : includesIgnoreCase(trainingNames, req[key]);
							break;
							default:
							satisfied = satisfied ? true : user[key] >= req[key];
							break;
						}
					}
				});
				// if a previous requirement wasn't satisfied, feat is not eligible
				is_eligible = !is_eligible ? false : satisfied;
			});
			if (is_eligible && !eligible_feats.includes(feat)) {
				feat['satisfied'] = true;
				eligible_feats.push(feat);
			} else if (!ineligible_feats.includes(feat)) {
				feat['satisfied'] = false;
				ineligible_feats.push(feat);
			}
		}
	});

	// sort and merge feat lists
	var list1 = [];
	for (var i in eligible_feats) {
		list1.push(eligible_feats[i]['name']);
	}
	list1.sort();
	var list2 = [];
	for (var i in ineligible_feats) {
		list2.push(ineligible_feats[i]['name']);
	}
	list2.sort();
	var list = list1.concat(list2);

	$("#feat_name").autocomplete({
		source: function(input, add) {
			var suggestions = [];
			$.each(list, function(i, feat_name) {
				if (feat_name.toLowerCase().includes(input['term'].toLowerCase())) {
					var entry = new Object();
					entry.value = feat_name;
					// check if attribute requirements are satisfied
					entry.satisfied = true;
					$.each(feat_list, function(j, feat_vals) {
						if (feat_vals['name'] == feat_name) {
							$.each(feat_vals['requirements'], function(k, reqs) {
								$.each(reqs, function(l, req) {
									for (key in req) {
										if (key == "character_creation") {
											entry.hidden = adminEditMode ? false : !characterCreation;
										}
									}
								});
								// set satisfied value to list entry - satisfied always true in GM edit mode
								entry.satisfied = adminEditMode ? true : feat_vals['satisfied'];
							});
						}
	 				});
	 				// check if user is already trained in the feat
	 				var found = false;
	 				for (var i in user_feats) {
	 					if (user_feats[i]['name'].toLowerCase() == entry['value'].toLowerCase()) {
	 						found = true;
	 						break;
	 					}
	 				}
	 				if (!found) {
						suggestions.push(entry);
	 				}
				}
			});
			add(suggestions);
		},
		create: function (event, ui) {
			$(this).data("ui-autocomplete")._renderItem = function(ul, item) {
				var listItem = $("<li></li>")
				.data("item.autocomplete", item)
				.append("<a>" + item.label + "</a>")
				.appendTo(ul);

				// adjust class based on whether requirements are satisfied or not
				if (!item.satisfied) {
					listItem.addClass("italic");
				} else {
					listItem.addClass("bold");
				}
				if (item.hidden) {
					listItem.addClass("hidden");
				}

				return listItem;
			};
		},
		select: function(event, ui) {
			// build user message if requirements aren't satisfied
			if (!ui.item.satisfied) {
				var requirements = "";
				for (var i in feat_list) {
					if (feat_list[i]['name'] == ui.item.value) {
						for (var j in feat_list[i]['requirements']) {
							for (var k in feat_list[i]['requirements'][j]) {
								for (var key in feat_list[i]['requirements'][j][k]) {
									requirements += key == "character_creation" ? "*only available during character creation" : 
									(key == "precision_" ? "Precision" : capitalize(key)) + " : " + feat_list[i]['requirements'][j][k][key];
								}
								if (feat_list[i]['requirements'][j].length > 1 && k < feat_list[i]['requirements'][j].length-1) {
									requirements += " OR ";
								}
							}
							requirements += "\n";
						}
						requirements += "\n";
						requirements += feat_list[i]['name']+"\n";
						requirements += feat_list[i]['description'];
					}
				}
				alert("Requirements not met for "+ui.item.value+":\n\n"+requirements);
				$("#feat_name").val("").removeClass("x onX");
				$("#feat_description").val("");
				return false;
			} else {
				// auto fill description on feat selection
				var description = "";
				for (var i in feat_list) {
					if (feat_list[i]['name'] == ui.item.value) {
						description = feat_list[i]['description'];
					}
				}
				$("#feat_name_val").val(ui.item.value);
				$("#feat_description").val(description).height($("#feat_description")[0].scrollHeight);
			}
		}
	});
}

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
	// set size text
	editSize();
	// set initiative
	adjustInitiative();
	// check for pending xp award
	if (user['xp_awarded'] != undefined && user['xp_awarded'] != 0) {
		$("#xp").val(parseInt(user['xp'])+parseInt(user['xp_awarded'])).trigger("change");
		// update character xp and award in database
		var users = [];
		var xp = [];
		var awards = [];
		var attribute_pts = [];
		users.push(user['id']);
		awards.push(0);
		xp.push($("#xp").val());
		attribute_pts.push(parseInt($("#attribute_pts").val()));
		$.ajax({
		  url: 'update_xp.php',
		  data: { 'users' : users, 'xp' : xp, 'awards' : awards, 'attribute_pts' : attribute_pts },
		  ContentType: "application/json",
		  type: 'POST',
		  success: function(response){
		  	// no action
		  }
		});	
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
	unsavedChanges = true;
	var originalVal = parseInt($("#"+attribute+"_val").val());
	var newVal = originalVal+parseInt(val);
	// check if we are allocating attribute points
	if (allocatingAttributePts) {
		// only allow +1 increase from saved val
		if (!characterCreation) {
			var savedVal = attributes.indexOf(attribute) == -1 ? trainingVals[attribute] : attributeVals[attributes.indexOf(attribute)];
			if (newVal < savedVal || newVal > parseInt(savedVal) + 1) {
				return;
			}
		}
		var pts = parseInt($(".attribute-count").html().split(" Points")[0]);
		// increasing attribute; reduce points by newVal, if newVal > 0, else reduce by originalVal
		if (val == 1) {
			var newPts = pts-Math.abs(newVal > 0 ? newVal : originalVal);
		// make sure we have enough points to allocate
			if (newPts >= 0) {
				$(".attribute-count").html(newPts+" Points");
			} else {
				$("#"+attribute+"_text").html(originalVal >= 0 ? "+"+originalVal : originalVal);
				$("#"+attribute+"_val").val(originalVal);
				return;
			}
			// decreasing attribute; increase points by originalVal, if original > 0, else increase by newVal
		} else {
			$(".attribute-count").html(pts+Math.abs(originalVal > 0 ? originalVal : newVal)+" Points");
		}
	}
	$("#"+attribute+"_text").html(newVal >= 0 ? "+"+newVal : newVal);
	$("#"+attribute+"_val").val(newVal);
	user[attribute] = newVal;
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
			// adjust melee weapon damage
			$(".weapon-select").each(function(){
				for(var i in weapons) {
					if ($(this).val() == weapons[i]['name'] && weapons[i]['type'] == 'Melee') {
						// set damage to max or damage + mod
						var id = this.id.slice(-1);
						var damage_mod = parseInt($("#strength_val").val()) >= 0 ? 
							Math.floor(parseInt($("#strength_val").val())/2) : Math.ceil(parseInt($("#strength_val").val())/3);
						var damage = weapons[i]['damage'];
						var max_damage = weapons[i]['max_damage'] == null || weapons[i]['max_damage'] == "" ? 0 : weapons[i]['max_damage'];
						if (max_damage != 0 && parseInt(damage)+damage_mod > weapons[i]['max_damage']) {
							damage_mod = max_damage - damage;
						}
						$("#weapon_damage_"+id).val(damage_mod > 0 ? damage+" (+"+damage_mod+")" : damage);
					}
				}
			});
			break;
		case 'fortitude':
			// adjust resilience
			var resilience = newVal >= 0 ? 3 + Math.floor(newVal/2) : 3 + Math.ceil(newVal/3);
			$("#resilience").val(resilience);
			// adjust max damage
			$("#damage").attr("max", resilience);
			break;
		case 'speed':
			adjustInitiative();
			updateTotalWeight();
			break;
		case 'agility':
			// adjust dodge and defend
			setDodge();
			setDefend();
			break;
		case 'precision_':
			// adjust ranged weapon damage
			$(".weapon-select").each(function(){
				for(var i in weapons) {
					if ($(this).val() == weapons[i]['name'] && weapons[i]['type'] == 'Ranged') {
						// set damage to max or damage + mod
						var id = this.id.slice(-1);
						var damage_mod = parseInt($("#precision__val").val()) >= 0 ? 
							Math.floor(parseInt($("#precision__val").val())/2) : Math.ceil(parseInt($("#precision__val").val())/3);
						var damage = weapons[i]['damage'];
						var max_damage = weapons[i]['max_damage'] == null || weapons[i]['max_damage'] == "" ? 0 : weapons[i]['max_damage'];
						if (max_damage != 0 && parseInt(damage)+damage_mod > weapons[i]['max_damage']) {
							damage_mod = max_damage - damage;
						}
						$("#weapon_damage_"+id).val(damage_mod > 0 ? damage+" (+"+damage_mod+")" : damage);
					}
				}
			});
			break;
		case 'awareness':
			// adjust initiative
			var initiative = newVal >= 0 ? 10 - Math.floor(newVal/2) : 10 - Math.ceil(newVal/3);
			$("#initiative").val(initiative);
			adjustInitiative();
			break;
	}
	// adjust eligibility of feat list
	setFeatList();
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
	  data: { 'password' : $("#password").val(), 'user_id' : $("#user_id").val(), 'campaign_id' : $("#campaign_id").val() },
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
  var regex = /\S+@\S+\.\S+/;
	// make sure passwords match
	if ($("#new_password").val() != $("#password_conf").val()) {
		alert("Passwords must match, nerd");
	// make sure we have a valid email address
	} else if (!regex.test($("#email").val())) {
	// make sure human response is correct
		alert("That doesn't look like a real email address, nerd");
	} else if ($("#nerd_test").val().toLowerCase() != keys['nerd_test']) {
		alert("That's not the secret word, nerd");
	} else {
		// set user email in form
		$("#user_email").val($("#email").val());
		// show submitting message
		$("#submit_load_modal").modal("show");
		// get recaptcha token before submit
		grecaptcha.ready(function () {
			grecaptcha.execute('6Lc_NB8gAAAAAF4AG63WRUpkeci_CWPoX75cS8Yi', { action: 'new_user' }).then(function (token) {
				$("#recaptcha_response").val(token);
				$("#password_val").val($("#new_password").val());
				$("#user_form").submit();
			});
		});
	}
}

function forgotPassword() {
	alert("Ok fine. Hang tight and we'll be along with a reset link shortly.");
	$.ajax({
	  url: 'email_password_reset_link.php',
	  data: { 'user_id' : $("#user_id").val() },
	  ContentType: "application/json",
	  type: 'POST',
	  success: function(response){
	  	// no action necessary
	  }
	});
}

// add a new feat from modal values
function newFeat() {
	var featName = $("#feat_name_val").val() == "" ? $("#feat_name").val() : $("#feat_name_val").val();
	var featDescription = $("#feat_description").val();
	if (featName != "" && featDescription != " ") {
		addFeatElements(featName, featDescription.trim(), $("#feat_id").val());
	}
}

// create html elements for feat
function addFeatElements(featName, featDescription, id) {
	var id_val = id == "" ? uuid() : "training_"+id;
	var featDescription = featDescription.split("\n\n")[0]; // remove extraneous text from feat descriptions

	// new or updating?
	if ($("#feat_modal_title").html() == "Update Feat") {

		// update feat name and description
		$("#"+id+"_name").html(featName+" : ");
		$("#"+id+"_descrip").html(featDescription.length > 100 ? featDescription.substring(0,100)+"..." : featDescription);

		// update hidden input values
		$("#"+id+"_name_val").val(featName);
		$("#"+id+"_descrip_val").val(featDescription);

	} else {
		// make sure we're not adding a duplicate training name
		if (includesIgnoreCase(featNames, featName)) {
			alert("Feat name already in use");
			return;
		}

		// only allow one profession, one compelling action, one social trait, one morale trait
		var featType = "";
		var cost = 0;
		for (var i in feat_list) {
			if (feat_list[i]['name'].toLowerCase() == featName.toLowerCase()) {
				featType = feat_list[i]['type'];
				cost = parseInt(feat_list[i]['cost']);

				if (featType == "profession") {
					for (var j in user_feats) {
						if (user_feats[j]['type'] == "profession") {
							alert("Only one Profession can be chosen");
							return;
						}
					}
				}
				if (featType == "compelling_action") {
					for (var j in user_feats) {
						if (user_feats[j]['type'] == "compelling_action") {
							alert("Only one Compelling Action can be chosen");
							return;
						}
					}
				}
				if (featType == "social_trait") {
					for (var j in user_feats) {
						if (user_feats[j]['type'] == "social_trait") {
							alert("Only one Social Trait can be chosen");
							return;
						}
					}
				}
				if (featType == "morale_trait") {
					for (var j in user_feats) {
						if (user_feats[j]['type'] == "morale_trait") {
							alert("Only one Morale Trait can be chosen");
							return;
						}
					}
				}
			}
		}

		// if allocating attribute points, decrease points
		if (allocatingAttributePts) {

			// only one feat/skill per level
			if (!characterCreation && feats.length > 0 || skills.length > 0) {
				alert("Only one new feat or unique skill can be added per level.");
				return;
			}

			// make sure we have enough points
			if (parseInt($(".attribute-count").html().split(" Points")[0]) - cost < 0) {
				alert("Not enough attribute points to allocate for a new feat/trait.");
				return;
			}

			var pts = parseInt($(".attribute-count").html().split(" Points")[0]);
			$(".attribute-count").html(pts - cost+" Points");
		}

		featNames.push(featName);
		user_feats.push({"name":featName, "type":featType});
		var feat_container = createElement('div', 'feat', '#feats', id_val);
		if (allocatingAttributePts) {
			feats.push(feat_container);
		}

		var feat_title_descrip = createElement('div', '', feat_container);

    $('<p />', {
    	'id': id_val+"_name",
    	'class': 'feat-title',
    	'text': featName+" : "
    }).appendTo(feat_title_descrip);

    var feat_descrip = $('<p />', {
    	'id': id_val+"_descrip",
      'text': featDescription.length > 100 ? featDescription.substring(0,100)+"..." : featDescription
    }).appendTo(feat_title_descrip);

		// if allocating points, make sure remove button is visible
		var removeBtn = createElement('span', 'glyphicon glyphicon-remove hidden-icon', feat_container, id_val+"_remove");
		if (allocatingAttributePts || adminEditMode || characterCreation) {
			removeBtn.show();
		}

    // add click function to edit button
    feat_title_descrip.on("click", function(){
    	var name = $("#"+id_val+"_name").html().split(" : ")[0];
    	// figure out what type of feat we are editing
    	var featType = "";
    	for (var i in feat_list) {
    		if (feat_list[i]['name'].toLowerCase() == name.toLowerCase()) {
    			featType = feat_list[i]['type'];
    			featType = featType == "physical_trait" ? (cost > 0 ? "physical_trait_pos" : "physical_trait_neg") : featType;
    			$("#select_feat_type").val(featType+"_name").trigger("change");
    			$("#"+featType+"_name").val(feat_list[i]['name']).attr("disabled", !characterCreation && !adminEditMode);
    		}
    	}
    	$("#feat_name").val(name);
    	var description = $("#"+id_val+"_descrip_val").val();
    	// feat is physical trait, add cost/bonus to description
    	if (featType == "physical_trait_pos" || featType == "physical_trait_neg") {
				description += "\n\n"+(cost > 0 ? "Attribute Point Cost: "+cost : "Attribute Point Bonus: "+(cost*-1));
    	}
    	$("#feat_description").val(description);
    	$("#feat_id").val(id_val);
    	$("#feat_modal_title").html("Update Feat");
    	$("#new_feat_modal").modal("show");
			$("#feat_description").height( $("#feat_description")[0].scrollHeight );
    });

    $("#"+id_val+"_remove").on("click", function(){
    	var name = $("#"+id_val+"_name").html();
    	// confirm delete
    	var conf = confirm("Remove feat, '"+name.split(" : ")[0]+"'?");
    	if (conf) {

	    	// if allocating attribute points, increase points
	    	if (allocatingAttributePts) {
					var pts = parseInt($(".attribute-count").html().split(" Points")[0]);
					// if removing a negative trait, make sure we have enough points
					if (cost < 0 && pts + cost < 0) {
						alert("Not enough attribute points to remove trait, "+featName);
						return;
					}

					$(".attribute-count").html(pts + cost +" Points");
					var index = feats.indexOf(feat_container);
					if (index !== -1) {
					  feats.splice(index, 1);
					}
	    	}

    		// remove elements and update arrays
    		unsavedChanges = true;
    		$("#"+id_val).remove();
				var index = featNames.indexOf(featName);
				if (index !== -1) {
				  featNames.splice(index, 1);
				}
				for (var i in user_feats) {
					if (user_feats[i]['name'] == featName) {
					  user_feats.splice(i, 1);
					  break;
					}
				}

	    	// check if we're removing feats that affect attributes
				if (featName.toLowerCase() == "improved critical hit") {
					selectWeapon(1);
					selectWeapon(2);
					selectWeapon(3);
				}
				if (featName.toLowerCase() == "quick and the dead") {
					adjustInitiative();
				}
    	}
    });

		// highlight on hover
		feat_container.hover(function(){
			$(this).addClass("highlight");
		}, function(){
			$(this).removeClass("highlight");
		});

		// add hidden inputs
    createInput('', 'hidden', 'feat_names[]', featName, feat_container, id_val+"_name_val");
    createInput('', 'hidden', 'feat_descriptions[]', featDescription, feat_container, id_val+"_descrip_val");
		createInput('', 'hidden', 'feat_ids[]', id, feat_container);
	}

	// check for specific feats and adjust attributes (quick and the dead, improved crit, etc)
	if (featName.toLowerCase() == "improved critical hit") {
		// adjust weapon crit values
		selectWeapon(1);
		selectWeapon(2);
		selectWeapon(3);
	}
	if (featName.toLowerCase() == "quick and the dead") {
		// adjust initiative value
		adjustInitiative();
	}
}

function adjustInitiative() {
	// check if speed is higher than awareness and awareness is >= 0
	var speed = user['speed'] == null ? 0 : parseInt(user['speed']);
	var awareness = user['awareness'] == null ? 0 : parseInt(user['awareness']);
	var quick = includesIgnoreCase(featNames, "quick and the dead");
	if (quick && speed > awareness && awareness >= 0) {
		// set initiative based on speed value
		var initiative = 10 - Math.floor(speed/2);
		var secondary = 10 - Math.floor(awareness/2);
	} else {
		var initiative = awareness >= 0 ? 10 - Math.floor(awareness/2) : 10 - Math.ceil(awareness/3);
		var secondary = speed >= 0 ? 10 - Math.floor(speed/2) : 10 - Math.ceil(speed/3);
	}
	$("#initiative").val(initiative+" / "+secondary+"");
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
		addTrainingElements(trainingName, attribute, '');
	}
}

// create html elements for training
function addTrainingElements(trainingName, attribute, id, value='') {
	// make sure we're not adding a duplicate training name
	if (includesIgnoreCase(trainingNames, trainingName)) {
		alert("Training name already in use");
		return;
	}

	// make sure skill type is selected - if adding a new training (value = '')
	var skill_pts = $('input[name=skill_type]:checked').val();
	if (skill_pts == undefined && value == '') {
		alert("Please select a skill type");
		return;
	} else {
		skill_pts = parseInt(skill_pts);
		// if skill_pts = 1 or 2 (focus or training), skill starts at +1
		if (skill_pts == 1 || skill_pts == 2) { value = 1; }
	}

	// allocating attribute points, make sure we have enough points
	if (allocatingAttributePts) {
		if (skill_pts == 4 && (feats.length > 0 || skills.length > 0)) {
			alert("Only one new feat or unique skill can be added per level.");
			return;
		}
		if ((skill_pts == 1 || skill_pts == 2) && trainings.length > 0) {
			alert("Only one new focus or training can be added per level.");
			return;
		}
		var pts = parseInt($(".attribute-count").html().split(" Points")[0]);
		if (pts - skill_pts < 0) {
			alert("Not enough attribute points to allocate for a new skill training.");
			return;
		}
		// decrease attribute points
		$(".attribute-count").html(pts - skill_pts +" Points");
	}

	trainingNames.push(trainingName);
	var id_val = id == "" ? uuid() : "training_"+id;

	var row = createElement('div', 'row training-row', '#'+attribute, id_val+"_row");
	if (allocatingAttributePts) {
		if (skill_pts == 1 || skill_pts == 2) {
			trainings.push(row);
		} else {
			skills.push(row);
		}
	}
	var div_left = createElement('div', 'col-md-7 col-xs-8', row);

	var label_left = $('<label />', {
	  'class': 'control-label with-hidden',
	  'for': id_val,
	  'text': trainingName,
	}).appendTo(div_left);

	// highlight training on hover
	if (!is_mobile) {
		row.hover(function(){
			$(this).addClass("highlight");
		}, function(){
			$(this).removeClass("highlight");
		});
	}

	// add remove button
	// if allocating points, make sure remove button is visible
	var removeBtn = createElement('span', 'glyphicon glyphicon-remove hidden-icon', label_left, id_val+"_text"+"_remove");
	if (allocatingAttributePts || adminEditMode || characterCreation) {
		removeBtn.show();
	}
	removeBtn.on("click", function(){
		var conf = confirm("Remove training '"+trainingName+"'?");
		if (conf) {
			unsavedChanges = true;
			// if allocating points, increase point count
			if (allocatingAttributePts) {
				var pts = parseInt($(".attribute-count").html().split(" Points")[0]);
				$(".attribute-count").html(pts + skill_pts +" Points");
				if (skill_pts == 1 || skill_pts == 2) {
					var index = trainings.indexOf(row);
					if (index !== -1) {
					  trainings.splice(index, 1);
					}
				} else {
					var index = skills.indexOf(row);
					if (index !== -1) {
					  skills.splice(index, 1);
					}
				}
			}
			row.remove();
			var index = trainingNames.indexOf(trainingName);
			if (index !== -1) {
			  trainingNames.splice(index, 1);
			}
		}
	});

	var div_right = createElement('div', 'col-md-5 col-xs-4', row);

	var label_right = $('<label />', {
	  'class': 'control-label'
	}).appendTo(div_right);

	$('<span />', {
		'id': id_val+"_text",
	  'class': 'attribute-val',
	  'html': value == '' ? '+0' : (value >= 0 ? "+"+value : value),
	}).appendTo(label_right);

	createInput('', 'hidden', 'training[]', trainingName+":"+attribute, label_right);
	createInput('', 'hidden', 'training_val[]', value == '' ? 0 : value, label_right, id_val+"_val");
	createInput('', 'hidden', 'training_ids[]', id, label_right);

	var up = createElement('span', 'glyphicon glyphicon-plus hidden-icon', label_right, id_val+"_up");
	$("#"+id_val+"_up").on("click", function(){
		adjustAttribute(id_val, 1);
	});

	var down = createElement('span', 'glyphicon glyphicon-minus hidden-icon', label_right, id_val+"_down");
	$("#"+id_val+"_down").on("click", function(){
		adjustAttribute(id_val, -1);
	});

	// GM edit mode - show plus minus icons
	if (adminEditMode || characterCreation) {
		up.show();
		down.show();
	}

	// enable label highlighting
	enableHighlighting();
	enableHiddenNumbers();

}

// get note values from modal
function newNote() {
	// check if we are editing
	var editing = $("#note_modal_title").html() == "Edit Note";
	var title = $("#note_title").val();
	var note = $("#note_content").val();
	if (note == "" && title == "") {
		return;
	}
	if (editing) {
		// update note text
		var note_id = $("#note_id").val();
		// title could be empty
		if (title == "") {
			$("#"+note_id+"_title").html("");
		} else {
			$("#"+note_id+"_title").html(title+": ");
		}
		$("#"+note_id+"_content").html(note.length > 90 ? note.substring(0,90)+"..." : note);
		$("#"+note_id+"_title_val").val(title);
		$("#"+note_id+"_content_val").val(note);
	} else {
		addNoteElements(title, note, '');
	}
}

// create note elements
function addNoteElements(title, note, id) {

	var id_val = id == "" ? uuid() : "note_"+id;

	var li = $('<li />', {
	}).appendTo("#notes");

	var span = $('<span />', {
	  'class': 'note',
	}).appendTo(li);

	$('<span />', {
		'id': id_val+"_title",
		'html': title == "" ? title : title+": ",
	  'class': 'note-title',
	}).appendTo(span);
	createInput('', 'hidden', 'titles[]', title, span, id_val+"_title_val");

	$('<span />', {
		'id': id_val+"_content",
		'html': note.length > 90 ? note.substring(0,90)+"..." : note,
	  'class': 'note-content',
	}).appendTo(span);

	var remove = $('<span />', {
	  'class': 'glyphicon glyphicon-remove',
	}).appendTo(li);

	createInput('', 'hidden', 'notes[]', note, span, id_val+"_content_val");
	createInput('', 'hidden', 'note_ids[]', id, span);

	// highlight on hover
	span.hover(function(){
		$(this).addClass("highlight");
	}, function(){
		$(this).removeClass("highlight");
	});

	// edit on click
	span.click(function(){
		editNote(id_val);
	});

	// enable remove button
	remove.click(function(){
		var conf = confirm("Delete note, " + (title == "" ? "[no title]" : title) + "?");
		if (conf) {
			li.remove();
			unsavedChanges = true;
		}
	});
}

function editNote(note_id) {
	// set modal values and launch
	$("#note_modal_title").html("Edit Note");
	$("#note_title").val($("#"+note_id+"_title_val").val());
	$("#note_content").val($("#"+note_id+"_content_val").val());
	$("#note_id").val(note_id);
	$("#new_note_modal").modal("show");
}

// add a new weapon from modal values - or edit existing weapon
function newWeapon() {
	// check if we are editing
	var editing = $("#weapon_modal_title").html() == "Edit Weapon";
	var type = $("#weapon_type").val();
	var name = $("#weapon_name").val();
	var damage = $("#weapon_damage").val();
	var max_damage = $("#weapon_max_damage").val();
	var range = $("#weapon_range").val();
	var rof = $("#weapon_rof").val();
	var defend = $("#weapon_defend").val();
	var crit = $("#weapon_crit").val();
	var notes = $("#weapon_notes").val();
	var weight = $("#weapon_weight").val() == "" ? 0 : $("#weapon_weight").val();
	var qty = $("#weapon_qty").val() == "" ? 1 : $("#weapon_qty").val();
	if (name == "") {
		alert("Name is required");
		return;
	} else if (damage == "") {
		alert("Damage is required");
		return;
	}
	$("#new_weapon_modal").modal("hide");
	if (editing) {
		// update weapon inputs
		var weapon_id = $("#weapon_id").val();
		$("#"+weapon_id+"_type").val(type);
		var originalName = $("#"+weapon_id+"_name").val();
		$("#"+weapon_id+"_name").val(name);
		$("#"+weapon_id+"_weight").val(weight);
		$("#"+weapon_id+"_damage_val").val(damage);
		var damage_text = max_damage != "" ? damage +" ("+max_damage+")" : damage;
		$("#"+weapon_id+"_damage").val(damage_text);
		$("#"+weapon_id+"_max_damage").val(max_damage);
		var noteMod = "";
		noteMod += range != "" ? "Range: "+range+"; " : "";
		noteMod += rof != "" ? "RoF: "+rof+"; " : "";
		noteMod += defend != "" ? "+"+defend+" Defend; " : "";
		noteMod += crit != "" ? "+"+crit+" Critical Threat Range; " : "";
		$("#"+weapon_id+"_notes").val(noteMod+notes);
		$("#"+weapon_id+"_notes_val").val(notes);
		$("#"+weapon_id+"_range").val(range);
		$("#"+weapon_id+"_rof").val(rof);
		$("#"+weapon_id+"_defend").val(defend);
		$("#"+weapon_id+"_crit").val(crit);
		$("#"+weapon_id+"_qty").val(qty);
		updateTotalWeight(true);
		// check if this weapon is selected - update stats
		for (var i in weapons) {
			if (weapons[i]['name'] == originalName) {
				$($(".weapon-select").get().reverse()).each(function(){
					if ($(this).val() == originalName) {
						weapons[i]['damage'] = damage;
						weapons[i]['defend'] = defend;
						weapons[i]['crit'] = crit;
						weapons[i]['max_damage'] = max_damage;
						weapons[i]['quantity'] = qty;
						weapons[i]['name'] = name;
						weapons[i]['range_'] = range;
						weapons[i]['rof'] = rof;
						selectWeapon(this.id.slice(-1));
					}
					// update select list with new name
					$(this).find("option").each(function(){
						if ($(this).val() == originalName) {
							$(this).val(name);
							$(this).html(name);
						}
					});
				});
			}
		}
	} else {
		addWeaponElements(type, name, 1, damage, max_damage, range, rof, defend, crit, notes, weight, '');
	}
}

// create html elements for weapon
function addWeaponElements(type, name, qty, damage, max_damage, range, rof, defend, crit, notes, weight, id) {
	var id_val = id == "" ? uuid() : "weapon_"+id;

	var div = createElement('div', 'form-group item', '#weapons', id_val);
	var div1 = createElement('div', 'col-xs-3 no-pad-mobile', div);
	var div2 = createElement('div', 'col-xs-1 no-pad-mobile', div);
	var div3 = createElement('div', 'col-xs-1 no-pad-mobile', div);
	var div4 = createElement('div', 'col-xs-5 no-pad-mobile', div);
	var div5 = createElement('div', 'col-xs-1 no-pad-mobile', div);
	var div6 = createElement('div', 'col-xs-1 no-pad-mobile center', div);

	var name_input = createInput('', 'text', 'weapons[]', name, div1, id_val+"_name");
	var qty_input = createInput('qty', 'text', 'weapon_qty[]', qty, div2, id_val+"_qty");
	// check for max damage
	var damageText = max_damage != null && max_damage != "" ? damage +" ("+max_damage+")" : damage;
	var dmg_input = createInput('', 'text', '', damageText, div3, id_val+"_damage");
	// add range, rof & defend bonus to notes
	var noteMod = "";
	noteMod += range != null && range != "" ? "Range: "+range+"; " : "";
	noteMod += rof != null && rof != "" ? "RoF: "+rof+"; " : "";
	noteMod += defend != null && defend != "" ? "+"+defend+" Defend; " : "";
	noteMod += crit != null && crit != "" ? "+"+crit+" Critical Threat Range; " : "";
	var note_input = createInput('', 'text', '', noteMod+notes, div4, id_val+"_notes");
	var wgt_input = createInput('wgt', 'text', 'weapon_weight[]', weight, div5, id_val+"_weight");
	createInput('', 'hidden', 'weapon_damage[]', damage, div5, id_val+"_damage_val");
	createInput('', 'hidden', 'weapon_notes[]', notes, div5, id_val+"_notes_val");
	createInput('', 'hidden', 'weapon_type[]', type, div5, id_val+"_type");
	createInput('', 'hidden', 'weapon_max_damage[]', max_damage, div5, id_val+"_max_damage");
	createInput('', 'hidden', 'weapon_range[]', range, div5, id_val+"_range");
	createInput('', 'hidden', 'weapon_rof[]', rof, div5, id_val+"_rof");
	createInput('', 'hidden', 'weapon_defend[]', defend, div5, id_val+"_defend");
	createInput('', 'hidden', 'weapon_crit[]', crit, div5, id_val+"_crit");
	createInput('', 'hidden', 'weapon_ids[]', id, div5);
	updateTotalWeight(true);

	// add click and hover functions
	name_input.attr("readonly", true);
	qty_input.attr("readonly", true);
	dmg_input.attr("readonly", true);
	note_input.attr("readonly", true);
	wgt_input.attr("readonly", true);
	name_input.click(function(){
		editWeapon(id_val, "name");
	});
	qty_input.click(function(){
		editWeapon(id_val, "qty");
	});
	dmg_input.click(function(){
		editWeapon(id_val, "damage");
	});
	note_input.click(function(){
		// look for range, rof, defend, and crit values
		var note_val = note_input.val();
		var focus = note_val.includes("Range") ? "range" : 
			(note_val.includes("RoF") ? "rof" : (note_val.includes("Defend") ? "defend" : (note_val.includes("Critical") ? "crit" : "notes")));
		editWeapon(id_val, focus);
	});
	wgt_input.click(function(){
		editWeapon(id_val, "weight");
	});
	// TODO why is this necessary? no name value?
	dmg_input.hover(function(){
		$("#weapon_dmg_label").addClass("highlight");
	},
	function(){
		$("#weapon_dmg_label").removeClass("highlight");
	});
	note_input.hover(function(){
		$("#weapon_note_label").addClass("highlight");
	},
	function(){
		$("#weapon_note_label").removeClass("highlight");
	});

	// add remove button
	createElement('span', 'glyphicon glyphicon-remove', div6, id_val+"_remove");
	$("#"+id_val+"_remove").on("click", function(){
		var item = $("#"+id_val+"_name").val();
		var conf = confirm("Remove item '"+item+"'?");
		if (conf) {
			unsavedChanges = true;
			$("#"+id_val).remove();
		  for (var i in weapons) {
		  	if (weapons[i]['name'] == name) {
		  		weapons.splice(i, 1);
		  		break;
		  	}
		  }
		  // clear inputs if weapon is selected
		  $(".weapon-select").find("option").each(function(){
		  	if ($(this).val() == name) {
			  	if ($(this).is(":selected")) {
			  		// clear inputs
			  		var id = $(this).parent().attr("id").split("weapon_select_")[1];
						$("#weapon_damage_"+id).val("");
						$("#weapon_crit_"+id).val("");
						$("#weapon_range_"+id).val("");
						$("#weapon_rof_"+id).val("");
						setDefend();
			  	}
		  		$(this).remove();
		  	}
		  });
		  // update total weight
		  updateTotalWeight(false);
		}
	});

	// make sure it isn't already in the select list
	var found = false;
	$("#weapon_select_1").find("option").each(function(){
		if ($(this).val() == name) {
			found = true;
		}
	});
	if (!found) {
		// add to dropdown
		var option1 = $('<option />', {
	  	'text': name,
	  	'value': name
		}).appendTo("#weapon_select_1");
		var option2 = option1.clone().appendTo("#weapon_select_2");
		var option3 = option1.clone().appendTo("#weapon_select_3");
		// add to weapons array
		var newWeapon = {};
		newWeapon['damage'] = damage;
		newWeapon['defend'] = defend;
		newWeapon['max_damage'] = max_damage;
		newWeapon['name'] = name;
		newWeapon['range_'] = range;
		newWeapon['rof'] = rof;
		newWeapon['type'] = type;
		weapons.push(newWeapon);
	}

	// enable label highlighting
	enableHighlighting();
	enableHiddenNumbers();

}

function editWeapon(weapon_id, input_id) {
	focus_id = "#weapon_"+input_id;
	// separate damage from max damage
	var damage = $("#"+weapon_id+"_damage").val();
	var max_damage = $("#"+weapon_id+"_max_damage").val();
	damage = max_damage != "" ? damage.split(" (")[0] : damage;
	// separate notes from range, rof, and defend
	var range = $("#"+weapon_id+"_range").val();
	var rof = $("#"+weapon_id+"_rof").val();
	var defend = $("#"+weapon_id+"_defend").val();
	var crit = $("#"+weapon_id+"_crit").val();
	var notes = $("#"+weapon_id+"_notes").val();
	notes = range != "" ? notes.slice(notes.indexOf("; ")+2) : notes;
	notes = rof != "" ? notes.slice(notes.indexOf("; ")+2) : notes;
	notes = defend != "" ? notes.slice(notes.indexOf("; ")+2) : notes;
	notes = crit != "" ? notes.slice(notes.indexOf("; ")+2) : notes;
	// set modal values and launch
	$("#weapon_modal_title").html("Edit Weapon");
	$("#weapon_type").val($("#"+weapon_id+"_type").val());
	$("#weapon_name").val($("#"+weapon_id+"_name").val());
	$("#weapon_damage").val(damage);
	$("#weapon_max_damage").val(max_damage);
	$("#weapon_range").val(range);
	$("#weapon_rof").val(rof);
	$("#weapon_defend").val(defend);
	$("#weapon_crit").val(crit);
	$("#weapon_qty").val($("#"+weapon_id+"_qty").val());
	$("#weapon_notes").val(notes);
	$("#weapon_weight").val($("#"+weapon_id+"_weight").val());
	$("#weapon_id").val(weapon_id);
	$("#new_weapon_modal").modal("show");
}

// don't allow ; in RoF inputs; used for parsing note value
$("#weapon_rof").on('keypress', function(e){
	if (e.charCode == 59) {
		e.preventDefault();
		return false;
	}
});

// add a new protection from modal values
function newProtection() {
	// check if we are editing
	var editing = $("#protection_modal_title").html() == "Edit Protection";
	var name = $("#protection_name").val();
	var bonus = $("#protection_bonus").val() == "" ? 0 : $("#protection_bonus").val();
	var notes = $("#protection_notes").val();
	var weight = $("#protection_weight").val() == "" ? 0 : $("#protection_weight").val();
	if (name == "") {
		alert("Name is required");
		return;
	}
	$("#new_protection_modal").modal("hide");
	if (editing) {
		// update protection inputs
		var protection_id = $("#protection_id").val();
		var originalName = $("#"+protection_id+"_name").val();
		$("#"+protection_id+"_name").val(name);
		$("#"+protection_id+"_bonus").val(bonus);
		$("#"+protection_id+"_notes").val(notes);
		$("#"+protection_id+"_weight").val(weight);
		// update protections array bonus value
		for (var i in protections) {
			if (protections[i]['name'] == originalName) {
				protections[i]['bonus'] = bonus;
				// track names changes
				protections[i]['name'] = name;
				var index = equipped.indexOf(originalName);
				if (index !== -1) {
				  equipped.splice(index, 1);
				}
				equipped.push(name);
				setDefend();
			}
		}
		updateTotalWeight(true);
	} else {
		addProtectionElements(name, bonus, notes, weight, false, '');
		// update protections array
		var protection = {'name':name, 'bonus':bonus}
		protections.push(protection);
	}
}

// create html elements for protection
function addProtectionElements(name, bonus, notes, weight, is_equipped, id) {
	var id_val = id == "" ? uuid() : "protection_"+id;

	var div = createElement('div', 'form-group item', '#protections', id_val);
	var div3 = createElement('div', 'col-xs-1 no-pad-mobile col-icon', div);
	var div1 = createElement('div', 'col-xs-3 no-pad-mobile col-icon-right', div);
	var div2 = createElement('div', 'col-xs-1 no-pad-mobile', div);
	var div4 = createElement('div', 'col-xs-5 no-pad-mobile', div);
	var div5 = createElement('div', 'col-xs-1 no-pad-mobile', div);
	var div6 = createElement('div', 'col-xs-1 no-pad-mobile center', div);

	var name_input = createInput('', 'text', 'protections[]', name, div1, id_val+"_name");
	var bonus_input = createInput('', 'text', 'protection_bonus[]', bonus, div2, id_val+"_bonus");
	var notes_input = createInput('', 'text', 'protection_notes[]', notes, div4, id_val+"_notes");
	var weight_input = createInput('wgt', 'text', 'protection_weight[]', weight, div5, id_val+"_weight");
	createInput('', 'hidden', 'protection_ids[]', id, div6);
	updateTotalWeight(true);

	name_input.attr("readonly", true);
	bonus_input.attr("readonly", true);
	notes_input.attr("readonly", true);
	weight_input.attr("readonly", true);
	name_input.click(function(){
		editProtection(id_val, "name");
	});
	bonus_input.click(function(){
		editProtection(id_val, "bonus");
	});
	notes_input.click(function(){
		editProtection(id_val, "notes");
	});
	weight_input.click(function(){
		editProtection(id_val, "weight");
	});

	// add equip button
	createElement('span', 'glyphicon svg fa-solid icon-armor custom-icon', div3, id_val+"_equip");
	createElement('span', 'glyphicon glyphicon-ban-circle', div3, id_val+"_equip_ban");
	createInput('', 'hidden', 'protection_equipped[]', is_equipped == null ? false : is_equipped, div3, id_val+"_equipped");
	$(div3).on("click", function(){
		var item = $("#"+id_val+"_name").val();
		var conf = confirm(($("#"+id_val+"_equip_ban").is(":visible") ? "Equip" : "Unequip")+" protection '"+item+"'?");
		if (conf) {
			$("#"+id_val+"_equip_ban").toggle();
			if (!$("#"+id_val+"_equip_ban").is(":visible")) {
				// add equipped protection
				equipped.push(item);
				$("#"+id_val+"_equipped").val(true);
			} else {
				// remove protection
				var index = equipped.indexOf(item);
				if (index !== -1) {
				  equipped.splice(index, 1);
				}
				$("#"+id_val+
					"_equipped").val(false);
			}
			setToughness();
		}
	});
	// to toggle ban icon on hover...
	// $(div3).hover(function(){
	// 	$("#"+id_val+"_equip_ban").toggle();
	// },
	// function(){
	// 	$("#"+id_val+"_equip_ban").toggle();
	// });

	// add remove button
	createElement('span', 'glyphicon glyphicon-remove', div6, id_val+"_remove");
	$("#"+id_val+"_remove").on("click", function(){
		var item = $("#"+id_val+"_name").val();
		var conf = confirm("Remove item '"+item+"'?");
		if (conf) {
			unsavedChanges = true;
			$("#"+id_val).remove();
			// check if protection was equipped
			var index = equipped.indexOf(item);
			if (index !== -1) {
			  equipped.splice(index, 1);
			  setToughness();
			}
		  // update total weight
		  updateTotalWeight(false);
		}
	});

	// enable label highlighting
	enableHighlighting();
	enableHiddenNumbers();

	// prompt user to equip new protection
	if (id == "") {
		var conf = confirm("Do you want to equip your new protection, "+name+"?");
		if (conf) {
			// equip item
			$("#"+id_val+"_equip_ban").toggle();
			equipped.push(name);
			$("#"+id_val+"_equipped").val(true);
			var protection = {'name':name, 'bonus':bonus}
			protections.push(protection);
			setToughness();
		}
	}

}

function editProtection(protection_id, input_id) {
	focus_id = "#protection_"+input_id;
	// set modal values and launch
	$("#protection_modal_title").html("Edit Protection");
	$("#protection_name").val($("#"+protection_id+"_name").val());
	$("#protection_bonus").val($("#"+protection_id+"_bonus").val());
	$("#protection_notes").val($("#"+protection_id+"_notes").val());
	$("#protection_weight").val($("#"+protection_id+"_weight").val());
	$("#protection_id").val(protection_id);
	$("#new_protection_modal").modal("show");
}

// add a new healing/potion/drug from modal values
function newHealing() {
	// check if we are editing
	var editing = $("#healing_modal_title").html() == "Edit Healing/Potion/Drug";
	var name = $("#healing_name").val();
	var quantity = $("#healing_quantity").val() == "" ? 1 : $("#healing_quantity").val();
	var effect = $("#healing_effect").val();
	var weight = $("#healing_weight").val() == "" ? 0 : $("#healing_weight").val();
	if (name == "") {
		alert("Name is required");
		return;
	}
	$("#new_healing_modal").modal("hide");
	if (editing) {
		// update healing inputs
		var healing_id = $("#healing_id").val();
		$("#"+healing_id+"_name").val(name);
		$("#"+healing_id+"_quantity").val(quantity);
		$("#"+healing_id+"_effect").val(effect);
		$("#"+healing_id+"_weight").val(weight);
		updateTotalWeight(true);
	} else {
		addHealingElements(name, quantity, effect, weight, '');
	}
}

// create html elements for healing
function addHealingElements(name, quantity, effect, weight, id) {
	var id_val = id == "" ? uuid() : "healing_"+id;

	var div = createElement('div', 'form-group item', '#healings', id_val);
	var div1 = createElement('div', 'col-xs-4 no-pad-mobile', div);
	var div2 = createElement('div', 'col-xs-1 no-pad-mobile', div);
	var div3 = createElement('div', 'col-xs-5 no-pad-mobile', div);
	var div4 = createElement('div', 'col-xs-1 no-pad-mobile', div);
	var div5 = createElement('div', 'col-xs-1 no-pad-mobile center', div);

	var name_input = createInput('', 'text', 'healings[]', name, div1, id_val+"_name");
	var qty_input = createInput('qty', 'text', 'healing_quantity[]', quantity, div2, id_val+"_quantity");
	var effect_input = createInput('', 'text', 'healing_effect[]', effect, div3, id_val+"_effect");
	var weight_input = createInput('wgt', 'text', 'healing_weight[]', weight, div4, id_val+"_weight");
	createInput('', 'hidden', 'healing_ids[]', id, div4);
	updateTotalWeight(true);

	name_input.attr("readonly", true);
	qty_input.attr("readonly", true);
	effect_input.attr("readonly", true);
	weight_input.attr("readonly", true);
	name_input.click(function(){
		editHealing(id_val, "name");
	});
	qty_input.click(function(){
		editHealing(id_val, "quantity");
	});
	effect_input.click(function(){
		editHealing(id_val, "effect");
	});
	weight_input.click(function(){
		editHealing(id_val, "weight");
	});

	// add remove button
	createElement('span', 'glyphicon glyphicon-remove', div5, id_val+"_remove");
	$("#"+id_val+"_remove").on("click", function(){
		var item = $("#"+id_val+"_name").val();
		var conf = confirm("Remove item '"+item+"'?");
		if (conf) {
			unsavedChanges = true;
			$("#"+id_val).remove();
		  // update total weight
		  updateTotalWeight(false);
		}
	});

	// enable label highlighting
	enableHighlighting();
	enableHiddenNumbers();

}

function editHealing(healing_id, input_id) {
	focus_id = "#healing_"+input_id;
	// set modal values and launch
	$("#healing_modal_title").html("Edit Healing/Potion/Drug");
	$("#healing_name").val($("#"+healing_id+"_name").val());
	$("#healing_quantity").val($("#"+healing_id+"_quantity").val());
	$("#healing_effect").val($("#"+healing_id+"_effect").val());
	$("#healing_weight").val($("#"+healing_id+"_weight").val());
	$("#healing_id").val(healing_id);
	$("#new_healing_modal").modal("show");
}

// add a new misc item from modal values
function newMisc() {
	// check if we are editing
	var editing = $("#misc_modal_title").html() == "Edit Miscellaneous Item";
	var name = $("#misc_name").val();
	var quantity = $("#misc_quantity").val() == "" ? 1 : $("#misc_quantity").val();
	var notes = $("#misc_notes").val();
	var weight = $("#misc_weight").val() == "" ? 0 : $("#misc_weight").val();
	if (name == "") {
		alert("Name is required");
		return;
	}
	$("#new_misc_modal").modal("hide");
	if (editing) {
		// update misc inputs
		var misc_id = $("#misc_id").val();
		$("#"+misc_id+"_name").val(name);
		$("#"+misc_id+"_quantity").val(quantity);
		$("#"+misc_id+"_notes").val(notes);
		$("#"+misc_id+"_weight").val(weight);
		updateTotalWeight(true);
	} else {
		addMiscElements(name, quantity, notes, weight, '');
	}
}

// create html elements for misc item
function addMiscElements(name, quantity, notes, weight, id) {
	var id_val = id == "" ? uuid() : "misc_"+id;

	var div = createElement('div', 'form-group item', '#misc', id_val);
	var div1 = createElement('div', 'col-xs-4 no-pad-mobile', div);
	var div2 = createElement('div', 'col-xs-1 no-pad-mobile', div);
	var div3 = createElement('div', 'col-xs-5 no-pad-mobile', div);
	var div4 = createElement('div', 'col-xs-1 no-pad-mobile', div);
	var div5 = createElement('div', 'col-xs-1 no-pad-mobile center', div);

	var name_input = createInput('', 'text', 'misc[]', name, div1, id_val+"_name");
	var qty_input = createInput('qty', 'text', 'misc_quantity[]', quantity, div2, id_val+"_quantity");
	var notes_input = createInput('', 'text', 'misc_notes[]', notes, div3, id_val+"_notes");
	var weight_input = createInput('wgt', 'text', 'misc_weight[]', weight, div4, id_val+"_weight");
	createInput('', 'hidden', 'misc_ids[]', id, div4);
	updateTotalWeight(true);

	name_input.attr("readonly", true);
	qty_input.attr("readonly", true);
	notes_input.attr("readonly", true);
	weight_input.attr("readonly", true);
	name_input.click(function(){
		editMisc(id_val, "name");
	});
	qty_input.click(function(){
		editMisc(id_val, "quantity");
	});
	notes_input.click(function(){
		editMisc(id_val, "notes");
	});
	weight_input.click(function(){
		editMisc(id_val, "weight");
	});

	// add remove button
	createElement('span', 'glyphicon glyphicon-remove', div5, id_val+"_remove");
	$("#"+id_val+"_remove").on("click", function(){
		var item = $("#"+id_val+"_name").val();
		var conf = confirm("Remove item '"+item+"'?");
		if (conf) {
			unsavedChanges = true;
			$("#"+id_val).remove();
		  // update total weight
		  updateTotalWeight(false);
		}
	});

	// enable label highlighting
	enableHighlighting();
	enableHiddenNumbers();

}

function editMisc(misc_id, input_id) {
	focus_id = "#misc_"+input_id;
	// set modal values and launch
	$("#misc_modal_title").html("Edit Miscellaneous Item");
	$("#misc_name").val($("#"+misc_id+"_name").val());
	$("#misc_quantity").val($("#"+misc_id+"_quantity").val());
	$("#misc_notes").val($("#"+misc_id+"_notes").val());
	$("#misc_weight").val($("#"+misc_id+"_weight").val());
	$("#misc_id").val(misc_id);
	$("#new_misc_modal").modal("show");
}

function updateTotalWeight(showMsg = false) {
	var totalWeight = 0
	// find all wgt inputs
	$(".item").each(function(){
		var qty = 1;
		var wgt = 0;
		$(this).find('.qty').each(function(){
			qty = $(this).val() == "" ? 1 : (isNaN($(this).val()) ? 1 : $(this).val());
		});
		$(this).find('.wgt').each(function(){
			wgt = $(this).val() == "" ? 0 : $(this).val();
		});
		totalWeight += parseFloat(qty) * parseFloat(wgt);
	});
	$("#total_weight").val(totalWeight.toFixed(1));

	// highlight weight capacity and get base action/move values
	var capacity = $("#overburdened").val();
	$("#overburdened").removeClass("selected");
	$("#burdened").removeClass("selected");
	$("#encumbered").removeClass("selected");
	$("#unhindered").removeClass("selected");
	var speed = user['speed'] == undefined ? 0 : user['speed'];
	var standard = speed >=0 ? 1 + Math.floor(speed/4) : 1 - Math.round(-1*speed/6);
	var quick = speed >= 0 ? (Math.floor(speed/2) % 2 == 0 ? 0 : 1) : (Math.ceil(speed/3) % 2 == 0 ? 0 : 1);
	var move = user['size'] == undefined ? 1 : (user['size'] == "Small" ? 0.5 : (user['size'] == "Large" ? 1.5 : 1));

	// adjust action/move values
	var msg = "";
	if (parseFloat(totalWeight) <= capacity/4) {
		// unhindered, no modification to actions
		$("#unhindered").addClass("selected");
	} else if (parseFloat(totalWeight) <= capacity/2) {
		// encumbered, -1 QA
		$("#encumbered").addClass("selected");
		standard = quick == 0 ? standard - 1 : standard;
		quick = quick == 0 ? 1 : 0;
		msg = "You are encumbered (-1 QA).<br>Reduce your item weight to remove the penalty.";
	} else if (parseFloat(totalWeight) <= capacity/4*3) {
		// burdened, -1 QA, -0.5 Move
		$("#burdened").addClass("selected");
		standard = quick == 0 ? standard - 1 : standard;
		quick = quick == 0 ? 1 : 0;
		move = move >= 0.5 ? move - 0.5 : 0;
		msg = "You are burdened (-1 QA, -0.5 Move).<br>Reduce your item weight to remove the penalty.";
	} else {
		// overburdened, -1 QA, -1 Move
		$("#overburdened").addClass("selected");
		standard = quick == 0 ? standard - 1 : standard;
		quick = quick == 0 ? 1 : 0;
		move = move >= 1 ? move - 1 : 0;
		msg = "You are overburdened (-1 QA, -1 Move).<br>Reduce your item weight to remove the penalty.";
	}
	$("#standard").val(standard);
	$("#quick").val(quick);
	$("#move").val(move);

	// if we are adding or editing items, show alert if character is encumbered
	var encumbered = parseFloat(totalWeight) > capacity/4;
	if (showMsg && encumbered && !loadingItems && !suppressAlerts) {
		$("#encumbered_msg").html(msg);
		$("#encumbered_modal").modal("show");
		// scroll to item weight?
		// $('html,body').animate({scrollTop: $("#section_weight").offset().top},'slow');
	}
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

function submitSuggestion() {
	// check nerd word value
	if ($("#nerd_word").val().toLowerCase() != keys['nerd_test']) {
		alert("That's not the secret word, nerd");
		return;
	}
	// submit suggestion
	$.ajax({
	  url: 'submit_suggestion.php',
	  data: { 'message' : $("#suggestion").val() },
	  ContentType: "application/json",
	  type: 'POST',
	  success: function(response){
	  	if (response == 'ok') {
	  		alert("Thanks for your suggestion! I'm sure someone is hard at work to address your concern.");
	  	}
	  }
	});
}

// craete any element type with a class and id value
function createElement(type, className, appendTo, id=null) {
	return $('<'+type+' />', {
		'id': id,
	  'class': className,
	}).appendTo(appendTo);
}

// create an input element
function createInput(additionalClass, type, name, value, appendTo, id=null) {
	var input = $('<input />', {
		'id': id,
		'class': 'form-control '+additionalClass,
		'type': type,
  	'name': name,
  	'value': value
	}).appendTo(appendTo);
	return input;
}

// generate a uuid
function uuid() {
  return ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g, c =>
    (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
  );
}

// check an array for a string - non-case sensitive comparison
function includesIgnoreCase(array, string) {
	included = false;
	for (var i in array) {
		if (array[i].toLowerCase() == string.toLowerCase()) {
			included = true;
		}
	}
	return included;
}

// capitalize a string
function capitalize(string) {
  return string.charAt(0).toUpperCase() + string.slice(1);
}