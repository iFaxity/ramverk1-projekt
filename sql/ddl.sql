--
-- Table User
--
DROP TABLE IF EXISTS User;
CREATE TABLE User (
  `id`       INTEGER NOT NULL,
  `alias`    VARCHAR(50) NOT NULL,
  `email`    VARCHAR(250) NOT NULL,
  `password` CHAR(60) NOT NULL,
  `rep`      INTEGER DEFAULT 0 NOT NULL,
  `created`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated`  TIMESTAMP,

  PRIMARY KEY(`id`),
  UNIQUE(`alias`, `email`)
);

--
-- Table Question
--
DROP TABLE IF EXISTS Question;
CREATE TABLE Question (
  `id`       INTEGER NOT NULL,
  `userId`   INTEGER NOT NULL,
  `answerId` INTEGER,
  `title`    TEXT NOT NULL,
  `content`  TEXT NOT NULL,
  `created`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated`  TIMESTAMP,

  PRIMARY KEY(`id`),
  FOREIGN KEY(`userId`) REFERENCES User(`id`),
  FOREIGN KEY(`answerId`) REFERENCES Answer(`id`)
);



--
-- Table Tag
--
DROP TABLE IF EXISTS Tag;
CREATE TABLE Tag (
  `id`  INTEGER NOT NULL,
  `tag` VARCHAR(100) NOT NULL,

  PRIMARY KEY(`id`),
  UNIQUE(`tag`)
);



--
-- Table QuestionToTags
--
DROP TABLE IF EXISTS QuestionToTags;
CREATE TABLE QuestionToTags (
  `id`         INTEGER NOT NULL,
  `questionId` INTEGER NOT NULL,
  `tagId`      INTEGER NOT NULL,

  PRIMARY KEY(`id`),
  FOREIGN KEY(`questionId`) REFERENCES Question(`id`),
  FOREIGN KEY(`tagId`) REFERENCES Tag(`id`)
);



--
-- Table Answers
--
DROP TABLE IF EXISTS Answer;
CREATE TABLE Answer (
  `id`         INTEGER NOT NULL,
  `userId`     INTEGER NOT NULL,
  `questionId` INTEGER NOT NULL,
  `content`    TEXT NOT NULL,
  `created`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated`    TIMESTAMP,

  PRIMARY KEY(`id`),
  FOREIGN KEY(`userId`) REFERENCES User(`id`),
  FOREIGN KEY(`questionId`) REFERENCES Question(`id`)
);



--
-- Table Comment
--
DROP TABLE IF EXISTS Comment;
CREATE TABLE Comment (
  `id`         INTEGER NOT NULL,
  `userId`     INTEGER NOT NULL,
  `questionId` INTEGER,
  `answerId`   INTEGER,
  `content`    TEXT NOT NULL,
  `created`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated`    TIMESTAMP,

  PRIMARY KEY(`id`),
  FOREIGN KEY(`userId`) REFERENCES User(`id`),
  FOREIGN KEY(`questionId`) REFERENCES Question(`id`),
  FOREIGN KEY(`answerId`) REFERENCES Answer(`id`)
);



--
-- Table Votes
--
-- because votes can be made on questions or on answers
DROP TABLE IF EXISTS Vote;
CREATE TABLE Vote (
  `id`         INTEGER NOT NULL,
  `userId`     INTEGER NOT NULL,
  `questionId` INTEGER,
  `answerId`   INTEGER,
  `commentId`  INTEGER,
  `vote`       TINYINT NOT NULL,

  PRIMARY KEY(`id`),
  FOREIGN KEY(`userId`) REFERENCES User(`id`),
  FOREIGN KEY(`questionId`) REFERENCES Question(`id`),
  FOREIGN KEY(`answerId`) REFERENCES Answer(`id`),
  FOREIGN KEY(`commentId`) REFERENCES Comment(`id`)
);
