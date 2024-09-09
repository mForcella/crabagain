
INSERT INTO feat_or_trait (name, description, type, cost) VALUES ("Professor/Researcher", "Whether in the University, museums or research institutes, you were once well-respected by the few people that knew your name. Unfortunately, your “extra-curricular” research got you into trouble and you've found yourself in need of alternate employment. You may choose one eclectic, academic area as a Focus under Intellect (i.e. American Folklore, Cryptozoology, etc.). This Focus is treated as if you have Eclectic Knowledge if you ever fail a roll, and if you also have Eclectic Knowledge, you double your normal percentile chance. You also gain the Esoteric Knowledge Skill.", "social_background", 0);

INSERT INTO feat_or_trait (name, description, type, cost) VALUES ("Prospector", "You travelled west to make your fortune, and things went well for a while, but eventually, your home on the coast called you back for better things. Spending so much time underground had its benefits though. You always know direction in the dark, or when underground, and can identify likely mining areas on a detailed map (even if you have never visited the are) by making an appropriate Attribute roll of DL 10 maximum. You can identify any ores or precious metals, without rolling, and begin with either the Demolitions OR Engineering Skill.", "social_background", 0);

INSERT INTO feat_or_trait (name, description, type, cost) VALUES ("Frontiersman", "You've made a living staking claims, mapping out new territories, hunting, and trapping. The wilderness is your home and you generally prefer it to city-life, but things get lonesome out west, and you know you're destined for bigger things. You can create detailed maps of any area you have travelled through with a DL 14. Additionally, if you have a map of an area, you can never become delayed or lost. You gain the Survival Skill.", "social_background", 0);

INSERT INTO feat_or_trait (name, description, type, cost) VALUES ("Cowboy", "You made your living on the range, and you had a way with Horses beyond mere training and commands. But you were at it all for a bit too long, and the tedium got to you. Now it's time to mosey on. Any time you encounter a Horse, wild or domestic, you can attempt to Tame it using your Intuition or Allure. The DL is determined by the GM, based on your relationship to the Horse as well as the Horses' relationship to their owner (if they have one). The maximum DL is 14. You gain the Ride Animal Skill and may use Train Animal on Horses only.", "social_background", 0);

INSERT INTO feat_or_trait (name, description, type, cost) VALUES ("Medicine Wo/Man", "Through your indigenous knowledge, you know the plants of America better than most. You've chosen to work with the white man because you know that greater evils are at play, and besides... maybe not every white man is a monster. You may choose one region: Mountain, Desert, Coastal or Forest. You can identify any plants with a maximum DL of 10 in your chosen region. Additionally, even if you fail a roll or you are outside of your chosen region, a DL 14 will identify likely properties of any plants you encounter. You also gain the First Aid Skill.", "social_background", 0);

INSERT INTO feat_or_trait (name, description, type, cost) VALUES ("Old Money", "Because of your deep pockets and fame, you have befriended (and bribed) people everywhere, but your family (or business) has been trying to cut you out. It's about time to make a name for yourself on your terms. Any time you visit a new town or city you may declare a relationship with an inhabitant. This can be someone of your own creation (with GM approval), or you can declare a relationship to a pre-existing NPC. The quality and nature of the relationship is still at the GM's discretion, but it will never be detrimental to you. Additionally, as long as you have access to a city with a bank, you can withdraw up to $50/week and double your starting funds.", "social_background", 0);

INSERT INTO feat_or_trait (name, description, type, cost) VALUES ("Definitely Illegal", "The frontier was the perfect place to make a living any way possible, and you took full advantage. But now it's time to do something bigger. You know your way around the seedy underbelly of American black markets, illegal businesses, criminal syndicates. Anytime you are in a new town, you can make an appropriate Attribute check to locate such businesses and activities, with a maximum DL of 10. You are fluent in the slang and culture of this world and will not be treated hostilely or with suspicion from such people unless you act in a contradictory way, or as the result of Fate. You gain the Security Skill AND any other Unique Skill of your choosing. You also have Bounty on your head (so watch your back).", "social_background", 0);

INSERT INTO feat_or_trait (name, description, type, cost) VALUES ("Snake-oil Salesman", "Everyone's got to make a living, and you can give people a good show and some hope in a bottle, who are you to argue? But not everything is smoke and mirrors, and you're determined to find the truth behind the rumors you've heard about otherworldly forces at work in the world - if for no other reason than to monetize real magic. Because you've travelled so much, you know people everywhere, and most of them never want to see you again. Anytime you visit a town or city, you must use a Motivator Bonus to declare yourself unknown, otherwise your bad reputation will precede you. You gain the Sleight of Hand AND Perform Skill.", "social_background", 0);

INSERT INTO feat_or_trait (name, description, type, cost) VALUES ("Soldier/Lawman", "You were a career gunman for many years, and you've seen your fair share of combat and shoot-outs. Eventually, you grew tired of the life you built and ventured out to find new excitement, and maybe make a name for yourself. You know guns better than your own body and may ignore Misfire on all weapons and reduce Accuracy penalties by 1. Unfortunately, you made a lot of enemies along the way, and you are wanted dead or alive by an outlaw gang. You gain the Tactics AND Ride Animal Skills.", "social_background", 0);

INSERT INTO feat_or_trait (name, description, type, cost) VALUES ("Freed Slave", "You were born into slavery but managed to escape from bondage and find freedom in the North. Life still hasn't been easy, but a positive mindset and a cunning mind have kept you safe and content, and you've found a few Northerners you think can trust. Unfortunately, it's also impossible to travel into Confederate lands without a white escort. Negative Morale Effects are reduced 1 Level (i.e. you suffer no effects until -4, and can never suffer the standard -8 Morale Effect). You also gain the Stealth AND Survival Skills.", "social_background", 0);


INSERT INTO feat_or_trait (name, description, type, cost) VALUES ("Shapeshifter Adept", "You have honed your shapeshifting into an art. You may Shift as a Quick Action and double the number of times you can Shift without suffering Fatigue. This begins at 4 times, but may be altered by Talents, Traits, Abilities and Magic Items. Any changes are applied to the standard base of 2 and then doubled. For example, someone with the Strong Constitution Trait could normally shift 3 times, and so with Master Shifter this doubles to 6, rather than 4 plus 1.", "magic_talent", 4);
	SET @feat_id := (SELECT LAST_INSERT_ID());
INSERT INTO feat_or_trait_req_set (feat_id) VALUES (@feat_id);
	SET @set_id := (SELECT LAST_INSERT_ID());
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (@set_id, "vitality", 6);
INSERT INTO feat_or_trait_req_set (feat_id) VALUES (@feat_id);
	SET @set_id := (SELECT LAST_INSERT_ID());
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (@set_id, "feat", "Shapeshifter");

INSERT INTO feat_or_trait (name, description, type, cost) VALUES ("Master Shapeshifter", "Your body is as fluid is water. You can now Shift an additional 2 times without suffering Fatigue. You can alter your body partially into any part of an Animal you can normally Shift into, such as growing wings or Talons, rather than fully Shifting into an Eagle. Additionally, you may now learn to Shift into Magical Beasts.", "magic_talent", 4);
	SET @feat_id := (SELECT LAST_INSERT_ID());
INSERT INTO feat_or_trait_req_set (feat_id) VALUES (@feat_id);
	SET @set_id := (SELECT LAST_INSERT_ID());
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (@set_id, "vitality", 8);
INSERT INTO feat_or_trait_req_set (feat_id) VALUES (@feat_id);
	SET @set_id := (SELECT LAST_INSERT_ID());
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (@set_id, "feat", "Master of Magic");
INSERT INTO feat_or_trait_req_set (feat_id) VALUES (@feat_id);
	SET @set_id := (SELECT LAST_INSERT_ID());
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (@set_id, "feat", "Shapeshifter Adept");
INSERT INTO feat_or_trait_req_set (feat_id) VALUES (@feat_id);
	SET @set_id := (SELECT LAST_INSERT_ID());
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (@set_id, "governing", 4);

INSERT INTO feat_or_trait (name, description, type, cost) VALUES ("Strong Constitution", "You can ignore your first Level of Fatigue.", "physical_trait", 4);

INSERT INTO feat_or_trait (name, description, type, cost) VALUES ("Frail Constitution", "You are Unconscious after 3 Levels of Fatigue instead of 4.", "physical_trait", -3);
