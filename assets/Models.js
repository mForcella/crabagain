// Database Models

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

	constructor(weapon) {
		this.id = weapon['id'] == null ? uuid() : parseInt(weapon['id']);
		this.name = weapon['name'];
		this.type = weapon['type'];
		this.quantity = parseInt(weapon['quantity']);
		this.damage = parseInt(weapon['damage']);
		this.max_damage = weapon['max_damage'] == null || weapon['max_damage'] == "" ? null : parseInt(weapon['max_damage']);
		this.range_ = weapon['type'] == "Melee" ? null : weapon['range_'] == null ? null : parseInt(weapon['range_']);
		this.rof = weapon['type'] == "Melee" ? null : weapon['rof'];
		this.defend = weapon['defend'] == null || weapon['defend'] == "" ? null : parseInt(weapon['defend']);
		this.crit = weapon['crit'] == null || weapon['crit'] == "" ? null : parseInt(weapon['crit']);
		this.notes = weapon['notes'];
		this.weight = parseInt(weapon['weight']);
		this.equipped = parseInt(weapon['equipped']);
	}

	// find a weapon by name or ID
	getWeapon(weapon_id) {
		for (var i in userWeapons) {
			if (userWeapons[i]['id'] == weapon_id || userWeapons[i]['name'] == weapon_id) {
				return userWeapons[i];
			}
		}
		return false;
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
		this.id = protection['id'] == null ? uuid() : parseInt(protection['id']);
		this.name = protection['name'];
		this.bonus = protection['bonus'] == null || protection['bonus'] == "" ? 0 : parseInt(protection['bonus']);
		this.notes = protection['notes'];
		this.weight = parseInt(protection['weight']);
		this.equipped = protection['equipped'] == "1" ? 1 : 0;
	}

	// find a protection by name or ID
	getProtection(protection_id) {
		for (var i in userProtections) {
			if (userProtections[i]['id'] == protection_id || userProtections[i]['name'] == protection_id) {
				return userProtections[i];
			}
		}
		return false;
	}

}