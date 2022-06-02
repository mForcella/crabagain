  CREATE TABLE user (
    id int PRIMARY KEY AUTO_INCREMENT,
    password varchar(255),
    character_name varchar(64),
    attribute_pts int,
    xp int,
    morale int,
    race varchar(64),
    height varchar(64),
    weight varchar(64),
    age varchar(64),
    eyes varchar(64),
    hair varchar(64),
    gender varchar(64),
    other varchar(255),
    size varchar(64),
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
    free int,
    move_penalty varchar(64),
    defend varchar(64),
    fear varchar(64),
    poison varchar(64),
    disease varchar(64),
    damage int,
    wounds int,
    wound_penalty varchar(64),
    notes varchar(2000),
    background varchar(2000),
    motivator_1 varchar(64),
    motivator_2 varchar(64),
    motivator_3 varchar(64),
    motivator_4 varchar(64),
    motivator_1_pts int,
    motivator_2_pts int,
    motivator_3_pts int,
    motivator_4_pts int,
    weapon_1 varchar(64),
    weapon_1_damage int,
    weapon_1_crit int,
    weapon_1_range int,
    weapon_1_rof varchar(64),
    weapon_2 varchar(64),
    weapon_2_damage int,
    weapon_2_crit int,
    weapon_2_range int,
    weapon_2_rof varchar(64),
    weapon_3 varchar(64),
    weapon_3_damage int,
    weapon_3_crit int,
    weapon_3_range int,
    weapon_3_rof varchar(64),
    created_at TIMESTAMP NOT NULL DEFAULT NOW()
  );

  CREATE TABLE user_training (
    id int PRIMARY KEY AUTO_INCREMENT,
    name varchar(64),
    value varchar(12),
    attribute_group varchar(64),
    user_id int,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    FOREIGN KEY (user_id) REFERENCES user(id)
  );

  CREATE TABLE user_feat (
    id int PRIMARY KEY AUTO_INCREMENT,
    name varchar(64),
    description varchar(255),
    user_id int,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    FOREIGN KEY (user_id) REFERENCES user(id)
  );

  CREATE TABLE user_weapon (
    id int PRIMARY KEY AUTO_INCREMENT,
    name varchar(64),
    quantity varchar(64),
    damage int,
    notes varchar(255),
    weight int,
    user_id int,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    FOREIGN KEY (user_id) REFERENCES user(id)
  );

  alter table user_weapon
  add column weapon_type varchar(64),
  add column max_damage int,
  add column range_ int,
  add column rof varchar(64),
  add column defend int,
  modify column damage int,
  modify column weight int;

  CREATE TABLE user_protection (
    id int PRIMARY KEY AUTO_INCREMENT,
    name varchar(64),
    bonus varchar(64),
    notes varchar(255),
    weight int,
    user_id int,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    FOREIGN KEY (user_id) REFERENCES user(id)
  );

  alter table user_protection
  modify column weight int;

  CREATE TABLE user_healing (
    id int PRIMARY KEY AUTO_INCREMENT,
    name varchar(64),
    quantity varchar(64),
    effect varchar(255),
    weight int,
    user_id int,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    FOREIGN KEY (user_id) REFERENCES user(id)
  );

  alter table user_healing
  modify column weight int;

  CREATE TABLE user_misc (
    id int PRIMARY KEY AUTO_INCREMENT,
    name varchar(64),
    quantity varchar(64),
    notes varchar(255),
    weight int,
    user_id int,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    FOREIGN KEY (user_id) REFERENCES user(id)
  );

  alter table user_misc
  modify column weight int;