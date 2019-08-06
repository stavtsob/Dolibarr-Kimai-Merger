CREATE TABLE customer
(
	doli_id int(11) NOT NULL,
	kimai_id int(11) NOT NULL,
	token varchar(32) NOT NULL,
	PRIMARY KEY(doli_id, kimai_id)
);

CREATE TABLE user
(
	doli_id int(11) NOT NULL,
	kimai_id int(11) NOT NULL,
	token varchar(32) NOT NULL,
	username varchar(50) NOT NULL,
	PRIMARY KEY(doli_id, kimai_id)
);

CREATE TABLE project
(
	doli_id int(11) NOT NULL,
	kimai_id int(11) NOT NULL,
	token varchar(32) NOT NULL,
	PRIMARY KEY(doli_id, kimai_id)
);

CREATE TABLE task
(
	doli_id int(11) NOT NULL,
	kimai_id int(11) NOT NULL,
	token varchar(32) NOT NULL,
	PRIMARY KEY(doli_id, kimai_id)
);

CREATE TABLE merged_timesheet (
        kimai_id int(11) NOT NULL,
		task_id int(11) NOT NULL,
        activity_id int(11) NOT NULL,
		project_id int(11) NOT NULL,
		duration int(11) NOT NULL,
		user_id int(11) NOT NULL,
		start DATETIME NOT NULL,
		token VARCHAR(32) NOT NULL,
		PRIMARY KEY (kimai_id)
);