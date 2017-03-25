CREATE DATABASE undefinedSpace;
USE undefinedSpace;

DROP TABLE IF EXISTS events;
CREATE TABLE events (
  id          INT  NOT NULL,
  description TEXT NOT NULL,
  PRIMARY KEY (id)
);
INSERT INTO events (id, description) VALUES ('-1', 'NO_SNAPSHOT');
INSERT INTO events (id, description) VALUES ('0', 'IS_EMPTY');
INSERT INTO events (id, description) VALUES ('1', 'INPUT_IS_EMPTY');
INSERT INTO events (id, description) VALUES ('2', 'OUTPUT_IS_EMPTY');
INSERT INTO events (id, description) VALUES ('3', 'IS_CREATED');
INSERT INTO events (id, description) VALUES ('4', 'IS_DELETED');
INSERT INTO events (id, description) VALUES ('5', 'NEW_NAME');
INSERT INTO events (id, description) VALUES ('6', 'NEW_TIME');
INSERT INTO events (id, description) VALUES ('7', 'NEW_HASH');
INSERT INTO events (id, description) VALUES ('8', 'IS_EQUAL');
INSERT INTO events (id, description) VALUES ('9', 'DIRECTORY_END');
INSERT INTO events (id, description) VALUES ('10', 'INIT_PROJECT');
INSERT INTO events (id, description) VALUES ('11', 'START_CONTENT');
INSERT INTO events (id, description) VALUES ('12', 'END_CONTENT');
INSERT INTO events (id, description) VALUES ('13', 'START_FILE_LIST');
INSERT INTO events (id, description) VALUES ('14', 'END_FILE_LIST');

DROP TABLE IF EXISTS changes;
CREATE TABLE changes (
  id          BIGINT    NOT NULL AUTO_INCREMENT,
  id_item     BIGINT    NOT NULL, /* Item ID which was updated */
  id_event    BIGINT, /* ID of event */
  description TEXT,
  time        TIMESTAMP NOT NULL, /* Event from server time */
  PRIMARY KEY (id)
);

DROP TABLE IF EXISTS items;
CREATE TABLE items (
  id        BIGINT    NOT NULL AUTO_INCREMENT,
  id_server INT       NOT NULL,
  id_type   TINYINT   NOT NULL, /* 0 - folder, 1 - file */
  id_parent BIGINT, /* ID of parent folder */
  inode     BIGINT, /* inode should be null if item was deleted */
  name      TEXT      NOT NULL,
  time      TIMESTAMP NOT NULL, /* Time of last update in timestamp */
  hash      BIGINT, /* Some hash, CRC for example */
  deleted   BOOL      NOT NULL DEFAULT FALSE,
  PRIMARY KEY (id)
);

DROP TABLE IF EXISTS projects;
CREATE TABLE projects (
  id         INT       NOT NULL AUTO_INCREMENT,
  id_item    INT, /* ID of current folder */
  id_server  INT       NOT NULL,
  path       TEXT      NOT NULL, /* Full path to project folder */
  time_start TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  time_stop  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
);

DROP TABLE IF EXISTS servers;
CREATE TABLE servers (
  id       INT  NOT NULL AUTO_INCREMENT,
  ip       TEXT NOT NULL, /* IP address of server */
  hostname TEXT NOT NULL,
  token    TEXT NOT NULL, /* Token for RNCryptor */
  PRIMARY KEY (id)
);
INSERT INTO servers (ip, hostname, token) VALUES ('127.0.0.1', 'localhost', 'token');

DROP TABLE IF EXISTS accords;
CREATE TABLE accords (
  id_item    BIGINT NOT NULL,
  id_project INT    NOT NULL,
  PRIMARY KEY (id_item, id_project)
);
