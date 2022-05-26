  CREATE TABLE user (
    id int PRIMARY KEY AUTO_INCREMENT,
    password varchar(255),
    character_name varchar(64),
    xp varchar(64),
    level varchar(12),
    morale varchar(12),
    morale_effect varchar(64),
    race varchar(64),
    height varchar(64),
    weight varchar(64),
    age varchar(64),
    eyes varchar(64),
    hair varchar(64),
    gender varchar(64),
    other varchar(255),
    strength varchar(12),
    fortitude varchar(12),
    speed varchar(12),
    agility varchar(12),
    precision_ varchar(12),
    awareness varchar(12),
    allure varchar(12),
    deception varchar(12),
    intellect varchar(12),
    innovation varchar(12),
    intuition varchar(12),
    vitality varchar(12),
    notes varchar(2000),
    background varchar(2000),
    standard varchar(12),
    quick varchar(12),
    free varchar(12),
    move varchar(12),
    initiative varchar(12),
    move_penalty varchar(64),
    toughness varchar(64),
    defend varchar(64),
    dodge varchar(64),
    fear varchar(64),
    poison varchar(64),
    disease varchar(64),
    damage varchar(12),
    resilience varchar(12),
    wounds varchar(12),
    wound_penalty varchar(64),
    total_weight varchar(12),
    unhindered varchar(12),
    encumbered varchar(12),
    burdened varchar(12),
    overburdened varchar(12),
    motivator_1 varchar(64),
    motivator_2 varchar(64),
    motivator_3 varchar(64),
    motivator_4 varchar(64),
    motivator_1_pts varchar(12),
    motivator_2_pts varchar(12),
    motivator_3_pts varchar(12),
    motivator_4_pts varchar(12),
    weapon_1 varchar(64),
    weapon_1_damage varchar(64),
    weapon_1_crit varchar(64),
    weapon_1_range varchar(64),
    weapon_1_rof varchar(64),
    weapon_2 varchar(64),
    weapon_2_damage varchar(64),
    weapon_2_crit varchar(64),
    weapon_2_range varchar(64),
    weapon_2_rof varchar(64),
    weapon_3 varchar(64),
    weapon_3_damage varchar(64),
    weapon_3_crit varchar(64),
    weapon_3_range varchar(64),
    weapon_3_rof varchar(64),
    created_at TIMESTAMP NOT NULL DEFAULT NOW()
  );

  CREATE TABLE user_training (
    id int PRIMARY KEY AUTO_INCREMENT,
    name varchar(64),
    value varchar(12),
    user_id int,
    attribute_group varchar(64),
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
    damage varchar(64),
    notes varchar(255),
    weight varchar(64),
    user_id int,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    FOREIGN KEY (user_id) REFERENCES user(id)
  );

  CREATE TABLE user_protection (
    id int PRIMARY KEY AUTO_INCREMENT,
    name varchar(64),
    bonus varchar(64),
    notes varchar(255),
    weight varchar(64),
    user_id int,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    FOREIGN KEY (user_id) REFERENCES user(id)
  );

  CREATE TABLE user_healing (
    id int PRIMARY KEY AUTO_INCREMENT,
    name varchar(64),
    quantity varchar(64),
    effect varchar(255),
    weight varchar(64),
    user_id int,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    FOREIGN KEY (user_id) REFERENCES user(id)
  );

  CREATE TABLE user_misc (
    id int PRIMARY KEY AUTO_INCREMENT,
    name varchar(64),
    quantity varchar(64),
    notes varchar(255),
    weight varchar(64),
    user_id int,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    FOREIGN KEY (user_id) REFERENCES user(id)
  );