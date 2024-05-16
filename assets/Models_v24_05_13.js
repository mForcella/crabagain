
let userTalents = [];
let userTrainings = [];
let userMotivators = [];
let userWeapons = [];
let userProtections = [];
let userHealings = [];
let userMisc = [];
let userNotes = [];


// UserTalent

// add a new feat from modal values
function updateFeat() {

	if (!adminEditMode) {
		return;
	}

	let featName = $("#feat_name_val").val() == "" ? $("#standard_talent_name").val() : $("#feat_name_val").val();
	let featType = $("#feat_type").val() == "" ? "feat" : $("#feat_type").val();
	let feat_id = $("#feat_id").val() == "" ? 0 : $("#feat_id").val();
	let user_feat_id = $("#user_feat_id").val();
	let featDescription = $("#feat_description").val();
	if (featDescription == "") {
		// get description from talents if empty
		for (var i in talents) {
			if (talents[i]['id'] == feat_id) {
				featDescription = talents[i]['description'];
			}
		}
	}

	if (feat_id == 0) {
		// update name
		updateDatabaseColumn('user_feat', 'name', featName, user_feat_id);
		$("#feat_"+user_feat_id+"_name").html(featName+" : ");
		getTalent(user_feat_id).name = featName;
	}

	if (featType == "standard_talent" || featType == "magic_talent" || featType == "school_talent") {
		// update description
		updateDatabaseColumn('user_feat', 'description', featDescription, user_feat_id);
		getTalent(user_feat_id).description = featDescription;
		$("#feat_"+user_feat_id+"_descrip").html(featDescription.length > 100 ? featDescription.substring(0,100)+"..." : featDescription);
	}
}

// set feats as eligible/ineligible and set autocomplete list
function setFeatList() {
	var eligible_feats = [];
	var ineligible_feats = [];
	// set autocomplete list for feats
	$.each(talents, function(i, feat) {
		if (feat['type'] == 'feat' || feat['type'] == 'standard_talent' || feat['type'] == 'magic_talent' || feat['type'] == "school_talent") {
			var is_eligible = true;
			// feat[requirements] is an array of arrays - each array must return true
			$.each(feat['requirements'], function(j, requirements) {
				var satisfied = false;
				// requirements is an array of dictionaries (req) - one req within requirements needs to return true
				$.each(requirements, function(k, req) {
					for (key in req) {
						switch (key) {
							case 'feat':
								for (var i in userTalents) {
									satisfied = satisfied ? true : userTalents[i].name.includes(req[key]);
								}
								break;
							case 'training':
								for (var i in userTrainings) {
									satisfied = satisfied ? true : userTrainings[i].name.includes(req[key]);
								}
								break;
							case 'character_creation':
								satisfied = characterCreation;
								break;
							case 'governing':
								// get user's governing magic school name and/or value
								for (var i in userTrainings) {
									if (userTrainings[i].governing_school == 1) {
										if (isNaN(req[key])) {
											satisfied = satisfied ? true : userTrainings[i].name == req[key];
										} else {
											satisfied = satisfied ? true : parseInt(userTrainings[i].value) >= parseInt(req[key]);
										}
									}
								}
								break;
							default: // skill
								// get adjusted value for attributes
								let attribute = key.replace("_","");
								var size_mod;
								var age_mod;
								switch(attribute) {
									case "strength":
									case "fortitude":
										size_mod = $("#power_mod").val() == "" ? 0 : parseInt($("#power_mod").val());
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
								let attribute_val = parseInt(user[key]) + size_mod + age_mod;
								satisfied = satisfied ? true : attribute_val >= parseInt(req[key]);
								break;
						}
					}
				});
				// if a previous requirement wasn't satisfied, feat is not eligible
				is_eligible = !is_eligible ? false : satisfied;
			});
			if (is_eligible && !eligible_feats.includes(feat) && feat['id'] != undefined) {
				feat['satisfied'] = true;
				eligible_feats.push(feat);
			} else if (!ineligible_feats.includes(feat) && feat['id'] != undefined) {
				feat['satisfied'] = false;
				ineligible_feats.push(feat);
			}
		}
	});

	// sort and merge feat lists - eligible feats at the top of the list in bold
	var standard_list1 = [];
	var magic_list1 = [];
	for (var i in eligible_feats) {
		if (eligible_feats[i]['type'] == 'magic_talent' || eligible_feats[i]['type'] == 'school_talent') {
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
		if (ineligible_feats[i]['type'] == 'magic_talent' || ineligible_feats[i]['type'] == 'school_talent') {
			magic_list2.push(ineligible_feats[i]['name']);
		} else {
			standard_list2.push(ineligible_feats[i]['name']);
		}
	}
	standard_list2.sort();
	magic_list2.sort();
	var standardList = standard_list1.concat(standard_list2);
	var magicList = magic_list1.concat(magic_list2);

	$("#standard_talent_name").autocomplete({
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

// determine which feats to show in the autocomplete list
function featSourceFunction(input, add, list) {
	var suggestions = [];
	$.each(list, function(i, feat_name) {
		if (feat_name.toLowerCase().includes(input['term'].toLowerCase())) {
			var entry = new Object();
			entry.value = feat_name;
			// check if attribute requirements are satisfied
			entry.satisfied = true;
			$.each(talents, function(j, feat_vals) {
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
			for (var i in userTalents) {
				if (userTalents[i].name == entry['value']) {
					// allow Shapeshifter, Elementalist, and Elemental Master to be learned multiple times
					found = (entry['value'] == "Shapeshifter" || entry['value'] == "Elementalist" || entry['value'] == "Elemental Master") ? false : true;
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

// renders talent in list with different class based on eligibility
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

// select a talent or magic talent from an autocomplete list
function featSelectFunction(ui) {
	// build user message if requirements aren't satisfied
	if (!ui.item.satisfied) {
		var requirements = "";
		for (var i in talents) {
			if (talents[i]['name'] == ui.item.value) {
				for (var j in talents[i]['requirements']) {
					for (var k in talents[i]['requirements'][j]) {
						for (var key in talents[i]['requirements'][j][k]) {
							// key types : feat, training, character_creation, governing, or attribute
							// adjust labels for requirements
							var item = key;
							var req = talents[i]['requirements'][j][k][key];
							item = capitalize(item);
							item = item == "Precision_" ? "Precision" : item;
							item = item == "Governing" ? "Governing School" : item;
							req = isNaN(req) ? req : "+"+req;
							requirements += key == "character_creation" ? "*only available during character creation" : 
								item + " : " + req;
						}
						if (talents[i]['requirements'][j].length > 1 && k < talents[i]['requirements'][j].length-1) {
							requirements += " OR ";
						}
					}
					requirements += "\n";
				}
				requirements += "\n";
				requirements += talents[i]['name']+"\n";
				requirements += talents[i]['description'];
			}
		}
		alert("Requirements not met for "+ui.item.value+":\n\n"+requirements);
		$("#standard_talent_name").val("").removeClass("x onX");
		$("#feat_description").val("");
		return false;
	} else {

		// check for talents requiring additional selection
		let talent = ui.item.value;
		hideMagicSelects(talent, "");

		// auto fill description on feat selection
		$("#feat_name_val").val(talent);
		for (var i in talents) {
			if (talents[i]['name'] == talent) {
				$("#feat_id").val(talents[i]['id']);
				$("#feat_type").val(talents[i]['type']);
				$("#feat_cost").val(talents[i]['cost']);
				$("#feat_description").val(talents[i]['description']).height($("#feat_description")[0].scrollHeight);
			}
		}
		return true;
	}
}

// show only the selected dropdown
function hideMagicSelects(select, value) {
	$(".elemental_select").addClass("hidden");
	$(".elementalist_select").addClass("hidden");
	$(".superhuman_select").addClass("hidden");
	$(".shapeshifter_select").addClass("hidden");
	if (select == "Elemental Master") {
		$(".elemental_select").removeClass("hidden").val(value == "" ? "Fire" : value).attr("disabled", false);
	} else if (select == "Elementalist") {
		$(".elementalist_select").removeClass("hidden").val(value == "" ? "Earth" : value).attr("disabled", false);
	} else if (select == "Superhuman") {
		$(".superhuman_select").removeClass("hidden").val(value == "" ? "Power/Dexterity" : value).attr("disabled", false);
	} else if (select == "Shapeshifter") {
		$(".shapeshifter_select").removeClass("hidden")
		$("#animal_name").val(value);
		// TODO any way to get animal level? would need to save cost to database; hide animal level input for now?
	}
}

function newFeat() {

	let featName = $("#feat_name_val").val() == "" ? $("#standard_talent_name").val() : $("#feat_name_val").val();
	let featDescription = $("#feat_description").val();
	let featType = $("#feat_type").val() == "" ? "feat" : $("#feat_type").val();
	let featCost = $("#feat_cost").val() == "" ? 4 : $("#feat_cost").val();
	let feat_id = $("#feat_id").val() == "" ? 0 : $("#feat_id").val();

	// check for talents requiring additional selection
	var featDisplayName = featName;
	if (featName == "Elemental Master") {
		featDisplayName += " ("+$(".elemental_select").val()+")";
	} else if (featName == "Elementalist") {
		featDisplayName += " ("+$(".elementalist_select").val()+")";
	} else if (featName == "Superhuman") {
		featDisplayName += " ("+$(".superhuman_select").val()+")";
	} else if (featName == "Shapeshifter") {
		// make sure animal name isn't empty
		if ($("#animal_name").val() == "") {
			alert("Please enter an animal name");
			return;
		}
		featDisplayName += " ("+$("#animal_name").val()+")";
	}

	// make sure we're not adding a duplicate training name
	for (var i in userTalents) {
		if (userTalents[i].display_name == featDisplayName) {
			alert("Talent name already in use");
			return;
		}
	}

	// only allow one profession, one compelling action, one social trait, one morale trait
	if (featType == "profession") {
		for (var j in userTalents) {
			if (userTalents[j]['type'] == "profession") {
				alert("Only one Profession can be chosen");
				return;
			}
		}
	}
	if (featType == "social_background") {
		for (var j in userTalents) {
			if (userTalents[j]['type'] == "social_background") {
				alert("Only one Social Background can be chosen");
				return;
			}
		}
	}
	if (featType == "compelling_action") {
		for (var j in userTalents) {
			if (userTalents[j]['type'] == "compelling_action") {
				alert("Only one Compelling Action can be chosen");
				return;
			}
		}
	}
	if (featType == "social_trait") {
		for (var j in userTalents) {
			if (userTalents[j]['type'] == "social_trait") {
				alert("Only one Social Trait can be chosen");
				return;
			}
		}
	}
	if (featType == "morale_trait") {
		for (var j in userTalents) {
			if (userTalents[j]['type'] == "morale_trait") {
				alert("Only one Morale Trait can be chosen");
				return;
			}
		}
	}

	// Giant and Dwarf not allowed at the same time
	if (featName == "Giant" && hasTalent("Dwarf")) {
		alert("Giant and Dwarf are not allowed concurrently");
		return;
	}
	if (featName == "Dwarf" && hasTalent("Giant")) {
		alert("Giant and Dwarf are not allowed concurrently");
		return;
	}

	// strong and frail constitution not allowed at the same time
	if (featName == "Strong Constitution" && hasTalent("Frail Constitution")) {
		alert("Strong Constitution and Frail Constitution are not allowed concurrently");
		return;
	}
	if (featName == "Frail Constitution" && hasTalent("Strong Constitution")) {
		alert("Strong Constitution and Frail Constitution are not allowed concurrently");
		return;
	}

	// check for 'Divine Magic'
	if (featName == "Divine Magic") {
		// prompt to choose a vow
		$("#vows_modal").modal("show");
	}

	if (featName != "" && featDescription != "") {

		// check if we can add this talent (allocating points)
		if (allocatingAttributePts) {

			// only one talent/skill per level
			var skillCount = 0;
			for (var i in userTrainings) {
				if (userTrainings[i].is_new && (userTrainings[i].skill_type == "skill" || userTrainings[i].skill_type == "school" || userTrainings[i].skill_type == "esoteric")) {
					skillCount += 1;
				}
			}
			var talentCount = 0;
			for (var i in userTalents) {
				if (userTalents[i].is_new) {
					talentCount += 1;
				}
			}
			if ((!addingNewSchool) && (talentCount >= maxSkillsAllocated || skillCount >= maxSkillsAllocated)) {
				alert("Only one new talent or unique skill can be added per level.");
				return;
			}

			// make magic talent free
			if (addingNewSchool) {
				featCost = 0;
				addingNewSchool = false;
			}

			// check if we're adding shapeshifter - add animal level to cost
			if (featName == "Shapeshifter") {
				featCost = 4 + parseInt($("#animal_level").val());
			}

			// make sure we have enough points
			var pts = parseInt($(".attribute-count").html().split(" Points")[0]);
			if (pts - featCost < 0) {
				alert("Not enough attribute points to allocate for a new talent/trait.");
				return;
			}

			// decrease points
			$(".attribute-count").html(pts - featCost+" Points");

		}

		// create new talent object
		let newTalent = new UserTalent({
			"name":featDisplayName,
			"display_name":featDisplayName,
			"description":featDescription.trim(),
			"feat_id":feat_id,
			"type":featType,
			"cost":featCost
		});

		insertDatabaseObject('user_feat', newTalent, newTalent.getColumns());
		newTalent.postInsertCallback = function(insert_id) {
			this.postInsertCallback = null;
			this.name = featName;
			userTalents.push(this);
			addFeatElements(this);
		};

	}
}

// create html elements for feat
function addFeatElements(talent) {

	// check if user is eligible for magic talents
	user['magic_talents'] = user['magic_talents'] || talent.name == "Arcane Blood" || talent.name == "Divine Magic";
	if (user['magic_talents']) {
		$("#magic_option").show();
	}

	var id_val = "feat_"+talent.id;
	let featDescription = talent.description.trim().split("\n\n")[0]; // remove extraneous text from feat descriptions

	var feat_container = createElement('div', 'feat', '#feats', id_val);
	talent.DOM_element = feat_container;
	if (allocatingAttributePts) {
		talent.is_new = true;
	}

	var feat_title_descrip = createElement('div', '', feat_container);

  $('<p />', {
  	'id': id_val+"_name",
  	'class': 'feat-title',
  	'text': talent.display_name+" : "
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
    	// editTalent(talent);
    	talent.edit();
    });

    $("#"+id_val+"_remove").on("click", function() {
    	talent.delete();
    });

	// highlight on hover
	feat_container.hover(function() {
		$(this).addClass("highlight");
	}, function() {
		$(this).removeClass("highlight");
	});

	// add hidden inputs
  createInput('', 'hidden', 'feat_names[]', talent.display_name, feat_container, id_val+"_name_val");
  createInput('', 'hidden', 'feat_descriptions[]', featDescription, feat_container, id_val+"_descrip_val");
	createInput('', 'hidden', 'feat_ids[]', talent.feat_id, feat_container);
	createInput('', 'hidden', 'user_feat_ids[]', talent.id, feat_container);

	// check for specific feats and adjust attributes (quick and the dead, improved crit, etc)
	if (talent.name == "Improved Critical Hit") {
		for (var i in userWeapons) {
			for (var j in userWeapons[i].equipped_index) {
				let crit = userWeapons[i].getCritModifier();
				$("#weapon_crit_"+userWeapons[i].equipped_index[j]).val(crit);
			}
		}
	}
	if (talent.name == "Diehard") {
		$("#damage").trigger("change");
	}
	if (talent.name == "Quick and the Dead") {
		adjustInitiative();
	}
	if (talent.name == "Lightning Reflexes") {
		setDodge();
	}
	if (talent.name == "Relentless Defense") {
		setDodge();
		setDefend();
	}
	// check for talents: Giant & Dwarf; adjust and lock size
	if (talent.name == "Giant" || talent.name == "Dwarf") {
		// get race size and adjust
		let sizes = [
			"Tiny",
			"Small",
			"Medium",
			"Large",
			"Giant"
		];
		let size_adjust = talent.name == "Dwarf" ? -1 : 1;
		let race = getRace($("#race").val());
		let base = race == false ? 2 : sizes.indexOf(race['size']);
		$("#character_size_select").val(sizes[base + size_adjust]);
		editSize(true);
		$("#size").attr("data-toggle", null).addClass("cursor-auto");
	}

	// adjust feat eligibility
	setFeatList();
}

// check if user has talent/trait
function hasTalent(talentName) {
	for (var i in userTalents) {
		if (userTalents[i]['name'] == talentName) {
			return true;
		}
	}
	return false;
}

function getTalent(talent_id) {
	for (var i in userTalents) {
		if (userTalents[i]['id'] == talent_id || userTalents[i]['name'] == talent_id) {
			return userTalents[i];
		}
	}
	return false;
}

class UserTalent {

	id;
    name;
    display_name;
    description;
    feat_id;
    type;
    cost;
    DOM_element;
    is_new;

	database_columns = [
		'name',
		'description',
		'feat_id'
	];

	constructor(talent) {
		this.id = parseInt(talent['id']);
		this.name = talent['name'];
		this.display_name = talent['display_name'];
		this.description = talent['description'];
		// these values may be empty for custom talents
		this.feat_id = parseInt(talent['feat_id']); // default = 0
		this.type = talent['type'] != undefined ? talent['type'] : "standard_talent"; // default = feat
		this.cost = talent['cost'] != undefined ? parseInt(talent['cost']) : 4; // default = 4
		this.is_new = false;
	}

	delete(prompt=true) {
    	// confirm delete
    	var conf = prompt == false ? true : confirm("Remove Talent, '"+this.display_name+"'?");
    	if (conf) {

			// if allocating attribute points, increase points
			if (allocatingAttributePts) {
				var pts = parseInt($(".attribute-count").html().split(" Points")[0]);
				// if removing a negative trait, make sure we have enough points
				if (this.cost < 0 && pts + this.cost < 0) {
					alert("Not enough attribute points to remove trait, "+this.name);
					return;
				}
				$(".attribute-count").html(pts + this.cost +" Points");
			}

			// remove elements and update arrays
			this.DOM_element.remove();
			for (var i in userTalents) {
				if (userTalents[i].display_name == this.display_name) {
				  deleteDatabaseObject('user_feat', userTalents[i].id);
				  userTalents.splice(i, 1);
				  break;
				}
			}

			// check if we're removing feats that affect other attributes
			if (this.name == "Improved Critical Hit") {
				for (var i in userWeapons) {
					for (var j in userWeapons[i].equipped_index) {
						let crit = userWeapons[i].getCritModifier();
						$("#weapon_crit_"+userWeapons[i].equipped_index[j]).val(crit);
					}
				}
			}
			if (this.name == "Diehard") {
				$("#damage").trigger("change");
			}
			if (this.name == "Quick and the Dead") {
				adjustInitiative();
			}
			if (this.name == "Lightning Reflexes") {
				setDodge();
			}
			if (this.name == "Relentless Defense") {
				setDodge();
				setDefend();
			}

			// check for talents: Giant & Dwarf; adjust and unlock size
			if (this.name == "Giant" || this.name == "Dwarf") {
				// get race size and adjust
				let sizes = [
					"Tiny",
					"Small",
					"Medium",
					"Large",
					"Giant"
				];
				let race = getRace($("#race").val());
				let base = race == false ? 2 : sizes.indexOf(race['size']);
				$("#character_size_select").val(sizes[base]);
				editSize(true);
				$("#size").attr("data-toggle", characterCreation ? "modal" : null).removeClass(characterCreation ? "cursor-auto" : "");
			}
		}
	}

	edit() {
		let featType = this.type == "physical_trait" ? (this.cost > 0 ? "physical_trait_pos" : "physical_trait_neg") : this.type;

		// check for elementalist, etc, and show additional dropdown
		let subType = this.display_name.split("(")[1];
		if (subType) {
			subType = subType.substring(0, subType.length - 1);
		}
		hideMagicSelects(this.name, subType);

		// set dropdown value
		featType = featType == "school_talent" ? "magic_talent" : featType;
		$("#select_feat_type").val(featType+"_name");
		$(".feat-type").addClass("hidden");
		$("#"+featType+"_name").val(this.name).removeClass("hidden").attr("disabled", !(adminEditMode && this.feat_id == 0));

		var description = this.description;

		// if editing is allowed, change feat_update_btn text to 'Update', else set to 'Ok'
		$("#feat_update_btn").html(adminEditMode && (featType == "standard_talent" || featType == "magic_talent") ? "Update" : "Ok");
		if (!adminEditMode || (featType != "standard_talent" && featType != "magic_talent")) {
			$("#feat_cancel_btn").addClass("hidden");
		}
		$("#feat_description").val(description).attr("disabled", !(adminEditMode && (featType == "standard_talent" || featType == "magic_talent")));
		// focus on description if editable, and name is not
		if (adminEditMode && (featType == "standard_talent" || featType == "magic_talent") && this.feat_id != 0) {
			$("#feat_description").focus();
			// set text hint equal to talents item description
			$("#feat_description").attr("placeholder", "");
			for (var i in talents) {
				if (talents[i]['id'] == this.feat_id) {
					$("#feat_description").attr("placeholder", talents[i]['description']);
				}
			}
		}
		$("#feat_id").val(this.feat_id);
		$("#user_feat_id").val(this.id);
		$("#feat_modal_title").html("Update Talent");
		$("#feat_update_btn").removeClass("hidden");
		$("#feat_submit_btn").addClass("hidden");
		$("#new_feat_modal").modal("show");
		$("#feat_description").height( $("#feat_description")[0].scrollHeight );
	}

	getColumns() {
		return this.database_columns;
	}

}


// UserTraining

function newTrainingModal(attribute) {

	// launch modal
	$("#training_modal_title").html("New "+attribute+" Training");
	$("#attribute_type").val(attribute);
	$("#training_name").val("");

	// look for esoteric knowledge, show esoteric skills
	$("#esoteric_inputs").hide();
	for (var i in userTrainings) {
		if (userTrainings[i]['name'] == "Esoteric Knowledge" && userTrainings[i]['value'] >= 4) {
			// check attribute for esoteric skills
			var skills = trainingAutocompletes[attribute]['esoteric'];
			if (skills.length > 0) {
				$("#esoteric_inputs").show();
			}
		}
	}

	// deselect radios and hide and clear inputs
	$("input:radio[name='skill_type']").each(function(i) {
	      this.checked = false;
	});
	$("#skill_name").val("").hide().removeClass("x onX");
	$("#training_name").val("").hide().removeClass("x onX");
	$("#focus_name").val("").hide().removeClass("x onX");
	$("#focus_name2").val("").hide().removeClass("x onX");
	$("#school_name").val("").hide().removeClass("x onX");
	$("#esoteric_name").val("").hide().removeClass("x onX");

	// if vitality, check for magic talents
	if (attribute == "Vitality" && user['magic_talents'] == true) {
		$("#magic_inputs").show();

		// if divine magic, only one school is allowed
		if (hasTalent("Divine Magic")) {
			var schoolCount = 0;
			for (var j in userTrainings) {
				if (userTrainings[j].magic_school == 1) {
					schoolCount += 1;
				}
			}
			if (schoolCount > 0) {
				$("#magic_inputs").hide();
			}
		}
	} else {
		$("#magic_inputs").hide();
	}

	// set autocomplete values to inputs - skill_name, training_name, focus_name
	let skill = trainingAutocompletes[attribute]['skill'];
	let training = trainingAutocompletes[attribute]['training'];
	let focus = trainingAutocompletes[attribute]['focus'];
	let esoteric = trainingAutocompletes[attribute]['esoteric'];
	$("#esoteric_name").autocomplete({
		source: esoteric
	});
	$("#skill_name").autocomplete({
		source: skill
	});
	$("#training_name").autocomplete({
		source: training
	});
	$("#focus_name").autocomplete({
		source: focus,
		select: function(event, ui) {
			// check for specifics required
			let val = ui.item.value;
			if (val.includes("(specific")) {
				$(this).val(val.split("(")[0]);
				// show additional input
				var specific = val.split("(specific ")[1];
				specific = specific.slice(0, -1); 
				$("#focus_name2").show().attr("placeholder", "specific "+(specific)+" name");
				return false;
			} else {
				// hide additional input
				$("#focus_name2").hide();
				return true;
			}
		}
	});

	$("#new_training_modal").modal("show");
}

// add a new training from modal values
function newTraining() {

	// make sure skill type is selected
	var skillType = $('input[name=skill_type]:checked').val();
	if (skillType == undefined) {
		alert("Please select a skill type");
		return;
	}

	var trainingName = $("#"+skillType+"_name").val();
	var attribute = $("#attribute_type").val();

	if (trainingName != "") {
		// check for additional input
		if ($("#focus_name2").is(":visible")) {
			if ($("#focus_name2").val() == "") {
				alert("Please specify a value");
				return;
			}
			trainingName = $("#focus_name2").val();
		}
		// check if user is already trained
		for (var i in userTrainings) {
			if (userTrainings[i]['name'] == trainingName) {
				alert("Training name already in use");
				return;
			}
		}

		// if Arcane Blood - check if first (governing) school
		var schoolCount = 0;
		if (hasTalent("Arcane Blood")) {
			for (var j in userTrainings) {
				if (userTrainings[j].magic_school == 1) {
					schoolCount += 1;
				}
			}
		}

		// allocating attribute points, make sure we are allowed to add the skill
		if (allocatingAttributePts) {

			var skillCount = 0;
			var trainingCount = 0;
			var talentCount = 0;

			for (var i in userTrainings) {
				if (userTrainings[i].is_new && (userTrainings[i].skill_type == "skill" || userTrainings[i].skill_type == "school" || userTrainings[i].skill_type == "esoteric")) {
					skillCount += 1;
				}
				if (userTrainings[i].is_new && (userTrainings[i].skill_type == "focus" || userTrainings[i].skill_type == "training")) {
					trainingCount += 1;
				}
			}
			for (var i in userTalents) {
				if (userTalents[i].is_new) {
					talentCount += 1;
				}
			}

			if ((skillType == "skill" || skillType == "school" || skillType == "esoteric") && skillCount >= maxSkillsAllocated || talentCount >= maxSkillsAllocated) {
				alert("Only one new talent or unique skill can be added per level.");
				return;
			}

			if ((skillType == "focus" || skillType == "training") && trainingCount >= maxSkillsAllocated) {
				alert("Only one new focus or training can be added per level.");
				return;
			}

			let skillPts = (skillType == "skill" || skillType == "training") ? 2 : ( (skillType == "school" || skillType == "esoteric") ? 4 : 1);
			var pts = parseInt($(".attribute-count").html().split(" Points")[0]);
			if (pts - skillPts < 0) {
				alert("Not enough attribute points to allocate for a new skill training.");
				return;
			}
			// decrease attribute points if training is allowed
			$(".attribute-count").html(pts - skillPts +" Points");
		}

		// create new training object
		let newTraining = new UserTraining({
			'attribute_group':attribute,
			'name':trainingName,
			'value': (skillType == "skill" || skillType == "school" || skillType == "esoteric") ? 0 : 1,
			'magic_school':skillType == "school",
			'governing_school':skillType == "school" && schoolCount == 0 ? 1 : 0
		});

		insertDatabaseObject('user_training', newTraining, newTraining.getColumns());
		newTraining.postInsertCallback = function(insert_id) {
			this.postInsertCallback = null;
			userTrainings.push(this);
			addTrainingElements(this, skillType);
		};

	}

}

// create html elements for training
function addTrainingElements(training, skillType) {

	var id_val = "training_"+training.id;

	// determine skill point cost and starting skill value if new training
	training.skill_pts = (skillType == "skill" || skillType == "training") ? 2 : ( (skillType == "school" || skillType == "esoteric") ? 4 : 1);

	var row = createElement('div', 'row training-row', '#'+training.attribute_group, id_val+"_row");
	training.DOM_element = row;
	training.skill_type = skillType;
	if ($("#"+training.attribute_group+"_up").is(":visible")) {
		training.is_new = true;
	}
	var div_left = createElement('div', 'col-md-7 col-xs-8', row);

	var label_left = $('<label />', {
	  'class': 'control-label with-hidden',
	  'for': id_val,
	  'text': training.getDisplayName(),
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
	var editingSection = $("#"+training.attribute_group+"_btn").find(".glyphicon-plus-sign").is(":visible");
	if (allocatingAttributePts || adminEditMode || editingSection) {
		removeBtn.show();
	}
	removeBtn.on("click", function() {
		training.delete();
	});

	var div_right = createElement('div', 'col-md-5 col-xs-4', row);

	var label_right = $('<label />', {
	  'class': 'control-label'
	}).appendTo(div_right);

	let text = $('<span />', {
		'id': id_val+"_text",
	  'class': 'attribute-val',
	  'html': training.value == '' ? '+0' : (training.value >= 0 ? "+"+training.value : training.value),
	}).appendTo(label_right);

	// check if new training is stealth, look for size mod
	if (training.name.toLowerCase() == "stealth" && $("#power_mod").val() != "") {
		let stealth_mod = parseInt(training.value) - parseInt($("#power_mod").val());
		text.html(stealth_mod >= 0 ? "+"+stealth_mod : stealth_mod);
	}

	createInput('', 'hidden', 'training[]', training.name+":"+training.attribute_group, label_right, id_val+"_name");
	createInput('', 'hidden', 'training_val[]', training.value == '' ? 0 : training.value, label_right, id_val+"_val");
	createInput('', 'hidden', 'training_magic[]', 0, label_right, id_val+"_magic");
	createInput('', 'hidden', 'training_governing[]', 0, label_right, id_val+"_governing");
	createInput('', 'hidden', 'training_ids[]', training.id, label_right);

	var up = createElement('span', 'glyphicon glyphicon-plus hidden-icon', label_right, id_val+"_up");
	$("#"+id_val+"_up").on("click", function() {
		adjustAttribute(id_val, 1);
	});

	var down = createElement('span', 'glyphicon glyphicon-minus hidden-icon', label_right, id_val+"_down");
	$("#"+id_val+"_down").on("click", function() {
		adjustAttribute(id_val, -1);
	});

	// show plus minus icons for new talents during character creation or GM edit mode
	if (training.is_new && (characterCreation || adminEditMode)) {
		up.show();
		down.show();
	}

	enableHiddenNumbers();

	// adjust feat eligibility
	setFeatList();

	// if new magic school - prompt to choose talent
	if (training.is_new && training.magic_school == 1) {
		// use id_val to update hidden input values
		$("#"+id_val+"_magic").val(1);
		$("#"+id_val+"_governing").val(training.governing_school);

		// update talent dropdown on new_school_modal
		var talents = schoolTalents[training.name];
		$("#magic_talents").html("");
		$('<option />', {
		  'value': '',
		}).appendTo($("#magic_talents"));
		for (var i in talents) {
			$('<option />', {
			  'value': training.name+":"+talents[i]['name'],
			  'text': talents[i]['name'],
			}).appendTo($("#magic_talents"));
		}
		$("#talent_descrip").height(54).val("");
		$(".elemental_select").addClass("hidden");
		$(".elementalist_select").addClass("hidden");
		$(".superhuman_select").addClass("hidden");
		$(".shapeshifter_select").addClass("hidden");
		$("#new_school_modal").modal("show");
	}
}

// add new magic school with starting talent
function newSchool() {
	addingNewSchool = true;
	$("#feat_name_val").val($("#magic_talents").val().split(":")[1]);
	$("#feat_description").val($("#talent_descrip").val());
	// set feat_id value for new talent
	for (var i in talents) {
		if (talents[i]['name'] == $("#feat_name_val").val()) {
			$("#feat_id").val(talents[i]['id']);
		}
	}
	newFeat();
}

// add new magic school canceled without selecting starting talent
// TODO replace 'cancel' button with 'back' button?
// set modal title to show school name?
function cancelMagic() {
	$("#magic_talents").val("");
}

// check if user has training/skill/focus
function isTrained(trainingName) {
	for (var i in userTrainings) {
		if (userTrainings[i].name == trainingName) {
			return true;
		}
	}
	return false;
}

// get a training by name or id
function getTraining(training_id) {
	for (var i in userTrainings) {
		if (userTrainings[i].id == training_id || userTrainings[i].name == training_id) {
			return userTrainings[i];
		}
	}
	return false;
}

// make sure school isn't already trained
$("#school_name").on("change", function() {
	for (var i in userTrainings) {
		if (userTrainings[i].name == $(this).val()) {
			$(this).val("");
		}
	}
});

class UserTraining {

	id;
    name;
    value;
    attribute_group;
    magic_school;
    governing_school;
    display_name;
    DOM_element;
    is_new;
    skill_type;
    skill_pts;

	database_columns = [
		'name',
		'value',
		'attribute_group',
		'magic_school',
		'governing_school'
	];

	constructor(training) {
		this.id = parseInt(training['id']);
		this.name = training['name'];
		this.value = parseInt(training['value']);
		this.attribute_group = training['attribute_group'];
		this.magic_school = training['magic_school'] == "1" ? 1 : 0;
		this.governing_school = training['governing_school'] == "1" ? 1 : 0;
		this.is_new = false;
	}

	delete() {
		var conf = confirm("Remove training '"+this.name+"'?");
		if (conf) {
			// if allocating points, increase point count
			if (allocatingAttributePts) {
				var pts = parseInt($(".attribute-count").html().split(" Points")[0]);
				$(".attribute-count").html(pts + this.skill_pts +" Points");
			}
			this.DOM_element.remove();
			deleteDatabaseObject('user_training', this.id);
			deleteArrayObject(userTrainings, this.id)

			// check if we're removing a magic school and delete any associated talents
			if (this.magic_school == 1) {
				var talents = schoolTalents[this.name];
				for (var i in talents) {
					for (var j in userTalents) {
						if (userTalents[j]["name"].includes(talents[i]["name"])) {
							$(".feat-title").each(function() {
								if ($(this).html().split(" : ")[0].includes(talents[i]["name"])) {
									// don't prompt to delete talent
									userTalents[j].delete(false);
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

	getColumns() {
		return this.database_columns;
	}

	// get the modified display name for a magic school
	getDisplayName() {

		// make sure we have a magic school
		if (this.magic_school != 1) {
			return this.name;
		}

		var displayName = this.name;

		// get governing school name
		var governing = "";
		for (var i in userTrainings) {
			if (userTrainings[i].governing_school == 1) {
				governing = userTrainings[i]['name'];
			}
		}

		// look for magic school - set label to governing/companion/opposition
		if (this.governing_school == 1) {
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
		return displayName;
	}

}


// UserMotivator

// change primary motivators (personality crisis)
function updateMotivators() {
	// get selected motivator to set primary_ to false
	let remove = $('input[name="update_motivators"]:checked').val();
	// get new motivator to set primary_ to true
	let add = $("#crisis_name").html();
	// update motivators in database
	for (var i in userMotivators) {
		if (userMotivators[i]['motivator'] == add) {
			userMotivators[i]['primary_'] = 1;
			updateDatabaseColumn('user_motivator', 'primary_', 1, userMotivators[i]['id']);
		}
		if (userMotivators[i]['motivator'] == remove) {
			userMotivators[i]['primary_'] = 0;
			updateDatabaseColumn('user_motivator', 'primary_', 0, userMotivators[i]['id']);
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

// set motivator bonuses based on primary motivator points
function setMotivatorBonus() {
	var m_pts = 0;
	for (var i in userMotivators) {
		if (userMotivators[i]['primary_'] == 1) {
			m_pts += parseInt(userMotivators[i]['points']);
		}
	}
	var bonuses = m_pts >= 64 ? 5 : (m_pts >= 32 ? 4 : (m_pts >= 16 ? 3 : (m_pts >= 8 ? 2 : (m_pts >= 4 ? 1 : 0))));

	// check for morale modifiers
	var morale = $("#morale").val();
	bonuses += morale >= 4 ? 1 : (morale <= -4 ? -1 : 0);
	$("#bonuses").val(bonuses);
}

// launch edit motivators modal
function editMotivators() {
	// check if editing is allowed
	if (characterCreation || adminEditMode || userMotivators.length == 0) {
		// set values to current motivators
		$("#m1").val($("#motivator_0").val());
		$("#m2").val($("#motivator_1").val());
		$("#m3").val($("#motivator_2").val());
		$("#m4").val($("#motivator_3").val());
		$("#motivator_modal").modal("show");
	}
}

// set motivators on modal close
function setMotivators() {

	// make sure primary motivators are set
	if ($("#m1").val() == "" || $("#m2").val() == "" || $("#m3").val() == "") {
		alert("Please set required motivators");
		return;
	}

	// iterate through userMotivators and update/insert/delete
	for (var i = 0; i < 4; i++) {
		let motivator = [];

		// get motivator name
		let motivator_name = $("#m"+(i+1)).val();
		$("#motivator_"+i).val(motivator_name);
		motivator['motivator'] = motivator_name;

		// get point values
		let val = i == 0 ? 2 : (i == 3 ? 0 : 1);
		$("#motivator_pts_"+i).val(val);
		$("#motivator_primary_"+i).val(i != 3 ? 1 : 0);
		motivator['points'] = val;
		motivator['primary_'] = i != 3 ? 1 : 0;

		// delete if userMotivators[i] exists and motivator_name is ""
		if (userMotivators[i] != null && motivator_name == "") {
			deleteDatabaseObject('user_motivator', userMotivators[i].id);
			userMotivators.splice(i, 1);
		}
		// update if userMotivators[i] exists and motivator_name is not ""
		else if (userMotivators[i] != null && motivator_name != "") {
			// update name and primary_ only
			updateDatabaseColumn('user_motivator', 'motivator', motivator_name, userMotivators[i].id);
			updateDatabaseColumn('user_motivator', 'primary_', motivator['primary_'], userMotivators[i].id);
		}
		// insert if userMotivators[i] does not exist and motivator_name is not ""
		else if (userMotivators[i] == null && motivator_name != "") {
			let userMotivator = new UserMotivator(motivator);
			userMotivators.push(userMotivator);
			insertDatabaseObject('user_motivator', userMotivator, userMotivator.getColumns());
		}
	}

	// leave m4 points blank if m4 name input is blank
	$("#motivator_pts_3").attr("readonly", $("#motivator_3").val() == "");
	$("#motivator_pts_3").val($("#motivator_3").val() == "" ? "" : 0);
	// set bonus value if needed
	$("#bonuses").val($("#bonuses").val() == 0 ? 1 : $("#bonuses").val());

	// close modal, hide and show elements
	$("#motivator_modal").modal("hide");
	$("#motivator_button").hide();
	$("#motivators").show();
}

/// makes sure motivator is only selected once
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

class UserMotivator {

	id;
    motivator;
    points;
    primary_;

	database_columns = [
		'motivator',
		'points',
		'primary_'
	];

	constructor(motivator) {
		this.id = parseInt(motivator['id']);
		this.motivator = motivator['motivator'];
		this.points = parseInt(motivator['points']);
		this.primary_ = motivator['primary_'] == "1" ? 1 : 0;
	}

	getColumns() {
		return this.database_columns;
	}

}


// UserWeapon

function getWeapon(weapon_id) {
	for (var i in userWeapons) {
		if (userWeapons[i].id == weapon_id || userWeapons[i].name == weapon_id) {
			return userWeapons[i];
		}
	}
	return false;
}

// add a new weapon from modal values - or edit existing weapon
function newWeapon() {
	// check if we are editing
	var editing = $("#weapon_modal_title").html() == "Edit Weapon";
	var type = $("#weapon_type").val();
	var name = $("#weapon_name").val();
	var damage = $("#weapon_damage").val();
	var max_damage = $("#weapon_max_damage").val();
	var range = $("#weapon_range_").val();
	var rof = $("#weapon_rof").val();
	var defend = $("#weapon_defend").val();
	var crit = $("#weapon_crit").val();
	var notes = $("#weapon_notes").val();
	var weight = $("#weapon_weight").val() == "" ? 0 : $("#weapon_weight").val();
	var qty = $("#weapon_quantity").val() == "" ? 1 : $("#weapon_quantity").val();
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
		var weapon_id = "weapon_"+$("#weapon_id").val();
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

		// update weapon object
		let weapon = getWeapon(originalName);
		weapon.type = type;
		weapon.damage = parseInt(damage);
		weapon.defend = defend == "" ? null : parseInt(defend);
		weapon.crit = crit == "" ? null : parseInt(crit);
		weapon.max_damage = max_damage == "" ? null : parseInt(max_damage);
		weapon.quantity = qty;
		weapon.name = name;
		weapon.range_ = range == "" ? null : parseInt(range);
		weapon.rof = rof;
		weapon.notes = notes;
		weapon.weight = weight;
		updateDatabaseObject('user_weapon', weapon, weapon.getColumns());

		// update weapon dropdowns
		$($(".weapon-select").get().reverse()).each(function() {
			$(this).find("option").each(function() {
				if ($(this).val() == originalName) {
					$(this).val(name);
					$(this).html(name);
				}
			});
			// re-select if equipped to update inputs
			if ($(this).val() == originalName) {
				selectWeapon(this.id.slice(-1), false);
			}
		});
	} else {
		// check to make sure name is not a duplicate
		for (var i in userWeapons) {
			if (userWeapons[i].name == name) {
				alert("Weapon name already in use");
				return;
			}
		}
		let newWeapon = new UserWeapon({
			'type':type,
			'name':name,
			'quantity':1,
			'damage':damage,
			'max_damage':max_damage,
			'range_':range,
			'rof':rof,
			'defend':defend,
			'crit':crit,
			'notes':notes,
			'weight':weight,
			'equipped':0,
		});
		insertDatabaseObject('user_weapon', newWeapon, newWeapon.getColumns());
		newWeapon.postInsertCallback = function(insert_id) {
			this.postInsertCallback = null;
			userWeapons.push(this);
			addWeaponElements(this);
		};
	}
}

// create html elements for weapon
function addWeaponElements(weapon) {
	let id_val = "weapon_"+weapon.id;

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
		weapon.delete(id_val);
	});

	let name_input = createInput('', 'text', 'weapons[]', weapon.name, div1, id_val+"_name");
	let qty_input = createInput('qty', 'number', 'weapon_qty[]', weapon.quantity, div2, id_val+"_qty");
	// check for max damage
	let damageText = weapon.max_damage != null ? weapon.damage +" ("+weapon.max_damage+")" : weapon.damage;
	let dmg_input = createInput('', 'text', '', damageText, div3, id_val+"_damage");
	// add range, rof & defend bonus to notes
	var noteMod = "";
	weapon.notes = weapon.notes == null ? "" : weapon.notes;
	noteMod += weapon.range_ != null && weapon.range_ != "" ? "Range: "+weapon.range_+"; " : "";
	noteMod += weapon.rof != null && weapon.rof != "" ? "RoF: "+weapon.rof+"; " : "";
	noteMod += weapon.defend != null && weapon.defend != "" ? "+"+weapon.defend+" Defend; " : "";
	noteMod += weapon.crit != null && weapon.crit != "" ? "+"+weapon.crit+" Critical Threat Range; " : "";
	let notesText = noteMod + weapon.notes;
	let note_input = createInput('', 'text', '', notesText, div4, id_val+"_notes");
	let wgt_input = createInput('wgt', 'text', 'weapon_weight[]', weapon.weight, div5, id_val+"_weight");
	createInput('', 'hidden', 'weapon_damage[]', weapon.damage, div5, id_val+"_damage_val");
	createInput('', 'hidden', 'weapon_notes[]', weapon.notes, div5, id_val+"_notes_val");
	createInput('', 'hidden', 'weapon_type[]', weapon.type, div5, id_val+"_type");
	createInput('', 'hidden', 'weapon_max_damage[]', weapon.max_damage, div5, id_val+"_max_damage");
	createInput('', 'hidden', 'weapon_range[]', weapon.range_, div5, id_val+"_range");
	createInput('', 'hidden', 'weapon_rof[]', weapon.rof, div5, id_val+"_rof");
	createInput('', 'hidden', 'weapon_defend[]', weapon.defend, div5, id_val+"_defend");
	createInput('', 'hidden', 'weapon_crit[]', weapon.crit, div5, id_val+"_crit");
	createInput('', 'hidden', 'weapon_ids[]', weapon.id, div5);
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
	itemTitle.html(weapon.name+" : ");
	let itemDetails = createElement('span', 'note-content', itemText, id_val+"_mobile_details");
	itemDetails.html("Damage: "+damageText+"; Weight: "+weapon.weight+"lbs; Qty: "+weapon.quantity+"; "+notesText);

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
		weapon.edit("name");
	});
	qty_input.click(function() {
		weapon.edit("quantity");
	});
	dmg_input.click(function() {
		weapon.edit("damage");
	});
	note_input.click(function() {
		// look for range, rof, defend, and crit values
		let note_val = note_input.val();
		let focus = note_val.includes("Range") ? "range_" : 
			(note_val.includes("RoF") ? "rof" : (note_val.includes("Defend") ? "defend" : (note_val.includes("Critical") ? "crit" : "notes")));
		weapon.edit(focus);
	});
	wgt_input.click(function() {
		weapon.edit("weight");
	});
	itemText.click(function() {
		weapon.edit("name");
	});

	// add weapon option to dropdown
	let option1 = $('<option />', {
  	'text': weapon.name,
  	'value': weapon.name
	}).appendTo("#weapon_select_1");
	let option2 = option1.clone().appendTo("#weapon_select_2");
	let option3 = option1.clone().appendTo("#weapon_select_3");

	enableHiddenNumbers();

}

// select a weapon from the dropdown
// param: dropdownID - the selected dropdown ID
// param: updateDatabase - true if we are making a selection from the dropdown; requires object/database update
function selectWeapon(dropdownID, updateDatabase=true) {
	let selectedVal = $("#weapon_select_"+dropdownID).val();

	// get selected and de-selected weapon from array
	var selectedWeapon;
	var deselectedWeapon;
	for (var i in userWeapons) {

		// find the de-selected weapon
		if (userWeapons[i].equipped_index.includes(dropdownID)) {
			deselectedWeapon = userWeapons[i];
		}

		// find the newly selected weapon
		if (selectedVal == userWeapons[i].name) {
			selectedWeapon = userWeapons[i];
		}
	}
			
	// check if equip is allowable (weapon quantity)
	let allowable = selectedVal == "" || !updateDatabase || selectedWeapon.equipped < selectedWeapon.quantity;
	if (!allowable) {
		// restore original selection name and return
		$("#weapon_select_"+dropdownID).val(deselectedWeapon == undefined ? "" : deselectedWeapon.name);
		return;
	}

	if (deselectedWeapon != undefined) {
		deselectedWeapon.equip(false, updateDatabase, dropdownID);
	}
	if (selectedWeapon != undefined) {
		selectedWeapon.equip(true, updateDatabase, dropdownID);
	}

	setDefend();
}

// don't allow ";" in RoF inputs; used for parsing note value
$("#weapon_rof").on('keypress', function(e) {
	if (e.charCode == 59) {
		e.preventDefault();
		return false;
	}
});

class UserWeapon {

	id;
	name;
	type;
	quantity;
	damage;
	max_damage;
	range_;
	rof;
	defend;
	crit;
	notes;
	weight;
	equipped;
	equipped_index;
	
	database_columns = [
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
	];

	constructor(weapon) {
		this.id = parseInt(weapon['id']);
		this.name = weapon['name'];
		this.type = weapon['type'];
		this.quantity = weapon['quantity'];
		this.damage = parseInt(weapon['damage']);
		this.max_damage = weapon['max_damage'] == null || weapon['max_damage'] == "" ? null : parseInt(weapon['max_damage']);
		this.range_ = weapon['range_'] == null ? null : parseInt(weapon['range_']);
		this.rof = weapon['type'] == "Melee" ? null : weapon['rof'];
		this.defend = weapon['defend'] == null || weapon['defend'] == "" ? null : parseInt(weapon['defend']);
		this.crit = weapon['crit'] == null || weapon['crit'] == "" ? null : parseInt(weapon['crit']);
		this.notes = weapon['notes'];
		this.weight = parseInt(weapon['weight']);
		this.equipped = parseInt(weapon['equipped']);
		this.equipped_index = [];
	}

	delete(element_id) {
		let weapon = this;
		let conf = confirm("Remove item, '"+weapon.name+"'?");
		if (conf) {
			$("#"+element_id).remove();
			$("#"+element_id+"_mobile").remove();
			deleteDatabaseObject('user_weapon', weapon.id);
			deleteArrayObject(userWeapons, weapon.id)
			updateTotalWeight(false);

			// remove weapon from dropdown and clear inputs if weapon was selected
			$(".weapon-select").find("option").each(function() {
				if ($(this).val() == weapon.name) {
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
		}
	}

	// equip or unequip a weapon
	// param: equip - boolean - true if equipping, false if unequipping
	// param: updateDatabase - true if we are making a selection from the dropdown; requires object/database update
	// param: dropdownID - the selected dropdown ID
	equip(equip, updateDatabase, dropdownID) {
		// weapon is being equipped
		if (equip) {
			$("#weapon_select_"+dropdownID).val(this.name);
			// get weapon damage
			let damage_mod = this.getDamageMod();
			$("#weapon_damage_"+dropdownID).val(damage_mod != 0 ? this.damage+" (+"+damage_mod+")" : this.damage);
			// look for crit modifiers
			let crit = this.getCritModifier();
			$("#weapon_crit_"+dropdownID).val(crit);
			$("#weapon_range_"+dropdownID).val(this.range_ == null || this.range_ == "" ? "-" : this.range_);
			$("#weapon_rof_"+dropdownID).val(this.rof == "" ? "-" : this.rof);
			if (updateDatabase) {
				this.equipped_index.push(dropdownID);
				this.equipped += 1;
				updateDatabaseColumn('user_weapon', 'equipped', this.equipped, this.id);
			}
		}
		// weapon is being unequipped
		else {
			$("#weapon_select_"+dropdownID).val("");
			$("#weapon_damage_"+dropdownID).val("");
			$("#weapon_crit_"+dropdownID).val("");
			$("#weapon_range_"+dropdownID).val("");
			$("#weapon_rof_"+dropdownID).val("");
			let index = this.equipped_index.indexOf(dropdownID);
			if (index != -1) {
				this.equipped_index.splice(index, 1);
				this.equipped -= 1;
				updateDatabaseColumn('user_weapon', 'equipped', this.equipped, this.id);
			}
		}
	}

	// set modal values and launch
	edit(input_id) {
		for (let [key,val] of Object.entries(this)) {
			$("#weapon_"+key).val(val);
		}
		focus_id = "#weapon_"+input_id;
		$("#weapon_modal_title").html("Edit Weapon");
		$("#new_weapon_modal").modal("show");
	}

	getColumns() {
		return this.database_columns;
	}

	// calculate critical threat range modifier based on weapon and user feats
	getCritModifier() {
		var crit = 6;
		if (this.crit != null && this.crit != '') {
			crit -= parseInt(this.crit);
		}
		for (var i in userTalents) {
			if (userTalents[i]['name'].toLowerCase() == "improved critical hit") {
				crit -= 1;
				break;
			}
		}
		return crit;
	}

	// calculate damage mod based on character attributes
	getDamageMod() {
		if (this.type == "Melee") {
			// check for strength modifier for melee weapons
			var damage_mod = parseInt($("#strength_val").val()) >= 0 ? 
				Math.floor(parseInt($("#strength_val").val())/2) : Math.ceil(parseInt($("#strength_val").val())/3);
		} else {
			// check for precision modifier for ranged weapons
			var damage_mod = parseInt($("#precision__val").val()) >= 0 ? 
				Math.floor(parseInt($("#precision__val").val())/2) : Math.ceil(parseInt($("#precision__val").val())/3);
		}
		// make sure damage doesn't exceed max allowed
		if (this.max_damage != null && (this.damage + damage_mod) > this.max_damage) {
			damage_mod = this.max_damage - this.damage;
		}
		return damage_mod;
	}

}


// UserProtection

function getProtection(protection_id) {
	for (var i in userProtections) {
		if (userProtections[i]['id'] == protection_id || userProtections[i]['name'] == protection_id) {
			return userProtections[i];
		}
	}
	return false;
}

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
		var protection_id = "protection_"+$("#protection_id").val();
		var originalName = $("#"+protection_id+"_name").val();
		$("#"+protection_id+"_name").val(name);
		$("#"+protection_id+"_bonus").val(bonus);
		$("#"+protection_id+"_notes").val(notes);
		$("#"+protection_id+"_weight").val(weight);
		// update mobile row
		$("#"+protection_id+"_mobile_title").html(name+" : ");
		$("#"+protection_id+"_mobile_details").html("Bonus: +"+bonus+"; Weight: "+weight+"lbs; "+notes);

		// update user protection object
		let protection = getProtection(originalName);
		protection.bonus = parseInt(bonus);
		protection.name = name;
		protection.notes = notes;
		protection.weight = parseInt(weight);
		// update database entry
		updateDatabaseObject('user_protection', protection, protection.getColumns());
		setToughness();
		updateTotalWeight(true);
	} else {
		// check to make sure name is not a duplicate
		for (var i in userProtections) {
			if (userProtections[i].name ==  name) {
				alert("Protection name already in use");
				return;
			}
		}
		let newProtection = new UserProtection({
			'name':name,
			'bonus':bonus,
			'notes':notes,
			'weight':weight,
			'equipped':0,
		});
		insertDatabaseObject('user_protection', newProtection, newProtection.getColumns());
		newProtection.postInsertCallback = function(insert_id) {
			this.postInsertCallback = null;
			userProtections.push(this);
			addProtectionElements(this, true);
		};

	}
}

// create html elements for protection
function addProtectionElements(protection, newProtection) {
	var id_val = "protection_"+protection.id;

	// TODO create elements via cloning?
	var div = createElement('div', '', '#protections', id_val);
	var div0 = createElement('div', 'form-group item item-protection', div); // desktop row container
	var div3 = createElement('div', 'col-xs-1 no-pad-mobile col-icon equip-btn', div); // equip btn
	var div1 = createElement('div', 'col-xs-3 no-pad-mobile col-icon-right', div0); // name
	var div2 = createElement('div', 'col-xs-1 no-pad-mobile', div0); // bonus
	var div4 = createElement('div', 'col-xs-5 no-pad-mobile', div0); // notes
	var div5 = createElement('div', 'col-xs-1 no-pad-mobile', div0); // weight
	var div6 = createElement('div', 'col-xs-1 no-pad-mobile center remove-btn', div); // remove btn

	var name_input = createInput('', 'text', 'protections[]', protection.name, div1, id_val+"_name");
	var bonus_input = createInput('', 'text', 'protection_bonus[]', protection.bonus, div2, id_val+"_bonus");
	let notesText = protection.notes == null ? "" : protection.notes;
	var notes_input = createInput('', 'text', 'protection_notes[]', notesText, div4, id_val+"_notes");
	var weight_input = createInput('wgt', 'text', 'protection_weight[]', protection.weight, div5, id_val+"_weight");
	name_input.attr("readonly", true);
	bonus_input.attr("readonly", true);
	notes_input.attr("readonly", true);
	weight_input.attr("readonly", true);
	createInput('', 'hidden', 'protection_ids[]', protection.id, div6);

	// mobile item layout
	let mobileItemRow = createElement('span', 'item-mobile item-protection', div);
	let itemText = createElement('span', 'note item-label-mobile', mobileItemRow);
	itemText.hover(function() {
		$(this).addClass("highlight");
	}, function() {
		$(this).removeClass("highlight");
	});
	let itemTitle = createElement('span', 'note-title', itemText, id_val+"_mobile_title");
	itemTitle.html(protection.name+" : ");
	let itemDetails = createElement('span', 'note-content', itemText, id_val+"_mobile_details");
	itemDetails.html("Bonus: +"+protection.bonus+"; Weight: "+protection.weight+"lbs; "+notesText);

	// enable highlighting and click functions for newly created elements
	enableHighlight(name_input, "protections[]");
	enableHighlight(bonus_input, "protection_bonus[]");
	enableHighlight(notes_input, "protection_notes[]");
	enableHighlight(weight_input, "protection_weight[]");
	name_input.click(function() {
		protection.edit("name");
	});
	bonus_input.click(function() {
		protection.edit("bonus");
	});
	notes_input.click(function() {
		protection.edit("notes");
	});
	weight_input.click(function() {
		protection.edit("weight");
	});
	itemText.click(function() {
		protection.edit("name");
	});

	// add equip button
	createElement('span', 'glyphicon svg fa-solid icon-armor custom-icon', div3, id_val+"_equip");
	createElement('span', 'glyphicon glyphicon-ban-circle', div3, id_val+"_equip_ban");
	createInput('', 'hidden', 'protection_equipped[]', protection.equipped == 1, div3, id_val+"_equipped");
	$(div3).on("click", function() {
		protection.equip(id_val);
	});

	// check if protection is currently equipped
	if (protection.equipped) {
		$("#protection_"+protection.id+"_equip_ban").toggle();
	}

	// add remove button
	let removeBtn = createElement('span', 'glyphicon glyphicon-remove', div6);
	removeBtn.on("click", function() {
		protection.delete(id_val);
	});

	enableHiddenNumbers();
	updateTotalWeight(true);

	// prompt user to equip new protection
	if (newProtection) {
		protection.equip(id_val);
	}
}

class UserProtection {

	id;
	name;
	bonus;
	notes;
	weight;
	equipped;
	
	database_columns = [
		'name',
		'bonus',
		'notes',
		'weight',
		'equipped'
	];

	constructor(protection) {
		this.id = parseInt(protection['id']);
		this.name = protection['name'];
		this.bonus = protection['bonus'] == null || protection['bonus'] == "" ? 0 : parseInt(protection['bonus']);
		this.notes = protection['notes'];
		this.weight = parseInt(protection['weight']);
		this.equipped = protection['equipped'] == "1" ? 1 : 0;
	}

	delete(element_id) {
		let protection_name = $("#"+element_id+"_name").val();
		let conf = confirm("Remove item, '"+protection_name+"'?");
		if (conf) {
			$("#"+element_id).remove();
		  	deleteDatabaseObject('user_protection', this.id);
			deleteArrayObject(userProtections, this.id);
			setToughness();
		  	updateTotalWeight(false);
		}
	}

	equip(element_id) {
		let equipped = !$("#"+element_id+"_equip_ban").is(":visible");
		let protection_name = $("#"+element_id+"_name").val();
		let conf = confirm((equipped ? "Unequip" : "Equip")+" protection '"+protection_name+"'?");
		if (conf) {
			$("#"+element_id+"_equip_ban").toggle();
			if (this) {
				$("#"+element_id+"_equipped").val(equipped);
				this.equipped = equipped ? 0 : 1;
				updateDatabaseColumn('user_protection', 'equipped', this.equipped, this.id);
				setToughness();
			}
		}
	}

	// set edit protection modal values and launch
	edit(input_id) {
		for (let [key,val] of Object.entries(this)) {
			$("#protection_"+key).val(val);
		}
		focus_id = "#protection_"+input_id;
		$("#protection_modal_title").html("Edit Protection");
		$("#new_protection_modal").modal("show");
	}

	getColumns() {
		return this.database_columns;
	}

}


// UserHealing

function getHealing(healing_id) {
	for (var i in userHealings) {
		if (userHealings[i]['id'] == healing_id || userHealings[i]['name'] == healing_id) {
			return userHealings[i];
		}
	}
	return false;
}

// add a new healing/potion/drug from modal values
function newHealing() {
	// check if we are editing
	let editing = $("#healing_modal_title").html() == "Edit Healing/Potion/Drug";
	let name = $("#healing_name").val();
	let quantity = $("#healing_quantity").val() == "" ? 1 : $("#healing_quantity").val();
	let effect = $("#healing_effect").val();
	let weight = $("#healing_weight").val() == "" ? 0 : $("#healing_weight").val();
	if (name == "") {
		alert("Name is required");
		return;
	}
	$("#new_healing_modal").modal("hide");
	if (editing) {
		// update healing inputs
		let healing_id = "healing_"+$("#healing_id").val();
		let originalName = $("#"+healing_id+"_name").val();
		$("#"+healing_id+"_name").val(name);
		$("#"+healing_id+"_quantity").val(quantity);
		$("#"+healing_id+"_effect").val(effect);
		$("#"+healing_id+"_weight").val(weight);
		// update mobile row
		$("#"+healing_id+"_mobile_title").html(name+" : ");
		$("#"+healing_id+"_mobile_details").html("Effect: "+effect+"; Weight: "+weight+"lbs; Qty: "+quantity);
		updateTotalWeight(true);
		// update healing in database
		let healing = getHealing(originalName);
		healing.name = name;
		healing.quantity = quantity;
		healing.effect = effect;
		healing.weight = weight;
		updateDatabaseObject('user_healing', healing, healing.getColumns());
	} else {
		// check to make sure name is not a duplicate
		for (var i in userHealings) {
			if (userHealings[i].name ==  name) {
				alert("Item name already in use");
				return;
			}
		}
		// insert new healing into database and user array
		let newHealing = new UserHealing({
			'name':name,
			'quantity':quantity,
			'effect':effect,
			'weight':weight,
		});
		insertDatabaseObject('user_healing', newHealing, newHealing.getColumns());

		// post insert callback function
		newHealing.postInsertCallback = function(insert_id) {
			this.postInsertCallback = null;
			userHealings.push(this);
			addHealingElements(this);
		};

	}
}

// create html elements for healing
function addHealingElements(healing) {
	var id_val = "healing_"+healing.id;

	var div = createElement('div', '', '#healings', id_val);
	let div0 = createElement('div', 'form-group item', div); // desktop row container
	var div1 = createElement('div', 'col-xs-4 no-pad-mobile', div0); // name
	var div3 = createElement('div', 'col-xs-5 no-pad-mobile', div0); // notes
	var div4 = createElement('div', 'col-xs-1 no-pad-mobile', div0); // weight
	var div2 = createElement('div', 'col-xs-1 no-pad-mobile', div0); // qty
	var div5 = createElement('div', 'col-xs-1 no-pad-mobile center remove-btn', div); // remove btn

	var name_input = createInput('', 'text', 'healings[]', healing.name, div1, id_val+"_name");
	var qty_input = createInput('qty', 'text', 'healing_quantity[]', healing.quantity, div2, id_val+"_quantity");
	var effect_input = createInput('', 'text', 'healing_effect[]', healing.effect, div3, id_val+"_effect");
	var weight_input = createInput('wgt', 'text', 'healing_weight[]', healing.weight, div4, id_val+"_weight");
	createInput('', 'hidden', 'healing_ids[]', healing.id, div4);
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
	itemTitle.html(healing.name+" : ");
	let itemDetails = createElement('span', 'note-content', itemText, id_val+"_mobile_details");
	itemDetails.html("Effect: "+healing.effect+"; Weight: "+healing.weight+"lbs; Qty: "+healing.quantity);

	name_input.attr("readonly", true);
	qty_input.attr("readonly", true);
	effect_input.attr("readonly", true);
	weight_input.attr("readonly", true);
	enableHighlight(name_input, "healings[]");
	enableHighlight(qty_input, "healing_quantity[]");
	enableHighlight(effect_input, "healing_effect[]");
	enableHighlight(weight_input, "healing_weight[]");
	name_input.click(function() {
		healing.edit("name");
	});
	qty_input.click(function() {
		healing.edit("quantity");
	});
	effect_input.click(function() {
		healing.edit("effect");
	});
	weight_input.click(function() {
		healing.edit("weight");
	});
	mobileItemRow.click(function() {
		healing.edit("name");
	});

	// add remove button
	createElement('span', 'glyphicon glyphicon-remove', div5, id_val+"_remove");
	$("#"+id_val+"_remove").on("click", function() {
		healing.delete(id_val);
	});

	enableHiddenNumbers();

}

class UserHealing {

	id;
	name;
	effect;
	weight;
	quantity;
	
	database_columns = [
		'name',
		'quantity',
		'effect',
		'weight'
	];

	constructor(healing) {
		this.id = parseInt(healing['id']);
		this.name = healing['name'];
		this.effect = healing['effect'];
		this.quantity = healing['quantity'];
		this.weight = parseInt(healing['weight']);
	}

	delete(element_id) {
		let healing_name = $("#"+element_id+"_name").val();
		let conf = confirm("Remove item, '"+healing_name+"'?");
		if (conf) {
			$("#"+element_id).remove();
		  	deleteDatabaseObject('user_healing', this.id);
			deleteArrayObject(userHealings, this.id);
		  	updateTotalWeight(false);
		}
	}

	edit(input_id) {
		for (let [key,val] of Object.entries(this)) {
			$("#healing_"+key).val(val);
		}
		focus_id = "#healing_"+input_id;
		$("#healing_modal_title").html("Edit Healing/Potion/Drug");
		$("#new_healing_modal").modal("show");
	}

	getColumns() {
		return this.database_columns;
	}

}


// UserMisc

function getMisc(misc_id) {
	for (var i in userMisc) {
		if (userMisc[i]['id'] == misc_id || userMisc[i]['name'] == misc_id) {
			return userMisc[i];
		}
	}
	return false;
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
		var misc_id = "misc_"+$("#misc_id").val();
		let originalName = $("#"+misc_id+"_name").val();
		$("#"+misc_id+"_name").val(name);
		$("#"+misc_id+"_quantity").val(quantity);
		$("#"+misc_id+"_notes").val(notes);
		$("#"+misc_id+"_weight").val(weight);
		// update mobile row
		$("#"+misc_id+"_mobile_title").html(name+" : ");
		$("#"+misc_id+"_mobile_details").html( (notes == "" ? "" : "Notes: "+notes+"; ")+"Weight: "+weight+"lbs; Qty: "+quantity);
		updateTotalWeight(true);
		// update misc in database
		let misc = getMisc(originalName);
		misc.name = name;
		misc.quantity = quantity;
		misc.notes = notes;
		misc.weight = weight;
		updateDatabaseObject('user_misc', misc, misc.getColumns());
	} else {
		// check to make sure name is not a duplicate
		for (var i in userMisc) {
			if (userMisc[i].name ==  name) {
				alert("Item name already in use");
				return;
			}
		}
		// insert new misc into database and user array
		var newMisc = new UserMisc({
			'name':name,
			'quantity':quantity,
			'notes':notes,
			'weight':weight,
		});
		insertDatabaseObject('user_misc', newMisc, newMisc.getColumns());
		newMisc.postInsertCallback = function(insert_id) {
			this.postInsertCallback = null;
			userMisc.push(this);
			addMiscElements(this);
		};
	}
}

// create html elements for misc item
function addMiscElements(misc) {
	var id_val = "misc_"+misc.id;

	var div = createElement('div', '', '#misc', id_val);
	let div0 = createElement('div', 'form-group item', div); // desktop row container
	var div1 = createElement('div', 'col-xs-4 no-pad-mobile', div0); // name
	var div3 = createElement('div', 'col-xs-5 no-pad-mobile', div0); // notes
	var div4 = createElement('div', 'col-xs-1 no-pad-mobile', div0); // weight
	var div2 = createElement('div', 'col-xs-1 no-pad-mobile', div0); // qty
	var div5 = createElement('div', 'col-xs-1 no-pad-mobile center remove-btn', div);

	var name_input = createInput('', 'text', 'misc[]', misc.name, div1, id_val+"_name");
	var qty_input = createInput('qty', 'text', 'misc_quantity[]', misc.quantity, div2, id_val+"_quantity");
	let notesText = misc.notes == null ? "" : misc.notes;
	var notes_input = createInput('', 'text', 'misc_notes[]', notesText, div3, id_val+"_notes");
	var weight_input = createInput('wgt', 'text', 'misc_weight[]', misc.weight, div4, id_val+"_weight");
	createInput('', 'hidden', 'misc_ids[]', misc.id, div4);
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
	itemTitle.html(misc.name+" : ");
	let itemDetails = createElement('span', 'note-content', itemText, id_val+"_mobile_details");
	itemDetails.html( (notesText == "" ? "" : "Notes: "+notesText+"; ")+"Weight: "+misc.weight+"lbs; Qty: "+misc.quantity);

	name_input.attr("readonly", true);
	qty_input.attr("readonly", true);
	notes_input.attr("readonly", true);
	weight_input.attr("readonly", true);
	enableHighlight(name_input, "misc[]");
	enableHighlight(qty_input, "misc_quantity[]");
	enableHighlight(notes_input, "misc_notes[]");
	enableHighlight(weight_input, "misc_weight[]");
	name_input.click(function() {
		misc.edit("name");
	});
	qty_input.click(function() {
		misc.edit("quantity");
	});
	notes_input.click(function() {
		misc.edit("notes");
	});
	weight_input.click(function() {
		misc.edit("weight");
	});
	mobileItemRow.click(function() {
		misc.edit("name");
	});

	// add remove button
	createElement('span', 'glyphicon glyphicon-remove', div5, id_val+"_remove");
	$("#"+id_val+"_remove").on("click", function() {
		misc.delete(id_val);
	});

	enableHiddenNumbers();

}

class UserMisc {

	id;
	name;
	notes;
	weight;
	quantity;
	
	database_columns = [
		'name',
		'quantity',
		'notes',
		'weight'
	];

	constructor(misc) {
		this.id = parseInt(misc['id']);
		this.name = misc['name'];
		this.notes = misc['notes'];
		this.quantity = misc['quantity'];
		this.weight = parseInt(misc['weight']);
	}

	delete(element_id) {
		let misc_name = $("#"+element_id+"_name").val();
		let conf = confirm("Remove item, '"+misc_name+"'?");
		if (conf) {
			$("#"+element_id).remove();
		  	deleteDatabaseObject('user_misc', this.id);
			deleteArrayObject(userMisc, this.id);
		  	updateTotalWeight(false);
		}
	}

	edit(input_id) {
		for (let [key,val] of Object.entries(this)) {
			$("#misc_"+key).val(val);
		}
		focus_id = "#misc_"+input_id;
		$("#misc_modal_title").html("Edit Miscellaneous Item");
		$("#new_misc_modal").modal("show");
	}

	getColumns() {
		return this.database_columns;
	}

}


// UserNote

function getNote(note_id) {
	for (var i in userNotes) {
		if (userNotes[i]['id'] == note_id || userNotes[i]['name'] == note_id) {
			return userNotes[i];
		}
	}
	return false;
}

// get note values from modal
function newNote() {
	// check if we are editing
	var editing = $("#note_modal_title").html() == "Edit Note";
	var title = $("#note_title").val();
	var note = $("#note_note").val();
	if (note == "" && title == "") {
		return;
	}

	if (editing) {
		// update note inputs values
		var note_id = "note_"+$("#note_id").val(); // ID value is set when a user opens the note modal
		$("#"+note_id+"_title").html(title == "" ? "" : title+": ");
		$("#"+note_id+"_content").html(note);
		$("#"+note_id+"_title_val").val(title);
		$("#"+note_id+"_content_val").val(note);

		// update note in database
		let userNote = getNote($("#note_id").val());
		userNote.title = title;
		userNote.note = note;
		updateDatabaseObject('user_note', userNote, userNote.getColumns());
	} else {
		// insert new note into database and user array
		var newNote = new UserNote({
			'title':title,
			'note':note,
		});
		insertDatabaseObject('user_note', newNote, newNote.getColumns());
		newNote.postInsertCallback = function(insert_id) {
			this.postInsertCallback = null;
			userNotes.push(this);
			addNoteElements(this);
		};
	}
}

// create note elements
function addNoteElements(note) {
	let id_val = "note_"+note.id;

	let li = $('<li />', {
		'id': id_val,
	}).appendTo("#notes");

	let span = $('<span />', {
	  'class': 'note',
	}).appendTo(li);

	$('<span />', {
		'id': id_val+"_title",
		'html': note.title == null || note.title == "" ? "" : note.title+": ",
	  'class': 'note-title',
	}).appendTo(span);

	$('<span />', {
		'id': id_val+"_content",
		// 'html': note.length > 90 ? note.substring(0,90)+"..." : note,
		'html': note.note,
	  'class': 'note-content',
	}).appendTo(span);

	let remove = $('<span />', {
	  'class': 'glyphicon glyphicon-remove',
	}).appendTo(li);

	// create hidden inputs to hold submit values
	createInput('', 'hidden', 'titles[]', note['title'], span, id_val+"_title_val");
	createInput('', 'hidden', 'notes[]', note['note'], span, id_val+"_content_val");
	createInput('', 'hidden', 'note_ids[]', note['id'], span);

	// highlight on hover
	span.hover(function() {
		$(this).addClass("highlight");
	}, function() {
		$(this).removeClass("highlight");
	});

	// enable edit and delete
	span.click(function() { note.edit(); });
	remove.click(function() { note.delete(id_val); });
}

class UserNote {

	id;
	title;
	note;

	database_columns = [
		'title',
		'note'
	];

	constructor(note) {
		this.id = parseInt(note['id']);
		this.title = note['title'];
		this.note = note['note'];
	}

	delete(element_id) {
		let conf = confirm("Delete note, '"+(this.title == null ? '[untitled]' : this.title)+"'?");
		if (conf) {
			$("#"+element_id).remove();
		  	deleteDatabaseObject('user_note', this.id);
			deleteArrayObject(userNotes, this.id);
		}
	}

	edit() {
		for (let [key,val] of Object.entries(this)) {
			$("#note_"+key).val(val);
		}
		$("#note_modal_title").html("Edit Note");
		$("#new_note_modal").modal("show");
	}

	getColumns() {
		return this.database_columns;
	}

}