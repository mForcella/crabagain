INSERT INTO feat_or_trait (id, name, description, type, cost) VALUES (1, 'Salty Dog', 'You can Intimidate (See Fear) during combat as a Free Quick Action in conjunction with any other Action. If Intimidate is the only Action you take, it is still a Quick Action. You also gain + 1 Critical Threat Range against anyone you successfully Intimidate. You can never be below -7 Morale, but you can’t be above +7 either.', 'feat', 4);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (1, 1);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (1, 'strength', 2);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (2, 1);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (2, 'fortitude', 2);

INSERT INTO feat_or_trait (id, name, description, type, cost) VALUES (2, 'Acrobat', 'You reduce falling damage by 2 levels for each DL rolled instead of 1. All Climbing and Jumping rolls count as Quick Actions, and you may also attempt Acrobatic Maneuvers inside or outside of combat. If an appropriate Agility check (determined by the GM) is made, you can move your full Move speed as a Quick Action. The GM may rule that certain terrain is required for this, or that some terrain will make it impossible. Failure results in no Movement.', 'feat', 4);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (3, 2);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (3, 'strength', 0);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (4, 2);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (4, 'fortitude', 2);

INSERT INTO feat_or_trait (id, name, description, type, cost) VALUES (3, 'Keen Senses', 'You no longer receive Passive penalties to Awareness, and the penalties for sleeping are reduced to -2. You can never be caught Unaware, unless you are sleeping. If you would be normally caught Unaware, you are considered Surprised. Additionally, you can never suffer an Epic Failure on an Awareness roll. If you would, you may re-roll the Fate Die until it comes up higher than a 1.', 'feat', 4);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (5, 3);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (5, 'awareness', 2);

INSERT INTO feat_or_trait (id, name, description, type, cost) VALUES (4, 'Powers of Observation', 'You have the ability to spot things that most people miss and make connections that others would not. You may make an Awareness roll any time you wish to gather information about a person or location and may use Awareness for Sense Motive rolls. The information provided from these rolls will include information beyond what is physically obvious, including, but not limited to a person’s occupation, their relationship status, where they may have recently been, etc. – so long as this information can be discerned physically in some way.', 'feat', 4);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (6, 4);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (6, 'awareness', 4);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (7, 4);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (7, 'intellect', 3);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (8, 4);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (8, 'feat', 'Keen Senses');

INSERT INTO feat_or_trait (id, name, description, type, cost) VALUES (5, 'Silver Tongue', 'Any time you are making a verbal Allure or Deception roll you can always roll the other if the first roll fails. Additionally, you can never suffer an Epic Failure on either Allure or Deception. If you would, you may re-roll the Fate Die until it comes up higher than a 1.', 'feat', 4);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (9, 5);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (9, 'allure', 2);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (9, 'deception', 2);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (10, 5);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (10, 'allure', 0);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (11, 5);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (11, 'deception', 0);

INSERT INTO feat_or_trait (id, name, description, type, cost) VALUES (6, 'I\'ve Seen Worse', 'Once at the start of each session you can make an Allure or Deception roll, starting at DL 10, modified by your party\'s current Morale Level. If you succeed, you increase your party Morale by +1 for 1 Session, or by +2 for 1 Encounter. Additionally, you gain +1 Morale when this Feat is purchased, and can never be at -10 Morale.', 'feat', 4);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (12, 6);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (12, 'allure', 4);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (13, 6);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (13, 'deception', 2);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (14, 6);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (14, 'feat', 'Silver Tongue');

INSERT INTO feat_or_trait (id, name, description, type, cost) VALUES (7, 'Eclectic Knowledge', 'Every time you fail a roll to see if you know a certain piece of information, you may roll your Percent Dice. You have a 25% chance to know the information anyway. This increases by 5% for every +1 above +2 Intellect, up to a Maximum of 75% at +12 . This will not function for information that the character could not have any conceivable way of knowing, unless an Alignment Bonus is used to declare a story point (this is always at the GMs discretion).', 'feat', 4);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (15, 7);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (15, 'intellect', 2);

INSERT INTO feat_or_trait (id, name, description, type, cost) VALUES (8, 'Jack of All Trades', 'You no longer suffer a -2 penalty for Untrained, Unique Skills, but you still cannot use any of the Trained effects and abilities listed under each Skill. Additionally, whenever you Train in a Unique Skill, you start with a +1 bonus rather than no bonus.', 'feat', 4);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (16, 8);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (16, 'intellect', 4);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (17, 8);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (17, 'intuition', 2);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (18, 8);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (18, 'feat', 'Eclectic Knowledge');

INSERT INTO feat_or_trait (id, name, description, type, cost) VALUES (9, 'Battle Fury', 'You ignore all Wound penalties in Fury, are immune to Fear effects, and if a Save vs. Death would be needed, you ignore it until after the Fury ends. Depending on the injury, you may be unable to act even if Conscious. Additionally, you get +1 Critical Damage, and every time you receive a Wound, you receive a cumulative +1 Critical Threat Range. When the Fury ends, all effects from Wounds resolve and you are Fatigued for a number of hours equal to the rounds spent in Fury.', 'feat', 4);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (19, 9);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (19, 'fortitude', 2);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (19, 'strength', 2);

INSERT INTO feat_or_trait (id, name, description, type, cost) VALUES (10, 'Diehard', 'You reduce Wound penalties by 1, and are Immune to Unconsciousness as a result of Physical Damage. If you would need to Save vs. Death, you can continue to act for 1 additional round before you need to roll and receive +1 Critical Damage and Threat Range against all opponents for that round. This stacks with Battle Fury.', 'feat', 4);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (20, 10);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (20, 'fortitude', 4);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (21, 10);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (21, 'strength', 3);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (22, 10);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (22, 'feat', 'Battle Fury');

INSERT INTO feat_or_trait (id, name, description, type, cost) VALUES (11, 'Defender', 'You increase your base Ready bonus to +2, and may Ready as a Standard Action for +3. If you use a Full Action, you may automatically ignore one Attack against you each Round, unless a Natural 20 was rolled, but they cannot score a Critical.', 'feat', 4);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (23, 11);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (23, 'agility', 2);

INSERT INTO feat_or_trait (id, name, description, type, cost) VALUES (12, 'Dual Weapon Master', 'You no longer suffer the additional -2 Dual-Wield penalty for your Free Quick Attack. Additionally, the first time any opponent misses you in Melee Combat each Round, you gain an immediate Riposte against your opponent at -6. This is reduced to -5 with either Quick and the Dead, Lightning Strike or Keen Senses, -4 with two of them, and -3 with all three. This can not allow you to go over your 8 Quick Action maximum.', 'feat', 4);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (24, 12);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (24, 'agility', 4);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (25, 12);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (25, 'speed', 3);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (26, 12);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (26, 'feat', 'Defender');

INSERT INTO feat_or_trait (id, name, description, type, cost) VALUES (13, 'Dual Pistol Master', 'You reduce your penalty for dual-wielding with pistols by 1. When using firearms with a Fire Rate of at least 4, you are treated as if the weapons have Autofire: 1 (+1 to Hit and Damage on Aim Actions). When using two firearms with Autofire (this includes Fire Rate: 4), you negate your additional penalty for threatening an Arc, but must still use 1 Quick Action per 45 degree arc. You may also threaten a full 360 degrees as 8 Quick Actions, or 1 Full Round Action at a -4 penalty.', 'feat', 4);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (27, 13);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (27, 'precision_', 4);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (28, 13);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (28, 'speed', 3);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (29, 13);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (29, 'feat', 'Take Aim');

INSERT INTO feat_or_trait (id, name, description, type, cost) VALUES (14, 'Quick and the Dead', 'You reduce your Quick Action attack penalty to -1, and as long as your Weapon is Light (3 pounds or less for Medium humanoids), or a Ranged Weapon, you can Draw and Attack as a single Action, but cannot Aim if it is done as a Quick Action. This also applies to bows when Drawing an arrow. If your weapon is already drawn, you can instead apply any Aim action to all attacks taken that round. Additionally, as long as you have no Penalty in Awareness, you can now use your Speed as your primary Initiative Bonus, and Awareness as your Secondary.', 'feat', 4);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (30, 14);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (30, 'speed', 4);

INSERT INTO feat_or_trait (id, name, description, type, cost) VALUES (15, 'Gunslinger', 'You reduce loading time (or cocking time) for all guns by 1 Action step (i.e. Standard to Quick). If loading or cocking would already be a Quick Action, you can now do so as a Free Action in conjunction with any other action. If it is done in conjunction with a Quick Action attack, you cannot Aim on that Attack, and you cannot be Dual Wielding. You also double the Fire Rates on firearms, but you cannot Aim if you go above the normal Fire Rate. Other gun’s Fire Rates are not affected. Additionally, you can make trick shots using any gun with a projectile that can ricochet or reflect off of a specific surface. Whether or not a surface is acceptable is at the GMs discretion. This is always an Aimed Called Shot at +5 DL, and it allows you to completely ignore an enemy\'s Cover even if they have Full Cover. This does not go through enemy Cover, but rather around it.', 'feat', 4);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (31, 15);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (31, 'precision_', 4);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (32, 15);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (32, 'speed', 6);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (33, 15);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (33, 'feat', 'Quick and the Dead');
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (34, 15);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (34, 'feat', 'Take Aim');

INSERT INTO feat_or_trait (id, name, description, type, cost) VALUES (16, 'Lightning Strike', 'As long as no one else has attacked or acted aggressively, the 1st Quick Action Attack you make during an Encounter will always count as a Surprise attack unless your opponent cannot be caught Unaware (such as with Keen Senses) or they also have Lightning Strike. You can now make Called Shots on Reaction Checks.', 'feat', 4);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (35, 16);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (35, 'deception', 3);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (36, 16);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (36, 'speed', 6);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (37, 16);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (37, 'feat', 'Quick and the Dead');

INSERT INTO feat_or_trait (id, name, description, type, cost) VALUES (17, 'Take Aim', 'You increase your base Aim bonus to +2 and may Aim as a Standard Action at +3. If you use a Full Action, you can declare an automatic hit at -1 Damage. You cannot make a Called Shot or score a Critical Hit. This cannot be used with Ranged attacks if the target has more than 1/5 cover, is smaller than Small size, or further than 1 Sector. This cannot be used with Melee attacks if the target has larger than a Small Shield, or Dual-Weapon Master.', 'feat', 4);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (38, 17);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (38, 'precision_', 2);

INSERT INTO feat_or_trait (id, name, description, type, cost) VALUES (18, 'Sneak Attack', 'If your opponent is Surprised or Unware, you score an automatic Critical Hit at an additional +1 Damage (+2 base). This damage is increased by an additional +1 for every +3 you have to hit above +3, up to a maximum of +6 damage before any Called Shot Bonuses.', 'feat', 4);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (39, 18);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (39, 'awareness', 4);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (40, 18);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (40, 'training', 'Stealth');
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (41, 18);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (41, 'feat', 'Take Aim');

INSERT INTO feat_or_trait (id, name, description, type, cost) VALUES (19, 'Brutal Throw', 'As long as you are throwing a weapon of 2 pounds or more, you can add half of your Strength to Damage instead of Precision. Alternately, you can add both, but cannot make Called Shots or declare automatic hits with Take Aim.', 'feat', 4);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (42, 19);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (42, 'strength', 2);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (43, 19);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (43, 'feat', 'Take Aim');

INSERT INTO feat_or_trait (id, name, description, type, cost) VALUES (20, 'Improved Critical Hit', 'You gain +1 to your Critical Threat Range and +1 Critical Damage and force your target to roll against a Knockdown every time you score a Critical as long as they are your Size or Smaller.', 'feat', 4);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (44, 20);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (44, 'precision_', 4);

INSERT INTO feat_or_trait (id, name, description, type, cost) VALUES (21, 'The Pummeling', 'You may deal Lethal or Non-lethal Damage with your Unarmed attacks. Additionally, anytime you make an unarmed Called Shot to the Head (+5 to the DL) and succeed, you automatically Daze your opponent once (regardless of Damage done) as long as they are your Size or smaller. If you score a Critical Hit on such a Called Shot, your Damage is added to their DL to resist Unconsciousness. The Base DL is 5 if it is less than a Wound, increasing by a cumulative +5 per Wound. This has no effect on Creatures Immune to Critical Hits.', 'feat', 4);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (45, 21);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (45, 'strength', 4);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (46, 21);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (46, 'feat', 'Improved Critical Hit');

INSERT INTO feat_or_trait (id, name, description, type, cost) VALUES (22, 'Inspiring Presence', 'As a Standard Action, you can inspire all allies that can hear you, as well as yourself, if you succeed an Allure or Deception DL 12. If successful, you and your allies get +2 Morale against all enemies until one of you is Wounded or the battle ends, and all PCs may utilize any granted Morale re-rolls once each Round.', 'feat', 4);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (47, 22);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (47, 'allure', 2);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (47, 'deception', 2);

INSERT INTO feat_or_trait (id, name, description, type, cost) VALUES (23, 'Bond of Friendship', 'Once per Session you may grant an ally any re-roll you would be allowed, including Motivator Bonuses, but cannot use it yourself. Additionally, if you are aiding an ally on a roll, and they fail their roll, you can roll your Skill instead, but must use the roll even if it is worse.', 'feat', 4);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (48, 23);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (48, 'allure', 4);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (49, 23);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (49, 'intuition', 2);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (50, 23);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (50, 'feat', 'Inspiring Presence');

INSERT INTO feat_or_trait (id, name, description, type, cost) VALUES (24, 'Anatomical Warfare', 'You know how to maximize Damage, and any time you make an Aimed Standard Action Called Shot and succeed, you can score an Automatic Critical Hit, or cause an Automatic Knockdown as long as you deal at least 1 Damage.', 'feat', 4);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (51, 24);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (51, 'intellect', 4);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (51, 'training', 'Medicine');

INSERT INTO feat_or_trait (id, name, description, type, cost) VALUES (25, 'Arterial Bleeding', 'If you make a Called Shot (+5 to the DL), you can cause extensive blood loss. This always deals 1 Damage only, ignoring natural Toughness, and they will then receive 1 Automatic Damage to Resilience at the end of each Round until they are Stabilized. This has no effect on Creatures Immune to Critical Hits.', 'feat', 4);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (52, 25);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (52, 'precision_', 4);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (53, 25);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (53, 'feat', 'Anatomical Warfare');

INSERT INTO feat_or_trait (id, name, description, type, cost) VALUES (26, 'Strength of Will', 'You receive a +2 bonus against all Mind Effecting abilities that require an opposed roll and will always know if someone is effecting you with Mental Magic, but not who. If you win an opposed roll against anyone using Mental Magic against you, you Daze them.', 'feat', 4);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (54, 26);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (54, 'vitality', 2);

INSERT INTO feat_or_trait (id, name, description, type, cost) VALUES (27, 'Gambler\'s Luck', 'Your Fate is a dangerous combination of spectacular and terrible. You may now choose to flip a coin any time you would use a Motivator Bonus for a re-roll, instead of rolling normally. A result of heads gives you an Epic Success, and tails gives you an Epic Failure.', 'feat', 4);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (55, 27);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (55, 'vitality', 2);
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (56, 27);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (56, 'character_creation', 'true');

