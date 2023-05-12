
-- set feat type to 'magic_talent'
update feat_or_trait set type = 'magic_talent' where name in (
	'Magic Adept',
	'Master of Magic',
	'Time Lord',
	'Blood Magic',
	'Enchanter',
	'Necromancer',
	'Lord of the Damned',
	'Shapeshifter'
);

-- remove all current requirements
delete from feat_or_trait_req_set where feat_id in (select id from feat_or_trait where type = 'magic_talent');

-- set updated requirements
SET @feat_set_id := (SELECT id FROM feat_or_trait_req_set ORDER BY id DESC LIMIT 0, 1);
SET @feat_set_id := @feat_set_id + 1;
SET @feat_id := (SELECT id FROM feat_or_trait WHERE name = 'Magic Adept');
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (@feat_set_id, @feat_id);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (@feat_set_id, 'vitality', 2);
SET @feat_set_id := @feat_set_id + 1;
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (@feat_set_id, @feat_id);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (@feat_set_id, 'training', 'Ka');
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (@feat_set_id, 'training', 'Avani');
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (@feat_set_id, 'training', 'Nouse');
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (@feat_set_id, 'training', 'Soma');
SET @feat_set_id := @feat_set_id + 1;
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (@feat_set_id, @feat_id);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (@feat_set_id, 'feat', 'Arcane Blood');
SET @feat_set_id := @feat_set_id + 1;

SET @feat_id := (SELECT id FROM feat_or_trait WHERE name = 'Master of Magic');
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (@feat_set_id, @feat_id);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (@feat_set_id, 'vitality', 6);
SET @feat_set_id := @feat_set_id + 1;
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (@feat_set_id, @feat_id);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (@feat_set_id, 'governing', 2);
SET @feat_set_id := @feat_set_id + 1;
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (@feat_set_id, @feat_id);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (@feat_set_id, 'feat', 'Magic Adept');
SET @feat_set_id := @feat_set_id + 1;

SET @feat_id := (SELECT id FROM feat_or_trait WHERE name = 'Time Lord');
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (@feat_set_id, @feat_id);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (@feat_set_id, 'vitality', 8);
SET @feat_set_id := @feat_set_id + 1;
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (@feat_set_id, @feat_id);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (@feat_set_id, 'feat', 'Master of Magic');
SET @feat_set_id := @feat_set_id + 1;
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (@feat_set_id, @feat_id);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (@feat_set_id, 'governing', 6);
SET @feat_set_id := @feat_set_id + 1;
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (@feat_set_id, @feat_id);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (@feat_set_id, 'training', 'Ka');
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (@feat_set_id, 'training', 'Nouse');
SET @feat_set_id := @feat_set_id + 1;

SET @feat_id := (SELECT id FROM feat_or_trait WHERE name = 'Blood Magic');
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (@feat_set_id, @feat_id);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (@feat_set_id, 'vitality', 2);
SET @feat_set_id := @feat_set_id + 1;
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (@feat_set_id, @feat_id);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (@feat_set_id, 'feat', 'Arcane Blood');
SET @feat_set_id := @feat_set_id + 1;
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (@feat_set_id, @feat_id);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (@feat_set_id, 'training', 'Ka');
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (@feat_set_id, 'training', 'Avani');
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (@feat_set_id, 'training', 'Nouse');
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (@feat_set_id, 'training', 'Soma');
SET @feat_set_id := @feat_set_id + 1;

SET @feat_id := (SELECT id FROM feat_or_trait WHERE name = 'Enchanter');
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (@feat_set_id, @feat_id);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (@feat_set_id, 'vitality', 4);
SET @feat_set_id := @feat_set_id + 1;
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (@feat_set_id, @feat_id);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (@feat_set_id, 'training', 'Ka');
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (@feat_set_id, 'training', 'Avani');
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (@feat_set_id, 'training', 'Nouse');
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (@feat_set_id, 'training', 'Soma');
SET @feat_set_id := @feat_set_id + 1;

SET @feat_id := (SELECT id FROM feat_or_trait WHERE name = 'Necromancer');
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (@feat_set_id, @feat_id);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (@feat_set_id, 'vitality', 4);
SET @feat_set_id := @feat_set_id + 1;
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (@feat_set_id, @feat_id);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (@feat_set_id, 'feat', 'Blood Magic');
SET @feat_set_id := @feat_set_id + 1;
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (@feat_set_id, @feat_id);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (@feat_set_id, 'governing', 1);
SET @feat_set_id := @feat_set_id + 1;

SET @feat_id := (SELECT id FROM feat_or_trait WHERE name = 'Lord of the Damned');
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (@feat_set_id, @feat_id);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (@feat_set_id, 'vitality', 8);
SET @feat_set_id := @feat_set_id + 1;
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (@feat_set_id, @feat_id);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (@feat_set_id, 'feat', 'Necromancer');
SET @feat_set_id := @feat_set_id + 1;
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (@feat_set_id, @feat_id);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (@feat_set_id, 'feat', 'Master of Magic');
SET @feat_set_id := @feat_set_id + 1;
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (@feat_set_id, @feat_id);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (@feat_set_id, 'governing', 8);
SET @feat_set_id := @feat_set_id + 1;

SET @feat_id := (SELECT id FROM feat_or_trait WHERE name = 'Shapeshifter');
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (@feat_set_id, @feat_id);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (@feat_set_id, 'vitality', 4);
SET @feat_set_id := @feat_set_id + 1;
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (@feat_set_id, @feat_id);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (@feat_set_id, 'training', 'Avani');
SET @feat_set_id := @feat_set_id + 1;
INSERT INTO feat_or_trait_req_set (id, feat_id) VALUES (@feat_set_id, @feat_id);
INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (@feat_set_id, 'training', 'Soma');

-- set additional feat types to 'magic_talent'
update feat_or_trait set type = 'magic_talent' where name in (
	'Elemental Master',
	'Metal Master',
	'Nature Master',
	'Elementalist',
	'Illusionist',
	'Psychic',
	'Ensi',
	'Seer',
	'Healer',
	'Tormentor',
	'Superhuman'
);

select * from feat_or_trait_req_set where feat_id in (select id from feat_or_trait where type = 'magic_talent');

