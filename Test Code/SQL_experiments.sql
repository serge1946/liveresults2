Base Schema
CREATE TABLE "results" (category text, round text, name text, countrycode text, climberID integer, startnumber integer, qranking integer, battempts1 integer, tattempts1 integer, battempts2 integer, tattempts2 integer, battempts3 integer, tattempts3 integer, battempts4 integer, tattempts4 integer, battempts5 integer, tattempts5 integer, T integer, Ta integer, B integer, Ba integer, Rk integer, ORk integer);
CREATE TABLE 'settings' (compID integer, round text, status integer, m_countback text, f_countback text, m_count integer, f_count integer);

CREATE VIEW overall_result AS
	SELECT qualification.ORk, qualification.category, qualification.name, qualification.countrycode, qualification.climberID, qualification.round, qualification.T, qualification.Ta, qualification.B, qualification.Ba, qualification.Rk, semifinal.T, semifinal.Ta, semifinal.B, semifinal.Ba, semifinal.Rk, final.T, final.Ta, final.B, final.Ba
	FROM qualification
	LEFT OUTER JOIN semifinal ON qualification.climberID=semifinal.climberID
	LEFT OUTER JOIN final ON semifinal.climberID=final.climberID;
CREATE VIEW qualification as select * from results WHERE results.round = 'qualification1' or results.round = 'qualification2';
CREATE VIEW semifinal AS select * from results WHERE results.round = 'semifinal';
CREATE VIEW final AS select * from results WHERE results.round = 'final';

CREATE TRIGGER fcount UPDATE OF tattempts1, tattempts2, tattempts3, tattempts4, tattempts5 ON results BEGIN UPDATE settings SET f_count = f_count + 1 WHERE round = old.round AND old.category = 'f'; END;
CREATE TRIGGER mcount UPDATE OF tattempts1, tattempts2, tattempts3, tattempts4, tattempts5 ON results BEGIN UPDATE settings SET m_count = m_count + 1 WHERE round = old.round AND old.category = 'm'; END;


Alternative Triggers
CREATE TRIGGER fcount UPDATE ON results BEGIN UPDATE settings SET f_count = f_count + 1 WHERE round = old.round AND old.category = 'f'; end;
CREATE TRIGGER mcount UPDATE ON results BEGIN UPDATE settings SET m_count = m_count + 1 WHERE round = old.round AND old.category = 'm'; end;


UPDATE results SET Ta = tattempts1+tattempts2+tattempts3+tattempts4+tattempts5, Ba = battempts1+battempts2+battempts3+battempts4+battempts5  WHERE name = 'Noguchi' AND round='final'

/* As a trigger the following is flawed - we can't be sure that the 'semifinal' round will exist when Rk is UPDATEd for the Qualification Round (indeed it is more likely that it will not) */
	
CREATE TRIGGER qrnk UPDATE OF Rk ON results 
BEGIN 
	UPDATE results SET qranking = new.Rk WHERE climberID = old.climberID AND round = 
		CASE old.round 
			WHEN 'semifinal' THEN 'final' 
			WHEN 'qualification1' THEN 'semifinal' 
			WHEN 'qualification2' THEN 'semifinal' 
		END; 
END;

CREATE TRIGGER qrnk UPDATE OF Rk ON results BEGIN UPDATE results SET qranking = new.Rk WHERE climberID = old.climberID AND round = CASE old.round WHEN 'semifinal' THEN 'final' WHEN 'qualification1' THEN 'semifinal' WHEN 'qualification2' THEN 'semifinal' END; END;