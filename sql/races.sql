INSERT INTO race (name) VALUES ("Danu");
SET @race_id := (SELECT LAST_INSERT_ID());
INSERT INTO race_skill (race_id,skill,attribute,value) VALUES (@race_id,"Religion (Elohim)","Intellect",1);
INSERT INTO race_skill (race_id,skill,attribute,value) VALUES (@race_id,"Diplomacy","Allure",2);
INSERT INTO campaign_race (race_id, campaign_id) VALUES (@race_id,7);

INSERT INTO race (name) VALUES ("Asgari");
SET @race_id := (SELECT LAST_INSERT_ID());
INSERT INTO race_skill (race_id,skill,attribute,value,input_required) VALUES (@race_id,"Religion","Intellect",2,true);
INSERT INTO race_skill (race_id,skill,attribute,value,input_required) VALUES (@race_id,"Academia","Intellect",1,true);
SET @trait_id := (SELECT id FROM feat_or_trait WHERE name = "Iron Soul");
INSERT INTO race_trait (race_id,trait_id,trait) VALUES (@race_id,@trait_id,"Iron Soul");
SET @trait_id := (SELECT id FROM feat_or_trait WHERE name = "Pacifist");
INSERT INTO race_trait (race_id,trait_id,trait) VALUES (@race_id,@trait_id,"Pacifist");
INSERT INTO campaign_race (race_id, campaign_id) VALUES (@race_id,7);

INSERT INTO race (name) VALUES ("Shenari");
SET @race_id := (SELECT LAST_INSERT_ID());
INSERT INTO race_skill (race_id,skill,attribute,value) VALUES (@race_id,"Sail","Innovation/Intellect",0);
INSERT INTO campaign_race (race_id, campaign_id) VALUES (@race_id,7);

INSERT INTO race (name) VALUES ("Zerzuran");
SET @race_id := (SELECT LAST_INSERT_ID());
INSERT INTO race_skill (race_id,skill,attribute,value) VALUES (@race_id,"Train Animal","Allure",0);
INSERT INTO race_skill (race_id,skill,attribute,value) VALUES (@race_id,"Sense Motive","Intuition",1);
INSERT INTO race_skill (race_id,skill,attribute,value) VALUES (@race_id,"Search","Awareness",1);
SET @trait_id := (SELECT id FROM feat_or_trait WHERE name = "Honest");
INSERT INTO race_trait (race_id,trait_id,trait) VALUES (@race_id,@trait_id,"Honest");
INSERT INTO campaign_race (race_id, campaign_id) VALUES (@race_id,7);

INSERT INTO race (name) VALUES ("Alyoni");
SET @race_id := (SELECT LAST_INSERT_ID());
INSERT INTO race_skill (race_id,skill,attribute,value) VALUES (@race_id,"Stealth","Awareness/Deception",0);
INSERT INTO race_skill (race_id,skill,attribute,value) VALUES (@race_id,"Listen","Awareness",1);
INSERT INTO race_skill (race_id,skill,attribute,value) VALUES (@race_id,"Search","Awareness",1);
SET @trait_id := (SELECT id FROM feat_or_trait WHERE name = "Honor-bound");
INSERT INTO race_trait (race_id,trait_id,trait) VALUES (@race_id,@trait_id,"Honor-bound");
INSERT INTO campaign_race (race_id, campaign_id) VALUES (@race_id,7);

INSERT INTO race (name) VALUES ("Nerran");
SET @race_id := (SELECT LAST_INSERT_ID());
INSERT INTO race_skill (race_id,skill,attribute,value) VALUES (@race_id,"Religion (Nergal)","Intellect",2);
INSERT INTO race_skill (race_id,skill,attribute,value) VALUES (@race_id,"Profession (Fisher)","Intellect",2);
SET @trait_id := (SELECT id FROM feat_or_trait WHERE name = "Ascetic");
INSERT INTO race_trait (race_id,trait_id,trait) VALUES (@race_id,@trait_id,"Ascetic");
INSERT INTO campaign_race (race_id, campaign_id) VALUES (@race_id,7);

INSERT INTO race (name) VALUES ("Vanir");
SET @race_id := (SELECT LAST_INSERT_ID());
INSERT INTO race_skill (race_id,skill,attribute,value) VALUES (@race_id,"Listen","Awareness",2);
INSERT INTO race_skill (race_id,skill,attribute,value) VALUES (@race_id,"Diplomacy","Allure",1);
SET @trait_id := (SELECT id FROM feat_or_trait WHERE name = "Master of the Wild");
INSERT INTO race_trait (race_id,trait_id,trait) VALUES (@race_id,@trait_id,"Master of the Wild");
SET @trait_id := (SELECT id FROM feat_or_trait WHERE name = "Protector of the Wild");
INSERT INTO race_trait (race_id,trait_id,trait) VALUES (@race_id,@trait_id,"Protector of the Wild");
INSERT INTO campaign_race (race_id, campaign_id) VALUES (@race_id,7);

INSERT INTO race (name,size) VALUES ("Aesir","Large");
SET @race_id := (SELECT LAST_INSERT_ID());
INSERT INTO race_skill (race_id,skill,attribute,value) VALUES (@race_id,"Climb","Strength",2);
INSERT INTO race_skill (race_id,skill,attribute,value) VALUES (@race_id,"Intimidate","Vitality",1);
SET @trait_id := (SELECT id FROM feat_or_trait WHERE name = "Warrior's Code");
INSERT INTO race_trait (race_id,trait_id,trait) VALUES (@race_id,@trait_id,"Warrior's Code");
INSERT INTO campaign_race (race_id, campaign_id) VALUES (@race_id,7);

INSERT INTO race (name,size) VALUES ("Sileni","Small");
SET @race_id := (SELECT LAST_INSERT_ID());
INSERT INTO race_skill (race_id,skill,attribute,value) VALUES (@race_id,"Perform","Allure/Deception",0);
INSERT INTO race_skill (race_id,skill,attribute,value) VALUES (@race_id,"Sense Motive","Intuition",2);
INSERT INTO race_skill (race_id,skill,attribute,value) VALUES (@race_id,"Barter","Allure",1);
SET @trait_id := (SELECT id FROM feat_or_trait WHERE name = "Curious");
INSERT INTO race_trait (race_id,trait_id,trait) VALUES (@race_id,@trait_id,"Curious");
INSERT INTO campaign_race (race_id, campaign_id) VALUES (@race_id,7);

INSERT INTO race (name,size) VALUES ("Pangu","Small");
SET @race_id := (SELECT LAST_INSERT_ID());
INSERT INTO race_skill (race_id,skill,attribute,value) VALUES (@race_id,"Engineering","Innovation/Intellect",0);
INSERT INTO race_skill (race_id,skill,attribute,value,input_required) VALUES (@race_id,"Academia","Intellect",1,true);
INSERT INTO race_skill (race_id,skill,attribute,value) VALUES (@race_id,"Diplomacy","Allure",1);
SET @trait_id := (SELECT id FROM feat_or_trait WHERE name = "Mentalist");
INSERT INTO race_trait (race_id,trait_id,trait) VALUES (@race_id,@trait_id,"Mentalist");
INSERT INTO campaign_race (race_id, campaign_id) VALUES (@race_id,7);

INSERT INTO race (name) VALUES ("Alfir");
SET @race_id := (SELECT LAST_INSERT_ID());
INSERT INTO race_skill (race_id,skill,attribute,value) VALUES (@race_id,"Climb","Strength",2);
INSERT INTO race_skill (race_id,skill,attribute,value) VALUES (@race_id,"Listen","Awareness",1);
SET @trait_id := (SELECT id FROM feat_or_trait WHERE name = "Energy Weaver");
INSERT INTO race_trait (race_id,trait_id,trait) VALUES (@race_id,@trait_id,"Energy Weaver");
SET @trait_id := (SELECT id FROM feat_or_trait WHERE name = "Rebel Yell");
INSERT INTO race_trait (race_id,trait_id,trait) VALUES (@race_id,@trait_id,"Rebel Yell");
INSERT INTO campaign_race (race_id, campaign_id) VALUES (@race_id,7);

INSERT INTO race (name) VALUES ("Halbarn");
SET @race_id := (SELECT LAST_INSERT_ID());
INSERT INTO race_skill (race_id,skill,attribute,value) VALUES (@race_id,"Barter","Allure",2);
INSERT INTO race_skill (race_id,skill,attribute,value) VALUES (@race_id,"Appraise","Intellect",2);
SET @trait_id := (SELECT id FROM feat_or_trait WHERE name = "Show Me the Money");
INSERT INTO race_trait (race_id,trait_id,trait) VALUES (@race_id,@trait_id,"Show Me the Money");
INSERT INTO campaign_race (race_id, campaign_id) VALUES (@race_id,7);

INSERT INTO race (name) VALUES ("Betu");
SET @race_id := (SELECT LAST_INSERT_ID());
INSERT INTO race_skill (race_id,skill,attribute,value) VALUES (@race_id,"Survival","Intellect/Intuition",0);
INSERT INTO campaign_race (race_id, campaign_id) VALUES (@race_id,7);

INSERT INTO race (name,size) VALUES ("Ogre","Large");
SET @race_id := (SELECT LAST_INSERT_ID());
INSERT INTO race_skill (race_id,skill,attribute,value) VALUES (@race_id,"Smell","Awareness",2);
INSERT INTO race_skill (race_id,skill,attribute,value) VALUES (@race_id,"Brawl","Agility",1);
SET @trait_id := (SELECT id FROM feat_or_trait WHERE name = "For the Clan");
INSERT INTO race_trait (race_id,trait_id,trait) VALUES (@race_id,@trait_id,"For the Clan");
INSERT INTO campaign_race (race_id, campaign_id) VALUES (@race_id,7);