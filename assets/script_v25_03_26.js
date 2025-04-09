// php array values
var campaign;
var user;
var xp_awards;
// booleans to determine rules and element visibility for various operating modes
var inputChanges = false;
var allocatingAttributePts = false;
var pointsAllocated = 0;
var maxSkillsAllocated = 1;
var characterCreation = false;
var adminEditMode = false;
var addingNewSchool = false;
// show encumbered alert
var loadingItems = false;
var suppressAlerts = false;
// arrays used to restore attribute/training values on cancel allocate points
var attributeVals = [];
var trainingVals = [];
// ID of input to gain focus on modal show
var focus_id = "";
var submitForm = false;

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
	"Rock'em Sock'em Robots!",
	"Toaster Pastry Pop Tarts!",
	"Testicular torsion!",
	"Potato pancakes!",
	"Yahtzee!",
	"Fart on a forklift!",
	"Blammo!",
	"Kapow!",
	"Excelsior!",
	"Party on!",
	"Cowabunga!",
	"Puddin' pops!",
	"Vroom vroom, bitches!"
];

var banners = [
	'banner-1',
	'banner-2',
	'banner-3',
	'banner-4',
	'banner-5'
];

var activeBanner = "banner-5";

// banner slider - every ten seconds
var slide = window.setInterval(function() {
	// get current active image
	$(".banner-image").each(function() {
		if ($(this).hasClass("active")) {
			// slide next image left
			var index = banners.indexOf(activeBanner) == banners.length - 1 ? 0 : banners.indexOf(activeBanner) + 1;
			var id = banners[index];
			$("#"+id).show().animate({
			    left: "0"
			}, 500, function() {
				// change active class
				$("#"+activeBanner).removeClass("active").css("left", "100vw").hide();
				activeBanner = id;
				$("#"+activeBanner).addClass("active");
			});
		}
	});
}, 15000);

// detect if we're on a tablet, mobile or touchscreen
let is_mobile_width = $('#is_mobile').css('display') == 'block';
let is_touchscreen = $('#is_touchscreen').css('display') == 'block';
let is_mobile = is_mobile_width || is_touchscreen;

// hover function for clearable inputs
function tog(v) {
	return v ? 'addClass' : 'removeClass';
}

// back to select campaign page
function back() {
	window.location.replace("/");
}

// text input clear functions
$(document).on('input', '.clearable', function() {
    $(this)[tog(this.value)]('x');
}).on('mousemove', '.x', function(e) {
    $(this)[tog(this.offsetWidth-18 < e.clientX-this.getBoundingClientRect().left)]('onX');   
}).on('click', '.onX', function() {
    $(this).removeClass('x onX').val('');
    if (this.id == "standard_talent_name") {
    	$("#feat_description").val("").height("125px");
    }
    if (this.id == "magic_talent_name") {
    	$("#feat_description").val("").height("125px");
		// hide additional inputs
		hideAdditionalInputs("", "");
    }
    if (this.id == "focus_name") {
    	$("#focus_name2").val("").hide();
    }
});

// prevent focus on resilience adjust buttons
$('.btn-resilience').on('mousedown', 
    function(event) {
        event.preventDefault();
    }
);

function deleteDatabaseObject(table, id) {
	if ($("#can_edit").val() == 0) {
		return;
	}
	// console.log("deleteDatabaseObject");
	$.ajax({
		url: '/scripts/delete_database_object.php',
		data: { 'table' : table, 'id' : id, 'user_id' : $("#user_id").val(), 'login_id' : $("#login_id").val() },
		ContentType: "application/json",
		type: 'POST',
		success: function(response) {
			// console.log(response);
		}
	});
}

function insertDatabaseObject(table, object, columns, user_id=null, override=false) {
	if ($("#can_edit").val() == 0) {
		return;
	}
	// console.log("insertDatabaseObject");
	$.ajax({
		url: '/scripts/insert_database_object.php',
		data: { 'table' : table, 'data' : object, 'columns' : columns, 'user_id' : user_id == null ? $("#user_id").val() : user_id, 'login_id' : $("#login_id").val() },
		ContentType: "application/json",
		type: 'POST',
		success: function(response) {
			// console.log(response);
			// get back insert ID and update object
			object['id'] = response;
			// check for callback function
			if (object.postInsertCallback != undefined) {
				object.postInsertCallback(response);
			}
		}
	});
}

// send updated object to database
function updateDatabaseObject(table, object, columns) {
	if ($("#can_edit").val() == 0) {
		return;
	}
	// console.log("updateDatabaseObject");
	$.ajax({
		url: '/scripts/update_database_object.php',
		data: { 'table' : table, 'data' : object, 'columns' : columns, 'user_id' : $("#user_id").val(), 'login_id' : $("#login_id").val() },
		ContentType: "application/json",
		type: 'POST',
		success: function(response) {
			// console.log(response);
		}
	});
}

// send updated value to database
function updateDatabaseColumn(table, column, value, id) {
	if ($("#can_edit").val() == 0) {
		return;
	}
	// console.log("updateDatabaseColumn");
	$.ajax({
		url: '/scripts/update_database_column.php',
		data: { 'table' : table, 'column' : column, 'value' : value, 'id' : id, 'user_id' : $("#user_id").val(), 'login_id' : $("#login_id").val() },
		ContentType: "application/json",
		type: 'POST',
		success: function(response) {
			// console.log(response);
		}
	});
}

// track changes to inputs and autosave to database
$(".track-changes").on("change", function() {
	if ($("#can_edit").val() == 0) {
		return;
	}
	let id = $(this).data("id");
	let table = $(this).data("table");
	let column = $(this).data("column") == undefined ? $(this).attr("name") : $(this).data("column");
	// check old value
	let oldVal = user[column];
	let newVal = $(this).val();
	if (oldVal != newVal) {
		user[column] = newVal;
		updateDatabaseColumn(table, column, newVal, id);
	}
});

$("#level").on("change", function() {
	// adjust xp
	$("#xp").val(levels[$(this).val()-1]).trigger("change");
	user['xp'] = levels[$(this).val()-1];
	$("#next_level").html(levels[$(this).val()]);
	// adjust attribute points
	$("#attribute_pts").val($(this).val()*12 - pointsAllocated).trigger("change");
	user['attribute_pts'] = $(this).val()*12 - pointsAllocated;
	// set max skills/focus to level value
	maxSkillsAllocated = $(this).val();
});

$(".motivator-input").on("click", function() {
	editMotivators();
});

// on hover input, show overflow text as tool tip
$("input").each(function() {
	var span = $('<span/>', {
	    id: $(this).attr("id")+"_output",
	    class: 'tooltiptext'
	}).appendTo($(this).parents()[0]);
	
	// make sure input element has an ID assigned and make sure it's not a radio button
	if ($(this).attr("id") != undefined && $(this).attr("type") != "radio") {
		$(this).hover(
			// mousein - show overflow text
			function() {
			  	// check for input content
			  	if ($(this).val() != "") {
			  		$(span).html($(this).val());
			  		// check for overflow and reveal span, unless input already has focus
			  		if ($(span).width() > $(this).width() && !$(this).is(":focus")) {
			  			$(span).show();
			  			$(this).addClass("input-hovered");
			  			// disable scrolling on hover, but enable on focus
			  			$(this).on("scroll", function(e) {
			  				$(this).scrollLeft(0);
			  			});
			  		}
			  	}
			// mouseout - hide overflow text
			}, function() {
			  	$(span).hide();
			  	$(this).removeClass("input-hovered");
			}
		);
	}
});

$("input").on("change", function() {
	inputChanges = true;
});

// hide overflow text on input focus; and disallow focus on readonly inputs
$("input").on("focus", function() {
	if (!$(this).is("[readonly]")) {
		$(this).removeClass("input-hovered");
		$(this).unbind("scroll");
	} else {
		$(this).blur();
	}
});

// trigger 'unsaved changes' alert when leaving the page
$(window).on("beforeunload", function(e) {
	if (user['is_new'] && inputChanges && !submitForm) {
  		return "Unsaved changes will be lost."; // custom message will not be displayed; message is browser specific
	}
});

// enable misc highlight functions
if (!is_mobile) {
	$("#size").hover(function () {
		$(this).addClass("highlight");
	}, 
	function () {
		$(this).removeClass("highlight");
	});
	$("#age_category").hover(function () {
		$(this).addClass("highlight");
	}, 
	function () {
		$(this).removeClass("highlight");
	});
}

// enable attribute edit btn hide / show on hover; don't hide on mobile
if (is_mobile) {
	$(".attribute-col").each(function() {
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

$("#attribute_pts").on("input change", function() {
	if (parseInt($(this).val()) == 0 && !characterCreation) {
		$("#attribute_pts_span").addClass("disabled");
	} else {
		$("#attribute_pts_span").removeClass("disabled");
	}
});

// show hidden inputs on skill type radio select
$(".skill-check").on("click", function() {
	$("#esoteric_name").hide();
	$("#skill_name").hide();
	$("#training_name").hide();
	$("#focus_name").hide();
	$("#focus_name2").hide();
	$("#school_name").hide();
	$("#attack_name").hide();
	$("#shoot_name").hide();
	$("#throw_name").hide();
});
$("#attack").on("click", function() {
	$("#attack_name").show();
});
$("#shoot").on("click", function() {
	$("#shoot_name").show();
});
$("#throw").on("click", function() {
	$("#throw_name").show();
});
$("#esoteric").on("click", function() {
	$("#esoteric_name").show();
});
$("#skill").on("click", function() {
	$("#skill_name").show();
});
$("#training").on("click", function() {
	$("#training_name").show();
});
$("#focus").on("click", function() {
	$("#focus_name").show();
});
$("#school").on("click", function() {
	$("#school_name").show();
});

// highlight attributes
if (!is_mobile) {
	$(".attribute-row").hover(function() {
		$(this).addClass("highlight");
	}, function() {
		$(this).removeClass("highlight");
	});
}

// launch GM modal
function settings() {
	toggleMenu();
	window.location.href = "/admin.php?campaign="+$('#campaign_id').val();
}

// enter GM edit mode
function GMEditMode() {

	adminEditMode = true;
	
	// show GM menu, hide hamburger menu
	toggleMenu();
  	$(".gm-menu").toggleClass("active");
	$(".glyphicon-menu-hamburger").hide().toggleClass("active");
	$(".lock-status").hide();
	$(".lock-status-open").show();
	$("#can_edit").val(1);

	// enable editing other inputs, etc
	$(".track-changes").attr("readonly", false);
	$(".motivator-pts").attr("readonly", false);
	$("select").attr("disabled", false);
	$(".modal-body textarea").attr("readonly", false);
	$(".modal-body input").attr("readonly", false);
	$(".modal-body select").attr("disabled", false);
	$(".glyphicon-plus-sign").attr("data-toggle", "modal");

	// show hidden attribute icons
	$(".attribute-col").find(".hidden-icon").each(function() {
		$(this).show();
	});

	// show new feat button
	$("#feats").find(".glyphicon").show();
	$("#new_feat_btn").show();

	// hide edit buttons
	if (characterCreation) {
		$(".attribute-col").unbind("mouseenter mouseleave");
		if (is_mobile) {
			$(".attribute-col").each(function() {
				$(this).find(".glyphicon-edit").hide();
			});
		}
	}

	// enable edit attribute pts input, enable edit xp input
	$("#attribute_pts").attr("readonly", false).attr("type", "number");
	$("#xp").attr("readonly", false).attr("type", "number").attr("data-toggle", null);
	$(".motivator-input").addClass("pointer");
	$("#size").attr("data-toggle", "modal").removeClass("cursor-auto");
	$("#age_category").attr("data-toggle", "modal").removeClass("cursor-auto");
	$("#race").attr("readonly", false);
}

// exit GM edit mode - restore inputs and elements
function endGMEdit() {
	adminEditMode = false;
  	$(".gm-menu").toggleClass("active");
	$(".glyphicon-menu-hamburger").show();
	$(".attribute-col").find(".hidden-icon").each(function() {
		$(this).hide();
	});
	$("#xp").attr("readonly", true).attr("type", "").attr("data-toggle", "modal");
	$(".lock-status").show();
	$(".lock-status-open").hide();
	$("#can_edit").val(0);

	// disable inputs, etc
	$(".track-changes").attr("readonly", true);
	$(".motivator-pts").attr("readonly", true);
	$("select").attr("disabled", true);
	$(".modal-body textarea").attr("readonly", true);
	$(".modal-body input").attr("readonly", true);
	$(".modal-body select").attr("disabled", true);
	$(".glyphicon-plus-sign").attr("data-toggle", null);
	$("#user_select").attr("disabled", false);

	// add hover functions back to edit buttons
	if (characterCreation) {
		if (!is_mobile) {
			$(".attribute-col").each(function() {
				$(this).hover(function() {
					$(this).find('.hover-hide').show();
				},
				function() {
					$(this).find('.hover-hide').hide();
				});
			});
		} else {
			$(".attribute-col").each(function() {
				$(this).find(".hover-hide").show();
			});
		}
	} else {
		$("#new_feat_btn").hide();
		$("#race").attr("readonly", true);
		$("#size").attr("data-toggle", null).addClass("cursor-auto");
		$("#age_category").attr("data-toggle", null).addClass("cursor-auto");
		$("#feats").find(".glyphicon").hide();
		$("#attribute_pts").attr("readonly", true).attr("type", "");
		$(".motivator-input").removeClass("pointer");
	}
}

// show user menu
function toggleMenu() {
  $(".nav-menu").toggleClass("active");
  $(".glyphicon-menu-hamburger").toggleClass("active");
}

// start point allocation mode
function allocateAttributePts(e) {
	// return if button is disabled
	if ($(e).hasClass("disabled")) {
		return;
	}

	// check userTrainings for new, set to not new
	for (var i in userTrainings) {
		if (userTrainings[i].is_new) {
			userTrainings[i].is_new = false;
		}
	}

	// show hidden attribute icons
	$(".attribute-col").find(".hidden-icon").each(function() {
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
			$(".attribute-col").each(function() {
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
	attributeVals = [];
	trainingVals = [];
	// save attribute values
	for (var i in attributes) {
		attributeVals.push($("#"+attributes[i]+"_val").val());
	}
 	// save training values
	$(".training-row").each(function() {
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
	// restore attribute-col hover function for edit buttons
	if (characterCreation) {
		if (!is_mobile) {
			$(".attribute-col").each(function() {
				$(this).hover(function() {
					$(this).find('.hover-hide').show();
				},
				function() {
					$(this).find('.hover-hide').hide();
				});
			});
		} else {
			$(".attribute-col").each(function() {
				$(this).find(".hover-hide").show();
			});
		}
	} else {
		$("#new_feat_btn").hide();
		$(".feat").find(".glyphicon-remove").hide();
	}
	if (accept) {
		// set pointsAllocated value
		pointsAllocated += $("#attribute_pts").val() - $(".attribute-count").html().split(" Points")[0];
		// update #attribute_pts input val from .attribute-count
		$("#attribute_pts").val($(".attribute-count").html().split(" Points")[0]).trigger("change");
		if (parseInt($("#attribute_pts").val()) == 0 && !characterCreation) {
			$("#attribute_pts_span").addClass("disabled");
		}
		// mark all trainings and talents as not new
		for (var i in userTrainings) {
			userTrainings[i].is_new = false;
		}
		for (var i in userTalents) {
			userTalents[i].is_new = false;
		}
	} else {

		// cancel edit attributes - restore old values and remove new trainings/talents
		// restore attribute values
		for (var i in attributes) {
			$("#"+attributes[i]+"_val").val(attributeVals[i]);
			$("#"+attributes[i]+"_text").html(attributeVals[i] >= 0 ? "+"+attributeVals[i] : attributeVals[i]);
			adjustAttribute(attributes[i], 0);
		}
		// restore training values
		for (var key in trainingVals) {
			$("#"+key+"_val").val(trainingVals[key]);
			$("#"+key+"_text").html(trainingVals[key] >= 0 ? "+"+trainingVals[key] : trainingVals[key]);
			for (var i in userTrainings) {
				if (userTrainings[i].id == key.split("training_")[1]) {
					userTrainings[i].value = trainingVals[key];
				}
			}
			adjustAttribute(key, 0);
		}

		// remove new skill trainings
		for (var i in userTrainings) {
			if (userTrainings[i].is_new) {
				userTrainings[i].DOM_element.remove();
				deleteDatabaseObject('user_training', userTrainings[i].id);
				userTrainings[i] = null;
			}
		}
		userTrainings = userTrainings.filter(function (el) {
		 	return el != null;
		});

		// remove new talents
		for (var i in userTalents) {
			if (userTalents[i].is_new) {
				userTalents[i].DOM_element.remove();
				deleteDatabaseObject('user_feat', userTalents[i].id);
				userTalents[i] = null;
			}
		}
		userTalents = userTalents.filter(function (el) {
		 	return el != null;
		});
	}
}

// save motivator point value on focus
$('.motivator-pts').on('focusin', function() {
    $(this).data('val', $(this).val());
});

// on motivator pt change, adjust bonus and xp
$(".motivator-pts").on("change touchend", function() {

	// get current and previous values and update userMotivators
	let m_id = this.id.split("motivator_pts_")[1];
	let current = $(this).val();
	if (current == "") {
		current = 0;
		$(this).val(0);
	}
	let prev = $(this).data('val');
	$(this).data('val', current);
	userMotivators[m_id]['points'] = current;

	// check if GM edit mode
	if (!adminEditMode) {

		// adjust xp if primary motivator
		if (userMotivators[m_id]['primary_'] == 1) {
			$("#xp").val( parseInt($("#xp").val()) + parseInt($("#level").val()) * (current - prev) ).trigger("change");
		}

		// if increasing a non-primary motivator, check if it has exceeded a primary motivator (personality crisis)
		else if (current > prev) {
			for (var i in userMotivators) {
				if (current > userMotivators[i]['points']) {
					// alert user and prompt to change primary motivators
					let motivator_name = userMotivators[m_id]['motivator'];
					$("#crisis_name").html(motivator_name);
					$("#crisis_modal").modal("show");
					// get other three motivators and set labels for the next modal
					var iter = 0;
					$(".motivator-row").hide();
					for (var j in userMotivators) {
						if (userMotivators[j]['points'] < current) {
							$("#remove_motivator_"+iter).val(userMotivators[j]['motivator']);
							$("#motivator_"+iter+"_label").html(userMotivators[j]['motivator'] + " ("+userMotivators[j]['points']+" Pts)");
							$("#motivator_"+iter+"_row").show();
							iter++;
						}
					}
					break;
				}
			}
		}
	}

	// update value in database
	updateDatabaseColumn('user_motivator', 'points', current, userMotivators[m_id]['id']);

	// update bonuses
	setMotivatorBonus();
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
			var index = Math.floor(Math.random() * (exclamations.length));
			alert(exclamations[index]+" You made it to level "+level+"!");
			// increase attribute points
			var innovation_val = parseInt($("#innovation_val").val());
			var innovation_mod = innovation_val > 0 ? Math.floor(innovation_val/2) : 0;
			var attribute_pts = $("#attribute_pts").val() == undefined || $("#attribute_pts").val() == "" ? 
				0 : parseInt($("#attribute_pts").val());
			$("#attribute_pts").val(attribute_pts + ( (12 + innovation_mod) * (level - current) )).trigger("change");
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
$("#morale").on("input change", function() {
	// max morale 8
	if ($(this).val() > 8) {
		$(this).val(8);
	}
	setMoraleEffect(parseInt($(this).val()));
	setMotivatorBonus();
});

// TODO can simplify these rules now that damage increase/decrease is always 1
// damage adjustment button functions
function adjustResilience(val) {
	if ($("#can_edit").val() == 0) {
		return;
	}
	if (parseInt($("#damage").val()) + parseInt(val) < $("#damage").attr("min")) {
		return;
	}
	$("#damage").val( parseInt($("#damage").val()) + parseInt(val) ).trigger("change");
}

// set max damage to resilience
$("#damage").attr("max", $("#resilience").val());

// on damage change, modify wounds
$("#damage").on("change", function() {
	var damage = parseInt($(this).val());
	let resilience = parseInt($(this).attr("max"));

	// check for wound reduction
	if (damage == -1) {
		damage = resilience - 1;
		$("#damage").val(resilience - 1);
		$("#wounds_val").val( parseInt($("#wounds_val").val()) - 1 <= 0 ? 0 : parseInt($("#wounds_val").val()) - 1 );
	}

	// check for wound increase
	while ( parseInt( $(this).val() ) >= parseInt( $(this).attr("max") ) ) {
		$(this).val( $(this).val() - $(this).attr("max") ).trigger("input");
		damage = parseInt($(this).val());
		$("#wounds_val").val( parseInt($("#wounds_val").val()) + 1 >= 4 ? 4 : parseInt($("#wounds_val").val()) + 1 );
	}
	let wounds = parseInt( $("#wounds_val").val() );
	var totalDamage = damage + (wounds * resilience);
	$("#damage").attr("min", wounds == 0 ? 0 : -1);
	// check for max total damage
	if (totalDamage >= resilience * 4) {
		totalDamage = resilience * 4;
		$("#damage").val(0);
	}
	$("#total_damage").val(totalDamage).trigger("change");
	$("#wounds").val($("#wounds_val option:selected").text());

	// get text value for wound penalty
	let penalties = [0, -1, -3, -5];
	if (parseInt($("#wounds_val").val()) == 4) {
		$("#wound_penalty").val("Yer Dead");
	} else {
		let wound_penalty = penalties[ parseInt( $("#wounds_val").val() ) ];
		// check for diehard talent, reduce wound penalty by 1
		if (hasTalent("Diehard") && wound_penalty < 0) {
			wound_penalty += 1;
		}
		// check for low pain tolerance trait, increase wound penalty by 1
		if (hasTalent("Low Pain Tolerance") && wound_penalty < 0) {
			wound_penalty -= 1;
		}
		$("#wound_penalty").val(wound_penalty == 0 ? "None" : wound_penalty);
	}
});

function editAge(modify_user) {
	let age = $("#character_age_select").val();

	if (modify_user) {
		user['age_category'] = age;
		updateDatabaseColumn('user', 'age_category', age, user['id']);
	}

	// update size texts
	let age_text = age == "Child" ? "Child; -2 Power, -1 Dexterity, -2 Intelligence, -1 Spirit" : ( age == "Adolescent" ? "Adolescent; -1 Power, +1 Dexterity" : (age == "Middle-Aged" ? "Middle-Aged; -1 Dexterity, +1 Spirit" : ( age == "Elder" ? "Elder; -1 Power, -2 Dexterity, +1 Intelligence, +2 Spirit" : ( age == "Venerable" ? "Venerable; -2 Power, -3 Dexterity, +2 Intelligence, +3 Spirit" : age))));
	$("#age_category_text").html(age_text);
	$("#age_category_val").val(age);

	// update attributes
	let age_power_mod = age == "Child" ? -2 : ( age == "Adolescent" ? -1 : (age == "Middle-Aged" ? 0 : ( age == "Elder" ? -1 : ( age == "Venerable" ? -2 : 0))));
	$("#age_power_mod").val(age_power_mod);
	let dexterity_mod = age == "Child" ? -1 : ( age == "Adolescent" ? +1 : (age == "Middle-Aged" ? -1 : ( age == "Elder" ? -2 : ( age == "Venerable" ? -3 : 0))));
	$("#age_dexterity_mod").val(dexterity_mod);
	let intelligence_mod = age == "Child" ? -2 : ( age == "Adolescent" ? 0 : (age == "Middle-Aged" ? 0 : ( age == "Elder" ? +1 : ( age == "Venerable" ? +2 : 0))));
	$("#age_intelligence_mod").val(intelligence_mod);
	let spirit_mod = age == "Child" ? -1 : ( age == "Adolescent" ? 0 : (age == "Middle-Aged" ? +1 : ( age == "Elder" ? +2 : ( age == "Venerable" ? +3 : 0))));
	$("#age_spirit_mod").val(spirit_mod);

	// check for size mod
	let size_power_mod = $("#size_power_mod").val() == "" ? 0 : parseInt($("#size_power_mod").val());

	// update attribute text
	let strength_mod = parseInt($("#strength_val").val()) + age_power_mod + size_power_mod;
	let fortitude_mod = parseInt($("#fortitude_val").val()) + age_power_mod + size_power_mod;
	let speed_mod = parseInt($("#speed_val").val()) + dexterity_mod;
	let agility_mod = parseInt($("#agility_val").val()) + dexterity_mod;
	let intellect_mod = parseInt($("#intellect_val").val()) + intelligence_mod;
	let innovation_mod = parseInt($("#innovation_val").val()) + intelligence_mod;
	let intuition_mod = parseInt($("#intuition_val").val()) + spirit_mod;
	let vitality_mod = parseInt($("#vitality_val").val()) + spirit_mod;

	$("#strength_text").html(strength_mod >= 0 ? "+"+strength_mod : strength_mod);
	$("#fortitude_text").html(fortitude_mod >= 0 ? "+"+fortitude_mod : fortitude_mod);
	$("#speed_text").html(speed_mod >= 0 ? "+"+speed_mod : speed_mod);
	$("#agility_text").html(agility_mod >= 0 ? "+"+agility_mod : agility_mod);
	$("#intellect_text").html(intellect_mod >= 0 ? "+"+intellect_mod : intellect_mod);
	$("#innovation_text").html(innovation_mod >= 0 ? "+"+innovation_mod : innovation_mod);
	$("#intuition_text").html(intuition_mod >= 0 ? "+"+intuition_mod : intuition_mod);
	$("#vitality_text").html(vitality_mod >= 0 ? "+"+vitality_mod : vitality_mod);
}

function editSize(modify_user) {
	// set size text
	let size = $("#character_size_select").val();
	// write size to database
	if (modify_user) {
		user['size'] = size;
		updateDatabaseColumn('user', 'size', size, user['id']);
	}
	// update size texts
	let size_text = size == "Tiny" ? "Tiny; -4 Power, +4 Defend/Dodge/Stealth, -20 Move" : ( size == "Small" ? "Small; -2 Power, +2 Defend/Dodge/Stealth, -10 Move" : (size == "Large" ? "Large; +2 Power, -2 Defend/Dodge/Stealth, +10 Move" : ( size == "Giant" ? "Giant; +4 Power, -4 Defend/Dodge/Stealth, +20 Move" : size)));
	$("#character_size_text").html(size_text);
	$("#character_size_val").val(size);
	setDodge();
	setDefend();
	updateTotalWeight(false); // update movement

	// check for age mod
	let age_power_mod = $("#age_power_mod").val() == "" ? 0 : parseInt($("#age_power_mod").val());

	// update strength and fortitude
	let size_power_mod = size == "Tiny" ? -4 : (size == "Small" ? -2 : (size == "Large" ? 2 : (size == "Giant" ? 4 : 0)));
	$("#size_power_mod").val(size_power_mod);
	let strength_mod = parseInt($("#strength_val").val()) + size_power_mod + age_power_mod;
	let fortitude_mod = parseInt($("#fortitude_val").val()) + size_power_mod + age_power_mod;
	$("#strength_text").html(strength_mod >= 0 ? "+"+strength_mod : strength_mod);
	$("#fortitude_text").html(fortitude_mod >= 0 ? "+"+fortitude_mod : fortitude_mod);
	
	// check for stealth training
	// for (var i in userTrainings) {
	// 	if (userTrainings[i]['name'].toLowerCase() == "stealth") {
	// 		let stealth_mod = parseInt($("#training_"+userTrainings[i]['id']+"_val").val()) - power_mod;
	// 		$("#training_"+userTrainings[i]['id']+"_text").html(stealth_mod >= 0 ? "+"+stealth_mod : stealth_mod);
	// 	}
	// }

	// update height dropdown values
	let height = $("#height").val();
	$("#height").html("");
	let lower = size == "Tiny" ? 24 : ( size == "Small" ? 36 : (size == "Large" ? 84 : ( size == "Giant" ? 108 : 60)));
	let upper = size == "Tiny" ? 35 : ( size == "Small" ? 59 : (size == "Large" ? 107 : ( size == "Giant" ? 143 : 84)));
	for (var i = lower; i < upper; i++) {
		var feet = 0;
		var inches = i;
		while (inches > 11) {
			feet += 1;
			inches -= 12;
		}
		$('<option />', {
			'value': i,
		  	'text': feet+"' "+inches+"\"",
		  	'selected': height == i ? true : false
		}).appendTo($("#height"));
	}
}

function setDodge() {
	// get size value and agility value
	let size = $("#character_size_select").val();
	let agility = parseInt($("#agility_val").val());
	let size_mod = size == "Tiny" ? 4 : (size == "Small" ? 2 : (size == "Large" ? -2 : ( size == "Giant" ? -4 : 0)));
	var dodge = (agility >= 0 ? Math.floor(agility/2) : Math.ceil(agility/3)) + size_mod;
	// check for feats - Lightning Reflexes and Relentless Defense
	if (hasTalent("Lightning Reflexes")) {
		let speed = parseInt($("#speed_val").val());
		dodge += Math.floor(speed/2);
	}
	if (hasTalent("Relentless Defense") && isTrained("Brawl")) {
		let brawl = getTraining("Brawl").value;
		dodge += Math.floor(brawl/2);
	}
	$("#dodge").val(dodge);
}

function setDefend() {
	// get size value and agility value
	let size = $("#character_size_select").val();
	let agility = parseInt($("#agility_val").val());
	let size_mod = size == "Tiny" ? 4 : (size == "Small" ? 2 : (size == "Large" ? -2 : ( size == "Giant" ? -4 : 0)));
	let defend = 10 + agility +size_mod;
	// check for weapon defend modifier
	var bonus = 0;
	for (var i in userWeapons) {
		if (userWeapons[i].equipped > 0 && userWeapons[i].defend != null) {
			for (var j = 0; j < userWeapons[i].equipped; j++) {
				bonus += parseInt(userWeapons[i].defend);
			}
		}
	}
	// check for feat - Relentless Defense
	for (var i in userTalents) {
		if (userTalents[i]['name'] == "Relentless Defense") {
			let speed = parseInt($("#speed_val").val());
			defend += Math.floor(speed/2);
		}
	}
	$("#defend").val(bonus > 0 ? defend + " (+"+bonus+")" : defend);
}

function setToughness() {
	// get base toughness value from strength
	var strength = user['strength'] == undefined ? 0 : parseInt(user['strength']);

	// check for size/age modifiers
	let size_power_mod = $("#size_power_mod").val() == "" ? 0 : parseInt($("#size_power_mod").val());
	let age_power_mod = $("#age_power_mod").val() == "" ? 0 : parseInt($("#age_power_mod").val());
	strength = strength + size_power_mod + age_power_mod;

	var toughness = strength > 0 ? Math.floor(strength/2) : Math.ceil(strength/3);
	var bonus = 0;
	for (var i in userProtections) {
		if (userProtections[i].equipped == 1) {
			bonus += userProtections[i].bonus;
		}
	}
	$("#toughness").val(bonus > 0 ? toughness + " (+"+bonus+")" : toughness);
}

// anchor link dropdown
$("#anchor_links").on("change", function() {
	if ($(this).val() != "") {
		$('html,body').animate({scrollTop: $($(this).val()).offset().top},'slow');
		$(this).val("");
	}
});

// enable hidden number input functions
enableHiddenNumbers();

// user navigation
$("#user_select").on("change", function() {
	if ($(this).val() == "") {
		window.location.replace("/?campaign="+$('#campaign_id').val());
	} else {
		window.location.replace("/?campaign="+$('#campaign_id').val()+"&user="+$(this).val());
	}
});

// highlight label on input click
enableHighlighting("input");
enableHighlighting("select");

// don't allow ':' character in training input - used to parse value on backend
$("#training_name").on('keypress', function (event) {
	var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
	if (key == ":") {
		event.preventDefault();
	return false;
	}
});

// focus inputs on modal open -  inputs on modal close
$("#new_training_modal").on('shown.bs.modal', function() {
	$("#training_name").focus();
});
// $("#new_training_modal").on('hidden.bs.modal', function() {

// });
$("#new_weapon_modal").on('shown.bs.modal', function() {
	$("#weapon_type").trigger("change");
	$(focus_id == "" ? "#weapon_name" : focus_id).focus();
	focus_id = "";
});
$("#new_weapon_modal").on('hidden.bs.modal', function() {
	$("#weapon_modal_title").html("New Weapon");
	$("#weapon_name").val("");
	$("#weapon_damage").val("");
	$("#weapon_max_damage").val("");
	$("#weapon_range_").val("");
	$("#weapon_rof").val("");
	$("#weapon_defend").val("");
	$("#weapon_crit").val("");
	$("#weapon_notes").val("");
	$("#weapon_weight").val("");
	$("#weapon_quantity").val("");
});
$("#new_protection_modal").on('shown.bs.modal', function() {
	$(focus_id == "" ? "#protection_name" : focus_id).focus();
	focus_id = "";
});
$("#new_protection_modal").on('hidden.bs.modal', function() {
	$("#protection_modal_title").html("New Protection");
	$("#protection_name").val("");
	$("#protection_bonus").val("");
	$("#protection_notes").val("");
	$("#protection_weight").val("");
});
$("#new_healing_modal").on('shown.bs.modal', function() {
	$(focus_id == "" ? "#healing_name" : focus_id).focus();
	focus_id = "";
});
$("#new_healing_modal").on('hidden.bs.modal', function() {
	$("#healing_modal_title").html("New Healing/Potion/Drug");
	$("#healing_name").val("");
	$("#healing_quantity").val("");
	$("#healing_effect").val("");
	$("#healing_weight").val("");
});
$("#new_misc_modal").on('shown.bs.modal', function() {
	$(focus_id == "" ? "#misc_name" : focus_id).focus();
	focus_id = "";
});
$("#new_misc_modal").on('hidden.bs.modal', function() {
	$("#misc_modal_title").html("New Miscellaneous Item");
	$("#misc_name").val("");
	$("#misc_quantity").val("");
	$("#misc_notes").val("");
	$("#misc_weight").val("");
});
$("#new_note_modal").on('shown.bs.modal', function() {
	$("#note_title").focus();
});
$("#new_note_modal").on('hidden.bs.modal', function() {
	$("#note_modal_title").html("New Note");
	$("#note_title").val("");
	$("#note_note").val("");
});
$("#xp_modal").on('shown.bs.modal', function() {
	$("#add_xp").focus();
});
$("#new_password_modal").on('shown.bs.modal', function() {
	$("#new_password").focus();
	toggleMenu();
});
$("#password_modal").on('shown.bs.modal', function() {
	$("#password").focus();
	toggleMenu();
});
$("#gm_modal").on('shown.bs.modal', function() {
	$("#gm_password").focus();
	toggleMenu();
});
$("#gm_modal").on('hidden.bs.modal', function() {
	$("#gm_title").html("GM Edit Mode")
});
$("#help_modal").on('shown.bs.modal', function() {
	// toggleMenu();
});

$("#encumbered_modal").on('hidden.bs.modal', function() {
	// check if we should suppress encumbered alerts
	if ($("#suppress_alert").is(":checked")) {
		suppressAlerts = true;
	}
});

// enable / disable password submit btn
$("#new_password").on("keypress", function() {
	$("#password_btn").attr("disabled", $("#new_password").val() == "" || $("#password_conf").val() == "" || $("#email").val() == "");
});
$("#password_conf").on("keypress", function() {
	$("#password_btn").attr("disabled", $("#new_password").val() == "" || $("#password_conf").val() == "" || $("#email").val() == "");
});
$("#email").on("keypress", function() {
	$("#password_btn").attr("disabled", $("#new_password").val() == "" || $("#password_conf").val() == "" || $("#email").val() == "");
});

// on weapon-name input focus, show other weapon inputs
$(".weapon-name").each(function() {
	$(this).on("focus", function() {
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
			user[attributes[i]] = 0;
			$("#"+attributes[i]+"_text").html("+0");
			$("#"+attributes[i]+"_val").val(0);
		}
	}
	// set morale effect from morale
	setMoraleEffect(user['morale'] == null ? 0 : parseInt(user['morale']));
	setMotivatorBonus();
	// set age and size text
	editAge(false);
	editSize(false);
	// set initiative
	adjustInitiative();
	// set race
	setRaceInput();
	if ($("#race").val() != "") {
		$("#race").trigger("change");
	}

	// check for pending xp awards
	if (xp_awards.length > 0 && $("#login_id").val() == user['login_id'] || user['login_id'] == -1) {
		for (var i in xp_awards) {
			if (xp_awards[i]['awarded'] == null) {
				user['xp'] = parseInt(user['xp']) + parseInt(xp_awards[i]['xp_award']);
				$("#xp").val(parseInt(user['xp']));
				// update character xp, awards, and attribute points in database
				$.ajax({
					url: '/scripts/update_xp.php',
					data: {
						'xp_award_id' : xp_awards[i]['id'],
						'user' : user['id'],
						'xp' : $("#xp").val(),
						'login_id' : $("#login_id").val()
					},
					ContentType: "application/json",
					type: 'POST',
					success: function(response) {
						// no action
					}
				});
			}
		}
		$("#xp").trigger("change");
	}
}

// enable race autocomplete
function setRaceInput(modify_user) {
	$("#race").autocomplete({
		source: function(input, add) {
			let suggestions = [];
			$.each(races, function(i, race) {
				if (race['name'].toLowerCase().includes(input['term'].toLowerCase())) {
					suggestions.push(race['name']);
				}
			});
			add(suggestions);
		},
		select: function(event, ui) {
			selectRace(ui.item.value);
		}
	});

	// add on change function
	$("#race").on("change", function() {
		selectRace($(this).val());
	});
}

// select race value from autocomplete list
function selectRace(input) {
	let race = getRace(input);

	// check for traits, Giant/Dwarf
	let sizes = [
		"Tiny",
		"Small",
		"Medium",
		"Large",
		"Giant"
	];
	let size_adjust = hasTalent("Dwarf") ? -1 : (hasTalent("Giant") ? 1 : 0);
	let base = race == false ? 2 : sizes.indexOf(race['size']);
	let old_size = $("#character_size_select").val();
	$("#character_size_select").val(sizes[base + size_adjust]);
	// check for size change
	if (old_size != $("#character_size_select").val()) {
		editSize(true);
	}

	if (race == false) {
		$("#race_traits").html("");
	} else {
		// get race traits
		$("#race_traits").html("");
		for (var i in race_traits) {
			if (race_traits[i]['race_id'] == race['id']) {
				// set race traits
				for (var j in talents) {
					if (talents[j]['name'] == race_traits[i]['trait']) {
						let newTalent = new UserTalent({
							"name":talents[j]['name'],
							"display_name":talents[j]['name'],
							"description":talents[j]['description'],
							"type":"race_trait"
						});
						let feat_container = createElement('div', 'feat', '#race_traits', "");
						let feat_title_descrip = createElement('div', '', feat_container);
						$('<p />', {
							'class': 'feat-title',
							'text': newTalent.name+" : "
						}).appendTo(feat_title_descrip);
						$('<p />', {
							'text': newTalent.description.length > 100 ? newTalent.description.substring(0,100)+"..." : newTalent.description
						}).appendTo(feat_title_descrip);
					    feat_title_descrip.on("click", function() {
					    	newTalent.edit();
					    });
						feat_container.hover(function() {
							$(this).addClass("highlight");
						}, function() {
							$(this).removeClass("highlight");
						});
					}
				}
			}
		}
	}
}

function getRace(race) {
	for (var i in races) {
		if (races[i]['name'] == race) {
			return races[i];
		}
	}
	return false;
}

function setMoraleEffect(morale) {
	var positiveEffects = {
		2: "You gain +1 Fate",
		4: "You gain 1 Motivator Bonus Each Session",
		6: "You gain +1 Fate",
		8: "Once per Session you can declare a Natural 20, leading to an Epic Success"
	};
	var negativeEffects = {
		2: "You suffer -1 Fate",
		4: "You lose 1 Motivator Bonus Each Session",
		6: "You suffer -1 Fate",
		8: "You no longer gain a Benefit on a Fate 6"
	};
	var moraleEffect = "No Effect";
	if (morale > 0) {
		for (var key in positiveEffects) {
			if (morale >= key) {
				moraleEffect = positiveEffects[key];
			}
		}
	} else {
		morale *= -1;
		for (var key in negativeEffects) {
			if (morale >= key) {
				moraleEffect = negativeEffects[key];
			}
		}
	}
	$("#morale_effect").val(moraleEffect);
	adjustFate();
}

function adjustFate() {
	var fate = 0;
	// get morale
	var morale = $("#morale").val();
	fate += morale >= 6 ? 2 : (morale >= 2 ? 1 : 0);
	fate -= morale <= -6 ? 2 : (morale <= -2 ? 1 : 0);
	// get vitality
	var vitality = $("#vitality_val").val();
	fate += vitality >= 0 ? Math.floor(vitality/2) : Math.ceil(vitality/3);
	fate = fate < 0 ? 0 : fate;
	$("#fate").val(fate);
}

// adjust attribute value
function adjustAttribute(attribute, val) {
	var originalVal = parseInt($("#" + attribute+"_val").val());
	var newVal = originalVal + parseInt(val);

	// training values should not be negative, and should not be greater than parent attribute value
	if (attribute.includes("training_")) {
		let parent_attribute = $("#"+attribute+"_row").parent().attr("id").toLowerCase();
		let parent_val = parseInt($("#"+parent_attribute+"_val").val());
		if (newVal < 0 || newVal > parent_val) {
			return;
		}
	}

	// check for attribute mods
	var modVal = 0;

	// need to check for all attribute mods
	var size_mod;
	var age_mod;
	// var stealth_mod;
	switch(attribute) {
		case "strength":
		case "fortitude":
			size_mod = $("#size_power_mod").val() == "" ? 0 : parseInt($("#size_power_mod").val());
			age_mod = $("#age_power_mod").val() == "" ? 0 : parseInt($("#age_power_mod").val());
			break;
		case "speed":
		case "agility":
			size_mod = 0;
			age_mod = $("#age_dexterity_mod").val() == "" ? 0 : parseInt($("#age_dexterity_mod").val());
			break;
		case "intellect":
		case "innovation":
			size_mod = 0;
			age_mod = $("#age_intelligence_mod").val() == "" ? 0 : parseInt($("#age_intelligence_mod").val());
			break;
		case "intuition":
		case "vitality":
			size_mod = 0;
			age_mod = $("#age_spirit_mod").val() == "" ? 0 : parseInt($("#age_spirit_mod").val());
			break;
		default:
			size_mod = 0;
			age_mod = 0;
	}

	// check for stealth training
	// var is_stealth = false;
	// for (var i in userTrainings) {
	// 	if (userTrainings[i]['name'].toLowerCase() == "stealth") {
	// 		is_stealth = userTrainings[i]['id'] == attribute.split("training_")[1];
	// 		stealth_mod = $("#size_power_mod").val() == "" ? 0 : parseInt($("#size_power_mod").val()) * -1;
	// 	}
	// }

	let newVal_NoMod = newVal;
	let originalVal_NoMod = originalVal;
	newVal = newVal + size_mod + age_mod;
	originalVal = originalVal + size_mod + age_mod;

	// check if we are allocating attribute points
	if (allocatingAttributePts) {
		// disallow lowering an attribute and only allow +1 increase from saved val
		if (!characterCreation) {
			var savedVal = attributes.indexOf(attribute) == -1 ? trainingVals[attribute] : attributeVals[attributes.indexOf(attribute)];
			if (newVal_NoMod < savedVal || newVal_NoMod > parseInt(savedVal) + 1) {
				return;
			}
		}
		var pts = parseInt($(".attribute-count").html().split(" Points")[0]);
		if (val == 1) {
			// increasing attribute; reduce points by newVal, if newVal > 0, else reduce by originalVal
			let cost = originalVal >= (8 + modVal) ? newVal + Math.round(newVal/2) : newVal;
			var newPts = pts - Math.abs(newVal > 0 ? cost : originalVal);
			// make sure we have enough points to allocate
			if (newPts >= 0) {
				$(".attribute-count").html(newPts + " Points");
			} else {
				$("#"+attribute+"_text").html(originalVal >= 0 ? "+" + originalVal : originalVal);
				$("#"+attribute+"_val").val(originalVal_NoMod).trigger("change");
				return;
			}
		} else {
			// decreasing attribute; increase points by originalVal, if original > 0, else increase by newVal
			let cost = originalVal > (8 + modVal) ? originalVal + Math.round(originalVal/2) : originalVal;
			$(".attribute-count").html(pts + Math.abs(originalVal > 0 ? cost : newVal)+" Points");
		}
	}

	$("#"+attribute+"_val").val(newVal_NoMod).trigger("change");
	$("#"+attribute+"_text").html(newVal >= 0 ? "+"+newVal : newVal);
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
			$(".weapon-select").each(function() {
				for(var i in userWeapons) {
					if ($(this).val() == userWeapons[i].name && userWeapons[i].type == 'Melee') {
						var id = this.id.slice(-1);
						let damage = userWeapons[i].damage;
						let max_damage = userWeapons[i].max_damage == null ? 0 : userWeapons[i].max_damage;
						let damage_mod =  userWeapons[i].getDamageMod();
						$("#weapon_damage_"+id).val(damage + damage_mod);
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
			updateTotalWeight(false);
			break;
		case 'agility':
			// adjust dodge and defend
			setDodge();
			setDefend();
			// adjust crit mods if necessary
			if (hasTalent("Improved Critical Hit")) {
				adjustCritMods(attribute);
			}
			break;
		case 'precision_':
			// adjust ranged weapon damage
			$(".weapon-select").each(function() {
				for(var i in userWeapons) {
					if ($(this).val() == userWeapons[i].name && userWeapons[i].type == 'Ranged') {
						var id = this.id.slice(-1);
						let damage = userWeapons[i].damage;
						let max_damage = userWeapons[i].max_damage == null ? 0 : userWeapons[i].max_damage;
						let damage_mod =  userWeapons[i].getDamageMod();
						$("#weapon_damage_"+id).val(damage + damage_mod);
					}
				}
			});
			// adjust crit mods if necessary
			if (hasTalent("Improved Critical Hit")) {
				adjustCritMods(attribute);
			}
			break;
		case 'awareness':
			// adjust initiative
			var initiative = newVal >= 0 ? 10 - Math.floor(newVal/2) : 10 - Math.ceil(newVal/3);
			$("#initiative").val(initiative);
			adjustInitiative();
			break;
		case 'vitality':
			// adjust fate and caster level
			var caster_level = 10 + newVal;
			$("#caster_level").val(caster_level);
			adjustFate();
			break;
	}
	// update userTrainings if attribute is training_x
	if (attribute.includes("training_")) {
		var training_id = attribute.split("training_")[1];
		for (var i in userTrainings) {
			if (userTrainings[i].id == training_id) {
				userTrainings[i].value = parseInt(userTrainings[i].value) + parseInt(val);
				updateDatabaseColumn('user_training', 'value', userTrainings[i].value, userTrainings[i].id);
				// if updating Brawl, might need to update Dodge (Relentless Defense)
				if (userTrainings[i].name == "Brawl" && hasTalent("Relentless Defense")) {
					setDodge();
				}
				// if updating an attack skill for an equipped weapon, may need to adjust crit mods
				if (hasTalent("Improved Critical Hit")) {
					adjustCritMods(userTrainings[i].name);
				}
			}
		}
	}
	// adjust eligibility of feat list
	setFeatList();
}

function adjustCritMods(attribute) {
	for (var j in userWeapons) {
		if (userWeapons[j].equipped && userWeapons[j].attack_attribute == attribute) {
			for (var k in userWeapons[j].equipped_index) {
				let dropdownID = userWeapons[j].equipped_index[k];
				$("#weapon_crit_"+dropdownID).val(userWeapons[j].getCritModifier());
				$("#weapon_crit_dmg_"+dropdownID).val(userWeapons[j].damage + userWeapons[j].getDamageMod() + userWeapons[j].getCritDamageMod());
			}
		}
	}
}

// show all icons for editing attribute values
function toggleHidden(col) {
	// if mobile, toggle all other edit buttons hidden
	if (is_mobile) {
		$(".attribute-col").each(function() {
			if (this.id != col) {
				$(this).find(".glyphicon-edit").toggle();
			}
		});
	} else {
		// if desktop, make sure all other hidden icons are hidden
		$(".attribute-col").each(function() {
			if (this.id != col) {
				$(this).find(".hidden-icon").hide();
			}
		});
	}
	$("#"+col).find('.hidden-icon').toggle();
	// adjust padding on .with-hidden labels
	$("#"+col).find(".with-hidden").css("padding-right", $("#"+col).find(".glyphicon-remove").is(":visible") ? "0" : "23px");
}

// hide / show weapon inputs on chevron click (mobile)
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
	submitUser();
}

// validate the form
function submitUser() {
	// show submitting message
	$("#submit_load_modal").modal("show");
	// get recaptcha token before submit
	grecaptcha.ready(function () {
		grecaptcha.execute('6Lc_NB8gAAAAAF4AG63WRUpkeci_CWPoX75cS8Yi', { action: 'new_user' }).then(function (token) {
			$("#recaptcha_response").val(token);
			submitForm = true;
			$("#user_form").submit();
		});
	});
}

$("#vows_modal").on('hidden.bs.modal', function() {
	// get selected radio value
	var vow = $("input[type='radio'][name='vow']:checked").val();
	// get vow from talents
	let vow_id = vow.split("vow_")[1];
	for (var i in talents) {
		if (talents[i]['id'] == vow_id) {
			let divine_vow = talents[i];
			$("#feat_name_val").val(divine_vow['name']);
			$("#feat_description").val(divine_vow['description']);
			$("#feat_id").val(divine_vow['id']);
			$("#feat_type").val(divine_vow['type']);
			$("#feat_cost").val(divine_vow['cost']);
			newFeat();
		}
	}
	// scroll back to feats section
	$([document.documentElement, document.body]).animate({
        scrollTop: $("#section_feats").offset().top-100
    }, 200);
});

function adjustInitiative() {
	// check if speed is higher than awareness and awareness is >= 0
	var speed = user['speed'] == null ? 0 : parseInt(user['speed']);
	var awareness = user['awareness'] == null ? 0 : parseInt(user['awareness']);
	var quick = false;
	for (var i in userTalents) {
		if (userTalents[i]['name'].toLowerCase() == "quick and the dead") {
			quick = true;
			break;
		}
	}
	if (quick && speed > awareness && awareness >= 0) {
		// set initiative based on speed value
		var initiative = 6 - Math.floor(speed/2);
		var secondary = 6 - Math.floor(awareness/2);
	} else {
		var initiative = awareness >= 0 ? 6 - Math.floor(awareness/2) : 6 - Math.ceil(awareness/3);
		var secondary = speed >= 0 ? 6 - Math.floor(speed/2) : 6 - Math.ceil(speed/3);
	}
	$("#initiative").val(initiative+" / "+secondary+"");
}

$("#new_school_modal").on('hidden.bs.modal', function() {
	// make sure that a talent has been selected
	if ($("#magic_talents").val() == "") {
		// remove the newly added school training
		for (var i in userTrainings) {
			if (userTrainings[i].is_new && userTrainings[i].magic_school == 1) {
				deleteDatabaseObject('user_training', userTrainings[i].id);
				userTrainings[i].DOM_element.remove();
				userTrainings.splice(i, 1);
			}
		}
		// if allocating points, reset point count
		if (allocatingAttributePts) {
			var pts = parseInt($(".attribute-count").html().split(" Points")[0]);
			$(".attribute-count").html(pts + 4 +" Points");
		}
		setFeatList();
	} else {
		newSchool();
	}
});


$("#weapon_type").on("change", function() {
	setAttackAttributes();
});

// set the dropdown for a weapon's attack attribute options
function setAttackAttributes() {
	$("#weapon_attack_attribute").html("");
	// get user precision/agility skills
	let precision_skills = [];
	let agility_skills = [];
	for (var i in userTrainings) {
		if (userTrainings[i].attribute_group == "precision_") {
			precision_skills.push(userTrainings[i]);
		}
		if (userTrainings[i].attribute_group == "agility") {
			agility_skills.push(userTrainings[i]);
		}
	}
	if ($("#weapon_type").val() == "Ranged") {
		// if ranged, get precision skills
		$('<option />', {
			'value': 'precision_',
			'text': 'Precision',
		}).appendTo($("#weapon_attack_attribute"));
		for (var i in precision_skills) {
			$('<option />', {
			  'value': precision_skills[i]['name'],
			  'text': precision_skills[i]['name'],
			}).appendTo($("#weapon_attack_attribute"));
		}
	} else {
		// if melee, get agility skills
		$('<option />', {
			'value': 'agility',
			'text': 'Agility',
		}).appendTo($("#weapon_attack_attribute"));
		for (var i in agility_skills) {
			$('<option />', {
			  'value': agility_skills[i]['name'],
			  'text': agility_skills[i]['name'],
			}).appendTo($("#weapon_attack_attribute"));
		}
	}
}

function updateTotalWeight(showMsg = false) {
	var totalWeight = 0
	// find all wgt inputs
	$(".item").each(function() {
		var qty = 1;
		var wgt = 0;
		$(this).find('.qty').each(function() {
			qty = $(this).val() == "" ? 1 : (isNaN($(this).val()) ? 1 : $(this).val());
		});
		$(this).find('.wgt').each(function() {
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
	var move = user['size'] == undefined ? 40 : ( user['size'] == "Tiny" ? 20 : (user['size'] == "Small" ? 30 : (user['size'] == "Large" ? 50 : ( user['size'] == "Giant" ? 60 : 40))));
	var fatigue = $("#fatigue").val();

	// adjust action/move values
	var msg = "";
	if (parseFloat(totalWeight) <= capacity/4) {
		// unhindered, no modification to actions
		$("#unhindered").addClass("selected");
		$("#encumbrance").val("Unhindered");
	} else if (parseFloat(totalWeight) <= capacity/2) {
		// encumbered, -10 Move
		$("#encumbered").addClass("selected");
		$("#encumbrance").val("Encumbered");
		msg = "You are encumbered (-10 Move).<br>Reduce your item weight to remove the penalty.";
	} else if (parseFloat(totalWeight) <= capacity/4*3) {
		// burdened, -1 QA, -10 Move
		$("#burdened").addClass("selected");
		$("#encumbrance").val("Burdened");
		msg = "You are burdened (-1 QA, -10 Move).<br>Reduce your item weight to remove the penalty.";
	} else {
		// overburdened, -1 SA, -10 Move
		$("#overburdened").addClass("selected");
		$("#encumbrance").val("Overburdened");
		msg = "You are overburdened (-1 SA, -10 Move).<br>Reduce your item weight to remove the penalty.";
	}

	// adjust fatigue for strong/frail constitution
	if (hasTalent("Strong Constitution") && fatigue == 1) {
		fatigue = 0;
	}
	if (hasTalent("Frail Constitution") && fatigue == 3) {
		fatigue = 4;
	}

	let unconcious = fatigue == 4;
	var movePenalty = 0;
	var actionPenalty = 0;

	// apply action penalty
	actionPenalty -= $("#encumbrance").val() == "Overburdened" ? 2 : ($("#encumbrance").val() == "Burdened" ? 1 : 0);
	actionPenalty -= fatigue >= 3 ? 2 : (fatigue >= 2 ? 1 : 0);
	$("#action_penalty").val(unconcious ? "Unconcious" : (actionPenalty == 0 ? "None" : actionPenalty+" QA"));
	while (standard > 0 && actionPenalty < 0) {
		standard = quick > 0 ? standard : standard - 1;
		quick = quick > 0 ? quick - 1 : quick + 1;
		actionPenalty += 1;
	}

	// apply move penalty
	movePenalty -= $("#encumbrance").val() == "Unhindered" ? 0 : 10;
	movePenalty -= fatigue == 0 ? 0 : 10;
	$("#move_penalty").val(unconcious ? "Unconcious" : (movePenalty == 0 ? "None" : movePenalty+" Move"));
	if (movePenalty == -20) {
		move = move >= 20 ? move + movePenalty : move;
	} else if (movePenalty == -10) {
		move = move >= 10 ? move + movePenalty : move;
	}

	// make sure user has at least one quick action
	quick = standard == 0 && quick == 0 ? 1 : quick;
	var run = move + speed * 5;
	$("#standard").val(unconcious ? 0 : standard);
	$("#quick").val(unconcious ? 0 : quick);
	$("#move").val(unconcious ? 0 : move+"/"+run);

	// if we are adding or editing items, show alert if character is encumbered
	var encumbered = parseFloat(totalWeight) > capacity/4;
	if (showMsg && encumbered && !loadingItems && !suppressAlerts) {
		$("#encumbered_msg").html(msg);
		$("#encumbered_modal").modal("show");
	}
}

// highlight function for input hover
function enableHighlighting(selector) {
	$(selector+":visible").each(function() {
		if (is_mobile) {
			$(this).on("focus", function(){
				$("label[for='"+this.id+"']").addClass("highlight");
			});
			$(this).on("focusout", function(){
				$("label[for='"+this.id+"']").removeClass("highlight");
			});
		} else {
			$(this).hover(function(){
				$("label[for='"+this.id+"']").addClass("highlight");
			}, function(){
				$("label[for='"+this.id+"']").removeClass("highlight");
			});
		}
	});
}

// highlight function for item labels
function enableHighlight(input, label) {
	if (is_mobile) {
		$(input).on("focus", function(){
			$("label[for='"+label+"']").addClass("highlight");
		});
		$(input).on("focusout", function(){
			$("label[for='"+label+"']").removeClass("highlight");
		});
	} else {
		$(input).hover(function(){
			$("label[for='"+label+"']").addClass("highlight");
		}, function(){
			$("label[for='"+label+"']").removeClass("highlight");
		});
	}
}

// hidden number inputs - to trigger the number keypad for text inputs (mobile)
function enableHiddenNumbers() {
	$(".hidden-number").each(function() {
		$(this).off("focus").on("focus", function() {
			// remove type='number' attribute to allow input of non-numeric input
			$(this).removeAttr("type");
			// get current input val of text field
			var input = $("#"+this.id+"_text");
			$(this).val(input.val());
			// empty text field
			input.val("");
		});
		$(this).off("focusout").on("focusout", function() {
			// copy value from number input to text input
			var input = $("#"+this.id+"_text");
			input.val($(this).val());
			// restore type='number' attribute to trigger number keyboard on next focus
			$(this).attr("type", "number");
		});
	});
}

// suggestion box submission
function submitSuggestion() {
	// check secret code in ajax
	$.ajax({
		url: '/scripts/check_secret_word.php',
		data: { 'secret_word' : $("#nerd_word").val().toLowerCase().trim() },
		ContentType: "application/json",
		type: 'POST',
		success: function(response) {
			if (response == 1) {
				// submit suggestion
				$.ajax({
					url: '/scripts/submit_suggestion.php',
					data: { 'message' : $("#suggestion").val() },
					ContentType: "application/json",
					type: 'POST',
					success: function(response) {
						if (response == 'ok') {
							alert("Thanks for your suggestion! I'm sure someone is hard at work to address your concern.");
						}
					}
				});
			} else {
				alert("That's not the secret word, nerd");
			}
		}
	});
}

// create any element type with a class and id value
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

// generate a new uuid
function uuid() {
	let uuid = ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g, c =>
		(c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
	);
	$("#uuid").val(uuid);
	return uuid;
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

// remove an object with a particular ID value from an array
function deleteArrayObject(array, target_id) {
	for (var i in array) {
		if (array[i]['id'] == target_id) {
			array.splice(i, 1);
			break;
		}
	}
}


// Database Models

// UserTalent

// modal when learning a new magic school- get a list of talents for the selected school
$("#magic_talents").on("change", function() {
	let school = $(this).val().split(":")[0];
	let talent = $(this).val().split(":")[1];
	$("#talent_descrip").height(54).val("");
	// get talent list for current school
	if (talent != "") {
		var talents = schoolTalents[school];
		for (var i in talents) {
			if (talents[i]['name'] == talent) {
				$("#talent_descrip").val(talents[i]['description']);
				$("#talent_descrip").height( $("#talent_descrip")[0].scrollHeight );
			}
		}
	}
	// show/hide additional dropdowns
	hideAdditionalInputs(talent, "");
});

// on new talent modal shown
$("#new_feat_modal").on('shown.bs.modal', function() {
	// add focus if inputs are editable
	if (!$("#standard_talent_name").prop('disabled')) {
		$("#standard_talent_name").focus();
	} else if (!$("#feat_description").prop('disabled')) {
		$("#feat_description").focus();
	}

	// TODO only change modal title in GM mode? enable editing inputs in GM mode?
	let editing = $("#feat_modal_title").html() == "Update Talent";
	$("#select_feat_type").attr("disabled", editing);
	$(".elemental_select").attr("disabled", editing);
	$(".elementalist_select").attr("disabled", editing);
	$(".superhuman_select").attr("disabled", editing);
	$("#animal_name").attr("disabled", editing);
	$("#animal_level").attr("disabled", editing);
	$("#addicted_name").attr("disabled", editing);
	$("#oath_name").attr("disabled", editing);

	// hide / show select options
	$("#select_feat_type").find("option").each(function() {
		let feat_type = $(this).val().split("_name")[0];
		if (adminEditMode && $(this).val() != "race_trait_name" && $(this).val() != "divine_vow_name") {
			$(this).attr("hidden", false);
		} else if ($(this).val() == "standard_talent_name") {
			$(this).attr("hidden", false);
		} else if ($(this).val() == "race_trait_name") {
			$(this).attr("hidden", true);
		} else if ($(this).val() == "divine_vow_name") {
			$(this).attr("hidden", true);
		} else if ($(this).val() == "martial_arts_talent_name") {
			$(this).attr("hidden", !hasTalent("Martial Arts"));
		} else if ($(this).val() == "magic_talent_name") {
			$(this).attr("hidden", !user['magic_talents']);
		} else {
			$(this).attr("hidden", !characterCreation || counts[feat_type] == undefined);
		}
	});

});

$("#new_feat_modal").on('hidden.bs.modal', function() {
	$("#feat_modal_title").html("New Talent");
	$("#feat_update_btn").addClass("hidden");
	$("#feat_submit_btn").removeClass("hidden");
	$("#feat_cancel_btn").removeClass("hidden");
	$("#standard_talent_name").val("").removeClass("x onX").attr("disabled", false);
	$("#magic_talent_name").val("").removeClass("x onX").attr("disabled", false);
	$("#martial_arts_talent_name").val("").removeClass("x onX").attr("disabled", false);
	$("#feat_description").val("").height("125px").attr("disabled", false);
	$("#feat_id").val("");
	$("#user_feat_id").val("");

	// reset all dropdowns
	$("#select_feat_type").val("standard_talent_name").trigger("change");
	$("#social_trait_name").val("").attr("disabled", false);
	$("#social_background_name").val("").attr("disabled", false);
	$("#physical_trait_pos_name").val("").attr("disabled", false);
	$("#physical_trait_neg_name").val("").attr("disabled", false);
	$("#compelling_action_name").val("").attr("disabled", false);
	$("#profession_name").val("").attr("disabled", false);
	$("#morale_trait_name").val("").attr("disabled", false);
});

// autofill talent description on option select; feat-select present for all talent types except standard and magic
$(".feat-select").on("change", function() {
	$("#feat_description").val("");
	$("#feat_id").val("");
	$("#feat_type").val("");
	$("#feat_cost").val("");
	$("#feat_name_val").val("");
	// find matching talent from selected name
	for (var i in talents) {
		if (talents[i]['name'] == $(this).val()) {
			var description = talents[i]['description'];
			let type = talents[i]['type'];
			let cost = talents[i]['cost'];
			// if morale trait, add line breaks between positive/negative states
			description = type == "morale_trait" ? description.replace('; ', '.\n\n') : description;
			// if physical trait, show attribute cost in description
			description += type == "physical_trait" ? "\n\n"+(cost > 0 ? "Attribute Point Cost: "+cost : "Attribute Point Bonus: "+(cost*-1)) : "";
			$("#feat_description").val(description);
			$("#feat_id").val(talents[i]['id']);
			$("#feat_type").val(type);
			$("#feat_cost").val(cost);
			$("#feat_name_val").val($(this).val());
		}
	}
});

// new talent modal - select talent type
$("#select_feat_type").on("change", function() {
	// hide all talent-type specific inputs - show inputs only for selected type
	$(".feat-type").addClass("hidden");
	$("#"+$(this).val()).val("").removeClass("hidden").trigger("change").removeClass("x onX");
	// clear all inputs
	$("#feat_description").val("");
	// hide additional inputs
	hideAdditionalInputs("", "");
});

// make sure all magic dropdowns have the same value
$(".elementalist_select").change(function() {
	$(".elementalist_select").val($(this).val());
});
$(".elemental_select").change(function() {
	$(".elemental_select").val($(this).val());
});
$(".superhuman_select").change(function() {
	$(".superhuman_select").val($(this).val());
});

