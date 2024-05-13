var featList = 
	[

	// *** Standard Talents *** //

	// *** Power Talents *** //
	{
		'name':'Battle Fury',
		'description':'You ignore all Wound penalties in Fury, are immune to Fear effects, and if a Save vs. Death would be needed, you ignore it until after the Fury ends. Depending on the injury, you may be unable to act even if Conscious. Additionally, you get +1 Critical Damage, and every time you receive a Wound, you receive a cumulative +1 Critical Threat Range. When the Fury ends, all effects from Wounds resolve and you are Fatigued for a number of hours equal to the rounds spent in Fury.',
		'requirements':[
			[{'fortitude':2}, {'strength':2}],
		],
		'type':'standard_talent',
		'cost':4
	},
	{
		'name':'Diehard',
		'description':'You reduce Wound penalties by 1, and are Immune to Unconsciousness as a result of Physical Damage. If you would need to Save vs. Death, you can continue to act for 1 additional round before you need to roll and receive +1 Critical Damage and Threat Range against all opponents for that round. This stacks with Battle Fury.',
		'requirements':[
			[{'fortitude':4}],
			[{'strength':3}],
			[{'feat':'Battle Fury'}],
		],
		'type':'standard_talent',
		'cost':4
	},
	{
		'name':'Salty Dog',
		'description':'You can Intimidate (See Fear) during combat as a Free Quick Action in conjunction with any other Action. If Intimidate is the only Action you take, it is still a Quick Action. You also gain + 1 Critical Threat Range against anyone you successfully Intimidate. You can never be below -7 Morale, but you can\'t be above +7 either.',
		'requirements':[
			[{'strength':2}],
			[{'fortitude':2}],
		],
		'type':'standard_talent',
		'cost':4
	},
	{
		'name':'The Pummeling',
		'description':'You may deal Lethal or Non-lethal Damage with your Unarmed attacks. Additionally, anytime you make an unarmed Called Shot to the Head (+5 to the DL) and succeed, you automatically Daze your opponent once (regardless of Damage done) as long as they are your Size or smaller. If you score a Critical Hit on such a Called Shot, your Damage is added to their DL to resist Unconsciousness. The Base DL is 5 if it is less than a Wound, increasing by a cumulative +5 per Wound. This has no effect on Creatures Immune to Critical Hits.',
		'requirements':[
			[{'strength':4}],
			[{'feat':'Improved Critical Hit'}],
		],
		'type':'standard_talent',
		'cost':4
	},

	// *** Dexterity Talents *** //
	{
		'name':'Acrobat',
		'description':'You reduce falling damage by 2 levels for each DL rolled instead of 1. All Climbing and Jumping rolls count as Quick Actions, and you may also attempt Acrobatic Maneuvers inside or outside of combat. If an appropriate Agility check (determined by the GM) is made, you can move your full Move speed as a Quick Action. The GM may rule that certain terrain is required for this, or that some terrain will make it impossible. Failure results in no Movement.',
		'requirements':[
			[{'strength':0}],
			[{'agility':2}],
		],
		'type':'standard_talent',
		'cost':4
	},
	{
		'name':'Defender',
		'description':'You increase your base Ready bonus to +2, and may Ready as a Standard Action for +3. If you use a Full Action, you may automatically ignore one Attack against you each Round, unless a Natural 20 was rolled, but they cannot score a Critical.',
		'requirements':[
			[{'agility':2}],
		],
		'type':'standard_talent',
		'cost':4
	},
	{
		'name':'Dual Weapon Master',
		'description':'You no longer suffer the additional -2 Dual-Wield penalty for your Free Quick Attack. Additionally, the first time any opponent misses you in Melee Combat each Round, you gain an immediate Riposte against your opponent at -6. This is reduced to -5 with either Quick and the Dead, Lightning Strike or Keen Senses, -4 with two of them, and -3 with all three. This can not allow you to go over your 8 Quick Action maximum.',
		'requirements':[
			[{'agility':4}],
			[{'speed':2}],
			[{'feat':'Defender'}],
		],
		'type':'standard_talent',
		'cost':4
	},
	{
		'name':'Lightning Reflexes',
		"description":"You react with uncanny speed and precision. You no longer suffer Reaction Check Penalties and can Aim or make Called Shots on Reaction Checks, but not both. Additionally, you may apply half of your Speed bonus to Dodge, and if you would be caught Unaware, you may still react at -3 as if you were Surprised.",
		'requirements':[
			[{'speed':2}],
		],
		'type':'standard_talent',
		'cost':4
	},
	{
		'name':'Quick and the Dead',
		"description":"When it comes to shooting and stabbing, there are few people faster than you. As long as your Weapon is Light (3 pounds or less for Medium humanoids), or a Ranged Weapon, you can Draw and Attack as a single Action, which counts as a Surprise attack. This is negated only by the Keen Senses Talent, or Quick and the Dead, both of which allow an opposed React Check against you. You also apply any Aim actions to all attacks in a Round. Additionally, as long as you have no penalty in Awareness, you can now use your Speed as your Primary Initiative, and Awareness as your Secondary.",
		'requirements':[
			[{'speed':4}],
			[{'agility':2}],
			[{'feat':'Lightning Reflexes'}],
		],
		'type':'standard_talent',
		'cost':4
	},
	{
		'name':'Martial Arts',
		"description":"You have trained extensively in hand-to-hand combat and can more effectively attack and defend while Unarmed. You no longer suffer Quick Action penalties on Unarmed attacks and treat all Disarm attempts as Quick Actions and may attempt Knockdowns in conjunction with any other attack without spending an additional Quick Action. You may also apply half your Brawl bonus to Dodge. If you also have Dual Weapon Master and are Unarmed or using light weapons you reduce your Free Action Attack and Riposte penalties by 2.",
		'requirements':[
			[{'agility':2}],
			[{'speed':2}],
		],
		'type':'standard_talent',
		'cost':4
	},
	{
		'name':'Relentless Defense',
		"description":"You've always said that the best offense is a good defense. You can no longer be Flanked and may also apply half of your Speed bonus to Defend now. Additionally, you may attempt to catch thrown weapons or arrows fired at you, or within 5 feet of you, by rolling your Brawl. The DL is equal to the attacker's roll +5 and is a minimum of DL 15. If you beat the attack roll, but fail to catch it, you are still not struck. If it was being fired at someone else, it may still strike them if you fail to catch it.",
		'requirements':[
			[{'agility':4}],
			[{'speed':4}],
			[{'feat':'Martial Arts'}],
			[{'feat':'Lightning Reflexes'}],
		],
		'type':'standard_talent',
		'cost':4
	},
	{
		'name':'Gunslinger',
		'description':'You reduce loading time (or cocking time) for all guns by 1 Action step (i.e. Standard to Quick). If loading or cocking would already be a Quick Action, you can now do so as a Free Action in conjunction with any other action. If it is done in conjunction with a Quick Action attack, you cannot Aim on that Attack, and you cannot be Dual Wielding. You also double the Fire Rates on firearms, but you cannot Aim if you go above the normal Fire Rate. Other gun’s Fire Rates are not affected. Additionally, you can make trick shots using any gun with a projectile that can ricochet or reflect off of a specific surface. Whether or not a surface is acceptable is at the GMs discretion. This is always an Aimed Called Shot at +5 DL, and it allows you to completely ignore an enemy\'s Cover even if they have Full Cover. This does not go through enemy Cover, but rather around it.',
		'requirements':[
			[{'speed':6}],
			[{'precision_':4}],
			[{'feat':'Quick and the Dead'}],
			[{'feat':'Take Aim'}],
		],
		'type':'standard_talent',
		'cost':4
	},

	// *** Perception Talents *** //
	{
		'name':'Keen Senses',
		'description':'You no longer receive Passive penalties to Awareness, and the penalties for sleeping are reduced to -2. You can never be caught Unaware, unless you are sleeping. If you would be normally caught Unaware, you are considered Surprised. Additionally, you can never suffer an Epic Failure on an Awareness roll. If you would, you may re-roll the Fate Die until it comes up higher than a 1.',
		'requirements':[
			[{'awareness':2}],
		],
		'type':'standard_talent',
		'cost':4
	},
	{
		'name':'Powers of Observation',
		'description':'You have the ability to spot things that most people miss and make connections that others would not. You may make an Awareness roll any time you wish to gather information about a person or location and may use Awareness for Sense Motive rolls. The information provided from these rolls will include information beyond what is physically obvious, including, but not limited to a person\'s occupation, their relationship status, where they may have recently been, etc. – so long as this information can be discerned physically in some way.',
		'requirements':[
			[{'awareness':4}],
			[{'intellect':3}],
			[{'feat':'Keen Senses'}],
		],
		'type':'standard_talent',
		'cost':4
	},
	{
		'name':'Take Aim',
		'description':'You increase your base Aim bonus to +2 and may Aim as a Standard Action at +3. If you use a Full Action, you can declare an automatic hit at -1 Damage. You cannot make a Called Shot or score a Critical Hit. This cannot be used with Ranged attacks if the target has more than 1/5 cover, is smaller than Small size, or further than 1 Sector. This cannot be used with Melee attacks if the target has larger than a Small Shield, or Dual-Weapon Master.',
		'requirements':[
			[{'precision_':2}],
		],
		'type':'standard_talent',
		'cost':4
	},
	{
		'name':'Dual Pistol Master',
		'description':'You reduce your penalty for dual-wielding with pistols by 1. When using firearms with a Fire Rate of at least 4, you are treated as if the weapons have Autofire: 1 (+1 to Hit and Damage on Aim Actions). When using two firearms with Autofire (this includes Fire Rate: 4), you negate your additional penalty for threatening an Arc, but must still use 1 Quick Action per 45 degree arc. You may also threaten a full 360 degrees as 8 Quick Actions, or 1 Full Round Action at a -4 penalty.',
		'requirements':[
			[{'precision_':4}],
			[{'speed':3}],
			[{'feat':'Take Aim'}],
		],
		'type':'standard_talent',
		'cost':4
	},
	{
		'name':'Improved Critical Hit',
		'description':'You gain +1 to your Critical Threat Range and +1 Critical Damage and force your target to roll against a Knockdown every time you score a Critical as long as they are your Size or Smaller.',
		'requirements':[
			[{'precision_':4}],
		],
		'type':'standard_talent',
		'cost':4
	},
	{
		'name':'Brutal Throw',
		'description':'As long as you are throwing a weapon of 2 pounds or more, you can add half of your Strength to Damage instead of Precision. Alternately, you can add both, but cannot make Called Shots or declare automatic hits with Take Aim.',
		'requirements':[
			[{'strength':2}],
			[{'feat':'Take Aim'}, {'feat':'Improved Critical Hit'}],
		],
		'type':'standard_talent',
		'cost':4
	},
	{
		'name':'Sneak Attack',
		'description':'If your opponent is Surprised or Unware, you score an automatic Critical Hit at an additional +1 Damage (+2 base). This damage is increased by an additional +1 for every +3 you have to hit above +3, up to a maximum of +6 damage before any Called Shot Bonuses.',
		'requirements':[
			[{'awareness':2}],
			[{'training':'Stealth'}],
			[{'feat':'Take Aim'}, {'feat':'Improved Critical Hit'}],
		],
		'type':'standard_talent',
		'cost':4
	},

	// *** Charisma Talents *** //
	{
		'name':'Inspiring Presence',
		'description':'As a Standard Action, you can inspire all allies that can hear you, as well as yourself, if you succeed an Allure or Deception DL 12. If successful, you and your allies get +2 Morale against all enemies until one of you is Wounded or the battle ends, and all PCs may utilize any granted Morale re-rolls once each Round.',
		'requirements':[
			[{'allure':2}, {'deception':2}],
		],
		'type':'standard_talent',
		'cost':4
	},
	{
		'name':'Bond of Friendship',
		'description':'Once per Session you may grant an ally any re-roll you would be allowed, including Motivator Bonuses, but cannot use it yourself. Additionally, if you are aiding an ally on a roll, and they fail their roll, you can roll your Skill instead, but must use the roll even if it is worse.',
		'requirements':[
			[{'allure':4}],
			[{'intuition':2}],
			[{'feat':'Inspiring Presence'}],
		],
		'type':'standard_talent',
		'cost':4
	},
	{
		'name':'Silver Tongue',
		'description':'Any time you are making a verbal Allure or Deception roll you can always roll the other if the first roll fails. Additionally, you can never suffer an Epic Failure on either Allure or Deception. If you would, you may re-roll the Fate Die until it comes up higher than a 1.',
		'requirements':[
			[{'allure':2}],
			[{'deception':2}],
		],
		'type':'standard_talent',
		'cost':4
	},
	{
		'name':'I\'ve Seen Worse',
		'description':'Once at the start of each session you can make an Allure or Deception roll, starting at DL 10, modified by your party\'s current Morale Level. If you succeed, you increase your party Morale by +1 for 1 Session, or by +2 for 1 Encounter. Additionally, you gain +1 Morale when this Feat is purchased, and can never be at -10 Morale.',
		'requirements':[
			[{'allure':4}],
			[{'deception':2}],
			[{'feat':'Silver Tongue'}, {'feat':'Inspiring Presence'}],
		],
		'type':'standard_talent',
		'cost':4
	},

	// *** Intelligence Talents *** //
	{
		'name':'Anatomical Warfare',
		'description':'You know how to maximize Damage, and any time you make an Aimed Standard Action Called Shot and succeed, you can score an Automatic Critical Hit, or cause an Automatic Knockdown as long as you deal at least 1 Damage.',
		'requirements':[
			[{'intellect':4}, {'training':'First Aid'}],
		],
		'type':'standard_talent',
		'cost':4
	},
	{
		'name':'Arterial Bleeding',
		'description':'If you make a Called Shot (+5 to the DL), you can cause extensive blood loss. This always deals 1 Damage only, ignoring natural Toughness, and they will then receive 1 Automatic Damage to Resilience at the end of each Round until they are Stabilized. This has no effect on Creatures Immune to Critical Hits.',
		'requirements':[
			[{'precision_':4}],
			[{'feat':'Anatomical Warfare'}],
		],
		'type':'standard_talent',
		'cost':4
	},
	{
		'name':'Eclectic Knowledge',
		'description':'Every time you fail a roll to see if you know a certain piece of information, you may roll your Percent Dice. You have a 25% chance to know the information anyway. This increases by 5% for every +1 above +2 Intellect, up to a Maximum of 75% at +12 . This will not function for information that the character could not have any conceivable way of knowing, unless an Alignment Bonus is used to declare a story point (this is always at the GMs discretion).',
		'requirements':[
			[{'intellect':2}],
		],
		'type':'standard_talent',
		'cost':4
	},
	{
		'name':'Jack of All Trades',
		'description':'You no longer suffer a -2 penalty for Untrained, Unique Skills, but you still cannot use any of the Trained effects and abilities listed under each Skill. Additionally, whenever you Train in a Unique Skill, you start with a +1 bonus rather than no bonus.',
		'requirements':[
			[{'intellect':4}],
			[{'intuition':2}],
			[{'feat':'Eclectic Knowledge'}],
		],
		'type':'standard_talent',
		'cost':4
	},

	// *** Spirit Talents *** //
	{
		'name':'Strength of Will',
		'description':'You receive a +2 bonus against all Mind Effecting abilities that require an opposed roll and will always know if someone is effecting you with Mental Magic, but not who. If you win an opposed roll against anyone using Mental Magic against you, you Daze them.',
		'requirements':[
			[{'vitality':2}],
		],
		'type':'standard_talent',
		'cost':4
	},
	{
		'name':'Gambler\'s Luck',
		'description':'Your Fate is a dangerous combination of spectacular and terrible. You may now choose to flip a coin any time you would use a Motivator Bonus for a re-roll, instead of rolling normally. A result of heads gives you an Epic Success, and tails gives you an Epic Failure.',
		'requirements':[
			[{'vitality':2}],
			[{'character_creation':true}],
		],
		'type':'standard_talent',
		'cost':4
	},

	// *** Magic Talents *** //
	{
		'name':'Arcane Blood',
		'description':'Powerful Magic runs through your veins. You gain the use of Animas Magic as a Talent. You cannot cast other Magic Schools without Training (4 Skill Points). The first School you learn is your Governing School.',
		'requirements':[
			[{'character_creation':true}],
		],
		'type':'standard_talent',
		'cost':4
	},
	{
		'name':'Divine Magic',
		'description':'The Gods have bestowed powerful Magic upon you. You gain the use of Animas Magic as a Talent. You cannot cast other Magic Schools without Training (4 Skill Points). You can never Train in more than 1 School, and it must be your deities favored School. You must also choose one Vow to follow in service to your God.',
		'requirements':[
			[{'intuition':2}],
			[{'character_creation':true}],
		],
		'type':'standard_talent',
		'cost':4
	},
	{
		'name':'Elemental Master',
		"description":"You may choose 1 type of Elemental Magic when this Talent is taken: Fire, Ice or Electricity. You can cause extreme temperature fluctuations to heat or freeze things, create protective bubbles against hot or cold, and manipulate electricity, creating lightning and force fields or disabling electronic devices. Attacking with this School deals Damage with either fire, cold, or electricity. Flammable objects stay on fire, dealing additional Damage on subsequent rounds. Cold Damage slows a target, Encumbering them. Electrical Damage is Non-lethal, Dazing targets and ignoring any armor that is completely sealed against electricity.",
		'requirements':[
			[{'training':'Ka'}],
		],
		'type':'school_talent',
		'cost':4
	},
	{
		'name':'Metal Master',
		"description":"You can control the properties of metal, as well as move metal objects with your mind by creating magnetic fields. You can magnetize or demagnetize objects, as well as weaken metal objects, animate metal statues and suits of armor or even transmute one metal into another.",
		'requirements':[
			[{'training':'Ka'}],
		],
		'type':'school_talent',
		'cost':4
	},
	{
		'name':'Nature Master',
		"description":"You are one with the beasts and the wild places. You may speak with, and sway the disposition of animals, or communicate with the land. You can use this to speak directly with animals or call upon them for help, and even bond your soul with an animal, seeing through them and speaking to them telepathically. You can alter and enhance the properties of plants, creating potions and poisons. You can purify water and even create sustenance from little more than dirt.",
		'requirements':[
			[{'training':'Avani'}],
		],
		'type':'school_talent',
		'cost':4
	},
	{
		'name':'Elementalist',
		"description":"You have become one with the elements. You may choose one type of Elemental Magic when this Talent is learned: Earth, Water or Air. You can manipulate dirt, mud, and rock, splitting the earth open, raising or shifting stone, animating stone statues or even causing violent earthquakes. You can control the movements of water, raising or lowering water levels, creating waterspouts or waves, fog, rain or snow, and walking on water. You can alter wind patterns and create powerful storms or tornados. When causing earthquakes and gusts of wind, the Agility or Strength DL to remain standing is the same as your roll, as is the Strength check for any non-living structures to remain standing.",
		'requirements':[
			[{'training':'Avani'}],
		],
		'type':'school_talent',
		'cost':4
	},
	{
		'name':'Illusionist',
		"description":"You are a master at manipulating the senses of others. You can make people see, hear, taste, smell and feel whatever you wish, however, you cannot truly control anyone's thoughts and desires.",
		'requirements':[
			[{'training':'Nouse'}],
		],
		'type':'school_talent',
		'cost':4
	},
	{
		'name':'Psychic',
		"description":"You are a master at sensing and reading minds, thoughts, and emotions and projecting your own thoughts and feelings into theirs.",
		'requirements':[
			[{'training':'Nouse'}],
		],
		'type':'school_talent',
		'cost':4
	},
	{
		'name':'Ensi',
		"description":"Your mind is merely an extension of your body and you can move, bend and break objects or people using only your willpower.",
		'requirements':[
			[{'training':'Nouse'}],
		],
		'type':'school_talent',
		'cost':4
	},
	{
		'name':'Seer',
		"description":"Your mind is untethered by time – You can even see into the past and futures of yourself and others, catching brief glimpses of what may come to pass, within seconds or even years. The future is not set, and this will only give you hints about what may come to pass if certain actions are taken.",
		'requirements':[
			[{'training':'Nouse'}],
		],
		'type':'school_talent',
		'cost':4
	},
	{
		'name':'Healer',
		"description":"You know how to manipulate the very fabric of the human body. You can Heal yourself and others, neutralize poison, ignore Wound penalties, and fight disease. The DL for neutralizing Poison and Disease is equal to the DL to resist. For Healing, Soma replaces the Natural Healing roll, and the character may roll immediately for themselves or others without Rest.",
		'requirements':[
			[{'training':'Soma'}],
		],
		'type':'school_talent',
		'cost':4
	},
	{
		'name':'Tormentor',
		"description":"You know how to cause pain, burst blood vessels, and rupture organs with little more than a touch. This Damage is always against a Toughness of 0 plus or minus Scale Modifiers only, and bypasses all Armor as long as skin can be touched.",
		'requirements':[
			[{'training':'Soma'}],
		],
		'type':'school_talent',
		'cost':4
	},
	{
		'name':'Superhuman',
		"description":"You can accomplish incredible, physical feats with your body. Choose 2 Major Physical Attributes when this Talent is taken (i.e. Power & Dexterity, OR Dexterity & Perception). You can greatly increase your strength, speed, and abilities or alter your perception, to see, hear, smell, and taste things beyond normal human perception.",
		'requirements':[
			[{'training':'Soma'}],
		],
		'type':'school_talent',
		'cost':4
	},
	{
		'name':'Magic Adept',
		"description":"You have honed your casting abilities beyond most. You may ready spells as a Quick Action and increase your Standard Action Ready Bonus to +3. Additionally, if you cast a Standard Action spell as a Full Round Action you may also apply your +3 Bonus to your Caster Level for the sake of determining Fatigue. This latter ability only applies to Governing and Companion Magic.",
		'requirements':[
			[{'vitality':2}],
			[{'feat':'Arcane Blood'}],
			[{'training':'Ka'},{'training':'Avani'},{'training':'Nouse'},{'training':'Soma'}],
		],
		'type':'magic_talent',
		'cost':4
	},
	{
		'name':'Master of Magic',
		"description":"There are few as good as you when it comes to slinging spells from your Governing School. You may now cast Magic from your Governing School as a Quick Action once per Encounter, and no longer need to roll to cast Cantrips. Additionally, you no longer suffer Reaction Check penalties for your School. If you also have Lightning Reflexes, you automatically win React Checks when using Magic, as long as your opponent does not also have Lightning Reflexes.",
		'requirements':[
			[{'vitality':6}],
			[{'feat':'Magic Adept'}],
			[{'training':'Ka'},{'training':'Avani'},{'training':'Nouse'},{'training':'Soma'}],
		],
		'type':'magic_talent',
		'cost':4
	},
	{
		'name':'Time Lord',
		"description":"You have gained control over spacetime. You can slow or halt the flow of time, open portals to other places, or travel forward or back in time. DLs begin at 10 for teleporting small objects short distances, slowing time enough to enhance your Defense and Reaction Checks, or traveling backward or forward in time a few seconds. Stopping time completely for a full Round or traveling forward or back in time more than a decade would start at DL 30. This is considered a Talent for purposes of Fatigue (i.e. you are not Fatigued for casting at or below your Caster Level).",
		'requirements':[
			[{'vitality':8}],
			[{'feat':'Master of Magic'}],
			[{'training':'Avani'}],
			[{'training':'Soma'}],
			[{'training':'Ka'}],
			[{'training':'Nouse'}],
		],
		'type':'magic_talent',
		'cost':4
	},
	{
		'name':'Blood Magic',
		"description":"You have honed your suffering into a source of power. Instead of taking a Level of Fatigue, you may instead inflict 1 Damage to your Resilience. You may do this any number of times, but the Damage you suffer must increase by 1 for each additional spell beyond the first. So, after your first spell, you would have to deal 2 Damage, then 3 Damage for the third, and so on.",
		'requirements':[
			[{'vitality':2}],
			[{'feat':'Arcane Blood'}],
		],
		'type':'magic_talent',
		'cost':4
	},
	{
		'name':'Enchanter',
		"description":"You have learned how to manipulate magical energies in order to alter the properties of a non-living object for extended periods. The time an Enchantment lasts starts at 1 Minute and is multiplied by 5 for each Enchantment Level above 1. Permanently Enchanting an object requires a Soul of the recently deceased. This must be from either a person or a magical beast. If a beast is used, their Combat Level is the maximum DL of the effect that can be Enchanted. Multiple souls are cumulative. A humanoid soul is always equal to DL 26, or DL 34 for Magic Users. Permanent Enchanting requires 1 hour and always Fatigues you 1 Level. You must have a Talent in all effects you want to Enchant. The Enchantment Level is also used to determine basic costs when selling or buying Enchanted weapons (See Weapons in Character Creation).",
		'requirements':[
			[{'vitality':4}],
			[{'training':'Ka'},{'training':'Avani'},{'training':'Nouse'},{'training':'Soma'}],
		],
		'type':'magic_talent',
		'cost':4
	},
	{
		'name':'Necromancer',
		"description":"You have learned how to manipulate magical energies in order to communicate with and control the dead. Communication DLs are based on the length of time the body has been dead, starting at DL 6 for someone that died within a few minutes. The DL for animating the dead is equal to the Level of the target during life +10. You may animate multiple beings with a single spell, but Levels are cumulative. This automatically Fatigues you, and you remain Fatigued for as long as you keep any beings animated. They will cease being animated when you end the spell. Permanently animating a body requires a Soul. Permanent animation requires 1 hour, and allows the Undead to operate independently of you, though they will always obey your most recent commands. Undead possess the abilities they had in life, suffer no penalties for Wounds, and are Immune to Critical Hits, disease and poison. Cold Damage deals no Damage but still Encumbers them 1 Level per Wound inflicted. Electric Damage deals no Damage but still Dazes them once per Wound inflicted.",
		'requirements':[
			[{'vitality':4}],
			[{'feat':'Blood Magic'}],
			[{'training':'Ka'},{'training':'Avani'},{'training':'Nouse'},{'training':'Soma'}],
		],
		'type':'magic_talent',
		'cost':4
	},
	{
		'name':'Lord of the Damned',
		"description":"You have achieved an ageless state of near immortality. You no longer age, you require no food, water or sleep; you suffer no penalties for being injured and are immune to critical hits, disease and poison. You can never be instantly slain and can only die from failing a Death Save. There is no limit to how much you can Heal from a Natural Healing or Soma roll. You still suffer Fatigue from Encumbrance and Casting only. Cold Damage deals no Damage but still Encumbers you 1 Level per Wound inflicted. Electric Damage deals no Damage but still Dazes you once per Wound inflicted. No roll is needed to become a Lich, but you must have the Soul of another human Caster and complete a 1-hour ceremony. To do this, you must remove your own Soul and place it in an external receptacle, which if destroyed, instantly slays you. Even if your body dies, your soul will remain intact within your receptacle.",
		'requirements':[
			[{'vitality':8}],
			[{'feat':'Necromancer'}],
			[{'feat':'Master of Magic'}],
			[{'training':'Avani'}],
			[{'training':'Soma'}],
			[{'training':'Ka'}],
			[{'training':'Nouse'}],
		],
		'type':'magic_talent',
		'cost':4
	},
	{
		'name':'Shapeshifter',
		"description":"You have gained incredible power over the expression of your own DNA. When this is learned, you may choose 1 Normal Animal, which you may Shapeshift into. You can change from human to animal and back again once per Session without Fatigue, and each time thereafter you suffer cumulative Fatigue. You can stay in Animal form for as long as you like and take on the Animal's normal Physical Stats. This Talent costs 4 Skill Points plus 1 SP/Level of the Animal. You cannot Shapeshift above Giant Scale but may still choose larger Scale Animals to transform into at Giant Scale. This will alter the Animal's Scale Modifiers appropriately (i.e. reducing Strength and Fortitude and increasing Defense). You may learn this as many times as you like for different Animals.",
		'requirements':[
			[{'vitality':4}],
			[{'training':'Avani'}],
			[{'training':'Soma'}],
		],
		'type':'magic_talent',
		'cost':4
	},

	// *** Social Traits *** //
	{
		'name':'Criminal',
		'description':'You\'ve got a record, as well as some unscrupulous contacts.',
		'type':'social_trait',
		'cost':0
	},
	{
		'name':'Debt',
		'description':'Starting money is tripled, but you have to pay it back some time.',
		'type':'social_trait',
		'cost':0
	},
	{
		'name':'Dependent',
		'description':'You have a staunch ally, but they always need something from you.',
		'type':'social_trait',
		'cost':0
	},
	{
		'name':'Highborn',
		'description':'Some people find you snooty, but you fit in well with high society.',
		'type':'social_trait',
		'cost':0
	},
	{
		'name':'I Know a Guy',
		'description':'You always run into someone you know, whether you want to or not.',
		'type':'social_trait',
		'cost':0
	},
	{
		'name':'Indigent',
		'description':'The slums are home, and it can be hard to fit in anywhere else.',
		'type':'social_trait',
		'cost':0
	},

	// *** Physical Traits *** //
	{
		'name':'Ambidexterity',
		'description':'You negate your -2 off-hand penalty.',
		'type':'physical_trait',
		'cost':2
	},
	{
		'name':'Beautiful',
		'description':'+2 Charisma when sexuality is relevant.',
		'type':'physical_trait',
		'cost':4
	},
	{
		'name':'Double-Jointed',
		'description':'You receive a +2 bonuses for escaping from bonds.',
		'type':'physical_trait',
		'cost':1
	},
	{
		'name':'Focused',
		'description':'You gain a +2 bonus any time you take a 10.',
		'type':'physical_trait',
		'cost':2
	},
	{
		'name':'Giant',
		'description':'+1 Size Category',
		'type':'physical_trait',
		'cost':2
	},
	{
		'name':'Hardened',
		'description':'+2 Vitality vs. Fear.',
		'type':'physical_trait',
		'cost':2
	},
	{
		'name':'Hardy Metabolism',
		'description':'+2 Fortitude vs. Poisons, Drugs and Alcohol.',
		'type':'physical_trait',
		'cost':2
	},
	{
		'name':'High Education',
		'description':'+2 in one Academia Focus area',
		'type':'physical_trait',
		'cost':2
	},
	{
		'name':'Night Vision',
		'description':'Lowers Darkness penalties by 2.',
		'type':'physical_trait',
		'cost':2
	},
	{
		'name':'Photographic Memory',
		'description':'If you saw it, you can remember it.',
		'type':'physical_trait',
		'cost':4
	},
	{
		'name':'Quick Healer',
		'description':'You can roll Natural Healing in half the normal time.',
		'type':'physical_trait',
		'cost':4
	},
	{
		'name':'Will to Live',
		'description':'You can always re-roll a Death Save once.',
		'type':'physical_trait',
		'cost':2
	},
	{
		'name':'All Thumbs',
		'description':'A mishap occurs on a Fate 1 or 2 when using technology.',
		'type':'physical_trait',
		'cost':-2
	},
	{
		'name':'Bad Luck',
		'description':'A mishap occurs on a Fate 1 or 2, and no Benefit on a 6.',
		'type':'physical_trait',
		'cost':-8
	},
	{
		'name':'Clumsy',
		'description':'A mishap occurs on a Fate 1 or 2 on movement-related rolls.',
		'type':'physical_trait',
		'cost':-4
	},
	{
		'name':'Dwarf',
		'description':'-1 Size Category',
		'type':'physical_trait',
		'cost':-2
	},
	{
		'name':'Incompetent',
		'description':'You receive another -2 bonus to Untrained Unique Skills.',
		'type':'physical_trait',
		'cost':-2
	},
	{
		'name':'Insomniac',
		'description':'You suffer a -2 to penalty to all rolls related to resting.',
		'type':'physical_trait',
		'cost':-1
	},
	{
		'name':'Low Pain Tolerance',
		'description':'You receive another -1 Wound penalty, starting at Bloodied.',
		'type':'physical_trait',
		'cost':-3
	},
	{
		'name':'Skittish',
		'description':'-2 Vitality vs. Fear, additional when Surprised.',
		'type':'physical_trait',
		'cost':-3
	},
	{
		'name':'Slow Healer',
		'description':'You roll Natural Healing after twice the normal time.',
		'type':'physical_trait',
		'cost':-3
	},
	{
		'name':'Tongue-Tied',
		'description':'A mishap occurs on a Fate 1 or 2 on speech-related rolls.',
		'type':'physical_trait',
		'cost':-4
	},
	{
		'name':'Unmistakable',
		'description':'Without a proper disguise, you’re easy to spot in a crowd.',
		'type':'physical_trait',
		'cost':-1
	},
	{
		'name':'Weak Metabolism',
		'description':'-2 Fortitude vs. Poisons, Drugs and Alcohol.',
		'type':'physical_trait',
		'cost':-2
	},

	// *** Morale Traits *** //
	{
		'name':'Celebrity',
		'description':'Positive State:\nYou gained 2 Renown last Session.\nNegative State:\nYou didn’t gain any Renown last Session.',
		'type':'morale_trait',
		'cost':0
	},
	{
		'name':'Extrovert',
		'description':'Positive State:\nA new NPC really likes you.\nNegative State:\nYou didn’t hang out with any NPC friends.',
		'type':'morale_trait',
		'cost':0
	},
	{
		'name':'Fancypants',
		'description':'Positive State:\nYou had a new luxury experience this Session.\nNegative State:\nNo luxury experience last Session.',
		'type':'morale_trait',
	},
	{
		'name':'Gamer',
		'description':'Positive State:\nYou won some kind of game this Session.\nNegative State:\nYou lost a game last Session.',
		'type':'morale_trait',
		'cost':0
	},
	{
		'name':'Glutton',
		'description':'Positive State:\nAll of your meals were excessive.\nNegative State:\nNone of your meals were decent last Session.',
		'type':'morale_trait',
		'cost':0
	},
	{
		'name':'Merrymaker',
		'description':'Positive State:\nYou got drunk this Session.\nNegative State:\nYou didn’t have any drinks last Session.',
		'type':'morale_trait',
		'cost':0
	},
	{
		'name':'Miracle',
		'description':'Positive State:\nYou used no Alignment Bonuses last Session.\nNegative State:\nYou used all Alignment Bonuses last Session.',
		'type':'morale_trait',
		'cost':0
	},
	{
		'name':'Pennypincher',
		'description':'Positive State:\nYou have more than 800 credits/8 Gold/$40.\nNegative State:\nYou have less than 400 credits/4 Gold/$20.',
		'type':'morale_trait',
		'cost':0
	},
	{
		'name':'Philanderer',
		'description':'Positive State:\nYou had 1 new sexual encounter this Session.\nNegative State:\nNo new encounters last Session.',
		'type':'morale_trait',
		'cost':0
	},
	{
		'name':'Rover',
		'description':'Positive State:\nYou went to a new planet/region this Session.\nNegative State:\nYou stayed in one place last Session.',
		'type':'morale_trait',
		'cost':0
	},
	{
		'name':'Spendthrift',
		'description':'Positive State:\nYou have less than 20 credits/4 Silver/$1.\nNegative State:\nYou have more than 200 credits/2 Gold/$10.',
		'type':'morale_trait',
		'cost':0
	},
	{
		'name':'Wunderkind',
		'description':'Positive State:\nYou succeeded all rolls last Session.\nNegative State:\nYou failed 2 or more rolls last Session.',
		'type':'morale_trait',
		'cost':0
	},

	// *** Professions *** //
	{
		'name':'Archaeologist',
		'description':'You have studied and taught ancient civilizations and cultures of South America at world renowned Universities and are one of the best in your field. You can identify artifacts and structures of all known societies in South America and can make educated guesses about the purpose and nature of unknown ones. You can translate ancient writing from all known past civilizations in South American and may attempt translation of unknown ones. Archaeology must be learned under Intellect.',
		'type':'profession',
		'cost':0
	},
	{
		'name':'Botanist/Herbalist',
		'description':'Whether through academia or indigenous knowledge, you know the plants of the Amazon better than most. You can identify any Amazonian plants with a DL 10 or less, and as long as you are in or near the Amazon, you can find any plant from the Drug/Poison Ingredients Lists once per day of travel without making a roll. Additionally, even if fail a roll or you are outside of the Amazon, a DL 15 will identify likely properties of any plants you encounter. Herbalism can be learned under Intuition or Intellect.',
		'type':'profession',
		'cost':0
	},
	{
		'name':'Cartographer',
		'description':'The Cartographer is a master of maps and geography. You can create detailed maps of any area you have travelled through with impressive accuracy (DL 14). Additionally, if you have a map of an unexplored area, you can never become lost and can also roll Cartography without a map to attempt to find your direction (DL 10). Cartography can be learned under Awareness or Innovation.',
		'type':'profession',
		'cost':0
	},
	{
		'name':'Ex-Military',
		'description':'You were a career soldier for many years, until that one mission went terribly wrong. Now you make your way as a hired gun, and you know guns well. You ignore Misfire on all weapons and reduce Accuracy penalties by 1. You also begin the game Trained in Demolitions and Tactics.',
		'type':'profession',
		'cost':0
	},
	{
		'name':'Philanthropist',
		'description':'Somehow you got filthy rich, and you\'ve spent most of your money funding the pursuit of the Lost City. Because of your deep pockets and fame, you have bought and befriended people everywhere. Any time you visit a new town or city you may declare a relationship with an inhabitant. This can be someone of your creation, with GM approval, or you can declare a relationship to any pre-existing NPC. The nature and quality of the relationship is still at the GM’s discretion. Even after you have done this, you can always name-drop with anyone, which will provide anywhere from a +4 to -4 to any Charisma roll. The bonus/penalty will be unknown to you until after the roll. Additionally, as long as you have access to a city with a bank, you can withdraw $100/day.',
		'type':'profession',
		'cost':0
	},
	{
		'name':'Street Rat',
		'description':'You\'ve never had a family to look out for you, and the roof over your head was the sky more often than not. But it made you resilient and creative, and you know how to get along with the wrong kind of people. Any time you are in a town or city you can make an Intuition or Awareness roll to find black market or illegal activities, and these people will never be outright hostile toward you as long as you are alone. You also begin the game Trained in Stealth and either Security OR Sleight of Hand.',
		'type':'profession',
		'cost':0
	},

	// *** Social Backgrounds *** //
	{
		'name':'Nobility',
		'description':'You had a posh upbringing that some might consider pampered and indulgent. You\'re well-read, well-educated and well-travelled. You gain 2 Bonus Skill Points to place in Diplomacy, Academia or Language Focuses. You are Literate and gain 2d4 additional starting funds.',
		'type':'social_background',
		'cost':0
	},
	{
		'name':'Courtier',
		'description':'You weren\'t rich, but you lived a wealthy life on the coattails of others, mainly by kissing the right asses and knowing how to manipulate your superiors. You gain 3 Bonus Skill Points to place in Diplomacy, Seduce or Sense Motive Focuses. You are Literate and gain 1d4 additional starting funds.',
		'type':'social_background',
		'cost':0
	},
	{
		'name':'Merchant',
		'description':'You came from a Merchant family, which meant you worked hard and often to keep yourselves above the peasant masses. You understand people and business well and travelled the world often buying, selling and trading. You gain 3 Bonus Skill Points to place in Appraise, Barter or Languages Focuses. You are Literate and gain 1d4 additional starting funds.',
		'type':'social_background',
		'cost':0
	},
	{
		'name':'Scholar',
		'description':'You trained extensively with the most educated minds of your city, tirelessly transcribing texts and reading more books than even the wealthiest minds have consumed. You gain 4 Bonus Skill Points to place in Academia, Culture, Languages or Religion. You are Literate.',
		'type':'social_background',
		'cost':0
	},
	{
		'name':'Soldier',
		'description':'You spent some time enlisted in the military, defending your home. You\'ve seen your share of battle, but you\'re ready for new adventures and some real freedom. You gain a +1 Bonus in Shoot and Attack. You must choose a specific Weapon Type for each Focus and you may apply the Bonus after initial Skill Points have been spent. You are Illiterate.',
		'type':'social_background',
		'cost':0
	},
	{
		'name':'Craftsman',
		'description':'You made stuff, and you made it well enough to make a decent living, but never well enough to really get noticed. You gain a +2 Bonus in a single Craft Item Focus and you may apply the Bonus after initial Skill Points have been spent. You are Illiterate.',
		'type':'social_background',
		'cost':0
	},
	{
		'name':'Peasant',
		'description':'You broke your body to keep the rich fat and barely enough food in your own belly. You\'ve survived the hardest of times and come out strong for it. You gain 5 Bonus Skill Points to place in Lift, Resist Disease or Craft Item Focuses. You are Illiterate and lose 1d4 starting funds.',
		'type':'social_background',
		'cost':0
	},
	{
		'name':'Street Urchin',
		'description':'You never had a real home and survived off of wit, luck and a lot of illegal activity. You\'re a survivor and as resourceful as they come. You gain 2 Bonus Skill Points to place in Disguise, Search, Listen or Run Focuses and gain the Security, Stealth or Sleight of Hand Unique Skill for free. You are Illiterate and lose 2d4 starting funds.',
		'type':'social_background',
		'cost':0
	},

	// *** Compelling Actions *** //
	{
		'name':'Addicted',
		'description':'You have a vice that you can\'t seem to escape. You may choose one vice when this taken (i.e. alcohol, tobacco, gambling, sex, etc.), and may take this multiple times for multiple vices at the GM’s discretion. Any time this vice is available to you, you must succeed a minimum Vitality roll DL 10 to resist. If you go a session without your vice, you receive a cumulative -1 penalty to Morale, up to -4 maximum.',
		'type':'compelling_action',
		'cost':-2
	},
	{
		'name':'Ascetic',
		'description':'You view the accumulation of wealth and enjoyment of luxuries as bad for the soul. As such, you will avoid these things, and must succeed a minimum Vitality roll DL 10 in order to earn money you don\'t need and enjoy activities or items that could be considered a distraction from suffering, indulgent or unnecessary. You will also never shy away from the harder or more dangerous path if it has the potential to produce the best out come.',
		'type':'compelling_action',
		'cost':-2
	},
	{
		'name':'Curious',
		'description':'You have an extremely difficult time resisting unknown, interesting and new situations. If you ever wish to avoid such a situation a minimum Vitality roll DL 10 is required.',
		'type':'compelling_action',
		'cost':-2
	},
	{
		'name':'For the Clan',
		'description':'If the honor or prowess of you or your people is ever questioned or insulted, you are always obligated to defend your honor. This may not always lead to a fight, but if the offender is already considered an enemy, it often will. Resisting this desire is always a minimum Vitality roll DL 10, unless engaging would somehow further sully your honor or your people\'s.',
		'type':'compelling_action',
		'cost':-2
	},
	{
		'name':'Rebel Yell',
		'description':'You don\'t like being told what to do, how to act, or what they can and cannot say. When this happens, you will often reject the command or suggestion simply for the sake of maintaining your sense of autonomy. To resist this desire requires a minimum Vitality roll DL 10 unless it would put your life at risk.',
		'type':'compelling_action',
		'cost':-2
	},
	{
		'name':'Honest',
		'description':'You value truth and honesty, and will rarely lie, especially to a friend or ally. If you ever wish to lie, except in clearly dire situations, you must first succeed a minimum Vitality roll DL 10.',
		'type':'compelling_action',
		'cost':-2
	},
	{
		'name':'Honor-bound',
		'description':'If you ever make a promise, you will do your best to never break it. This is doubly so if the recipient is close friend or ally, or someone who has helped you in some way. If you wish to go back on your word, a minimum Vitality roll DL 10 is necessary.',
		'type':'compelling_action',
		'cost':-2
	},
	{
		'name':'Pacifist',
		'description':'You value peace and harmony above all else and will do all you can to avoid violent conflict, or even aggressive conversation. To resist this desire requires a minimum Vitality roll DL 10 unless your life, or someone close to you, would be in danger.',
		'type':'compelling_action',
		'cost':-2
	},
	{
		'name':'Protector of The Wild',
		'description':'You view yourself as a guardian of the wild places. If you ever witness abuse, misuse or wanton destruction of wild places or wild animals, you are obliged to intercede. If you wish to resist this compulsion, a minimum Vitality roll DL 10 is necessary.',
		'type':'compelling_action',
		'cost':-2
	},
	{
		'name':'Show Me the Money',
		'description':'Anytime you have an opportunity to barter, gamble or otherwise make money without significant risk, you must succeed a Vitality roll of a minimum of DL 10 to resist the urge.',
		'type':'compelling_action',
		'cost':-2
	},
	{
		'name':'Solemn Oath',
		'description':'You have made a promise to an important individual or organization or have sworn vengeance against a person or organization. The oath must be agreed upon with the GM when this is taken and may take multiple Oath\'s at the GM’s discretion. You must pursue this Oath whenever possible, and anything that could be a distraction from fulfilling your Oath, or is in conflict with your Oath, cannot be undertaken without a minimum Vitality roll of DL 10. This is Compelling Action is removed when the Oath is fulfilled.',
		'type':'compelling_action',
		'cost':-2
	},
	{
		'name':'Warrior\'s Code',
		'description':'You value honor in battle above most things. As a result, you will rarely attack an Unaware or Unarmed opponent, and will attempt to stop or intercede in a fight you see as dishonorable. If you wish to resist this compulsion, a minimum Vitality roll DL 10 is necessary.',
		'type':'compelling_action',
		'cost':-2
	},
];