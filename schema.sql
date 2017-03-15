DROP TABLE users;
DROP TABLE groups;
DROP TABLE groupUserRel;
DROP TABLE bills;
DROP TABLE billTypes;
DROP TABLE billContributors;
DROP TABLE notifications;
DROP TABLE notiTypes;
DROP TABLE notiBill;
DROP TABLE notiGroup;
DROP TABLE notiUser;

CREATE TABLE users
(
	userID INTEGER NOT NULL UNIQUE PRIMARY KEY AUTOINCREMENT,
	username VARCHAR(30) NOT NULL UNIQUE,
	realname VARCHAR(30) NOT NULL,
	pass VARCHAR(50) NOT NULL,
	salt VARCHAR(50) NOT NULL,
	email VARCHAR(50) NOT NULL UNIQUE,
	createTS TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	lastlogTS TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE groups
(
	groupID INTEGER NOT NULL UNIQUE PRIMARY KEY AUTOINCREMENT,
	name VARCHAR(30) NOT NULL,
	createTS TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE groupUserRel
(
	userID INTEGER NOT NULL,
	groupID INTEGER NOT NULL,
	owner INTEGER NOT NULL DEFAULT 0, /* 0=member, 1=owner */
	PRIMARY KEY(userID, groupID),
	FOREIGN KEY(userID) REFERENCES users(userID),
	FOREIGN KEY(groupID) REFERENCES groups(groupID)
);

CREATE TABLE bills
(
	billID INTEGER NOT NULL UNIQUE PRIMARY KEY AUTOINCREMENT,
	name VARCHAR(50) NOT NULL,
	total DOUBLE NOT NULL,
	typeID INTEGER NOT NULL DEFAULT 1,
	createTS TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	editTS TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	dueTS TIMESTAMP NOT NULL DEFAULT CURRENT_DATE,
	complete INTEGER NOT NULL DEFAULT 0,
	FOREIGN KEY(typeID) REFERENCES billTypes(typeID)
);

CREATE TABLE billTypes
(
	typeID INTEGER NOT NULL UNIQUE PRIMARY KEY AUTOINCREMENT,
	typename VARCHAR(30) NOT NULL,
	icon VARCHAR(50)
);

INSERT INTO billTypes(typename)
VALUES ('Generic'), ('Water Bill'), ('Gas Bill'), ('Transport'),
	('Groceries'), ('Shopping'), ('Entertainment');

CREATE TABLE billContributors
(
	billID INTEGER NOT NULL,
	userID INTEGER NOT NULL,
	groupID INTEGER DEFAULT 0,
	owner INTEGER NOT NULL DEFAULT 0,
	ammount DOUBLE NOT NULL DEFAULT 0,
	paid INTEGER NOT NULL DEFAULT 0,
	recieved INTEGER NOT NULL DEFAULT 0,
	PRIMARY KEY(billID, userID),
	FOREIGN KEY(billID) REFERENCES bills(billID),
	FOREIGN KEY(userID) REFERENCES users(userID),
	FOREIGN KEY(groupID) REFERENCES groups(groupID)
);
	
CREATE TABLE notifications
(
	notiID INTEGER NOT NULL UNIQUE PRIMARY KEY AUTOINCREMENT,
	userID INTEGER NOT NULL,
	typeID INTEGER NOT NULL DEFAULT 1,
	custmsg VARCHAR(50) DEFAULT NULL,
	addTS TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	checked INTEGER NOT NULL DEFAULT 0,
	FOREIGN KEY(userID) REFERENCES users(userID),
	FOREIGN KEY(typeID) REFERENCES notiTypes(typeID)
);

CREATE TABLE notiTypes
(
	typeID INTEGER NOT NULL UNIQUE PRIMARY KEY AUTOINCREMENT,
	message VARCHAR(50),
	icon VARCHAR(50)
);

INSERT INTO notiTypes(message)
VALUES ('Generic'), ('Invited to Group'), ('Invite Accepted'),
	('Left your Group'), ('Removed from Group'), ('Group Dissolved'),
	('Invited to Contribute'), ('Left Contribution'), ('Contribution Sent'),
	('Contribution Recieved'), ('Bill Marked Complete'), ('Bill Dissolved'), 
	('Bill Edited'), ('Contribution Updated'), ('Invited to Contribute as Part of Group'), 
	('Invite Rejected'), ('Group Invite Requested'), ('Contribution Retracted'),
	('Bill Marked Incomplete'), ('Removed from Bill'), ('Group Ownership Transfered');

CREATE TABLE notiBill
(
	notiID INTEGER NOT NULL UNIQUE PRIMARY KEY,
	billID INTEGER NOT NULL,
	FOREIGN KEY(notiID) REFERENCES notifications(notiID),
	FOREIGN KEY(billID) REFERENCES bills(billID)
);

CREATE TABLE notiGroup
(
	notiID INTEGER NOT NULL UNIQUE PRIMARY KEY,
	groupID INTEGER NOT NULL,
	FOREIGN KEY(notiID) REFERENCES notifications(notiID),
	FOREIGN KEY(groupID) REFERENCES groups(groupID)
);

CREATE TABLE notiUser
(
	notiID INTEGER NOT NULL UNIQUE PRIMARY KEY,
	secondUserID INTEGER NOT NULL,
	FOREIGN KEY(notiID) REFERENCES notifications(notiID),
	FOREIGN KEY(secondUserID) REFERENCES users(userID)
);
