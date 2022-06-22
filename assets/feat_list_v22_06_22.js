var feat_list = [
	{
		'name':'Salty Dog',
		'description':'You can Intimidate (See Fear) during combat as a Free Quick Action in conjunction with any other Action. '+
		'If Intimidate is the only Action you take, it is still a Quick Action. You also gain + 1 Critical Threat Range against '+
		'anyone you successfully Intimidate. You can never be below -7 Morale, but you can’t be above +7 either.',
		'requirements':[
			[{'strength':2}],
			[{'fortitude':2}],
		]
	},
	{
		'name':'Acrobat',
		'description':'You reduce falling damage by 2 levels for each DL rolled instead of 1. All Climbing and Jumping rolls count '+
		'as Quick Actions, and you may also attempt Acrobatic Maneuvers inside or outside of combat. If an appropriate Agility check '+
		'(determined by the GM) is made, you can move your full Move speed as a Quick Action. The GM may rule that certain terrain is '+
		'required for this, or that some terrain will make it impossible. Failure results in no Movement.',
		'requirements':[
			[{'strength':0}],
			[{'agility':2}],
		]
	},
	{
		'name':'Keen Senses',
		'description':'You no longer receive Passive penalties to Awareness, and the penalties for sleeping are reduced to -2. You '+
		'can never be caught Unaware, unless you are sleeping. If you would be normally caught Unaware, you are considered Surprised. '+
		'Additionally, you can never suffer an Epic Failure on an Awareness roll. If you would, you may re-roll the Fate Die until it '+
		'comes up higher than a 1.',
		'requirements':[
			[{'awareness':2}],
		]
	},
	{
		'name':'Powers of Observation',
		'description':'You have the ability to spot things that most people miss and make connections that others would not. You may '+
		'make an Awareness roll any time you wish to gather information about a person or location and may use Awareness for Sense '+
		'Motive rolls. The information provided from these rolls will include information beyond what is physically obvious, including, '+
		'but not limited to a person’s occupation, their relationship status, where they may have recently been, etc. – so long as '+
		'this information can be discerned physically in some way.',
		'requirements':[
			[{'awareness':4}],
			[{'intellect':3}],
			[{'feat':'Keen Senses'}],
		]
	},
	{
		'name':'Silver Tongue',
		'description':'Any time you are making a verbal Allure or Deception roll you can always roll the other if the first roll fails. '+
		'Additionally, you can never suffer an Epic Failure on either Allure or Deception. If you would, you may re-roll the Fate Die '+
		'until it comes up higher than a 1.',
		'requirements':[
			[{'allure':2}, {'deception':2}],
			[{'allure':0}],
			[{'deception':0}],
		]
	},
	{
		'name':'I\'ve Seen Worse',
		'description':'Once at the start of each session you can make an Allure or Deception roll, starting at DL 10, modified by '+
		'your party\'s current Morale Level. If you succeed, you increase your party Morale by +1 for 1 Session, or by +2 for 1 Encounter. '+
		'Additionally, you gain +1 Morale when this Feat is purchased, and can never be at -10 Morale.',
		'requirements':[
			[{'allure':4}],
			[{'deception':2}],
			[{'feat':'Silver Tongue'}],
		]
	},
	{
		'name':'Eclectic Knowledge',
		'description':'Every time you fail a roll to see if you know a certain piece of information, you may roll your Percent Dice. '+
		'You have a 25% chance to know the information anyway. This increases by 5% for every +1 above +2 Intellect, up to a Maximum of '+
		'75% at +12 . This will not function for information that the character could not have any conceivable way of knowing, unless '+
		'an Alignment Bonus is used to declare a story point (this is always at the GMs discretion).',
		'requirements':[
			[{'intellect':2}],
		]
	},
	{
		'name':'Jack of All Trades',
		'description':'You no longer suffer a -2 penalty for Untrained, Unique Skills, but you still cannot use any of the Trained '+
		'effects and abilities listed under each Skill. Additionally, whenever you Train in a Unique Skill, you start with a +1 bonus '+
		'rather than no bonus.',
		'requirements':[
			[{'intellect':4}],
			[{'intuition':2}],
			[{'feat':'Eclectic Knowledge'}],
		]
	},
	{
		'name':'Battle Fury',
		'description':'You ignore all Wound penalties in Fury, are immune to Fear effects, and if a Save vs. Death would be needed, '+
		'you ignore it until after the Fury ends. Depending on the injury, you may be unable to act even if Conscious. Additionally, '+
		'you get +1 Critical Damage, and every time you receive a Wound, you receive a cumulative +1 Critical Threat Range. When the '+
		'Fury ends, all effects from Wounds resolve and you are Fatigued for a number of hours equal to the rounds spent in Fury.',
		'requirements':[
			[{'fortitude':2}, {'strength':2}],
		]
	},
	{
		'name':'Diehard',
		'description':'You reduce Wound penalties by 1, and are Immune to Unconsciousness as a result of Physical Damage. If you '+
		'would need to Save vs. Death, you can continue to act for 1 additional round before you need to roll and receive +1 '+
		'Critical Damage and Threat Range against all opponents for that round. This stacks with Battle Fury.',
		'requirements':[
			[{'fortitude':4}],
			[{'strength':3}],
			[{'feat':'Battle Fury'}],
		]
	},
	{
		'name':'Defender',
		'description':'You increase your base Ready bonus to +2, and may Ready as a Standard Action for +3. If you use a Full Action, '+
		'you may automatically ignore one Attack against you each Round, unless a Natural 20 was rolled, but they cannot score a '+
		'Critical.',
		'requirements':[
			[{'agility':2}],
		]
	},
	{
		'name':'Dual Weapon Master',
		'description':'You no longer suffer the additional -2 Dual-Wield penalty for your Free Quick Attack. Additionally, the '+
		'first time any opponent misses you in Melee Combat each Round, you gain an immediate Riposte against your opponent at -6. '+
		'This is reduced to -5 with either Quick and the Dead, Lightning Strike or Keen Senses, -4 with two of them, and -3 with '+
		'all three. This can not allow you to go over your 8 Quick Action maximum.',
		'requirements':[
			[{'agility':4}],
			[{'speed':3}],
			[{'feat':'Defender'}],
		]
	},
	{
		'name':'Dual Pistol Master',
		'description':'You reduce your penalty for dual-wielding with pistols by 1. When using firearms with a Fire Rate of at '+
		'least 4, you are treated as if the weapons have Autofire: 1 (+1 to Hit and Damage on Aim Actions). When using two firearms '+
		'with Autofire (this includes Fire Rate: 4), you negate your additional penalty for threatening an Arc, but must still use '+
		'1 Quick Action per 45 degree arc. You may also threaten a full 360 degrees as 8 Quick Actions, or 1 Full Round Action at '+
		'a -4 penalty.',
		'requirements':[
			[{'precision_':4}],
			[{'speed':3}],
			[{'feat':'Take Aim'}],
		]
	},
	{
		'name':'Quick and the Dead',
		'description':'You reduce your Quick Action attack penalty to -1, and as long as your Weapon is Light (3 pounds or less '+
		'for Medium humanoids), or a Ranged Weapon, you can Draw and Attack as a single Action, but cannot Aim if it is done as a '+
		'Quick Action. This also applies to bows when Drawing an arrow. If your weapon is already drawn, you can instead apply any '+
		'Aim action to all attacks taken that round. Additionally, as long as you have no Penalty in Awareness, you can now use '+
		'your Speed as your primary Initiative Bonus, and Awareness as your Secondary.',
		'requirements':[
			[{'speed':4}],
		]
	},
	{
		'name':'Gunslinger',
		'description':'You reduce loading time (or cocking time) for all guns by 1 Action step (i.e. Standard to Quick). If '+
		'loading or cocking would already be a Quick Action, you can now do so as a Free Action in conjunction with any other '+
		'action. If it is done in conjunction with a Quick Action attack, you cannot Aim on that Attack, and you cannot be '+
		'Dual Wielding. You also double the Fire Rates on firearms, but you cannot Aim if you go above the normal Fire Rate. '+
		'Other gun’s Fire Rates are not affected. Additionally, you can make trick shots using any gun with a projectile that '+
		'can ricochet or reflect off of a specific surface. Whether or not a surface is acceptable is at the GMs discretion. '+
		'This is always an Aimed Called Shot at +5 DL, and it allows you to completely ignore an enemy\'s Cover even if they '+
		'have Full Cover. This does not go through enemy Cover, but rather around it.',
		'requirements':[
			[{'speed':6}],
			[{'precision_':4}],
			[{'feat':'Quick and the Dead'}],
			[{'feat':'Take Aim'}],
		]
	},
	{
		'name':'Lightning Strike',
		'description':'As long as no one else has attacked or acted aggressively, the 1st Quick Action Attack you make during '+
		'an Encounter will always count as a Surprise attack unless your opponent cannot be caught Unaware (such as with Keen Senses) '+
		'or they also have Lightning Strike. You can now make Called Shots on Reaction Checks.',
		'requirements':[
			[{'speed':6}],
			[{'deception':3}],
			[{'feat':'Quick and the Dead'}],
		]
	},
	{
		'name':'Take Aim',
		'description':'You increase your base Aim bonus to +2 and may Aim as a Standard Action at +3. If you use a Full Action, '+
		'you can declare an automatic hit at -1 Damage. You cannot make a Called Shot or score a Critical Hit. This cannot be used '+
		'with Ranged attacks if the target has more than 1/5 cover, is smaller than Small size, or further than 1 Sector. This '+
		'cannot be used with Melee attacks if the target has larger than a Small Shield, or Dual-Weapon Master.',
		'requirements':[
			[{'precision_':2}],
		]
	},
	{
		'name':'Sneak Attack',
		'description':'If your opponent is Surprised or Unware, you score an automatic Critical Hit at an additional +1 Damage '+
		'(+2 base). This damage is increased by an additional +1 for every +3 you have to hit above +3, up to a maximum of +6 '+
		'damage before any Called Shot Bonuses.',
		'requirements':[
			[{'awareness':4}],
			[{'training':'Stealth'}],
			[{'feat':'Take Aim'}],
		]
	},
	{
		'name':'Brutal Throw',
		'description':'As long as you are throwing a weapon of 2 pounds or more, you can add half of your Strength to Damage '+
		'instead of Precision. Alternately, you can add both, but cannot make Called Shots or declare automatic hits with Take Aim.',
		'requirements':[
			[{'strength':2}],
			[{'feat':'Take Aim'}],
		]
	},
	{
		'name':'Improved Critical Hit',
		'description':'You gain +1 to your Critical Threat Range and +1 Critical Damage and force your target to roll against a '+
		'Knockdown every time you score a Critical as long as they are your Size or Smaller.',
		'requirements':[
			[{'precision_':4}],
		]
	},
	{
		'name':'The Pummeling',
		'description':'You may deal Lethal or Non-lethal Damage with your Unarmed attacks. Additionally, anytime you make an '+
		'unarmed Called Shot to the Head (+5 to the DL) and succeed, you automatically Daze your opponent once (regardless of '+
		'Damage done) as long as they are your Size or smaller. If you score a Critical Hit on such a Called Shot, your Damage '+
		'is added to their DL to resist Unconsciousness. The Base DL is 5 if it is less than a Wound, increasing by a cumulative +5 '+
		'per Wound. This has no effect on Creatures Immune to Critical Hits.',
		'requirements':[
			[{'strength':4}],
			[{'feat':'Improved Critical Hit'}],
		]
	},
	{
		'name':'Inspiring Presence',
		'description':'As a Standard Action, you can inspire all allies that can hear you, as well as yourself, if you succeed an '+
		'Allure or Deception DL 12. If successful, you and your allies get +2 Morale against all enemies until one of you is Wounded '+
		'or the battle ends, and all PCs may utilize any granted Morale re-rolls once each Round.',
		'requirements':[
			[{'allure':2}, {'deception':2}],
		]
	},
	{
		'name':'Bond of Friendship',
		'description':'Once per Session you may grant an ally any re-roll you would be allowed, including Motivator Bonuses, '+
		'but cannot use it yourself. Additionally, if you are aiding an ally on a roll, and they fail their roll, you can roll '+
		'your Skill instead, but must use the roll even if it is worse.',
		'requirements':[
			[{'allure':4}],
			[{'intuition':2}],
			[{'feat':'Inspiring Presence'}],
		]
	},
	{
		'name':'Anatomical Warfare',
		'description':'You know how to maximize Damage, and any time you make an Aimed Standard Action Called Shot and succeed, '+
		'you can score an Automatic Critical Hit, or cause an Automatic Knockdown as long as you deal at least 1 Damage.',
		'requirements':[
			[{'intellect':4}, {'training':'Medicine'}],
		]
	},
	{
		'name':'Arterial Bleeding',
		'description':'If you make a Called Shot (+5 to the DL), you can cause extensive blood loss. This always deals 1 Damage '+
		'only, ignoring natural Toughness, and they will then receive 1 Automatic Damage to Resilience at the end of each Round '+
		'until they are Stabilized. This has no effect on Creatures Immune to Critical Hits.',
		'requirements':[
			[{'precision_':4}],
			[{'feat':'Anatomical Warfare'}],
		]
	},
	{
		'name':'Strength of Will',
		'description':'You receive a +2 bonus against all Mind Effecting abilities that require an opposed roll and will always '+
		'know if someone is effecting you with Mental Magic, but not who. If you win an opposed roll against anyone using Mental '+
		'Magic against you, you Daze them.',
		'requirements':[
			[{'vitality':2}],
		]
	},
	{
		'name':'Gambler\'s Luck',
		'description':'Your Fate is a dangerous combination of spectacular and terrible. You may now choose to flip a coin any time '+
		'you would use a Motivator Bonus for a re-roll, instead of rolling normally. A result of heads gives you an Epic Success, and '+
		'tails gives you an Epic Failure.',
		'requirements':[
			[{'vitality':2}],
			[{'character_creation':true}],
		]
	},
	// {
	// 	'name':'Cartographer',
	// 	'description':'',
	// 	'requirements':[
	// 		[{'character_creation':true}, {'profession':true}],
	// 	]
	// },
	// {
	// 	'name':'Archaeologist',
	// 	'description':'',
	// 	'requirements':[
	// 		[{'character_creation':true}, {'profession':true}],
	// 	]
	// },
	// {
	// 	'name':'Botanist/Herbalist',
	// 	'description':'',
	// 	'requirements':[
	// 		[{'character_creation':true}, {'profession':true}],
	// 	]
	// },
	// {
	// 	'name':'Philanthropist',
	// 	'description':'',
	// 	'requirements':[
	// 		[{'character_creation':true}, {'profession':true}],
	// 	]
	// },
	// {
	// 	'name':'Ex-Military',
	// 	'description':'',
	// 	'requirements':[
	// 		[{'character_creation':true}, {'profession':true}],
	// 	]
	// },
	// {
	// 	'name':'Street Rat',
	// 	'description':'',
	// 	'requirements':[
	// 		[{'character_creation':true}, {'profession':true}],
	// 	]
	// },
	// {
	// 	'name':'Addicted',
	// 	'description':'',
	// 	'requirements':[
	// 		[{'character_creation':true}, {'compelling_action':true}],
	// 	]
	// },
	// {
	// 	'name':'Pacifist',
	// 	'description':'',
	// 	'requirements':[
	// 		[{'character_creation':true}, {'compelling_action':true}],
	// 	]
	// },
	// {
	// 	'name':'Honest',
	// 	'description':'',
	// 	'requirements':[
	// 		[{'character_creation':true}, {'compelling_action':true}],
	// 	]
	// },
	// {
	// 	'name':'Ascetic',
	// 	'description':'',
	// 	'requirements':[
	// 		[{'character_creation':true}, {'compelling_action':true}],
	// 	]
	// },
	// {
	// 	'name':'Protector of The Wild',
	// 	'description':'',
	// 	'requirements':[
	// 		[{'character_creation':true}, {'compelling_action':true}],
	// 	]
	// },
	// {
	// 	'name':'Warrior\'s Code',
	// 	'description':'',
	// 	'requirements':[
	// 		[{'character_creation':true}, {'compelling_action':true}],
	// 	]
	// },
	// {
	// 	'name':'Curious',
	// 	'description':'',
	// 	'requirements':[
	// 		[{'character_creation':true}, {'compelling_action':true}],
	// 	]
	// },
	// {
	// 	'name':'Fuck Authority',
	// 	'description':'',
	// 	'requirements':[
	// 		[{'character_creation':true}, {'compelling_action':true}],
	// 	]
	// },
	// {
	// 	'name':'Honor-bound',
	// 	'description':'',
	// 	'requirements':[
	// 		[{'character_creation':true}, {'compelling_action':true}],
	// 	]
	// },
	// {
	// 	'name':'For the Clan',
	// 	'description':'',
	// 	'requirements':[
	// 		[{'character_creation':true}, {'compelling_action':true}],
	// 	]
	// },
	// {
	// 	'name':'Show Me the Money',
	// 	'description':'',
	// 	'requirements':[
	// 		[{'character_creation':true}, {'compelling_action':true}],
	// 	]
	// },
	// {
	// 	'name':'Solemn Oath',
	// 	'description':'',
	// 	'requirements':[
	// 		[{'character_creation':true}, {'compelling_action':true}],
	// 	]
	// },
];