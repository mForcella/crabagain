
// UserWeapon

function getWeapon(weapon_id) {
	for (var i in userWeapons) {
		if (userWeapons[i]['id'] == weapon_id || userWeapons[i]['name'] == weapon_id) {
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
		// TODO range_ not recorded for melee weapons; needed for thrown weapons
		weapon.range_ = range == "" ? null : parseInt(range);
		weapon.rof = rof;
		weapon.notes = notes;
		weapon.weight = weight;
		updateDatabaseObject('user_weapon', weapon, columns['user_weapon']);

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
		insertDatabaseObject('user_weapon', newWeapon, columns['user_weapon']);
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
	let qty_input = createInput('qty', 'text', 'weapon_qty[]', weapon.quantity, div2, id_val+"_qty");
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

	constructor(weapon) {
		this.id = parseInt(weapon['id']);
		this.name = weapon['name'];
		this.type = weapon['type'];
		this.quantity = weapon['quantity'];
		this.damage = parseInt(weapon['damage']);
		this.max_damage = weapon['max_damage'] == null || weapon['max_damage'] == "" ? null : parseInt(weapon['max_damage']);
		this.range_ = weapon['type'] == "Melee" ? null : weapon['range_'] == null ? null : parseInt(weapon['range_']);
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

	// calculate critical threat range modifier based on weapon and user feats
	getCritModifier() {
		var crit = 6;
		if (this.crit != null && this.crit != '') {
			crit -= parseInt(this.crit);
		}
		for (var i in user_feats) {
			if (user_feats[i]['name'].toLowerCase() == "improved critical hit") {
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
		updateDatabaseObject('user_protection', protection, columns['user_protection']);
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
		insertDatabaseObject('user_protection', newProtection, columns['user_protection']);
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

	// TODO create elements using cloning?
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
		updateDatabaseObject('user_healing', healing, columns['user_healing']);
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
		insertDatabaseObject('user_healing', newHealing, columns['user_healing']);

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
		$("#"+misc_id+"_name").val(name);
		$("#"+misc_id+"_quantity").val(quantity);
		$("#"+misc_id+"_notes").val(notes);
		$("#"+misc_id+"_weight").val(weight);
		// update mobile row
		$("#"+misc_id+"_mobile_title").html(name+" : ");
		$("#"+misc_id+"_mobile_details").html( (notes == "" ? "" : "Notes: "+notes+"; ")+"Weight: "+weight+"lbs; Qty: "+quantity);
		updateTotalWeight(true);
		// update misc in database
		let misc = getMisc(name);
		misc.name = name;
		misc.quantity = quantity;
		misc.notes = notes;
		misc.weight = weight;
		updateDatabaseObject('user_misc', misc, columns['user_misc']);
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
		insertDatabaseObject('user_misc', newMisc, columns['user_misc']);
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
		updateDatabaseObject('user_note', userNote, columns['user_note']);
	} else {
		// insert new note into database and user array
		var newNote = new UserNote({
			'title':title,
			'note':note,
		});
		insertDatabaseObject('user_note', newNote, columns['user_note']);
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

}