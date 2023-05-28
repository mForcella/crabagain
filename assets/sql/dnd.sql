
  -- To get foreign key names
  -- SHOW CREATE TABLE `yourtable`;

  -- To add on delete cascade
  -- ALTER TABLE feat_or_trait_req_set DROP FOREIGN KEY `feat_or_trait_req_set_ibfk_1`;
  -- ALTER TABLE feat_or_trait_req_set ADD CONSTRAINT `feat_or_trait_req_set_ibfk_1`
  -- FOREIGN KEY (`feat_id`) REFERENCES feat_or_trait(`id`) ON DELETE CASCADE;

  CREATE TABLE feat_or_trait (
    id int PRIMARY KEY AUTO_INCREMENT,
    name varchar(255),
    description varchar(2000),
    type varchar(255),
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
    admin_password varchar(255),
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

  CREATE TABLE user (
    -- Non-character values
    id int PRIMARY KEY AUTO_INCREMENT,
    email varchar(255),
    password varchar(255),
    reset_token varchar(255),
    campaign_id int,
    -- XP, morale and leveling
    xp int,
    attribute_pts int,
    morale int,
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
    strength int,
    fortitude int,
    speed int,
    agility int,
    precision_ int,
    awareness int,
    allure int,
    deception int,
    intellect int,
    innovation int,
    intuition int,
    vitality int,
    -- Damage, fatigue and defense
    damage int,
    fatigue int,
    magic varchar(64),
    fear varchar(64),
    poison varchar(64),
    disease varchar(64),
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    FOREIGN KEY (campaign_id) REFERENCES campaign(id)
  );

  CREATE TABLE user_feat (
    id int PRIMARY KEY AUTO_INCREMENT,
    name varchar(64),
    description varchar(2000),
    user_id int,
    feat_id int,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    FOREIGN KEY (user_id) REFERENCES user(id),
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
    FOREIGN KEY (user_id) REFERENCES user(id)
  );

  CREATE TABLE user_note (
    id int PRIMARY KEY AUTO_INCREMENT,
    title varchar(255),
    note varchar(2000),
    user_id int,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    FOREIGN KEY (user_id) REFERENCES user(id)
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
    FOREIGN KEY (user_id) REFERENCES user(id)
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
    FOREIGN KEY (user_id) REFERENCES user(id)
  );

  CREATE TABLE user_healing (
    id int PRIMARY KEY AUTO_INCREMENT,
    name varchar(64),
    quantity varchar(64),
    effect varchar(255),
    weight float,
    user_id int,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    FOREIGN KEY (user_id) REFERENCES user(id)
  );

  CREATE TABLE user_misc (
    id int PRIMARY KEY AUTO_INCREMENT,
    name varchar(64),
    quantity varchar(64),
    notes varchar(255),
    weight float,
    user_id int,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    FOREIGN KEY (user_id) REFERENCES user(id)
  );

  CREATE TABLE user_xp_award (
    id int PRIMARY KEY AUTO_INCREMENT,
    xp_award int,
    awarded bool,
    xp_after_award int,
    user_id int,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    FOREIGN KEY (user_id) REFERENCES user(id)
  );

  -- To view a list of characters and their XP progress
  -- SELECT character_name, xp_award, awarded, xp_after_award, user_xp_award.created_at FROM user_xp_award JOIN user ON user.id = user_xp_award.user_id WHERE campaign_id = 7 ORDER BY character_name ASC;

  CREATE TABLE user_motivator (
    id int PRIMARY KEY AUTO_INCREMENT,
    user_id int,
    motivator varchar(64),
    points int,
    primary_ bool,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
  );

  -- To create user_motivators from user table
  -- SET @user_id := 37;
  -- SET @motivator := (SELECT motivator_1 FROM user WHERE id = @user_id LIMIT 0, 1);
  -- SET @pts := (SELECT motivator_1_pts FROM user WHERE id = @user_id LIMIT 0, 1);
  -- insert into user_motivator (user_id,motivator,points,primary_) values(@user_id,@motivator,@pts,1);
  -- SET @motivator := (SELECT motivator_2 FROM user WHERE id = @user_id LIMIT 0, 1);
  -- SET @pts := (SELECT motivator_2_pts FROM user WHERE id = @user_id LIMIT 0, 1);
  -- insert into user_motivator (user_id,motivator,points,primary_) values(@user_id,@motivator,@pts,1);
  -- SET @motivator := (SELECT motivator_3 FROM user WHERE id = @user_id LIMIT 0, 1);
  -- SET @pts := (SELECT motivator_3_pts FROM user WHERE id = @user_id LIMIT 0, 1);
  -- insert into user_motivator (user_id,motivator,points,primary_) values(@user_id,@motivator,@pts,1);
  -- SET @motivator := (SELECT motivator_4 FROM user WHERE id = @user_id LIMIT 0, 1);
  -- SET @pts := (SELECT motivator_4_pts FROM user WHERE id = @user_id LIMIT 0, 1);
  -- insert into user_motivator (user_id,motivator,points,primary_) values(@user_id,@motivator,@pts,0);

