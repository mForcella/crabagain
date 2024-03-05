
  CREATE TABLE feat_or_trait (
    id int PRIMARY KEY AUTO_INCREMENT,
    name varchar(255),
    description varchar(2000),
    type varchar(255),
    race varchar(64),
    cost int DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT NOW()
  );

  CREATE TABLE feat_or_trait_req_set (
    id int PRIMARY KEY AUTO_INCREMENT,
    feat_id int,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    FOREIGN KEY (feat_id) REFERENCES feat_or_trait(id) ON DELETE CASCADE
  );

  CREATE TABLE feat_or_trait_req (
    id int PRIMARY KEY AUTO_INCREMENT,
    req_set_id int,
    type varchar(255),
    value varchar(255),
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    FOREIGN KEY (req_set_id) REFERENCES feat_or_trait_req_set(id) ON DELETE CASCADE
  );

  CREATE TABLE campaign (
    id int PRIMARY KEY AUTO_INCREMENT,
    name varchar(255),
    created_at TIMESTAMP NOT NULL DEFAULT NOW()
  );

  CREATE TABLE campaign_feat (
    id int PRIMARY KEY AUTO_INCREMENT,
    campaign_id int,
    feat_id int,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    FOREIGN KEY (campaign_id) REFERENCES campaign(id) ON DELETE CASCADE,
    FOREIGN KEY (feat_id) REFERENCES feat_or_trait(id) ON DELETE CASCADE
  );

  CREATE TABLE login (
    id int PRIMARY KEY AUTO_INCREMENT,
    email varchar(255),
    password varchar(255),
    reset_token varchar(255),
    confirmed int DEFAULT 0,
    confirmation_code varchar(255),
    created_at TIMESTAMP NOT NULL DEFAULT NOW()
  );

  CREATE TABLE login_campaign (
    id int PRIMARY KEY AUTO_INCREMENT,
    login_id int,
    campaign_id int,
    campaign_role int DEFAULT 2,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    FOREIGN KEY (login_id) REFERENCES login(id) ON DELETE CASCADE,
    FOREIGN KEY (campaign_id) REFERENCES campaign(id) ON DELETE CASCADE
  );

  CREATE TABLE user (
    -- Non-character values
    id int PRIMARY KEY AUTO_INCREMENT,
    campaign_id int,
    login_id int,
    -- XP, morale and leveling
    xp int DEFAULT 0,
    attribute_pts int DEFAULT 0,
    morale int DEFAULT 0,
    -- Physical characteristics and descriptors
    character_name varchar(64),
    race varchar(64),
    height varchar(64),
    weight varchar(64),
    age varchar(64),
    eyes varchar(64),
    hair varchar(64),
    gender varchar(64),
    other varchar(255),
    size varchar(64),
    background varchar(2000),
    -- Attributes
    strength int DEFAULT 0,
    fortitude int DEFAULT 0,
    speed int DEFAULT 0,
    agility int DEFAULT 0,
    precision_ int DEFAULT 0,
    awareness int DEFAULT 0,
    allure int DEFAULT 0,
    deception int DEFAULT 0,
    intellect int DEFAULT 0,
    innovation int DEFAULT 0,
    intuition int DEFAULT 0,
    vitality int DEFAULT 0,
    -- Damage, fatigue and defense
    damage int DEFAULT 0,
    fatigue int DEFAULT 0,
    magic varchar(64),
    fear varchar(64),
    poison varchar(64),
    disease varchar(64),
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    FOREIGN KEY (login_id) REFERENCES login(id),
    FOREIGN KEY (campaign_id) REFERENCES campaign(id)
  );

  CREATE TABLE user_feat (
    id int PRIMARY KEY AUTO_INCREMENT,
    name varchar(64),
    description varchar(2000),
    user_id int,
    feat_id int,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
    FOREIGN KEY (feat_id) REFERENCES feat_or_trait(id) ON DELETE CASCADE
  );

  CREATE TABLE user_training (
    id int PRIMARY KEY AUTO_INCREMENT,
    name varchar(64),
    value varchar(12),
    attribute_group varchar(64),
    user_id int,
    magic_school bool DEFAULT 0,
    governing_school bool DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
  );

  CREATE TABLE user_note (
    id int PRIMARY KEY AUTO_INCREMENT,
    title varchar(255),
    note varchar(2000),
    user_id int,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
  );

  CREATE TABLE user_weapon (
    id int PRIMARY KEY AUTO_INCREMENT,
    name varchar(64),
    type varchar(64),
    quantity varchar(64),
    damage int,
    max_damage int,
    range_ float,
    rof varchar(64),
    defend int,
    crit int,
    notes varchar(255),
    weight float,
    equipped int,
    user_id int,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
  );

  CREATE TABLE user_protection (
    id int PRIMARY KEY AUTO_INCREMENT,
    name varchar(64),
    bonus varchar(64),
    notes varchar(255),
    weight float,
    equipped bool,
    user_id int,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
  );

  CREATE TABLE user_healing (
    id int PRIMARY KEY AUTO_INCREMENT,
    name varchar(64),
    quantity varchar(64),
    effect varchar(255),
    weight float,
    user_id int,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
  );

  CREATE TABLE user_misc (
    id int PRIMARY KEY AUTO_INCREMENT,
    name varchar(64),
    quantity varchar(64),
    notes varchar(255),
    weight float,
    user_id int,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
  );

  CREATE TABLE user_xp_award (
    id int PRIMARY KEY AUTO_INCREMENT,
    xp_award int,
    awarded bool,
    xp_after_award int,
    user_id int,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
  );

  CREATE TABLE user_motivator (
    id int PRIMARY KEY AUTO_INCREMENT,
    user_id int,
    motivator varchar(64),
    points int,
    primary_ bool,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
  );

  CREATE TABLE race (
    id int PRIMARY KEY AUTO_INCREMENT,
    name varchar(64),
    size varchar(64) DEFAULT "Medium",
    created_at TIMESTAMP NOT NULL DEFAULT NOW()
  );

  CREATE TABLE campaign_race (
    id int PRIMARY KEY AUTO_INCREMENT,
    race_id int,
    campaign_id int,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    FOREIGN KEY (race_id) REFERENCES race(id) ON DELETE CASCADE,
    FOREIGN KEY (campaign_id) REFERENCES campaign(id) ON DELETE CASCADE
  );

  CREATE TABLE race_trait (
    id int PRIMARY KEY AUTO_INCREMENT,
    race_id int,
    trait varchar(64),
    trait_id int,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    FOREIGN KEY (race_id) REFERENCES race(id) ON DELETE CASCADE
  );

  CREATE TABLE race_skill (
    id int PRIMARY KEY AUTO_INCREMENT,
    race_id int,
    skill varchar(64),
    attribute varchar(64),
    input_required bool DEFAULT false,
    value int,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    FOREIGN KEY (race_id) REFERENCES race(id) ON DELETE CASCADE
  );

  insert into feat_or_trait (name,description,type,cost) values ("Iron Soul","Asgari add half their Soma Bonus to their Caster Level for Soma Magic only.","race_trait",0);
  insert into feat_or_trait (name,description,type,cost) values ("Master of the Wild","Vanir add half their Avani Bonus to their Caster Level for Avani Magic only.","race_trait",0);
  insert into feat_or_trait (name,description,type,cost) values ("Mentalist","Pangu add half their Nous Bonus to their Caster Level for Nous Magic only.","race_trait",0);
  insert into feat_or_trait (name,description,type,cost) values ("Energy Weaver","Alfir add half their Ka Bonus to their Caster Level for Ka Magic only.","race_trait",0);

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

  -- To get foreign key names
  -- SHOW CREATE TABLE `yourtable`;

  -- To view a list of characters and their XP progress
  -- SELECT character_name, xp_award, awarded, xp_after_award, user_xp_award.created_at FROM user_xp_award JOIN user ON user.id = user_xp_award.user_id WHERE campaign_id = 7 ORDER BY character_name ASC;

  -- To add on delete cascade
  -- ALTER TABLE feat_or_trait_req_set DROP FOREIGN KEY `feat_or_trait_req_set_ibfk_1`;
  -- ALTER TABLE feat_or_trait_req_set ADD CONSTRAINT `feat_or_trait_req_set_ibfk_1`
  --    FOREIGN KEY (`feat_id`) REFERENCES feat_or_trait(`id`) ON DELETE CASCADE;

  -- Add default values to user table
  -- ALTER TABLE `user` ALTER COLUMN xp SET DEFAULT 0;
  -- ALTER TABLE `user` ALTER COLUMN attribute_pts SET DEFAULT 0;
  -- ALTER TABLE `user` ALTER COLUMN morale SET DEFAULT 0;

  -- Add delete cascade to user foreign keys
  -- ALTER TABLE `user_feat` DROP FOREIGN KEY `user_feat_ibfk_1`;
  -- ALTER TABLE `user_feat` ADD CONSTRAINT `user_feat_ibfk_1` FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE;
  -- ALTER TABLE `user_healing` DROP FOREIGN KEY `user_healing_ibfk_1`;
  -- ALTER TABLE `user_healing` ADD CONSTRAINT `user_healing_ibfk_1` FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE;
  -- ALTER TABLE `user_misc` DROP FOREIGN KEY `user_misc_ibfk_1`;
  -- ALTER TABLE `user_misc` ADD CONSTRAINT `user_misc_ibfk_1` FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE;
  -- ALTER TABLE `user_note` DROP FOREIGN KEY `user_note_ibfk_1`;
  -- ALTER TABLE `user_note` ADD CONSTRAINT `user_note_ibfk_1` FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE;
  -- ALTER TABLE `user_protection` DROP FOREIGN KEY `user_protection_ibfk_1`;
  -- ALTER TABLE `user_protection` ADD CONSTRAINT `user_protection_ibfk_1` FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE;
  -- ALTER TABLE `user_training` DROP FOREIGN KEY `user_training_ibfk_1`;
  -- ALTER TABLE `user_training` ADD CONSTRAINT `user_training_ibfk_1` FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE;
  -- ALTER TABLE `user_weapon` DROP FOREIGN KEY `user_weapon_ibfk_1`;
  -- ALTER TABLE `user_weapon` ADD CONSTRAINT `user_weapon_ibfk_1` FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE;
  -- ALTER TABLE `user_xp_award` DROP FOREIGN KEY `user_xp_award_ibfk_1`;
  -- ALTER TABLE `user_xp_award` ADD CONSTRAINT `user_xp_award_ibfk_1` FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE;

  -- To create user_motivators from user table
  -- SET @user_id := 37;
  -- SET @motivator := (SELECT motivator_1 FROM user WHERE id = @user_id LIMIT 0, 1);
  -- SET @pts := (SELECT motivator_1_pts FROM user WHERE id = @user_id LIMIT 0, 1);
  -- INSERT INTO user_motivator (user_id,motivator,points,primary_) values(@user_id,@motivator,@pts,1);
  -- SET @motivator := (SELECT motivator_2 FROM user WHERE id = @user_id LIMIT 0, 1);
  -- SET @pts := (SELECT motivator_2_pts FROM user WHERE id = @user_id LIMIT 0, 1);
  -- INSERT INTO user_motivator (user_id,motivator,points,primary_) values(@user_id,@motivator,@pts,1);
  -- SET @motivator := (SELECT motivator_3 FROM user WHERE id = @user_id LIMIT 0, 1);
  -- SET @pts := (SELECT motivator_3_pts FROM user WHERE id = @user_id LIMIT 0, 1);
  -- INSERT INTO user_motivator (user_id,motivator,points,primary_) values(@user_id,@motivator,@pts,1);
  -- SET @motivator := (SELECT motivator_4 FROM user WHERE id = @user_id LIMIT 0, 1);
  -- SET @pts := (SELECT motivator_4_pts FROM user WHERE id = @user_id LIMIT 0, 1);
  -- INSERT INTO user_motivator (user_id,motivator,points,primary_) values(@user_id,@motivator,@pts,0);

