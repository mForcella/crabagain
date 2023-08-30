// php array values
var keys;
var campaign;
var user;
var xp_awards;
var feat_list;
var feat_reqs;
var user_feats;
var user_trainings;
var user_motivators;
var user_weapons;
var user_protections;
var user_healings;
var user_misc;
var user_notes;
// arrays to make sure elements are not assigned redundant functions - TODO can probably be removed?
var hiddenEnabled = [];
// booleans to determine rules and element visibility for various operating modes
var inputChanges = false;
var allocatingAttributePts = false;
var characterCreation = false;
var adminEditMode = false;
var addingNewSchool = false;
// show encumbered alert
var loadingItems = false;
var suppressAlerts = false;
// arrays used to restore attribute values, feats, and trainings on cancel allocate points
var attributeVals = [];
var trainingVals = [];
var trainings = []; // holds row elements
var feats = []; // holds row elements
var skills = []; // holds row elements
var equipped_weapons = []; // needed for unequipping weapons from dropdowns
// ID of input to gain focus on modal show
var focus_id = "";

// database columns for inserting/updating objects
var columns = {
	"user_weapon": [
		'name',
		'type',
		'quantity',
		'damage',
		'max_damage',
		'range_',
		'rof',
		'defend',
		'crit',
		'notes',
		'weight',
		'equipped'
	],
	"user_protection": [
		'name',
		'bonus',
		'notes',
		'weight',
		'equipped'
	],
	"user_healing": [
		'name',
		'quantity',
		'effect',
		'weight'
	],
	"user_misc": [
		'name',
		'quantity',
		'notes',
		'weight'
	],
	"user_note": [
		'title',
		'note'
	],
	"user_motivator": [
		'motivator',
		'points',
		'primary_'
	],
	"user_feat": [
		'name',
		'description',
		'feat_id'
	],
	"user_training": [
		'name',
		'value',
		'attribute_group',
		'magic_school',
		'governing_school'
	]
};

var schoolTalents = {
	"Ka": [
		{
			"name":"Elemental Master",
			"description":"You may choose 1 type of Elemental Magic when this Talent is taken: Fire, Ice or Electricity. "+
			"You can cause extreme temperature fluctuations to heat or freeze things, create protective bubbles against "+
			"hot or cold, and manipulate electricity, creating lightning and force fields or disabling electronic devices. "+
			"Attacking with this School deals Damage with either fire, cold, or electricity. Flammable objects stay on fire, "+
			"dealing additional Damage on subsequent rounds. Cold Damage slows a target, Encumbering them. Electrical Damage "+
			"is Non-lethal, Dazing targets and ignoring any armor that is completely sealed against electricity."
		},
		{
			"name":"Metal Master",
			"description":"You can control the properties of metal, as well as move metal objects with your mind by "+
			"creating magnetic fields. You can magnetize or demagnetize objects, as well as weaken metal objects, "+
			"animate metal statues and suits of armor or even transmute one metal into another."
		},
	],
	"Avani": [
		{
			"name":"Nature Master",
			"description":"You are one with the beasts and the wild places. You may speak with, and sway the disposition "+
			"of animals, or communicate with the land. You can use this to speak directly with animals or call upon them "+
			"for help, and even bond your soul with an animal, seeing through them and speaking to them telepathically. "+
			"You can alter and enhance the properties of plants, creating potions and poisons. You can purify water and "+
			"even create sustenance from little more than dirt."
		},
		{
			"name":"Elementalist",
			"description":"You have become one with the elements. You may choose one type of Elemental Magic when this "+
			"Talent is learned: Earth, Water or Air. You can manipulate dirt, mud, and rock, splitting the earth open, "+
			"raising or shifting stone, animating stone statues or even causing violent earthquakes. You can control "+
			"the movements of water, raising or lowering water levels, creating waterspouts or waves, fog, rain or snow, "+
			"and walking on water. You can alter wind patterns and create powerful storms or tornados. When causing "+
			"earthquakes and gusts of wind, the Agility or Strength DL to remain standing is the same as your roll, as "+
			"is the Strength check for any non-living structures to remain standing."
		},
	],
	"Nouse": [
		{
			"name":"Illusionist",
			"description":"You are a master at manipulating the senses of others. You can make people see, hear, taste, "+
			"smell and feel whatever you wish, however, you cannot truly control anyone’s thoughts and desires."
		},
		{
			"name":"Psychic",
			"description":"You are a master at sensing and reading minds, thoughts, and emotions and projecting your own "+
			"thoughts and feelings into theirs."
		},
		{
			"name":"Ensi",
			"description":"Your mind is merely an extension of your body and you can move, bend and break objects or "+
			"people using only your willpower."
		},
		{
			"name":"Seer",
			"description":"Your mind is untethered by time – You can even see into the past and futures of yourself and "+
			"others, catching brief glimpses of what may come to pass, within seconds or even years. The future is not set, "+
			"and this will only give you hints about what may come to pass if certain actions are taken."
		},
	],
	"Soma": [
		{
			"name":"Healer",
			"description":"You know how to manipulate the very fabric of the human body. You can Heal yourself and others, "+
			"neutralize poison, ignore Wound penalties, and fight disease. The DL for neutralizing Poison and Disease is "+
			"equal to the DL to resist. For Healing, Soma replaces the Natural Healing roll, and the character may roll "+
			"immediately for themselves or others without Rest."
		},
		{
			"name":"Tormentor",
			"description":"You know how to cause pain, burst blood vessels, and rupture organs with little more than a "+
			"touch. This Damage is always against a Toughness of 0 plus or minus Scale Modifiers only, and bypasses all "+
			"Armor as long as skin can be touched."
		},
		{
			"name":"Superhuman",
			"description":"You can accomplish incredible, physical feats with your body. Choose 2 Major Physical "+
			"Attributes when this Talent is taken (i.e. Power & Dexterity, OR Dexterity & Perception). You can greatly "+
			"increase your strength, speed, and abilities or alter your perception, to see, hear, smell, and taste "+
			"things beyond normal human perception."
		},
	]
};

var skillAutocompletes = {
	'Strength':
	{
		'skill':[
			'Swimming'
		],
		'training':[],
		'focus':[
			'Climb',
			'Jump',
			'Lift'
		],
	},
	'Fortitude':
	{
		'skill':[
			'Swimming'
		],
		'training':[],
		'focus':[
			'Resist Poison',
			'Resist Disease'
		],
	},
	'Speed':
	{
		'skill':[],
		'training':[],
		'focus':[
			'Run',
			'React'
		],
	},
	'Agility':
	{
		'skill':[
			'Ride Animal'
		],
		'training':[],
		'focus':[
			// 'Attack - specific weapon'
		],
	},
	'Awareness':
	{
		'skill':[
			'Stealth'
		],
		'training':[],
		'focus':[
			'Search',
			'Listen',
			'Smell',
			'Taste'
		],
	},
	'Precision':
	{
		'skill':[
			'Demolitions',
			'Security',
			'Drive',
			'Pilot',
			'Sleight of Hand'
		],
		'training':[],
		'focus':[
			// 'Shoot - specific weapon',
			// 'Throw - specific weapon'
		],
	},
	'Allure':
	{
		'skill':[
			'Train Animal',
			'Perform'
		],
		'training':[],
		'focus':[
			'Seduce',
			'Diplomacy',
			'Barter'
		],
	},
	'Deception':
	{
		'skill':[
			'Hacking',
			'Perform',
			'Sleight of Hand',
			'Stealth'
		],
		'training':[],
		'focus':[
			'Disguise'
		],
	},
	'Innovation':
	{
		'skill':[
			'Engineering',
			'Hacking',
			'First Aid',
			'Tactics',
			'Security',
			'Drive',
			'Pilot',
			'Sail'
		],
		'training':[],
		'focus':[
			// 'Craft - specific item'
		],
	},
	'Intellect':
	{
		'skill':[
			'Engineering',
			'First Aid',
			'Survival',
			'Demolitions',
			'Sail'
		],
		'training':[],
		'focus':[
			'Appraise',
			// 'Academia - specific area',
			// 'Culture - specific area',
			'Languages',
			// 'Religion - specific religion',
			'Magic',
			// 'Profession - specific profession'
		],
	},
	'Intuition':
	{
		'skill':[
			'Survival',
			'Tactics',
			'Ride Animal'
		],
		'training':[],
		'focus':[
			'Sense Motive',
			'Interrogate'
		],
	},
	'Vitality':
	{
		'skill':[],
		'training':[],
		'focus':[
			'Intimidate',
			'Willpower'
		],
	},
};

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
	"Party on!",
	"Cowabunga!",
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

// detect if we're on a touchscreen
var is_mobile = $('#is_mobile').css('display')=='none';

// hover function for clearable inputs
function tog(v) { return v?'addClass':'removeClass'; }

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
    if (this.id == "feat_name") {
    	$("#feat_description").val("").height("125px");
    }
    if (this.id == "magic_talent_name") {
    	$("#feat_description").val("").height("125px");
		// hide additional inputs
		$(".elemental_select").hide();
		$(".elementalist_select").hide();
		$(".superhuman_select").hide();
		$(".shapeshifter_select").hide();
    }
});

function deleteDatabaseObject(table, id) {
	if ($("#user_id").val() == "" || adminEditMode) {
		return;
	}
	// console.log("deleteDatabaseObject");
	$.ajax({
		url: '/scripts/delete_database_object.php',
		data: { 'table' : table, 'id' : id },
		ContentType: "application/json",
		type: 'POST',
		success: function(response) {
			// console.log(response);
		}
	});
}

function insertDatabaseObject(table, object, columns) {
	if ($("#user_id").val() == "" || adminEditMode) {
		return;
	}
	// console.log("insertDatabaseObject");
	$.ajax({
		url: '/scripts/insert_database_object.php',
		data: { 'table' : table, 'data' : object, 'columns' : columns, 'user_id' : $("#user_id").val() },
		ContentType: "application/json",
		type: 'POST',
		success: function(response) {
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
	if ($("#user_id").val() == "" || adminEditMode) {
		return;
	}
	// console.log("updateDatabaseObject");
	$.ajax({
		url: '/scripts/update_database_object.php',
		data: { 'table' : table, 'data' : object, 'columns' : columns },
		ContentType: "application/json",
		type: 'POST',
		success: function(response) {
			// console.log(response);
		}
	});
}

// send updated value to database
function updateDatabaseTable(table, column, value, id) {
	if ($("#user_id").val() == "" || adminEditMode) {
		return;
	}
	// console.log("updateDatabaseTable");
	$.ajax({
		url: '/scripts/update_database_column.php',
		data: { 'table' : table, 'column' : column, 'value': value, 'id': id },
		ContentType: "application/json",
		type: 'POST',
		success: function(response) {
			// console.log(response);
		}
	});
}

// track changes to inputs and autosave to database
$(".track-changes").on("change", function() {
	let id = $(this).data("id");
	let table = $(this).data("table");
	let column = $(this).data("column") == undefined ? $(this).attr("name") : $(this).data("column");
	// check old value
	let oldVal = user[column];
	let newVal = $(this).val();
	if (oldVal != newVal) {
		user[column] = newVal;
		updateDatabaseTable(table, column, newVal, id);
	}
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
	if (characterCreation && inputChanges) {
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
	if (parseInt($(this).val()) == 0) {
		$("#attribute_pts_span").addClass("disabled");
	} else {
		$("#attribute_pts_span").removeClass("disabled");
	}
});

$("#select_feat_type").on("change", function() {
	$(".feat-type").addClass("hidden");
	$("#"+$(this).val()).removeClass("hidden").trigger("change");
	if ($(this).val() == "feat_name") {
		$("#feat_name").val("").removeClass("x onX");
		$("#feat_name_val").val("");
		$("#feat_description").val("");
	}
	// hide additional inputs
	$(".elemental_select").hide();
	$(".elementalist_select").hide();
	$(".superhuman_select").hide();
	$(".shapeshifter_select").hide();
});

// show hidden inputs on skill type radio select
$("#unique").on("click", function() {
	$("#skill_name").show();
	$("#training_name").hide();
	$("#focus_name").hide();
	$("#school_name").hide();
});
$("#training").on("click", function() {
	$("#skill_name").hide();
	$("#training_name").show();
	$("#focus_name").hide();
	$("#school_name").hide();
});
$("#focus").on("click", function() {
	$("#skill_name").hide();
	$("#training_name").hide();
	$("#focus_name").show();
	$("#school_name").hide();
});
$("#school").on("click", function() {
	$("#skill_name").hide();
	$("#training_name").hide();
	$("#focus_name").hide();
	$("#school_name").show();
});


// make sure school isn't already trained
$("#school_name").on("change", function() {
	for (var i in user_trainings) {
		if (user_trainings[i]['name'] == $(this).val()) {
			$(this).val("");
		}
	}
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
	// check password
	var password = $("#gm_password").val();
	$("#gm_password").val("");
	// check admin_password
	$.ajax({
	  url: '/scripts/check_admin_password.php',
	  data: { 'password' : password, 'admin_password' : campaign['admin_password'], 'hashed_password': "" },
	  ContentType: "application/json",
	  type: 'POST',
	  success: function(response) {
	  	if (response == 1) {
	  		// enter admin edit mode
			adminEditMode = true;
			
			// show GM menu, hide hamburger menu
		  	$(".gm-menu").toggleClass("active");
			$(".glyphicon-menu-hamburger").hide().toggleClass("active");

			// show hidden attribute icons
			$(".attribute-col").find(".hidden-icon").each(function() {
				$(this).show();
			});

			// show new feat button
			$("#new_feat_btn").show();

			// show hidden feat buttons and unbind hover functions
			$("#feats").find(".glyphicon").show();

			// enable edit attribute pts input, enable edit xp input
			$("#attribute_pts").attr("readonly", false).attr("type", "number");
			$("#xp").attr("readonly", false).attr("type", "number").attr("data-toggle", null);
			$(".motivator-input").addClass("pointer");
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
		window.location.reload();
	} else {
		// confirm and save
		var conf = confirm("Save changes?");
		if (conf) {
			$("#user_form").submit();
		}
	}
}

// show user menu
function toggleMenu() {
  $(".nav-menu").toggleClass("active");
  $(".glyphicon-menu-hamburger").toggleClass("active");
}

function getDamageMod(weapon_type, damage, max_damage) {
	if (weapon_type == "Melee") {
		// check for strength modifier for melee weapons
		var damage_mod = parseInt($("#strength_val").val()) >= 0 ? 
			Math.floor(parseInt($("#strength_val").val())/2) : Math.ceil(parseInt($("#strength_val").val())/3);
	} else {
		// check for precision modifier for ranged weapons
		var damage_mod = parseInt($("#precision__val").val()) >= 0 ? 
			Math.floor(parseInt($("#precision__val").val())/2) : Math.ceil(parseInt($("#precision__val").val())/3);
	}
	// make sure damage doesn't exceed max
	if (max_damage != 0 && parseInt(damage)+parseInt(damage_mod) > max_damage) {
		damage_mod = max_damage - damage;
	}
	return damage_mod;
}

// select a weapon from the dropdown
// param: id - dropdown ID
// param: equip - only true if we are making a selection from the dropdown
function selectWeapon(id, equip=true) {
	let selected = $("#weapon_select_"+id).val();

	// get weapon object from array
	var user_weapon;
	for (var i in user_weapons) {
		if (selected == user_weapons[i]['name']) {
			user_weapon = user_weapons[i];
		}
	}
	let clearSelection = selected == "" || user_weapon['equipped'] == user_weapon['quantity'];

	// if selected is empty we need to unequip the weapon
	if (selected == "") {
		let unequipped = equipped_weapons[id-1]['name'];
		equipped_weapons[id-1] = null;
		for (var i in user_weapons) {
			if (unequipped == user_weapons[i]['name']) {
				user_weapons[i]['equipped'] = parseInt(user_weapons[i]['equipped']) - 1;
				// update equipped value in database
				updateDatabaseTable('user_weapon', 'equipped', user_weapons[i]['equipped'], user_weapons[i]['id']);
			}
		}
	}

	// // check if selection is allowed; number equipped cannot exceed quantity
	if (!equip || !clearSelection) {

		// update equipped value on user weapon
		if (equip) {
			equipped_weapons[id-1] = user_weapon;
			user_weapon['equipped'] = parseInt(user_weapon['equipped']) + 1;
			// update equipped value in database
			updateDatabaseTable('user_weapon', 'equipped', user_weapon['equipped'], user_weapon['id']);
		}

		// get weapon damage
		let damage = user_weapon['damage'];
		let max_damage = user_weapon['max_damage'] == null || user_weapon['max_damage'] == "" ? 0 : user_weapon['max_damage'];
		let damage_mod = getDamageMod(user_weapon['type'], damage, max_damage);
		$("#weapon_damage_"+id).val(damage_mod != 0 ? damage+" (+"+damage_mod+")" : damage);

		// look for crit modifiers
		let crit = getCritModifier(user_weapon);
		$("#weapon_crit_"+id).val(crit);
		$("#weapon_range_"+id).val(user_weapon['range_'] == null || user_weapon['range_'] == "" ? "-" : user_weapon['range_']);
		$("#weapon_rof_"+id).val(user_weapon['rof'] == "" ? "-" : user_weapon['rof']);

	} else {
		// clear inputs
		$("#weapon_select_"+id).val("");
		$("#weapon_damage_"+id).val("");
		$("#weapon_crit_"+id).val("");
		$("#weapon_range_"+id).val("");
		$("#weapon_rof_"+id).val("");
	}

	// update user defend value
	setDefend();
}

// calculate crit modifier based on weapon and user feats
function getCritModifier(weapon) {
	var crit = 6;
	if (weapon['crit'] != null && weapon['crit'] != '') {
		crit -= parseInt(weapon['crit']);
	}
	for (var i in user_feats) {
		if (user_feats[i]['name'].toLowerCase() == "improved critical hit") {
			crit -= 1;
			break;
		}
	}
	return crit;
}

// start point allocation mode
function allocateAttributePts(e) {
	// return if button is disabled
	if ($(e).hasClass("disabled")) {
		return;
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
	$(".feat").find(".glyphicon-remove").hide();
	if (!characterCreation) {
		$("#new_feat_btn").hide();
	}
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
	}
	if (accept) {
		// update #attribute_pts input val from .attribute-count
		$("#attribute_pts").val($(".attribute-count").html().split(" Points")[0]).trigger("change");
		if (parseInt($("#attribute_pts").val()) == 0) {
			$("#attribute_pts_span").addClass("disabled");
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
			for (var i in user_trainings) {
				if (user_trainings[i]['id'] == key.split("training_")[1]) {
					user_trainings[i]['value'] = trainingVals[key];
				}
			}
			adjustAttribute(key, 0);
		}
		// remove training names and remove training elements
		for (var i in trainings) {
			var training = trainings[i].text().split("+0")[0];
			for (var j in user_trainings) {
				if (user_trainings[j]['name'] == training) {
					deleteDatabaseObject('user_training', user_trainings[j]['id']);
					user_trainings.splice(j, 1);
				}
			}
			trainings[i].remove();
		}
		for (var i in skills) {
			var training = skills[i].text().split("+0")[0];
			for (var j in user_trainings) {
				if (user_trainings[j]['name'] == training) {
					deleteDatabaseObject('user_training', user_trainings[j]['id']);
					user_trainings.splice(j, 1);
				}
			}
			skills[i].remove();
		}
		// remove feat names and remove feat elements
		for (var i in feats) {
			var feat = feats[i].text().split(" : ")[0];
			for (var j in user_feats) {
				if (user_feats[j]['name'] == feat) {
					deleteDatabaseObject('user_feat', user_feats[j]['id']);
					user_feats.splice(j, 1);
				}
			}
			feats[i].remove();
		}
	}
}

// save motivator point value on focus
$('.motivator-pts').on('focusin', function() {
    $(this).data('val', $(this).val());
});

// on motivator pt change, adjust bonuses
$(".motivator-pts").on("change touchend", function() {

	// get current and previous values and update user_motivators
	let m_id = this.id.split("motivator_pts_")[1];
	let current = $(this).val();
	if (current == "") {
		current = 0;
		$(this).val(0);
	}
	let prev = $(this).data('val');
	$(this).data('val', current);
	user_motivators[m_id]['points'] = current;

	// adjust xp if primary motivator
	if (user_motivators[m_id]['primary_'] == 1) {
		$("#xp").val( parseInt($("#xp").val()) + parseInt($("#level").val()) * (current - prev) ).trigger("change");
	} else if (current > prev) {
		// if increasing a non-primary motivator, check if it has exceeded a primary motivator
		for (var i in user_motivators) {
			if (current > user_motivators[i]['points']) {
				// alert user and prompt to change primary motivators
				let motivator = user_motivators[m_id]['motivator'];
				$("#crisis_name").html(motivator);
				$("#crisis_modal").modal("show");
				// get other three motivators and set labels for the next modal
				var iter = 0;
				$(".motivator-row").hide();
				for (var j in user_motivators) {
					if (user_motivators[j]['points'] < current) {
						$("#remove_motivator_"+iter).val(user_motivators[j]['motivator']);
						$("#motivator_"+iter+"_label").html(user_motivators[j]['motivator'] + " ("+user_motivators[j]['points']+" Pts)");
						$("#motivator_"+iter+"_row").show();
						iter++;
					}
				}
				break;
			}
		}
	}
	// update value in database
	updateDatabaseTable('user_motivator', 'points', current, user_motivators[m_id]['id']);

	// update bonuses
	setMotivatorBonus();
});

function updateMotivators() {
	// get selected motivator to set primary_ to false
	let remove = $('input[name="update_motivators"]:checked').val();
	// get new motivator to set primary_ to true
	let add = $("#crisis_name").html();
	// update motivators in database
	for (var i in user_motivators) {
		if (user_motivators[i]['motivator'] == add) {
			user_motivators[i]['primary_'] = 1;
			updateDatabaseTable('user_motivator', 'primary_', 1, user_motivators[i]['id']);
		}
		if (user_motivators[i]['motivator'] == remove) {
			user_motivators[i]['primary_'] = 0;
			updateDatabaseTable('user_motivator', 'primary_', 0, user_motivators[i]['id']);
		}
	}
	alert("Your Primary Motivators have been updated. And remember, NO Motivator bonuses for your next session!");
	// reduce morale by -2
	$("#morale").val($("#morale").val()-2).trigger("change");
	// add / remove 'bold' class from labels
	$(".motivator-input").each(function(){
		if ($(this).val() == remove) {
			$(this).removeClass("bold");
		}
		if ($(this).val() == add) {
			$(this).addClass("bold");
		}
	});
	setMotivatorBonus();
}

function setMotivatorBonus() {
	var m_pts = 0;
	for (var i in user_motivators) {
		if (user_motivators[i]['primary_'] == 1) {
			m_pts += parseInt(user_motivators[i]['points']);
		}
	}
	var bonuses = m_pts >= 64 ? 5 : (m_pts >= 32 ? 4 : (m_pts >= 16 ? 3 : (m_pts >= 8 ? 2 : (m_pts >= 4 ? 1 : 0))));

	// check for morale modifiers
	var morale = $("#morale").val();
	bonuses += morale >= 4 ? 1 : (morale <= -4 ? -1 : 0);
	$("#bonuses").val(bonuses);
}

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
	setMoraleEffect(parseInt($(this).val()));
	setMotivatorBonus();
});

// mobile damage adjustment button functions
function adjustResilience(val) {
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
		$("#wounds_val").val( parseInt($("#wounds_val").val())-1 <= 0 ? 0 : parseInt($("#wounds_val").val())-1 );
		$("#wound_penalty_val").val( parseInt($("#wound_penalty_val").val())-1 <= 0 ? 0 : parseInt($("#wound_penalty_val").val())-1 );
	}
	// check for wound increase
	while (parseInt($(this).val()) >= parseInt($(this).attr("max"))) {
		$(this).val($(this).val() - $(this).attr("max")).trigger("input");
		$("#wounds_val").val( parseInt($("#wounds_val").val())+1 >= 4 ? 4 : parseInt($("#wounds_val").val())+1 );
		$("#wound_penalty_val").val( parseInt($("#wound_penalty_val").val())+1 >= 4 ? 4 : parseInt($("#wound_penalty_val").val())+1 );
	}
	let wounds = parseInt($("#wounds_val").val());
	var totalDamage = damage + (wounds * resilience);
	$("#damage").attr("min", wounds == 0 ? 0 : -1);
	// check for max total damage
	if (totalDamage >= resilience * 4) {
		totalDamage = resilience * 4;
		$("#damage").val(0);
	}
	$("#total_damage").val(totalDamage).trigger("change");
	$("#wounds").val($("#wounds_val option:selected").text());
	$("#wound_penalty").val($("#wound_penalty_val option:selected").text());
});

function editSize(modify_user) {
	// set size text
	let size = $("#character_size_select").val();
	// write size to database
	if (modify_user) {
		user['size'] = size;
		updateDatabaseTable('user', 'size', size, user['id']);
	}
	let size_text = size == "Small" ? "Small; +2 Defend/Dodge/Stealth, -10 Move" : (size == "Large" ? "Large; -2 Defend/Dodge/Stealth, +10 Move" : size)
	$("#character_size_text").html(size_text);
	$("#character_size_val").val(size);
	setDodge();
	setDefend();
	updateTotalWeight(false);

	// update height dropdown values
	let height = $("#height").val();
	$("#height").html("");
	let lower = size == "Small" ? 36 : (size == "Large" ? 84 : 60);
	let upper = size == "Small" ? 60 : (size == "Large" ? 108 : 84);
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
	var bonus = 0;
	for (var i in user_weapons) {
		if (user_weapons[i]['equipped'] > 0 && user_weapons[i]['defend'] != null) {
			for (var j = 0; j < parseInt(user_weapons[i]['equipped']); j++) {
				bonus += parseInt(user_weapons[i]['defend']);
			}
		}
	}
	$("#defend").val(bonus > 0 ? defend + " (+"+bonus+")" : defend);
}

function setToughness() {
	// get base toughness value from strength
	var strength = user['strength'] == undefined ? 0 : user['strength'];
	var toughness = strength > 0 ? Math.floor(strength/2) : Math.ceil(strength/3);
	var bonus = 0;
	for (var i in user_protections) {
		if (user_protections[i]['equipped'] == 1) {
			bonus += parseInt(user_protections[i]['bonus']);
		}
	}
	$("#toughness").val(bonus > 0 ? toughness + " (+"+bonus+")" : toughness);
}

// penalty inputs - if val is zero, clear input
// $(".penalty-val").on("input", function() {
// 	if ($(this).val() == 0) {
// 		$(this).val("");
// 	}
// });

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
$("#new_training_modal").on('hidden.bs.modal', function() {

});
$("#new_weapon_modal").on('shown.bs.modal', function() {
	$(focus_id == "" ? "#weapon_name" : focus_id).focus();
	focus_id = "";
});
$("#new_weapon_modal").on('hidden.bs.modal', function() {
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
	$("#note_content").val("");
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

// on modal shown, update modal title and clear inputs
$("#new_feat_modal").on('shown.bs.modal', function() {
	$("#feat_name").focus();

	// always hide feat type select if editing
	if ($("#feat_modal_title").html() == "Update Feat") {
		$("#select_feat_type").addClass("hidden");
		$("#select_feat_type_label").addClass("hidden");
	} else if (adminEditMode || characterCreation) {
		// gm edit mode - unhide select feat type elements
		$("#select_feat_type").removeClass("hidden");
		$("#select_feat_type_label").removeClass("hidden");
	} else if(user['magic_talents']) {
		// check if character has magic training
		$("#select_feat_type").removeClass("hidden");
		$("#select_feat_type_label").removeClass("hidden");
		// hide all options from select list other than standard and magic talents
		$("#select_feat_type").children().each(function(){
			if ($(this).attr("id") == undefined) {
				$(this).hide();
			}
		});
	}
	// hide additional inputs
	$(".elemental_select").hide();
	$(".elementalist_select").hide();
	$(".superhuman_select").hide();
	$(".shapeshifter_select").hide();

});
$("#new_feat_modal").on('hidden.bs.modal', function() {
	$("#feat_modal_title").html("New Feat");
	$("#feat_name").val("").removeClass("x onX").attr("disabled", false);
	$("#feat_description").val("").height("125px").attr("disabled", false);
	$("#feat_id").val("");
	$("#user_feat_id").val("");
	// reset all dropdowns
	$("#select_feat_type").val("feat_name").trigger("change");
	$("#social_trait_name").val("").attr("disabled", false);
	$("#social_background_name").val("").attr("disabled", false);
	$("#physical_trait_pos_name").val("").attr("disabled", false);
	$("#physical_trait_neg_name").val("").attr("disabled", false);
	$("#compelling_action_name").val("").attr("disabled", false);
	$("#profession_name").val("").attr("disabled", false);
	$("#morale_trait_name").val("").attr("disabled", false);
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

// autofill feat description on option select
$(".feat-select").on("change", function() {
	var description = "";
	var cost = "";
	var feat_id = "";
	for (var i in feat_list) {
		if (feat_list[i]['name'].replaceAll("'","") == $(this).val()) {
			description = feat_list[i]['description'];
			feat_id = feat_list[i]['id'];
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
	$("#feat_id").val(feat_id);
	$("#feat_name_val").val($(this).val());
});

// set feats as eligible/ineligible and set autocomplete list
function setFeatList() {
	var eligible_feats = [];
	var ineligible_feats = [];
	// set autocomplete list for feats
	$.each(feat_list, function(i, feat) {
		if (feat['type'] == 'feat' || feat['type'] == 'magic_talent') {
			var is_eligible = true;
			// feat[requirements] is an array of arrays - each array must return true
			$.each(feat['requirements'], function(j, requirements) {
				var satisfied = false;
				// requirements is an array of dictionaries (req) - one req within requirements needs to return true
				$.each(requirements, function(k, req) {
					for (key in req) {
						switch (key) {
							case 'feat':
								for (var i in user_feats) {
									satisfied = satisfied ? true : user_feats[i]['name'].includes(req[key]);
								}
								break;
							case 'training':
								for (var i in user_trainings) {
									satisfied = satisfied ? true : user_trainings[i]['name'].includes(req[key]);
								}
								break;
							case 'character_creation':
								satisfied = characterCreation;
								break;
							case 'governing':
								// get user's governing magic school value
								for (var i in user_trainings) {
									if (user_trainings[i]['governing_school'] == 1) {
										satisfied = satisfied ? true : user_trainings[i]['value'] >= req[key];
									}
								}
								break;
							default: // skill
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
	var standard_list1 = [];
	var magic_list1 = [];
	for (var i in eligible_feats) {
		if (eligible_feats[i]['type'] == 'magic_talent') {
			magic_list1.push(eligible_feats[i]['name']);
		} else {
			standard_list1.push(eligible_feats[i]['name']);
		}
	}
	standard_list1.sort();
	magic_list1.sort();
	var standard_list2 = [];
	var magic_list2 = [];
	for (var i in ineligible_feats) {
		if (ineligible_feats[i]['type'] == 'magic_talent') {
			magic_list2.push(ineligible_feats[i]['name']);
		} else {
			standard_list2.push(ineligible_feats[i]['name']);
		}
	}
	standard_list2.sort();
	magic_list2.sort();
	var standardList = standard_list1.concat(standard_list2);
	var magicList = magic_list1.concat(magic_list2);

	$("#feat_name").autocomplete({
		source: function(input, add) {
			featSourceFunction(input, add, standardList);
		},
		create: function (event, ui) {
			$(this).data("ui-autocomplete")._renderItem = function(ul, item) {
				return featCreateFunction(ul, item);
			};
		},
		select: function(event, ui) {
			return featSelectFunction(ui);
		}
	});

	$("#magic_talent_name").autocomplete({
		source: function(input, add) {
			featSourceFunction(input, add, magicList);
		},
		create: function (event, ui) {
			$(this).data("ui-autocomplete")._renderItem = function(ul, item) {
				return featCreateFunction(ul, item);
			};
		},
		select: function(event, ui) {
			return featSelectFunction(ui);
		}
	});
}

function featSourceFunction(input, add, list) {
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
				if (user_feats[i]['name'].toLowerCase().includes(entry['value'].toLowerCase())) {
					// allow Shapeshifter, Elementalist, and Elemental Master to be learned multiple times
					found = user_feats[i]['name'].includes("Shapeshifter") ? false : true;
					found = user_feats[i]['name'].includes("Elementalist") ? false : true;
					found = user_feats[i]['name'].includes("Elemental Master") ? false : true;
					break;
				}
			}
			if (!found) {
				suggestions.push(entry);
			}
		}
	});
	add(suggestions);
}

function featCreateFunction(ul, item) {
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
}

function featSelectFunction(ui) {
	// build user message if requirements aren't satisfied
	if (!ui.item.satisfied) {
		var requirements = "";
		for (var i in feat_list) {
			if (feat_list[i]['name'] == ui.item.value) {
				for (var j in feat_list[i]['requirements']) {
					for (var k in feat_list[i]['requirements'][j]) {
						for (var key in feat_list[i]['requirements'][j][k]) {
							// key types : feat, training, character_creation, governing, or attribute
							// adjust labels for requirements
							var item = key;
							var req = feat_list[i]['requirements'][j][k][key];
							item = capitalize(item);
							item = item == "Precision_" ? "Precision" : item;
							item = item == "Governing" ? "Governing School" : item;
							var attributes = [
								'governing',
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
							if (attributes.includes(key)) {
								req = "+"+req;
							}
							requirements += key == "character_creation" ? "*only available during character creation" : 
								item + " : " + req;
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

		// check for talents requiring additional selection
		var talent = ui.item.value;
		$(".elemental_select").hide();
		$(".elementalist_select").hide();
		$(".superhuman_select").hide();
		if (talent == "Elemental Master") {
			$(".elemental_select").show();
		} else if (talent == "Elementalist") {
			$(".elementalist_select").show();
		} else if (talent == "Superhuman") {
			$(".superhuman_select").show();
		} else if (talent == "Shapeshifter") {
			$(".shapeshifter_select").show();
		}

		// auto fill description on feat selection
		var description = "";
		var feat_id = "";
		for (var i in feat_list) {
			if (feat_list[i]['name'] == ui.item.value) {
				description = feat_list[i]['description'];
				feat_id = feat_list[i]['id'];
			}
		}
		$("#feat_name_val").val(ui.item.value);
		$("#feat_description").val(description).height($("#feat_description")[0].scrollHeight);
		$("#feat_id").val(feat_id);
		return true;
	}
}

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
	// set size text
	editSize(false);
	// set initiative
	adjustInitiative();

	// check for pending xp awards
	if (xp_awards.length > 0) {
		for (var i in xp_awards) {
			user['xp'] = parseInt(user['xp']) + parseInt(xp_awards[i]['xp_award']);
			$("#xp").val(parseInt(user['xp']));
			// update character xp, awards, and attribute points in database
			$.ajax({
				url: '/scripts/update_xp.php',
				data: {
					'xp_award_id' : xp_awards[i]['id'],
					'user' : user['id'],
					'xp' : $("#xp").val()
				},
				ContentType: "application/json",
				type: 'POST',
				success: function(response) {
					// no action
				}
			});
		}
		// TODO this should auto update database value?
		$("#xp").trigger("change");
	}
}

function setMoraleEffect(morale) {
	var positiveEffects = {
		2: "You gain +1 Fate",
		4: "You gain 1 Motivator Bonus Each Session",
		6: "You gain +1 Fate",
		8: "Once per Session you can declare a Fate 6, leading to an Epic Success"
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
	$("#fate").val(fate);
}

// adjust attribute value
function adjustAttribute(attribute, val) {
	var originalVal = parseInt($("#" + attribute+"_val").val());
	var newVal = originalVal + parseInt(val);
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
		if (val == 1) {
			// increasing attribute; reduce points by newVal, if newVal > 0, else reduce by originalVal
			let cost = originalVal >= 8 ? newVal + Math.round(newVal/2) : newVal;
			var newPts = pts - Math.abs(newVal > 0 ? cost : originalVal);
			// make sure we have enough points to allocate
			if (newPts >= 0) {
				$(".attribute-count").html(newPts + " Points");
			} else {
				$("#"+attribute+"_text").html(originalVal >= 0 ? "+" + originalVal : originalVal);
				$("#"+attribute+"_val").val(originalVal).trigger("change");
				return;
			}
		} else {
			// decreasing attribute; increase points by originalVal, if original > 0, else increase by newVal
			let cost = originalVal > 8 ? originalVal + Math.round(originalVal/2) : originalVal;
			$(".attribute-count").html(pts + Math.abs(originalVal > 0 ? cost : newVal)+" Points");
		}
	}
	$("#"+attribute+"_text").html(newVal >= 0 ? "+"+newVal : newVal);
	$("#"+attribute+"_val").val(newVal).trigger("change");
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
			$(".weapon-select").each(function() {
				for(var i in user_weapons) {
					if ($(this).val() == user_weapons[i]['name'] && user_weapons[i]['type'] == 'Melee') {
						var id = this.id.slice(-1);
						let damage = user_weapons[i]['damage'];
						let max_damage = user_weapons[i]['max_damage'] == null || user_weapons[i]['max_damage'] == "" ? 0 : user_weapons[i]['max_damage'];
						let damage_mod =  getDamageMod(user_weapons[i]['type'], damage, max_damage);
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
			updateTotalWeight(false);
			break;
		case 'agility':
			// adjust dodge and defend
			setDodge();
			setDefend();
			break;
		case 'precision_':
			// adjust ranged weapon damage
			$(".weapon-select").each(function() {
				for(var i in user_weapons) {
					if ($(this).val() == user_weapons[i]['name'] && user_weapons[i]['type'] == 'Ranged') {
						var id = this.id.slice(-1);
						let damage = user_weapons[i]['damage'];
						let max_damage = user_weapons[i]['max_damage'] == null || user_weapons[i]['max_damage'] == "" ? 0 : user_weapons[i]['max_damage'];
						let damage_mod =  getDamageMod(user_weapons[i]['type'], damage, max_damage);
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
		case 'vitality':
			// adjust fate and caster level
			var caster_level = 10 + newVal;
			$("#caster_level").val(caster_level);
			adjustFate();
			break;
	}
	// update user_trainings if attribute is training_x
	if (attribute.includes("training_")) {
		var training_id = attribute.split("training_")[1];
		for (var i in user_trainings) {
			if (user_trainings[i]['id'] == training_id) {
				user_trainings[i]['value'] = parseInt(user_trainings[i]['value']) + parseInt(val);
				updateDatabaseTable('user_training', 'value', user_trainings[i]['value'], user_trainings[i]['id']);
			}
		}
	}
	// adjust eligibility of feat list
	setFeatList();
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
	  url: '/scripts/check_password.php',
	  data: { 'password' : $("#password").val(), 'user_id' : $("#user_id").val(), 'campaign_id' : $("#campaign_id").val() },
	  ContentType: "application/json",
	  type: 'POST',
	  success: function(response) {
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
	  url: '/scripts/email_password_reset_link.php',
	  data: { 'user_id' : $("#user_id").val() },
	  ContentType: "application/json",
	  type: 'POST',
	  success: function(response) {
	  	// no action necessary
	  }
	});
}

// add a new feat from modal values
function newFeat() {
	var featName = $("#feat_name_val").val() == "" ? $("#feat_name").val() : $("#feat_name_val").val();
	var featDescription = $("#feat_description").val();
	let editing = $("#feat_modal_title").html() == "Update Feat";

	// make sure we're not adding a duplicate training name
	if (!editing) {
		for (var i in user_feats) {
			if (user_feats[i]['name'] == featName && !user_feats[i]['name'].includes("Shapeshifter")) {
				alert("Talent name already in use");
				return;
			}
		}

		// only allow one profession, one compelling action, one social trait, one morale trait
		var featType = "";
		for (var i in feat_list) {
			if (feat_list[i]['name'].toLowerCase() == featName.toLowerCase()) {
				featType = feat_list[i]['type'];

				if (featType == "profession") {
					for (var j in user_feats) {
						if (user_feats[j]['type'] == "profession") {
							alert("Only one Profession can be chosen");
							return;
						}
					}
				}
				if (featType == "social_background") {
					for (var j in user_feats) {
						if (user_feats[j]['type'] == "social_background") {
							alert("Only one Social Background can be chosen");
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

		// check for 'Divine Magic'
		if (featName == "Divine Magic") {
			// prompt to choose a vow
			$("#vows_modal").modal("show");
		}
	}

	if (featName != "" && featDescription != " ") {

		// check for talents requiring additional selection
		var featDisplayName = "";
		if (featName == "Elemental Master") {
			featDisplayName = featName + " ("+$(".elemental_select").val()+")";
		} else if (featName == "Elementalist") {
			featDisplayName = featName + " ("+$(".elementalist_select").val()+")";
		} else if (featName == "Superhuman") {
			featDisplayName = featName + " ("+$(".superhuman_select").val()+")";
		}else if (featName == "Shapeshifter") {
			// make sure animal name isn't empty
			if ($("#animal_name").val() == "") {
				alert("Please enter an animal name");
				return;
			}
			featName = featName + " ("+$("#animal_name").val()+")";
		}

		if (!editing) {
			// feat_id for custom feats = 0
			let feat_id = $("#feat_id").val() == "" ? 0 : $("#feat_id").val();
			let newFeat = {
				"feat_id":feat_id,
				"name":featDisplayName == "" ? featName : featDisplayName,
				"type":featType,
				"description":featDescription
			};
			user_feats.push(newFeat);
			insertDatabaseObject('user_feat', newFeat, columns['user_feat']);
		}

		addFeatElements(featName, featDisplayName, featDescription.trim(), $("#feat_id").val(), $("#user_feat_id").val());
	}
}

$("#vows_modal").on('hidden.bs.modal', function() {
	// get selected radio value
	var vow = $("input[type='radio'][name='vow']:checked").val();
	// add new feat
	$("#feat_name_val").val("Vow of "+vow);
	$("#feat_description").val($("#"+vow+"_description").html());
	newFeat();
	// scroll back to feats section
	$([document.documentElement, document.body]).animate({
        scrollTop: $("#section_feats").offset().top-100
    }, 200);
});

// create html elements for feat
function addFeatElements(featName, featDisplayName, featDescription, feat_id, user_feat_id) {
	// check for magic talents
	user['magic_talents'] = user['magic_talents'] || featName == "Arcane Blood" || featName == "Divine Magic";
	if (user['magic_talents']) {
		$("#magic_option").show();
	}

	// get uuid from user_feats
	var id_val;
	if (user_feat_id == "") {
		for (var i in user_feats) {
			if (featName.includes(user_feats[i]['name'])) {
				id_val = "feat_"+user_feats[i]['feat_id'];
			}
		}
	} else {
		id_val = "feat_"+user_feat_id;
	}
	var featDescription = featDescription.split("\n\n")[0]; // remove extraneous text from feat descriptions

	// new or updating?
	let editing = $("#feat_modal_title").html() == "Update Feat";
	if (editing) {
		// update feat name and description
		$("#"+id_val+"_name").html(featName+" : ");
		$("#"+id_val+"_descrip").html(featDescription.length > 100 ? featDescription.substring(0,100)+"..." : featDescription);

		// update hidden input values
		$("#"+id_val+"_name_val").val(featName);
		$("#"+id_val+"_descrip_val").val(featDescription);

	} else {
		// get feat cost - default cost = 4
		var cost = 4;
		for (var i in feat_list) {
			if (feat_list[i]['name'].toLowerCase() == featName.toLowerCase()) {
				cost = parseInt(feat_list[i]['cost']);
			}
		}

		// if allocating attribute points, decrease points
		if (allocatingAttributePts) {

			// only one feat/skill per level
			if ((!addingNewSchool && !characterCreation) && (feats.length > 0 || skills.length > 0)) {
				alert("Only one new feat or unique skill can be added per level.");
				return;
			}

			// make magic talent free
			if (addingNewSchool) {
				cost = 0;
				addingNewSchool = false;
			}

			// check if we're adding shapeshifter - add animal level to cost
			if (featName.includes("Shapeshifter")) {
				cost = 4 + parseInt($("#animal_level").val());
			}

			// make sure we have enough points
			if (parseInt($(".attribute-count").html().split(" Points")[0]) - cost < 0) {
				alert("Not enough attribute points to allocate for a new feat/trait.");
				return;
			}

			var pts = parseInt($(".attribute-count").html().split(" Points")[0]);
			$(".attribute-count").html(pts - cost+" Points");
		}

		var feat_container = createElement('div', 'feat', '#feats', id_val);
		if (allocatingAttributePts) {
			feats.push(feat_container);
		}

		var feat_title_descrip = createElement('div', '', feat_container);

	    $('<p />', {
	    	'id': id_val+"_name",
	    	'class': 'feat-title',
	    	'text': (featDisplayName == "" ? featName : featDisplayName)+" : "
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
	    feat_title_descrip.on("click", function() {
	    	var name = $("#"+id_val+"_name").html().split(" : ")[0];
	    	// figure out what type of feat we are editing
	    	var featType = "";
	    	let custom = true;
	    	for (var i in feat_list) {
	    		if (name.toLowerCase().includes(feat_list[i]['name'].toLowerCase())) {
	    			featType = feat_list[i]['type'];
	    			featType = featType == "physical_trait" ? (cost > 0 ? "physical_trait_pos" : "physical_trait_neg") : featType;
	    			$("#select_feat_type").val(featType+"_name").trigger("change");
	    			$("#"+featType+"_name").val(feat_list[i]['name']).attr("disabled", !characterCreation && !adminEditMode);
	    			custom = false;
	    		}
	    	}
	    	// check for custom non-db feat
	    	if (custom) {
	    		$("#feat_name").val(name).attr("disabled", !characterCreation && !adminEditMode);
	    	}
	    	var description = $("#"+id_val+"_descrip_val").val();
	    	// if feat is physical trait, add cost/bonus to description
	    	if (featType == "physical_trait_pos" || featType == "physical_trait_neg") {
				description += "\n\n"+(cost > 0 ? "Attribute Point Cost: "+cost : "Attribute Point Bonus: "+(cost*-1));
	    	}
	    	$("#feat_description").val(description).attr("disabled", !adminEditMode && !characterCreation);
	    	$("#feat_id").val(feat_id);
	    	$("#user_feat_id").val(user_feat_id);
	    	$("#feat_modal_title").html("Update Feat");
	    	$("#new_feat_modal").modal("show");
			$("#feat_description").height( $("#feat_description")[0].scrollHeight );
	    });

	    $("#"+id_val+"_remove").on("click", function() {
	    	var name = $("#"+id_val+"_name").html();
	    	// confirm delete
	    	var conf = confirm("Remove Talent, '"+name.split(" : ")[0]+"'?");
	    	if (conf) {
	    		removeFeatFunction(id_val, featName, cost, feat_container);
	    	}
	    });

		// highlight on hover
		feat_container.hover(function() {
			$(this).addClass("highlight");
		}, function() {
			$(this).removeClass("highlight");
		});

		// add hidden inputs
	    createInput('', 'hidden', 'feat_names[]', (featDisplayName == "" ? featName : featDisplayName), feat_container, id_val+"_name_val");
	    createInput('', 'hidden', 'feat_descriptions[]', featDescription, feat_container, id_val+"_descrip_val");
		createInput('', 'hidden', 'feat_ids[]', feat_id, feat_container);
		createInput('', 'hidden', 'user_feat_ids[]', user_feat_id, feat_container);
	}

	// check for specific feats and adjust attributes (quick and the dead, improved crit, etc)
	if (featName.toLowerCase() == "improved critical hit") {
		for (var i in equipped_weapons) {
			let crit = getCritModifier(equipped_weapons[i]);
			$("#weapon_crit_"+(parseInt(i)+1)).val(crit);
		}
	}
	if (featName.toLowerCase() == "quick and the dead") {
		// adjust initiative value
		adjustInitiative();
	}

	// adjust feat eligibility
	setFeatList();
}

function removeFeatFunction(id_val, featName, cost, feat_container=null) {
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
	$("#"+id_val).remove();
	for (var i in user_feats) {
		if (user_feats[i]['name'] == featName) {
		  deleteDatabaseObject('user_feat', user_feats[i]['id']);
		  user_feats.splice(i, 1);
		  break;
		}
	}

	// check if we're removing feats that affect attributes
	if (featName.toLowerCase() == "improved critical hit") {
		// adjust crit value for equipped weapons
		for (var i in equipped_weapons) {
			let crit = getCritModifier(equipped_weapons[i]);
			$("#weapon_crit_"+(parseInt(i)+1)).val(crit);
		}
	}
	if (featName.toLowerCase() == "quick and the dead") {
		adjustInitiative();
	}
}

function adjustInitiative() {
	// check if speed is higher than awareness and awareness is >= 0
	var speed = user['speed'] == null ? 0 : parseInt(user['speed']);
	var awareness = user['awareness'] == null ? 0 : parseInt(user['awareness']);
	var quick = false;
	for (var i in user_feats) {
		if (user_feats[i]['name'].toLowerCase() == "quick and the dead") {
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

function newTrainingModal(attribute) {

	// launch modal
	$("#training_modal_title").html("New "+attribute+" Training");
	$("#attribute_type").val(attribute);
	$("#training_name").val("");

	// unselect radios and hide and clear inputs
	$("input:radio[name='skill_type']").each(function(i) {
	      this.checked = false;
	});
	$("#skill_name").val("").hide();
	$("#training_name").val("").hide();
	$("#focus_name").val("").hide();
	$("#school_name").val("").hide();

	// if vitality, check for magic talents
	if (attribute == "Vitality" && user['magic_talents'] == true) {
		$("#magic_inputs").show();
		// if divine magic, only one school is allowed
		for (var i in user_feats) {
			if (user_feats[i]['name'] == "Divine Magic") {
				var schoolCount = 0;
				for (var j in user_trainings) {
					if (user_trainings[j]['magic_school'] == 1) {
						schoolCount += 1;
					}
				}
				
				if (schoolCount > 0) {
					$("#magic_inputs").hide();
				}
			}
		}
	} else {
		$("#magic_inputs").hide();
	}

	// set autocomplete values to inputs - skill_name, training_name, focus_name
	var skill = skillAutocompletes[attribute]['skill'];
	var training = skillAutocompletes[attribute]['training'];
	var focus = skillAutocompletes[attribute]['focus'];
	$("#skill_name").autocomplete({
		source: skill
	});
	$("#training_name").autocomplete({
		source: training
	});
	$("#focus_name").autocomplete({
		source: focus
	});

	$("#new_training_modal").modal("show");
}

// add a new training from modal values
function newTraining() {
	// check inputs - skill_name, training_name, focus_name, school_name - get value from non-hidden input
	// TODO simplify this by adding hidden 'training_type' input
	var trainingName = $("#skill_name").is(":visible") ? $("#skill_name").val() : 
	( $("#training_name").is(":visible") ? $("#training_name").val() : 
		( $("#focus_name").is(":visible") ? $("#focus_name").val() : 
			($("#school_name").is(":visible") ? $("#school_name").val() : "" ) ) );
	var trainingType = $("#skill_name").is(":visible") ? "unique_skill" : 
	( $("#training_name").is(":visible") ? "training" : 
		( $("#focus_name").is(":visible") ? "focus" : 
			($("#school_name").is(":visible") ? "magic_school" : "" ) ) );
	var attribute = $("#attribute_type").val();

	if (trainingName != "") {
		// check if user is already trained
		for (var i in user_trainings) {
			if (user_trainings[i]['name'] == trainingName) {
				alert("Training name already in use");
				return;
			}
		}

		var user_training = {};
		user_training['attribute_group'] = attribute;
		user_training['name'] = trainingName;
		// if focus or training, value = 1, otherwise value = 0
		user_training['value'] = trainingType == "training" || trainingType == "focus" ? 1 : 0;

		// check for magic school
		user_training['magic_school'] = trainingName.includes("Ka") || trainingName.includes("Avani") || 
			trainingName.includes("Nouse") || trainingName.includes("Soma") ? 1 : 0;
		// check for governing school
		for (var i in user_feats) {
			if (user_feats[i]['name'] == "Arcane Blood") {
				// if arcane blood - check if governing (first) school
				var schoolCount = 0;
				for (var j in user_trainings) {
					if (user_trainings[j]['magic_school'] == 1) {
						schoolCount += 1;
					}
				}
				user_training['governing_school'] = schoolCount == 0 ? 1 : 0;
			}
		}
		user_trainings.push(user_training);
		insertDatabaseObject('user_training', user_training, columns['user_training']);

		// check for existing governing school - add companion/opposition to training label
		var governing = "";
		for (var i in user_trainings) {
			if (user_trainings[i]['governing_school'] == 1) {
				governing = user_trainings[i]['name'];
			}
		}
		var displayName = trainingName;
		if (user_training['magic_school'] == 1) {
			if (user_training['governing_school'] == 1) {
				displayName += " (Gov)";
			} else if (governing != "") {
				if (governing == "Soma") {
					displayName += displayName == "Avani" ? " (Comp)" : " (Opp)";
				} else if (governing == "Avani") {
					displayName += displayName == "Soma" ? " (Comp)" : " (Opp)";
				} else if (governing == "Nouse") {
					displayName += displayName == "Ka" ? " (Comp)" : " (Opp)";
				} else {
					displayName += displayName == "Nouse" ? " (Comp)" : " (Opp)";
				}
			}
		}

		var id_val = addTrainingElements(trainingName, displayName, attribute, '');
	}

	// if magic school - prompt to choose talent
	if (user_training['magic_school'] == 1) {
		// use id_val to update hidden input values
		$("#"+id_val+"_magic").val(1);
		$("#"+id_val+"_governing").val(user_training['governing_school']);

		// update talent dropdown on new_school_modal
		var talents = schoolTalents[trainingName];
		$("#magic_talents").html("");
		$('<option />', {
		  'value': '',
		}).appendTo($("#magic_talents"));
		for (var i in talents) {
			$('<option />', {
			  'value': trainingName+":"+talents[i]['name'],
			  'text': talents[i]['name'],
			}).appendTo($("#magic_talents"));
		}
		$("#talent_descrip").height(54).val("");
		$(".elemental_select").hide();
		$(".elementalist_select").hide();
		$(".superhuman_select").hide();
		$(".shapeshifter_select").hide();
		$("#new_school_modal").modal("show");
	}
}

// add new magic school with starting talent
function newSchool() {
	addingNewSchool = true;
	$("#feat_name_val").val($("#magic_talents").val().split(":")[1]);
	$("#feat_description").val($("#talent_descrip").val());
	// set feat_id value for new talent
	for (var i in feat_list) {
		if (feat_list[i]['name'] == $("#feat_name_val").val()) {
			$("#feat_id").val(feat_list[i]['id']);
		}
	}
	newFeat();
}


$("#new_school_modal").on('hidden.bs.modal', function() {
	// make sure that a talent has been selected
	if ($("#magic_talents").val() == "") {
		cancelMagic();
	} else {
		newSchool();
	}
});

// add new magic school canceled without selecting starting talent
function cancelMagic() {
	$("#magic_talents").val("");
	// remove the last added school training
	for (var i in skills) {
		var training = skills[i].text().split("+0")[0];
		for (var j in user_trainings) {
			if (user_trainings[j]['name'] == training) {
				deleteDatabaseObject('user_training', user_trainings[j]['id']);
				user_trainings.splice(j, 1);
			}
		}
		skills[i].remove();
	}
	// if allocating points, reset point count
	if (allocatingAttributePts) {
		var pts = parseInt($(".attribute-count").html().split(" Points")[0]);
		$(".attribute-count").html(pts + 4 +" Points");
	}
	setFeatList();
}

// show description on talent select
$("#magic_talents").on("change", function() {
	$("#talent_descrip").height(54).val("");
	var talents = schoolTalents[$(this).val().split(":")[0]];
	for (var i in talents) {
		if (talents[i]['name'] == $(this).val().split(":")[1]) {
			$("#talent_descrip").val(talents[i]['description']);
			$("#talent_descrip").height( $("#talent_descrip")[0].scrollHeight );
		}
	}
	// check for talents requiring additional selection
	var talent = $(this).val().split(":")[1];
	$(".elemental_select").hide();
	$(".elementalist_select").hide();
	$(".superhuman_select").hide();
	$(".shapeshifter_select").hide();
	if (talent == "Elemental Master") {
		$(".elemental_select").show();
	} else if (talent == "Elementalist") {
		$(".elementalist_select").show();
	} else if (talent == "Superhuman") {
		$(".superhuman_select").show();
	}else if (talent == "Shapeshifter") {
		$(".shapeshifter_select").show();
	}
});

// create html elements for training
function addTrainingElements(trainingName, trainingDisplayName, attribute, id, value='') {
	// make sure skill type is selected
	var skill_type = $('input[name=skill_type]:checked').val();
	if (skill_type == undefined && value == '') {
		alert("Please select a skill type");
		return;
	}

	// determine skill point cost and starting skill value if new training
	var skill_pts = (skill_type == "unique" || skill_type == "training") ? 2 : (skill_type == "school" ? 4 : 1);
	if (value == '') {
		// determine starting skill value; +0 for unique skill or magic school, +1 for training or focus
		value = skill_type == "unique" || skill_type == "school" ? 0 : 1;
	}

	// allocating attribute points, make sure we have enough points
	if (allocatingAttributePts) {
		if (skill_type == "unique" && (feats.length > 0 || skills.length > 0)) {
			alert("Only one new feat or unique skill can be added per level.");
			return;
		}
		if ((skill_type == "training" || skill_type == "focus") && trainings.length > 0) {
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

	var id_val = id == "" ? uuid() : "training_"+id;

	var row = createElement('div', 'row training-row', '#'+attribute, id_val+"_row");
	if (allocatingAttributePts) {
		if (skill_type == "training" || skill_type == "focus") {
			trainings.push(row);
		} else {
			skills.push(row);
		}
	}
	var div_left = createElement('div', 'col-md-7 col-xs-8', row);

	var label_left = $('<label />', {
	  'class': 'control-label with-hidden',
	  'for': id_val,
	  'text': trainingDisplayName,
	  'id': id_val+"_label"
	}).appendTo(div_left);

	// highlight training on hover
	if (!is_mobile) {
		row.hover(function() {
			$(this).addClass("highlight");
		}, function() {
			$(this).removeClass("highlight");
		});
	}

	// add remove button
	// if allocating points, make sure remove button is visible
	var removeBtn = createElement('span', 'glyphicon glyphicon-remove hidden-icon', label_left, id_val+"_text"+"_remove");
	var editingSection = $("#"+attribute+"_btn").find(".glyphicon-plus-sign").is(":visible");
	if (allocatingAttributePts || adminEditMode || editingSection) {
		removeBtn.show();
	}
	removeBtn.on("click", function() {
		removeTrainingFunction(trainingName, row, skill_pts);
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

	createInput('', 'hidden', 'training[]', trainingName+":"+attribute, label_right, id_val+"_name");
	createInput('', 'hidden', 'training_val[]', value == '' ? 0 : value, label_right, id_val+"_val");
	createInput('', 'hidden', 'training_magic[]', 0, label_right, id_val+"_magic");
	createInput('', 'hidden', 'training_governing[]', 0, label_right, id_val+"_governing");
	createInput('', 'hidden', 'training_ids[]', id, label_right);

	var up = createElement('span', 'glyphicon glyphicon-plus hidden-icon', label_right, id_val+"_up");
	$("#"+id_val+"_up").on("click", function() {
		adjustAttribute(id_val, 1);
	});

	var down = createElement('span', 'glyphicon glyphicon-minus hidden-icon', label_right, id_val+"_down");
	$("#"+id_val+"_down").on("click", function() {
		adjustAttribute(id_val, -1);
	});

	// GM edit mode or character creation - show plus minus icons
	if (adminEditMode || characterCreation) {
		up.show();
		down.show();
	}

	enableHiddenNumbers();

	// adjust feat eligibility
	setFeatList();

	return id_val;
}

// remove a training element
function removeTrainingFunction(trainingName, row, skill_pts) {
	var conf = confirm("Remove training '"+trainingName+"'?");
	if (conf) {
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
		var schoolRemoval = false;
		var schoolName = "";
		for (var i in user_trainings) {
			if (user_trainings[i]['name'] == trainingName) {
				schoolRemoval = user_trainings[i]['magic_school'] == 1;
				schoolName = user_trainings[i]['name'];
				deleteDatabaseObject('user_training', user_trainings[i]['id']);
				user_trainings.splice(i, 1);
			}
		}
		if (schoolRemoval) {
			// check if we're removing a magic school and delete any associated talents
			var talents = schoolTalents[schoolName];
			for (var i in talents) {
				for (var j in user_feats) {
					if (user_feats[j]["name"].includes(talents[i]["name"])) {
						$(".feat-title").each(function() {
							if ($(this).html().split(" : ")[0].includes(talents[i]["name"])) {
								var id_val = $(this).attr("id").split("_name")[0];
								removeFeatFunction(id_val, talents[i]["name"], 0);
							}
						});
					}
				}
			}
		}
		// update feat list
		setFeatList();
	}
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
		// update mobile row
		$("#"+weapon_id+"_mobile_title").html(name+" : ");
		$("#"+weapon_id+"_mobile_details").html("Damage: "+damage_text+"; Weight: "+weight+"lbs; Qty: "+qty+"; "+noteMod+notes);

		updateTotalWeight(true);
		// check if this weapon is selected - update stats
		for (var i in user_weapons) {
			if (user_weapons[i]['name'] == originalName) {
				$($(".weapon-select").get().reverse()).each(function() {
					if ($(this).val() == originalName) {
						user_weapons[i]['damage'] = damage;
						user_weapons[i]['defend'] = defend;
						user_weapons[i]['crit'] = crit;
						user_weapons[i]['max_damage'] = max_damage;
						user_weapons[i]['quantity'] = qty;
						user_weapons[i]['name'] = name;
						user_weapons[i]['range_'] = range;
						user_weapons[i]['rof'] = rof;
						user_weapons[i]['notes'] = notes;
						selectWeapon(this.id.slice(-1), false);
						// update weapon in database
						updateDatabaseObject('user_weapon', user_weapons[i], columns['user_weapon']);
					}
					// update select list with new name
					$(this).find("option").each(function() {
						if ($(this).val() == originalName) {
							$(this).val(name);
							$(this).html(name);
						}
					});
				});
			}
		}
	} else {
		// check to make sure name is not a duplicate
		for (var i in user_weapons) {
			if (user_weapons[i]['name'] ==  name) {
				alert("Weapon name already in use");
				return;
			}
		}
		let newWeapon = {
			type:type,
			name:name,
			quantity:1,
			damage:damage,
			max_damage:max_damage,
			range_:range,
			rof:rof,
			defend:defend,
			crit:crit,
			notes:notes,
			weight:weight,
			equipped:0
		};
		// insert into database and get back id
		insertDatabaseObject('user_weapon', newWeapon, columns['user_weapon']);
		user_weapons.push(newWeapon);
		addWeaponElements(newWeapon);
	}
}

// create html elements for weapon
function addWeaponElements(weapon) {
	let id_val = weapon['id'] == "" ? uuid() : "weapon_"+weapon['id'];

	let div = createElement('div', '', '#weapons', id_val);
	let div0 = createElement('div', 'form-group item', div); // desktop row container
	let div1 = createElement('div', 'col-xs-3 no-pad-mobile', div0); // name
	let div3 = createElement('div', 'col-xs-1 no-pad-mobile', div0); // damage
	let div4 = createElement('div', 'col-xs-5 no-pad-mobile', div0); // notes
	let div5 = createElement('div', 'col-xs-1 no-pad-mobile', div0); // weight
	let div2 = createElement('div', 'col-xs-1 no-pad-mobile', div0); // qty
	let div6 = createElement('div', 'col-xs-1 no-pad-mobile center remove-btn', div); // delete btn
	let remove = createElement('span', 'glyphicon glyphicon-remove', div6);
	remove.on("click", function() {
		removeWeapon(id_val);
	});

	let name_input = createInput('', 'text', 'weapons[]', weapon['name'], div1, id_val+"_name");
	let qty_input = createInput('qty', 'text', 'weapon_qty[]', weapon['quantity'], div2, id_val+"_qty");
	// check for max damage
	let damageText = weapon['max_damage'] != null && weapon['max_damage'] != "" ? weapon['damage'] +" ("+weapon['max_damage']+")" : weapon['damage'];
	let dmg_input = createInput('', 'text', '', damageText, div3, id_val+"_damage");
	// add range, rof & defend bonus to notes
	var noteMod = "";
	weapon['notes'] = weapon['notes'] == null ? "" : weapon['notes'];
	noteMod += weapon['range_'] != null && weapon['range_'] != "" ? "Range: "+weapon['range_']+"; " : "";
	noteMod += weapon['rof'] != null && weapon['rof'] != "" ? "RoF: "+weapon['rof']+"; " : "";
	noteMod += weapon['defend'] != null && weapon['defend'] != "" ? "+"+weapon['defend']+" Defend; " : "";
	noteMod += weapon['crit'] != null && weapon['crit'] != "" ? "+"+weapon['crit']+" Critical Threat Range; " : "";
	let notesText = noteMod + weapon['notes'];
	let note_input = createInput('', 'text', '', notesText, div4, id_val+"_notes");
	let wgt_input = createInput('wgt', 'text', 'weapon_weight[]', weapon['weight'], div5, id_val+"_weight");
	createInput('', 'hidden', 'weapon_damage[]', weapon['damage'], div5, id_val+"_damage_val");
	createInput('', 'hidden', 'weapon_notes[]', weapon['notes'], div5, id_val+"_notes_val");
	createInput('', 'hidden', 'weapon_type[]', weapon['type'], div5, id_val+"_type");
	createInput('', 'hidden', 'weapon_max_damage[]', weapon['max_damage'], div5, id_val+"_max_damage");
	createInput('', 'hidden', 'weapon_range[]', weapon['range_'], div5, id_val+"_range");
	createInput('', 'hidden', 'weapon_rof[]', weapon['rof'], div5, id_val+"_rof");
	createInput('', 'hidden', 'weapon_defend[]', weapon['defend'], div5, id_val+"_defend");
	createInput('', 'hidden', 'weapon_crit[]', weapon['crit'], div5, id_val+"_crit");
	createInput('', 'hidden', 'weapon_ids[]', weapon['id'], div5);
	updateTotalWeight(true);

	// mobile item layout
	let mobileItemRow = createElement('span', 'item-mobile', div, id_val+'_mobile');
	let itemText = createElement('span', 'note item-label', mobileItemRow);
	itemText.hover(function() {
		$(this).addClass("highlight");
	}, function() {
		$(this).removeClass("highlight");
	});
	let itemTitle = createElement('span', 'note-title', itemText, id_val+"_mobile_title");
	itemTitle.html(weapon['name']+" : ");
	let itemDetails = createElement('span', 'note-content', itemText, id_val+"_mobile_details");
	itemDetails.html("Damage: "+damageText+"; Weight: "+weapon['weight']+"lbs; Qty: "+weapon['quantity']+"; "+notesText);

	// add click and hover functions
	name_input.attr("readonly", true);
	qty_input.attr("readonly", true);
	dmg_input.attr("readonly", true);
	note_input.attr("readonly", true);
	wgt_input.attr("readonly", true);
	enableHighlight(name_input, "weapons[]");
	enableHighlight(qty_input, "weapon_qty[]");
	enableHighlight(dmg_input, "weapon_damage[]");
	enableHighlight(note_input, "weapon_notes[]");
	enableHighlight(wgt_input, "weapon_weight[]");

	name_input.click(function() {
		editWeapon(id_val, "name");
	});
	qty_input.click(function() {
		editWeapon(id_val, "qty");
	});
	dmg_input.click(function() {
		editWeapon(id_val, "damage");
	});
	note_input.click(function() {
		// look for range, rof, defend, and crit values
		let note_val = note_input.val();
		let focus = note_val.includes("Range") ? "range" : 
			(note_val.includes("RoF") ? "rof" : (note_val.includes("Defend") ? "defend" : (note_val.includes("Critical") ? "crit" : "notes")));
		editWeapon(id_val, focus);
	});
	wgt_input.click(function() {
		editWeapon(id_val, "weight");
	});
	itemText.click(function() {
		editWeapon(id_val, "name");
	});

	// add to dropdown
	let option1 = $('<option />', {
  	'text': weapon['name'],
  	'value': weapon['name']
	}).appendTo("#weapon_select_1");
	let option2 = option1.clone().appendTo("#weapon_select_2");
	let option3 = option1.clone().appendTo("#weapon_select_3");

	enableHiddenNumbers();

}

// remove item layout elements and delete from database
function removeWeapon(id_val) {
	let item = $("#"+id_val+"_name").val();
	let conf = confirm("Remove item '"+item+"'?");
	if (conf) {
		$("#"+id_val).remove();
		$("#"+id_val+"_mobile").remove();
		for (var i in user_weapons) {
			if (user_weapons[i]['name'] == item) {
				deleteDatabaseObject('user_weapon', user_weapons[i]['id']);
				user_weapons.splice(i, 1);
				break;
			}
		}
	  // clear inputs if weapon is selected
	  $(".weapon-select").find("option").each(function() {
	  	if ($(this).val() == item) {
		  	if ($(this).is(":selected")) {
		  		// clear inputs
		  		let id = $(this).parent().attr("id").split("weapon_select_")[1];
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
$("#weapon_rof").on('keypress', function(e) {
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
		// update mobile row
		$("#"+protection_id+"_mobile_title").html(name+" : ");
		$("#"+protection_id+"_mobile_details").html("Bonus: +"+bonus+"; Weight: "+weight+"lbs; "+notes);

		// update protections array bonus value
		for (var i in user_protections) {
			if (user_protections[i]['name'] == originalName) {
				user_protections[i]['bonus'] = bonus;
				user_protections[i]['name'] = name;
				user_protections[i]['notes'] = notes;
				user_protections[i]['weight'] = weight;
				// update database entry
				updateDatabaseObject('user_protection', user_protections[i], columns['user_protection']);
				setDefend();
			}
		}
		updateTotalWeight(true);
	} else {
		// check to make sure name is not a duplicate
		for (var i in user_protections) {
			if (user_protections[i]['name'] ==  name) {
				alert("Protection name already in use");
				return;
			}
		}
		var newProtection = {
			'name':name,
			'bonus':bonus,
			'notes':notes,
			'weight':weight,
			'equipped':0,
			'id':""
		}
		user_protections.push(newProtection);
		addProtectionElements(newProtection);
	}
}

// create html elements for protection
function addProtectionElements(protection) {
	var id_val = protection['id'] == "" ? uuid() : "protection_"+protection['id'];

	var div = createElement('div', '', '#protections', id_val);
	var div0 = createElement('div', 'form-group item item-protection', div); // desktop row container
	var div3 = createElement('div', 'col-xs-1 no-pad-mobile col-icon equip-btn', div); // equip btn
	var div1 = createElement('div', 'col-xs-3 no-pad-mobile col-icon-right', div0); // name
	var div2 = createElement('div', 'col-xs-1 no-pad-mobile', div0); // bonus
	var div4 = createElement('div', 'col-xs-5 no-pad-mobile', div0); // notes
	var div5 = createElement('div', 'col-xs-1 no-pad-mobile', div0); // weight
	var div6 = createElement('div', 'col-xs-1 no-pad-mobile center remove-btn', div); // remove btn

	var name_input = createInput('', 'text', 'protections[]', protection['name'], div1, id_val+"_name");
	var bonus_input = createInput('', 'text', 'protection_bonus[]', protection['bonus'], div2, id_val+"_bonus");
	let notesText = protection['notes'] == null ? "" : protection['notes'];
	var notes_input = createInput('', 'text', 'protection_notes[]', notesText, div4, id_val+"_notes");
	var weight_input = createInput('wgt', 'text', 'protection_weight[]', protection['weight'], div5, id_val+"_weight");
	createInput('', 'hidden', 'protection_ids[]', protection['id'], div6);
	updateTotalWeight(true);

	// mobile item layout
	let mobileItemRow = createElement('span', 'item-mobile item-protection', div);
	let itemText = createElement('span', 'note item-label-mobile', mobileItemRow);
	itemText.hover(function() {
		$(this).addClass("highlight");
	}, function() {
		$(this).removeClass("highlight");
	});
	let itemTitle = createElement('span', 'note-title', itemText, id_val+"_mobile_title");
	itemTitle.html(protection['name']+" : ");
	let itemDetails = createElement('span', 'note-content', itemText, id_val+"_mobile_details");
	itemDetails.html("Bonus: +"+protection['bonus']+"; Weight: "+protection['weight']+"lbs; "+notesText);

	name_input.attr("readonly", true);
	bonus_input.attr("readonly", true);
	notes_input.attr("readonly", true);
	weight_input.attr("readonly", true);
	enableHighlight(name_input, "protections[]");
	enableHighlight(bonus_input, "protection_bonus[]");
	enableHighlight(notes_input, "protection_notes[]");
	enableHighlight(weight_input, "protection_weight[]");
	name_input.click(function() {
		editProtection(id_val, "name");
	});
	bonus_input.click(function() {
		editProtection(id_val, "bonus");
	});
	notes_input.click(function() {
		editProtection(id_val, "notes");
	});
	weight_input.click(function() {
		editProtection(id_val, "weight");
	});
	itemText.click(function() {
		editProtection(id_val, "name");
	});

	// add equip button
	createElement('span', 'glyphicon svg fa-solid icon-armor custom-icon', div3, id_val+"_equip");
	createElement('span', 'glyphicon glyphicon-ban-circle', div3, id_val+"_equip_ban");
	createInput('', 'hidden', 'protection_equipped[]', protection['equipped'] == null || protection['equipped'] == 0 ? false : true, div3, id_val+"_equipped");
	$(div3).on("click", function() {
		var item = $("#"+id_val+"_name").val();
		var conf = confirm(($("#"+id_val+"_equip_ban").is(":visible") ? "Equip" : "Unequip")+" protection '"+item+"'?");
		if (conf) {
			$("#"+id_val+"_equip_ban").toggle();
			for (var i in user_protections) {
				if (user_protections[i]['name'] == item) {
					$("#"+id_val+"_equipped").val(!$("#"+id_val+"_equip_ban").is(":visible"));
					user_protections[i]['equipped'] = $("#"+id_val+"_equip_ban").is(":visible") ? 0 : 1;
					updateDatabaseTable('user_protection', 'equipped', user_protections[i]['equipped'], user_protections[i]['id']);
					setToughness();
				}
			}
		}
	});

	// add remove button
	let removeBtn = createElement('span', 'glyphicon glyphicon-remove', div6);
	removeBtn.on("click", function() {
		var item = $("#"+id_val+"_name").val();
		var conf = confirm("Remove item '"+item+"'?");
		if (conf) {
			$("#"+id_val).remove();
			// check if protection was equipped
			for (var i in user_protections) {
				if (user_protections[i]['name'] == item) {
					// delete database entry
		  			deleteDatabaseObject('user_protection', user_protections[i]['id']);
				  	user_protections.splice(i, 1);
					setToughness();
					break;
				}
			}
		  	// update total weight
		  	updateTotalWeight(false);
		}
	});

	enableHiddenNumbers();

	// prompt user to equip new protection
	if (protection['id'] == "") {
		var conf = confirm("Do you want to equip your new protection, "+protection['name']+"?");
		if (conf) {
			// equip item
			$("#"+id_val+"_equip_ban").toggle();
			$("#"+id_val+"_equipped").val(true);
		}
		protection['equipped'] = conf ? 1 : 0;
		setToughness();
		// insert protection into database
		insertDatabaseObject('user_protection', protection, columns['user_protection']);
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
		// update mobile row
		$("#"+healing_id+"_mobile_title").html(name+" : ");
		$("#"+healing_id+"_mobile_details").html("Effect: "+effect+"; Weight: "+weight+"lbs; Qty: "+quantity);
		updateTotalWeight(true);
		// update healing in database
		for (var i in user_healings) {
			if (user_healings[i]['name'] == name) {
				user_healings[i]['quantity'] = quantity;
				user_healings[i]['effect'] = effect;
				user_healings[i]['weight'] = weight;
				updateDatabaseObject('user_healing', user_healings[i], columns['user_healing']);
			}
		}
	} else {
		// check to make sure name is not a duplicate
		for (var i in user_healings) {
			if (user_healings[i]['name'] ==  name) {
				alert("Item name already in use");
				return;
			}
		}
		// insert new healing into database and user array
		var newHealing = {
			'name':name,
			'quantity':quantity,
			'effect':effect,
			'weight':weight,
			'id':""
		}
		user_healings.push(newHealing);
		addHealingElements(newHealing);
		insertDatabaseObject('user_healing', newHealing, columns['user_healing']);
	}
}

// create html elements for healing
function addHealingElements(healing) {
	var id_val = healing['id'] == "" ? uuid() : "healing_"+healing['id'];

	var div = createElement('div', '', '#healings', id_val);
	let div0 = createElement('div', 'form-group item', div); // desktop row container
	var div1 = createElement('div', 'col-xs-4 no-pad-mobile', div0); // name
	var div3 = createElement('div', 'col-xs-5 no-pad-mobile', div0); // notes
	var div4 = createElement('div', 'col-xs-1 no-pad-mobile', div0); // weight
	var div2 = createElement('div', 'col-xs-1 no-pad-mobile', div0); // qty
	var div5 = createElement('div', 'col-xs-1 no-pad-mobile center remove-btn', div); // remove btn

	var name_input = createInput('', 'text', 'healings[]', healing['name'], div1, id_val+"_name");
	var qty_input = createInput('qty', 'text', 'healing_quantity[]', healing['quantity'], div2, id_val+"_quantity");
	var effect_input = createInput('', 'text', 'healing_effect[]', healing['effect'], div3, id_val+"_effect");
	var weight_input = createInput('wgt', 'text', 'healing_weight[]', healing['weight'], div4, id_val+"_weight");
	createInput('', 'hidden', 'healing_ids[]', healing['id'], div4);
	updateTotalWeight(true);

	// mobile item layout
	let mobileItemRow = createElement('span', 'item-mobile', div);
	let itemText = createElement('span', 'note item-label', mobileItemRow);
	itemText.hover(function() {
		$(this).addClass("highlight");
	}, function() {
		$(this).removeClass("highlight");
	});
	let itemTitle = createElement('span', 'note-title', itemText, id_val+"_mobile_title");
	itemTitle.html(healing['name']+" : ");
	let itemDetails = createElement('span', 'note-content', itemText, id_val+"_mobile_details");
	itemDetails.html("Effect: "+healing['effect']+"; Weight: "+healing['weight']+"lbs; Qty: "+healing['quantity']);

	name_input.attr("readonly", true);
	qty_input.attr("readonly", true);
	effect_input.attr("readonly", true);
	weight_input.attr("readonly", true);
	enableHighlight(name_input, "healings[]");
	enableHighlight(qty_input, "healing_quantity[]");
	enableHighlight(effect_input, "healing_effect[]");
	enableHighlight(weight_input, "healing_weight[]");
	name_input.click(function() {
		editHealing(id_val, "name");
	});
	qty_input.click(function() {
		editHealing(id_val, "quantity");
	});
	effect_input.click(function() {
		editHealing(id_val, "effect");
	});
	weight_input.click(function() {
		editHealing(id_val, "weight");
	});
	mobileItemRow.click(function() {
		editHealing(id_val, "name");
	});

	// add remove button
	createElement('span', 'glyphicon glyphicon-remove', div5, id_val+"_remove");
	$("#"+id_val+"_remove").on("click", function() {
		var item = $("#"+id_val+"_name").val();
		var conf = confirm("Remove item '"+item+"'?");
		if (conf) {
			$("#"+id_val).remove();
			// update total weight
			updateTotalWeight(false);
			// delete healing from database
			for (var i in user_healings) {
				if (user_healings[i]['name'] == item) {
					deleteDatabaseObject('user_healing', user_healings[i]['id']);
					user_healings.splice(i, 1);
					break;
				}
			}
		}
	});

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
		// update mobile row
		$("#"+misc_id+"_mobile_title").html(name+" : ");
		$("#"+misc_id+"_mobile_details").html( (notes == "" ? "" : "Notes: "+notes+"; ")+"Weight: "+weight+"lbs; Qty: "+quantity);
		updateTotalWeight(true);
		// update misc in database
		for (var i in user_misc) {
			if (user_misc[i]['name'] == name) {
				user_misc[i]['quantity'] = quantity;
				user_misc[i]['notes'] = notes;
				user_misc[i]['weight'] = weight;
				updateDatabaseObject('user_misc', user_misc[i], columns['user_misc']);
			}
		}
	} else {
		// insert new misc into database and user array
		var newMisc = {
			'name':name,
			'quantity':quantity,
			'notes':notes,
			'weight':weight,
			'id':""
		}
		user_misc.push(newMisc);
		addMiscElements(newMisc);
		insertDatabaseObject('user_misc', newMisc, columns['user_misc']);
	}
}

// create html elements for misc item
function addMiscElements(misc) {
	var id_val = misc['id'] == "" ? uuid() : "misc_"+misc['id'];

	var div = createElement('div', '', '#misc', id_val);
	let div0 = createElement('div', 'form-group item', div); // desktop row container
	var div1 = createElement('div', 'col-xs-4 no-pad-mobile', div0); // name
	var div3 = createElement('div', 'col-xs-5 no-pad-mobile', div0); // notes
	var div4 = createElement('div', 'col-xs-1 no-pad-mobile', div0); // weight
	var div2 = createElement('div', 'col-xs-1 no-pad-mobile', div0); // qty
	var div5 = createElement('div', 'col-xs-1 no-pad-mobile center remove-btn', div);

	var name_input = createInput('', 'text', 'misc[]', misc['name'], div1, id_val+"_name");
	var qty_input = createInput('qty', 'text', 'misc_quantity[]', misc['quantity'], div2, id_val+"_quantity");
	let notesText = misc['notes'] == null ? "" : misc['notes'];
	var notes_input = createInput('', 'text', 'misc_notes[]', notesText, div3, id_val+"_notes");
	var weight_input = createInput('wgt', 'text', 'misc_weight[]', misc['weight'], div4, id_val+"_weight");
	createInput('', 'hidden', 'misc_ids[]', misc['id'], div4);
	updateTotalWeight(true);

	// mobile item layout
	let mobileItemRow = createElement('span', 'item-mobile', div);
	let itemText = createElement('span', 'note item-label', mobileItemRow);
	itemText.hover(function() {
		$(this).addClass("highlight");
	}, function() {
		$(this).removeClass("highlight");
	});
	let itemTitle = createElement('span', 'note-title', itemText, id_val+"_mobile_title");
	itemTitle.html(misc['name']+" : ");
	let itemDetails = createElement('span', 'note-content', itemText, id_val+"_mobile_details");
	itemDetails.html( (notesText == "" ? "" : "Notes: "+notesText+"; ")+"Weight: "+misc['weight']+"lbs; Qty: "+misc['quantity']);

	name_input.attr("readonly", true);
	qty_input.attr("readonly", true);
	notes_input.attr("readonly", true);
	weight_input.attr("readonly", true);
	enableHighlight(name_input, "misc[]");
	enableHighlight(qty_input, "misc_quantity[]");
	enableHighlight(notes_input, "misc_notes[]");
	enableHighlight(weight_input, "misc_weight[]");
	name_input.click(function() {
		editMisc(id_val, "name");
	});
	qty_input.click(function() {
		editMisc(id_val, "quantity");
	});
	notes_input.click(function() {
		editMisc(id_val, "notes");
	});
	weight_input.click(function() {
		editMisc(id_val, "weight");
	});
	mobileItemRow.click(function() {
		editMisc(id_val, "name");
	});

	// add remove button
	createElement('span', 'glyphicon glyphicon-remove', div5, id_val+"_remove");
	$("#"+id_val+"_remove").on("click", function() {
		var item = $("#"+id_val+"_name").val();
		var conf = confirm("Remove item '"+item+"'?");
		if (conf) {
			$("#"+id_val).remove();
			// update total weight
			updateTotalWeight(false);
			// delete misc from database
			for (var i in user_misc) {
				if (user_misc[i]['name'] == item) {
					deleteDatabaseObject('user_misc', user_misc[i]['id']);
					user_misc.splice(i, 1);
					break;
				}
			}
		}
	});

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
		// $("#"+note_id+"_content").html(note.length > 90 ? note.substring(0,90)+"..." : note);
		$("#"+note_id+"_content").html(note);
		$("#"+note_id+"_title_val").val(title);
		$("#"+note_id+"_content_val").val(note);
		// update note in database
		for (var i in user_notes) {
			if (user_notes[i]['id'] == note_id.split("note_")[1]) {
				user_notes[i]['title'] = title;
				user_notes[i]['note'] = note;
				updateDatabaseObject('user_note', user_notes[i], columns['user_note']);
			}
		}
	} else {
		// insert new note into database and user array
		var newNote = {
			'title':title,
			'note':note,
			'id':""
		}
		user_notes.push(newNote);
		addNoteElements(newNote);
		newNote.postInsertCallback = function(insert_id) {
			if (insert_id != undefined) {
				let uuid = $("#uuid").val();
				// update element IDs with returned insert ID
				$("#"+uuid+"_title").attr("id", "note_"+insert_id+"_title");
				$("#"+uuid+"_content").attr("id", "note_"+insert_id+"_content");
				$("#"+uuid+"_title_val").attr("id", "note_"+insert_id+"_title_val");
				$("#"+uuid+"_content_val").attr("id", "note_"+insert_id+"_content_val");
			}
		};
		insertDatabaseObject('user_note', newNote, columns['user_note']);
	}
}

// create note elements
function addNoteElements(note) {
	var id_val = note['id'] == "" ? uuid() : "note_"+note['id'];

	var li = $('<li />', {
	}).appendTo("#notes");

	var span = $('<span />', {
	  'class': 'note',
	}).appendTo(li);

	$('<span />', {
		'id': id_val+"_title",
		'html': note['title'] == null || note['title'] == "" ? "" : note['title']+": ",
	  'class': 'note-title',
	}).appendTo(span);
	createInput('', 'hidden', 'titles[]', note['title'], span, id_val+"_title_val");

	$('<span />', {
		'id': id_val+"_content",
		// 'html': note.length > 90 ? note.substring(0,90)+"..." : note,
		'html': note['note'],
	  'class': 'note-content',
	}).appendTo(span);

	var remove = $('<span />', {
	  'class': 'glyphicon glyphicon-remove',
	}).appendTo(li);

	createInput('', 'hidden', 'notes[]', note['note'], span, id_val+"_content_val");
	createInput('', 'hidden', 'note_ids[]', note['id'], span);

	// highlight on hover
	span.hover(function() {
		$(this).addClass("highlight");
	}, function() {
		$(this).removeClass("highlight");
	});

	// edit on click
	span.click(function() {
		editNote(note);
	});

	// enable remove button
	remove.click(function() {
		var conf = confirm("Delete note, " + (note['title'] == "" ? "[no title]" : note['title']) + "?");
		if (conf) {
			li.remove();
			// delete note from database
			for (var i in user_notes) {
				if (user_notes[i]['note'] == note['note']) {
					deleteDatabaseObject('user_note', user_notes[i]['id']);
					user_notes.splice(i, 1);
					break;
				}
			}
		}
	});
}

function editNote(note) {
	// set modal values and launch
	$("#note_modal_title").html("Edit Note");
	$("#note_title").val($("#note_"+note['id']+"_title_val").val());
	$("#note_content").val($("#note_"+note['id']+"_content_val").val());
	$("#note_id").val("note_"+note['id']);
	$("#new_note_modal").modal("show");
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
	var move = user['size'] == undefined ? 40 : (user['size'] == "Small" ? 30 : (user['size'] == "Large" ? 50 : 40));
	var fatigue = $("#fatigue").val();

	// adjust action/move values
	var msg = "";
	if (parseFloat(totalWeight) <= capacity/4) {
		// unhindered, no modification to actions
		$("#unhindered").addClass("selected");
		$("#encumberence").val("Unhindered");
	} else if (parseFloat(totalWeight) <= capacity/2) {
		// encumbered, -10 Move
		$("#encumbered").addClass("selected");
		$("#encumberence").val("Encumbered");
		msg = "You are encumbered (-10 Move).<br>Reduce your item weight to remove the penalty.";
	} else if (parseFloat(totalWeight) <= capacity/4*3) {
		// burdened, -1 QA, -10 Move
		$("#burdened").addClass("selected");
		$("#encumberence").val("Burdened");
		msg = "You are burdened (-1 QA, -10 Move).<br>Reduce your item weight to remove the penalty.";
	} else {
		// overburdened, -1 SA, -10 Move
		$("#overburdened").addClass("selected");
		$("#encumberence").val("Overburdened");
		msg = "You are overburdened (-1 SA, -10 Move).<br>Reduce your item weight to remove the penalty.";
	}

	let unconcious = fatigue == 4;
	var movePenalty = 0;
	var actionPenalty = 0;

	// apply action penalty
	actionPenalty -= $("#encumberence").val() == "Overburdened" ? 2 : ($("#encumberence").val() == "Burdened" ? 1 : 0);
	actionPenalty -= fatigue >= 3 ? 2 : (fatigue >= 2 ? 1 : 0);
	$("#action_penalty").val(unconcious ? "Unconcious" : (actionPenalty == 0 ? "None" : actionPenalty+" QA"));
	while (standard > 0 && actionPenalty < 0) {
		standard = quick > 0 ? standard : standard - 1;
		quick = quick > 0 ? quick - 1 : quick + 1;
		actionPenalty += 1;
	}

	// apply move penalty
	movePenalty -= $("#encumberence").val() == "Unhindered" ? 0 : 10;
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
		// scroll to item weight?
		// $('html,body').animate({scrollTop: $("#section_weight").offset().top},'slow');
	}
}

// launch edit motivators modal
function editMotivators() {
	// only allowed during character creation and GM mode
	if (user_motivators.length == 0 || adminEditMode) {
		// set values to current motivators
		$("#m1").val($("#motivator_0").val());
		$("#m2").val($("#motivator_1").val());
		$("#m3").val($("#motivator_2").val());
		$("#m4").val($("#motivator_3").val());
		$("#motivator_modal").modal("show");
	}
}

function setMotivators() {

	// make sure primary motivators are set
	if ($("#m1").val() == "" || $("#m2").val() == "" || $("#m3").val() == "") {
		alert("Please set required motivators");
		return;
	}

	for (var i = 0; i < 4; i++) {
		let motivator = $("#m"+(i+1)).val();
		// set motivator values
		$("#motivator_"+i).val(motivator);
		// update values in user_motivators
		user_motivators[i] = {
			'motivator':motivator
		};
	}

	// set point values and user_motivators array, only during character creation (NOT in admin edit mode)
	if (!adminEditMode) {
		for (var i = 0; i < 4; i++) {
			let val = i == 0 ? 2 : (i == 3 ? 0 : 1);
			$("#motivator_pts_"+i).val(val);
			$("#motivator_primary_"+i).val(i != 3 ? 1 : 0);
			user_motivators[i]['points'] = val;
			user_motivators[i]['primary_'] = i != 3 ? 1 : 0;
			// insert motivators into database
			if (user_motivators[i]['motivator'] != "") {
				insertDatabaseObject('user_motivator', user_motivators[i], columns['user_motivator']);
			}
		}
		// leave m4 points blank if m4 name input is blank
		$("#motivator_pts_3").attr("readonly", $("#motivator_3").val() == "");
		$("#motivator_pts_3").val($("#motivator_3").val() == "" ? "" : 0);
		$("#bonuses").val(1);
	}

	// close modal, hide and show elements
	$("#motivator_modal").modal("hide");
	$("#motivator_button").hide();
	$("#motivators").show();
}

function motivatorCheck(id) {
	var ids = ['m1', 'm2', 'm3', 'm4']
	for (var i in ids) {
		if (ids[i] != id) {
			if ($("#"+ids[i]).val() == $("#"+id).val()) {
				$("#"+id).val("");
			}
		}
	}
}

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
		if (!hiddenEnabled.includes(this.id)) {
			hiddenEnabled.push(this.id);
			$(this).on("focus", function() {
				// remove type='number' attribute to allow input of non-numeric input
				$(this).removeAttr("type");
				// get current input val of text field
				var input = $("#"+this.id+"_text");
				$(this).val(input.val());
				// empty text field
				input.val("");
			});
			$(this).on("focusout", function() {
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