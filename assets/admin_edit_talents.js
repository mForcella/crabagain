

	$("#req_type_select").on("change", function(){
		$(".req-inputs").addClass("hidden");
		$("#"+$(this).val()+"_inputs").removeClass("hidden");
	});
	$("#req_type_select2").on("change", function(){
		$(".req-inputs2").addClass("hidden");
		$("#"+$(this).val()+"_inputs2").removeClass("hidden");
	});

	$("#multi_req").on("change", function(){
		if ($(this).is(":checked")) {
			$("#multi_req_container").removeClass("hidden");
		} else {
			$("#multi_req_container").addClass("hidden");
		}
	});

	// set training list for autocomplete
	var trainings = ["Swimming", "Engineering", "Train Animal", "Perform", "First Aid", "Tactics", "Demolitions", "Security", "Survival", "Sleight of Hand", "Ride Animal", "Stealth"];
	$("#training_val").autocomplete({
		source: trainings.sort(),
		select: function(event, ui) {}
	});
	$("#training_val2").autocomplete({
		source: trainings.sort(),
		select: function(event, ui) {}
	});

	// launch new feat modal
	function newFeatModal(type) {
		$("#feat_type_val").val(type);
		$("#new_feat_modal_title").html( ($("#feat_id").val() == "" ? "New " : "Update ") + $("#section_"+type).html());
		// hide/show elements based on feat type
		$(".new-feat-element").addClass("hidden");
		if (type != "morale_trait") {
			$("#feat_description").removeClass("hidden");
		} else {
			$("#feat_neg_state").removeClass("hidden");
			$("#feat_pos_state").removeClass("hidden");
		}
		if (type == "feat") {
			$("#feat_requirements").removeClass("hidden");
			$("#character_create").removeClass("hidden");
		}
		if (type == "physical_trait_neg") {
			$("#feat_bonus").removeClass("hidden");
		}
		if (type == "physical_trait_pos") {
			$("#feat_cost").removeClass("hidden");
		}
		$("#new_feat_modal").modal("show");
		$("#feat_description_val").height( $("#feat_description_val")[0].scrollHeight );
	}

	$("#new_feat_modal").on('hidden.bs.modal', function(){
		$("#delete_feat_btn").addClass("hidden");
		$("#update_feat_btn").html("Ok");
		$("#new_feat_modal_title").html("New Feat");
		$("#feat_id").val("");
		$("#feat_name_val").val("");
		$("#feat_description_val").val("");
		$("#feat_description_val").height('100px');
		$("#feat_pos_state_val").val("");
		$("#feat_neg_state_val").val("");
		$("#feat_cost_val").val("");
		$("#feat_bonus_val").val("");
		$("#feat_requirements").val("");
		$("#character_create_only").prop("checked", false);
		$("#requirement_container").html("");
	});

	$("#new_req_modal").on('hidden.bs.modal', function(){
		$("#req_type_select").val("").trigger("change");
		$("#attribute_type_val").val("");
		$("#attribute_value").val("");
		$("#training_val").val("");
		$("#feat_val").val("");
		$("#multi_req").prop("checked", false).trigger("change");
		$("#req_type_select2").val("").trigger("change");
		$("#attribute_type_val2").val("");
		$("#attribute_value2").val("");
		$("#training_val2").val("");
		$("#feat_val2").val("");
	});

	// add a new feat requirement
	function newRequirement() {
		// make sure inputs aren't empty
		var multi_req = $("#multi_req").is(":checked");
		var value  = "";
		var value2  = "";
		var feat_type = $("#req_type_select").val();
		var error = "";
		if (feat_type == "") {
			error = "Please select a requirement type";
		} else if (feat_type == "attribute") {
			var attribute_type = $("#attribute_type_val").val();
			var attribute_val = $("#attribute_value").val();
			if (attribute_type == "") {
				error = "Please select an attribute";
			} else if (attribute_val == "") {
				error = "Please enter an attribute value";
			} else {
				value = capitalize(attribute_type.replace("_", ""))+": "+attribute_val;
			}
		} else if (feat_type == "training") {
			var training = $("#training_val").val();
			if (training == "") {
				error = "Please enter a training name";
			} else {
				value = "Training: "+training;
			}
		} else {
			var feat = $("#feat_val").val();
			if (feat == "") {
				error = "Please enter a feat name";
			} else {
				value = "Feat: "+feat;
			}
		}
		if (multi_req) {
			var feat_type2 = $("#req_type_select2").val();
			if (feat_type2 == "") {
				error = "Please select a requirement type";
			} else if (feat_type2 == "attribute") {
				var attribute_type2 = $("#attribute_type_val2").val();
				var attribute_val2 = $("#attribute_value2").val();
				if (attribute_type2 == "") {
					error = "Please select an attribute";
				} else if (attribute_val2 == "") {
					error = "Please enter an attribute value";
				} else {
					value2 = " OR "+capitalize(attribute_type2.replace("_", ""))+": "+attribute_val2;
				}
			} else if (feat_type2 == "training") {
				var training2 = $("#training_val2").val();
				if (training2 == "") {
					error = "Please enter a training name";
				} else {
					value2 = " OR Training: "+training2;
				}
			} else {
				var feat2 = $("#feat_val2").val();
				if (feat2 == "") {
					error = "Please enter a feat name";
				} else {
					value2 = " OR Feat: "+feat2;
				}
			}
			value += value2;
		}
		if (error == "") {
			addRequirement(value);
			$("#new_req_modal").modal("hide");
		} else {
			alert(error);
		}
	}

	// create elements with requirement value
	function addRequirement(value) {
		// create elements
		var span = $('<span />', {
		  'class': 'feat-requirement',
		}).appendTo($("#requirement_container"));
	    $('<p />', {
	    	'class': 'feat-requirement-label',
	    	'text': value
	    }).appendTo(span);
	    $('<input />', {
	    	'type': 'hidden',
	    	'value': value,
	    	'name': 'feat_reqs[]',
	    	'class': 'feat-req-val'
	    }).appendTo(span);
		var removeBtn = $('<span />', {
		  'class': 'glyphicon glyphicon-remove',
		}).appendTo(span);
		removeBtn.on("click", function(){
			span.remove();
		});
	}

	function editFeat(name) {
		// get feat from feat list
		for (var i in talents) {
			if (talents[i]['name'].replace("'", "") == name) {
				$("#delete_feat_btn").removeClass("hidden");
				$("#update_feat_btn").html("Update");
				$("#feat_id").val(talents[i]['id']);
				$("#feat_type_val").val(talents[i]['type']);
				// fill modal values and launch modal
				$("#feat_name_val").val(talents[i]['name']);
				if (talents[i]['type'] != "morale_trait") {
					$("#feat_description_val").val(talents[i]['description']);
				} else {
					var pos_state = talents[i]['description'].split("Positive State: ")[1].split("; Negative State")[0];
					var neg_state = talents[i]['description'].split("Negative State: ")[1];
					$("#feat_neg_state_val").val(neg_state);
					$("#feat_pos_state_val").val(pos_state);
				}
				if (talents[i]['type'] == "feat") {
					for (var j in talents[i]['requirements']) {
						value = "";
						for (var k in talents[i]['requirements'][j]) {
							if (k > 0) {
								value += " OR ";
							}
							for (var l in talents[i]['requirements'][j][k]) {
								if (l == "character_creation") {
									$("#character_create_only").prop("checked", true);
								} else {
									if (l == "feat") {
										value += "Feat: "+talents[i]['requirements'][j][k][l];
									} else if (l == "training") {
										value += "Training: "+talents[i]['requirements'][j][k][l];
									} else {
										value += capitalize(l).replace("_", "")+": "+talents[i]['requirements'][j][k][l];
									}
								}
							}
						}
						if (value != "") {
							addRequirement(value);
						}
					}
				}
				if (talents[i]['type'] == "physical_trait" && talents[i]['cost'] < 0) {
					$("#feat_bonus_val").val(talents[i]['cost']*-1);
				}
				if (talents[i]['type'] == "physical_trait" && talents[i]['cost'] > 0) {
					$("#feat_cost_val").val(talents[i]['cost']);
				}
				newFeatModal(talents[i]['type'] == "physical_trait" ? 
					(talents[i]['cost'] > 0 ? "physical_trait_pos" : "physical_trait_neg") : talents[i]['type']);

			}
		}
	}

	// remove feat from database
	function deleteFeat() {
		var conf = confirm("Are you sure you want to delete this feat?");
		if (conf) {
			// get feat id
			var feat_id = $("#feat_id").val();
			// close modal
			$("#new_feat_modal").modal("hide");
			// send ajax request
			$.ajax({
				url: '/scripts/delete_feat.php',
				data: { 'feat_id' : feat_id, 'login_id' : $("#login_id").val() },
				ContentType: "application/json",
				type: 'POST',
				success: function(response){
					// remove row from table
					if (response == 'ok') {
						$("#row_"+feat_id).remove();
						// update talents and feats
						var feat_name = "";
						for (var i in talents) {
							if (talents[i]['id'] == feat_id) {
								feat_name = talents[i]['name'];
								talents.splice(i,1);
								break;
							}
						}
						for (var i in feats) {
							if (feats[i] == feat_name) {
								feats.splice(i,1);
								break;
							}
						}
					}
				}
			});
		}
	}

	// create/update feat from modal values
	function newFeat() {
		var error = "";
		if ($("#feat_name_val").val() == "") {
			error = "Name is required";
		}
		switch ($("#feat_type_val").val()) {
			case "feat":
				if ($("#feat_description_val").val() == "") {
					error = "Description is required";
				}
				if ($("#requirement_container").html() == "") {
					error = "Feat requirements is required";
				}
				break;
			case "morale_trait":
				if ($("#feat_pos_state_val").val() == "") {
					error = "Positive state is required";
				}
				if ($("#feat_neg_state_val").val() == "") {
					error = "Negative state is required";
				}
				break;
			case "physical_trait_pos":
				if ($("#feat_description_val").val() == "") {
					error = "Description is required";
				}
				if ($("#feat_cost_val").val() == "") {
					error = "Cost is required";
				}
				break;
			case "physical_trait_neg":
				if ($("#feat_description_val").val() == "") {
					error = "Description is required";
				}
				if ($("#feat_bonus_val").val() == "") {
					error = "Bonus is required";
				}
				break;
			case "social_background":
			case "social_trait":
			case "compelling_action":
			case "profession":
				if ($("#feat_description_val").val() == "") {
					error = "Description is required";
				}
				break;
		}
		if (error != "") {
			alert(error);
			return;
		}

		// check if we are editing or creating a new feat
		var udpate = $("#feat_id").val() != undefined && $("#feat_id").val() != "";
		var conf = udpate ? confirm("Are you sure you want to update this feat?") : confirm("Are you sure you want to create a new feat?");
		if (conf) {
			// submit form via ajax
			$.ajax({
                url: udpate ? '/scripts/update_feat.php' : '/scripts/feat_submit.php',
                type: 'POST',
                data: $("#new_feat_form").serialize(),
                success:function(result){
                	if (isNaN(result)) {
                		if (result == "update ok") {
                			alert("Feat updated successfully");
                			var feat_id = $("#feat_id").val();
                			var row = getRowForFeat(feat_id);
                			$("#row_"+feat_id).replaceWith(row);
                			// update talents and feats
                			var feat = getNewFeatVals(feat_id);
                			var feat_name = "";
                			for (var i in talents) {
                				if (talents[i]['id'] == feat['id']) {
                					feat_name = talents[i]['name'];
                					talents.splice(i, 1);
                					break;
                				}
                			}
                			talents.push(feat);
                			if (feat['type'] == 'feat') {
	                			for (var i in feats) {
	                				if (feats[i] == feat_name) {
	                					feats.splice(i, 1);
	                					break;
	                				}
	                			}
	                			feats.push(feat['name']);
                			}
                			$("#new_feat_modal").modal("hide");
                			saveCampaignSettings();
                		} else {
                			alert(result);
                		}
                	} else {
                		var feat = getNewFeatVals(result);
	    				talents.push(feat);
						if (feat['type'] == 'feat') {
							feats.push(feat['name']);
							$("#feat_val").autocomplete({
								source: feats
							});
							$("#feat_val2").autocomplete({
								source: feats
							});
						}
						var row = getRowForFeat(result);
						switch($("#feat_type_val").val()) {
							case "feat":
							    var element = $("#feat_table");
				    			break;
							case "physical_trait_pos":
							    var element = $("#physical_trait_pos_table");
								break;
							case "physical_trait_neg":
							    var element = $("#physical_trait_neg_table");
								break;
							case "social_trait":
							    var element = $("#social_trait_table");
								break;
							case "morale_trait":
							    var element = $("#morale_trait_table");
								break;
							case "compelling_action":
							    var element = $("#compelling_action_table");
								break;
							case "profession":
							    var element = $("#profession_table");
								break;
							case "social_background":
							    var element = $("#social_background_table");
								break;
						}
						row.appendTo(element);
                		$("#new_feat_modal").modal("hide");
                		saveCampaignSettings();
                	}
                }
            });
		}
	}

	function getNewFeatVals(feat_id) {
		feat = [];
		feat['id'] = feat_id;
		feat['type'] = $("#feat_type_val").val().includes("physical_trait") ? "physical_trait" : $("#feat_type_val").val();
		feat['name'] = $("#feat_name_val").val();
	    if ($("#feat_type_val").val() != "morale_trait") {
			feat['description'] = $("#feat_description_val").val();
	    } else {
	    	feat['description'] = "Positive State: "+$("#feat_pos_state_val").val()+"; Negative State: "+$("#feat_neg_state_val").val();
	    }
		switch($("#feat_type_val").val()) {
			case "feat":
				// add feat['requirements']
			    var requirements = [];
			    $(".feat-req-val").each(function(){
			    	// create req set array
			    	var req_set = [];
			    	// split on ' OR '
			    	var reqs = $(this).val().split(" OR ");
			    	for (var i in reqs) {
			    		// for each - create dictionary type : value
			    		var req = [];
			    		req[reqs[i].split(":")[0]] = reqs[i].split(":")[1];
			    		req_set.push(req);
			    	}
			    	requirements.push(req_set);
			    });
			    // check for 'character_create_only'
			    if ($("#character_create_only").prop("checked", true)) {
			    	var req_set = [];
			    	var req = [];
			    	req["character_creation"] = true;
			    	req_set.push(req);
			    	requirements.push(req_set);
				}
			    feat['requirements'] = requirements;
    			break;
			case "physical_trait_pos":
				feat['cost'] = $("#feat_cost_val").val();
				break;
			case "physical_trait_neg":
				feat['cost'] = parseInt($("#feat_bonus_val").val()) * -1;
				break;
		}
		return feat;
	}

	function getRowForFeat(feat_id) {
		// add new entry row to table
	    var row = $('<tr />', {
	    	'class': 'table-row',
	    	'id': 'row_'+feat_id
	    });
		var check = $('<td />', {
	    	'class': 'center'
	    }).appendTo(row);
	    // disable check if section is disabled
	    var type = $("#feat_type_val").val();
	    var enabled = type == 'feat' || $("#"+type+"_toggle").prop("checked");
	    $('<input />', {
	    	'type': 'checkbox',
	    	'class': $("#feat_type_val").val().replaceAll("_","-")+"-check",
	    	'value': feat_id,
	    	'name': 'feat_status[]',
	    	"checked": "checked",
	    	"disabled": !enabled
	    }).appendTo(check);
		$('<td />', {
	    	'text': $("#feat_name_val").val()
	    }).appendTo(row);
	    if ($("#feat_type_val").val() != "morale_trait") {
			$('<td />', {
		    	'text': $("#feat_description_val").val()
		    }).appendTo(row);
	    }
		switch($("#feat_type_val").val()) {
			case "feat":
			    var reqs = "";
			    $(".feat-req-val").each(function(){
			    	reqs += "&#8226;"+$(this).val()+"<br>";
			    });
			    if ($("#character_create_only").prop("checked", true)) {
				    reqs += "&#8226;Character Creation Only<br>";
				}
    			$('<td />', {
			    	'html': reqs
			    }).appendTo(row);
    			break;
			case "physical_trait_pos":
    			$('<td />', {
    				'class': 'center',
			    	'text': $("#feat_cost_val").val()
			    }).appendTo(row);
				break;
			case "physical_trait_neg":
    			$('<td />', {
    				'class': 'center',
			    	'text': $("#feat_bonus_val").val()
			    }).appendTo(row);
				break;
			case "morale_trait":
    			$('<td />', {
			    	'text': $("#feat_pos_state_val").val()
			    }).appendTo(row);
    			$('<td />', {
			    	'text': $("#feat_neg_state_val").val()
			    }).appendTo(row);
				break;
		}
		var edit = $('<td />', {
	    }).appendTo(row);
	    var btn = $('<span />', {
	    	'class': 'glyphicon glyphicon-edit'
	    }).appendTo(edit);
	    var name = $("#feat_name_val").val();
	    btn.on("click", function(){
	    	editFeat(name.replaceAll("'",""));
	    });
	    return row;
	}



	