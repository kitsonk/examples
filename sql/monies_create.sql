/**
 * Creates the tables for the monies.sqlite
 * Author: kitson.kelly (at) bskyb.com
 * Date: 11-Feb-2012
 */

DROP TABLE IF EXISTS monies;

CREATE TABLE monies (
  id  INTEGER PRIMARY KEY AUTOINCREMENT,
  fname  TEXT,
  lname  TEXT,
  bday  TEXT,
  banksort  TEXT,
  bankacct  TEXT,
  amount  TEXT
);

INSERT INTO monies(fname,lname,bday,banksort,bankacct,amount) VALUES ('Scott','Mackay','15/01/1950','44-04-40','12345678','5434393.30');