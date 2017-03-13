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

INSERT INTO users(username, realname, pass, salt, email)
VALUES('SomeUserName', 'First Last', 'd2335ebb952039cdf519ef1eb4c089499d7bec68', 'ae4f281df5a5d0ff3cad6371f76d5c29b6d953ec', 'some@gmail.com'),
	('AnotherUser', 'Random Name', 'd2335ebb952039cdf519ef1eb4c089499d7bec68', 'ae4f281df5a5d0ff3cad6371f76d5c29b6d953ec', 'another@yahoo.com'),
	('Idunno', 'John Smith', 'd2335ebb952039cdf519ef1eb4c089499d7bec68', 'ae4f281df5a5d0ff3cad6371f76d5c29b6d953ec', 'dunno@microsoft.live.co.uk'),
	('A', 'This Name is long', 'd2335ebb952039cdf519ef1eb4c089499d7bec68', 'ae4f281df5a5d0ff3cad6371f76d5c29b6d953ec', 'C@D');
	/*Passwords are all 'A'*/
	/*All salts are 'B'*/
	/*Encrypted Passwords are sha1($salt."--A")*/


CREATE TABLE groups
(
	groupID INTEGER NOT NULL UNIQUE PRIMARY KEY AUTOINCREMENT,
	name VARCHAR(30) NOT NULL,
	createTS TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO groups(name)
VALUES ('First Group'), ('New Group'), ('House Group');

CREATE TABLE groupUserRel
(
	userID INTEGER NOT NULL,
	groupID INTEGER NOT NULL,
	owner BOOLEAN NOT NULL DEFAULT FALSE, /* 0=member, 1=owner */
	joined BOOLEAN NOT NULL DEFAULT FALSE, /* Users only added to group if they accept the invitation. */
	PRIMARY KEY(userID, groupID),
	FOREIGN KEY(userID) REFERENCES users(userID),
	FOREIGN KEY(groupID) REFERENCES groups(groupID)
);

INSERT INTO groupUserRel(userID, groupID, owner, joined)
VALUES (1,1,1,1), (1,2,0,1), (2,2,0,1), (2,1,0,1), (3,2,1,1), (4,3,1,1);

CREATE TABLE bills
(
	billID INTEGER NOT NULL UNIQUE PRIMARY KEY AUTOINCREMENT,
	name VARCHAR(50) NOT NULL,
	total DOUBLE NOT NULL,
	typeID INTEGER NOT NULL DEFAULT 1,
	createTS TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	editTS TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	dueTS TIMESTAMP NOT NULL DEFAULT CURRENT_DATE,
	complete BOOLEAN NOT NULL DEFAULT FALSE,
	FOREIGN KEY(typeID) REFERENCES billTypes(typeID)
);

INSERT INTO bills(name, total, typeID)
VALUES ('Water Bill', 55.6, 3),
	('Gas Bill', 20.5, 2),
	('Taxis for last night', 20, 4);
	
INSERT INTO bills(name, total, complete)
VALUES ('Lemon Tea', 5, 1);

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
	owner BOOLEAN NOT NULL DEFAULT FALSE,
	ammount DOUBLE NOT NULL DEFAULT 0,
	paid BOOLEAN NOT NULL DEFAULT FALSE,
	recieved BOOLEAN NOT NULL DEFAULT FALSE,
	PRIMARY KEY(billID, userID),
	FOREIGN KEY(billID) REFERENCES bills(billID),
	FOREIGN KEY(userID) REFERENCES users(userID),
	FOREIGN KEY(groupID) REFERENCES groups(groupID)
);

INSERT INTO billContributors(billID, userID, groupID, owner, ammount)
VALUES (1, 1, 1, 1, 50), (1, 2, 1, 0, 5.6), (2, 3, 2, 1, 10), (2, 4, 0, 0, 10.5),
	(3, 1, 0, 1, 10	), (4, 1, 0, 1, 5);
INSERT INTO billContributors(billID, userID, ammount, paid, recieved)
VALUES (2, 1, 2.5, 1, 1);
	
CREATE TABLE notifications
(
	notiID INTEGER NOT NULL UNIQUE PRIMARY KEY AUTOINCREMENT,
	userID INTEGER NOT NULL,
	typeID INTEGER NOT NULL DEFAULT 1,
	custmsg VARCHAR(50) DEFAULT NULL,
	addTS TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	checked BOOLEAN NOT NULL DEFAULT FALSE,
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
	('Contribution Recieved'), ('Bill Complete'), ('Bill Dissolved'), 
	('Bill Edited'), ('Contribution Updated'), ('Invited to Contribute as Part of Group'), 
	('Invite Rejected'), ('Group Invite Requested');

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