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
    weapon_1 varchar(64),
    weapon_2 varchar(64),
    weapon_3 varchar(64),
    motivator_1 varchar(64),
    motivator_2 varchar(64),
    motivator_3 varchar(64),
    motivator_4 varchar(64),
    motivator_1_pts int,
    motivator_2_pts int,
    motivator_3_pts int,
    motivator_4_pts int,
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
    type varchar(64),
    quantity varchar(64),
    damage int,
    max_damage int,
    range_ float,
    rof varchar(64),
    defend int,
    notes varchar(255),
    weight int,
    user_id int,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    FOREIGN KEY (user_id) REFERENCES user(id)
  );

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
