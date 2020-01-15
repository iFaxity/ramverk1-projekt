--
-- Question triggers
--

-- Increase user reputation when posting question
DROP TRIGGER IF EXISTS InsertQuestion;
CREATE TRIGGER InsertQuestion
AFTER INSERT ON Question
BEGIN
  UPDATE User
    SET rep = rep + 1
    WHERE id = NEW.userId
  ;
END;

-- Update 'updated' timestamp to current time
DROP TRIGGER IF EXISTS UpdateQuestion;
CREATE TRIGGER UpdateQuestion
AFTER UPDATE OF `content`, `title` ON Question
BEGIN
  UPDATE Question
    SET updated = CURRENT_TIMESTAMP
    WHERE id = OLD.id
  ;
END;

DROP TRIGGER IF EXISTS DeleteQuestion;
CREATE TRIGGER DeleteQuestion
BEFORE DELETE ON Question
BEGIN
  UPDATE User
    SET rep = rep - 1
    WHERE id = OLD.userId
  ;
  DELETE FROM Answer WHERE questionId = OLD.id;
  DELETE FROM Comment WHERE questionId = OLD.id AND answerId IS NULL;
  DELETE FROM Vote WHERE questionId = OLD.id;
END;



--
-- Answer triggers
--

-- Increase user reputation when posting answer
DROP TRIGGER IF EXISTS InsertAnswer;
CREATE TRIGGER InsertAnswer
AFTER INSERT ON Answer
BEGIN
  UPDATE User
    SET rep = rep + 1
    WHERE id = NEW.userId
  ;
END;

-- Update 'updated' timestamp to current time
DROP TRIGGER IF EXISTS UpdateAnswer;
CREATE TRIGGER UpdateAnswer
AFTER UPDATE ON Answer
BEGIN
  UPDATE Answer
    SET updated = CURRENT_TIMESTAMP
    WHERE id = OLD.id
  ;
END;

-- Remove rep from user
DROP TRIGGER IF EXISTS DeleteAnswer;
CREATE TRIGGER DeleteAnswer
BEFORE DELETE ON Answer
BEGIN
  UPDATE User
    SET rep = rep - 1
    WHERE id = OLD.userId
  ;
  DELETE FROM Comment WHERE answerId = OLD.id;
  DELETE FROM Vote WHERE answerId = OLD.id;
END;



--
-- Comment triggers
--

-- Increase user reputation when posting comment
DROP TRIGGER IF EXISTS InsertComment;
CREATE TRIGGER InsertComment
AFTER INSERT ON Comment
BEGIN
  UPDATE User
    SET rep = rep + 1
    WHERE id = NEW.userId
  ;
END;

-- Update 'updated' timestamp to current time
DROP TRIGGER IF EXISTS UpdateComment;
CREATE TRIGGER UpdateComment
AFTER UPDATE ON Comment
BEGIN
  UPDATE Comment
    SET updated = CURRENT_TIMESTAMP
    WHERE id = OLD.id
  ;
END;

DROP TRIGGER IF EXISTS DeleteComment;
CREATE TRIGGER DeleteComment
BEFORE DELETE ON Comment
BEGIN
  UPDATE User
    SET rep = rep - 1
    WHERE id = OLD.userId
  ;
  DELETE FROM Vote WHERE commentId = OLD.id;
END;



--
-- User triggers
--

-- Update 'updated' timestamp to current time
-- Don't run if 'rep' updates, as it only happens in triggers
DROP TRIGGER IF EXISTS UpdateUser;
CREATE TRIGGER UpdateUser
AFTER UPDATE OF `alias`, `email`, `password` ON User
WHEN OLD.rep == NEW.rep
BEGIN
  UPDATE User
    SET updated = CURRENT_TIMESTAMP
    WHERE id = OLD.id
  ;
END;

--
-- Vote triggers
--

-- Update reputation of user who created the post
-- On downvote its -2 rep and on upvote its +10 rep
-- formula: (vote * 7 + 5)
DROP TRIGGER IF EXISTS InsertQuestionVote;
CREATE TRIGGER InsertQuestionVote
AFTER INSERT ON Vote
WHEN NEW.questionId IS NOT NULL
BEGIN
  UPDATE User
    SET rep = rep + (NEW.vote * 7 + 5)
  WHERE
    id IN (SELECT userId FROM Question WHERE id = NEW.questionId)
  ;
END;

DROP TRIGGER IF EXISTS InsertAnswerVote;
CREATE TRIGGER InsertAnswerVote
AFTER INSERT ON Vote
WHEN NEW.answerId IS NOT NULL
BEGIN
  UPDATE User
    SET rep = rep + (NEW.vote * 7 + 5)
  WHERE
    id IN (SELECT userId FROM Answer WHERE id = NEW.answerId)
  ;
END;


DROP TRIGGER IF EXISTS InsertCommentVote;
CREATE TRIGGER InsertCommentVote
AFTER INSERT ON Vote
WHEN NEW.commentId IS NOT NULL
BEGIN
  UPDATE User
    SET rep = rep + (NEW.vote * 7 + 5)
  WHERE
    id IN (SELECT userId FROM Comment WHERE id = NEW.commentId)
  ;
END;


-- Update when vote changes from upvote to downvote
DROP TRIGGER IF EXISTS UpdateQuestionVote;
CREATE TRIGGER UpdateQuestionVote
AFTER UPDATE OF `vote` ON Vote
WHEN NEW.questionId IS NOT NULL AND OLD.vote != NEW.vote
BEGIN
  UPDATE User
    SET rep = rep + (NEW.vote * 7 + 5) - (OLD.vote * 7 + 5)
  WHERE
    id IN (SELECT userId FROM Question WHERE id = NEW.questionId)
  ;
END;

DROP TRIGGER IF EXISTS UpdateAnswerVote;
CREATE TRIGGER UpdateAnswerVote
AFTER UPDATE OF `vote` ON Vote
WHEN NEW.answerId IS NOT NULL AND OLD.vote != NEW.vote
BEGIN
  UPDATE User
    SET rep = rep + (NEW.vote * 7 + 5) - (OLD.vote * 7 + 5)
  WHERE
    id IN (SELECT userId FROM Answer WHERE id = NEW.answerId)
  ;
END;


DROP TRIGGER IF EXISTS UpdateCommentVote;
CREATE TRIGGER UpdateCommentVote
AFTER UPDATE OF `vote` ON Vote
WHEN NEW.commentId IS NOT NULL AND OLD.vote != NEW.vote
BEGIN
  UPDATE User
    SET rep = rep + (NEW.vote * 7 + 5) - (OLD.vote * 7 + 5)
  WHERE
    id IN (SELECT userId FROM Comment WHERE id = NEW.commentId)
  ;
END;


-- Update reputation of user who created the post
DROP TRIGGER IF EXISTS DeleteQuestionVote;
CREATE TRIGGER DeleteQuestionVote
BEFORE DELETE ON Vote
WHEN OLD.questionId IS NOT NULL
BEGIN
  UPDATE User
    SET rep = rep - (OLD.vote * 7 + 5)
  WHERE
    id IN (SELECT userId FROM Question WHERE id = OLD.questionId)
  ;
END;

DROP TRIGGER IF EXISTS DeleteAnswerVote;
CREATE TRIGGER DeleteAnswerVote
BEFORE DELETE ON Vote
WHEN OLD.answerId IS NOT NULL
BEGIN
  UPDATE User
    SET rep = rep - (OLD.vote * 7 + 5)
  WHERE
    id IN (SELECT userId FROM Answer WHERE id = OLD.answerId)
  ;
END;

DROP TRIGGER IF EXISTS DeleteCommentVote;
CREATE TRIGGER DeleteCommentVote
BEFORE DELETE ON Vote
WHEN OLD.commentId IS NOT NULL
BEGIN
  UPDATE User
    SET rep = rep - (OLD.vote * 7 + 5)
  WHERE
    id IN (SELECT userId FROM Comment WHERE id = OLD.commentId)
  ;
END;
