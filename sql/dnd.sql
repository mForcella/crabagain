
  CREATE TABLE feat_or_trait (
    id int PRIMARY KEY AUTO_INCREMENT,
    name varchar(255),
    description varchar(2000),
    type varchar(255),
    race varchar(64),
    cost int DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT NOW()
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

  CREATE TABLE invitation (
    id int PRIMARY KEY AUTO_INCREMENT,
    invite_code varchar(255),
    email varchar(255),
    campaign_id int,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    FOREIGN KEY (campaign_id) REFERENCES campaign(id) ON DELETE CASCADE
  );

  CREATE TABLE user (
    -- Non-character values
    id int PRIMARY KEY AUTO_INCREMENT,
    campaign_id int,
    login_id int,
    -- XP, morale and leveling
    xp int DEFAULT 0,
    attribute_pts int DEFAULT 12,
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
    age_category varchar(64),
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
    FOREIGN KEY (login_id) REFERENCES login(id) ON DELETE SET NULL,
    FOREIGN KEY (campaign_id) REFERENCES campaign(id) ON DELETE SET NULL
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
    attack_attribute varchar(255),
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

  CREATE TABLE sql_query (
    id int PRIMARY KEY AUTO_INCREMENT,
    query text,
    source varchar(255),
    type varchar(64),
    login_id int,
    character_id int,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    FOREIGN KEY (login_id) REFERENCES login(id) ON DELETE CASCADE,
    FOREIGN KEY (character_id) REFERENCES user(id) ON DELETE CASCADE
  );

  

  -- To get foreign key names
  -- SHOW CREATE TABLE `yourtable`;

  -- To view a list of characters and their XP progress
  -- SELECT character_name, xp_award, awarded, xp_after_award, user_xp_award.created_at FROM user_xp_award JOIN user ON user.id = user_xp_award.user_id WHERE campaign_id = 7 ORDER BY character_name ASC;

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

