
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
    -- Attributes and XP
    xp int,
    attribute_pts int,
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
    -- Defense
    magic varchar(64),
    fear varchar(64),
    poison varchar(64),
    disease varchar(64),
    -- Wounds and morale states
    morale int,
    damage int,
    wounds int,
    fatigue int,
    wound_penalty varchar(64),
    move_penalty varchar(64),
    -- Equipped weapons
    weapon_1 varchar(64),
    weapon_2 varchar(64),
    weapon_3 varchar(64),
    -- Motivators
    motivator_1 varchar(64),
    motivator_2 varchar(64),
    motivator_3 varchar(64),
    motivator_4 varchar(64),
    motivator_1_pts int,
    motivator_2_pts int,
    motivator_3_pts int,
    motivator_4_pts int,
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
    user_id int,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    FOREIGN KEY (user_id) REFERENCES user(id)
  );
