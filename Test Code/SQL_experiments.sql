Overall Results
CREATE VIEW overall_result AS
SELECT qualification.ORk, qualification.category, qualification.name, qualification.countrycode, qualification.climberID, qualification.round, qualification.T, qualification.Ta, qualification.B, qualification.Ba, qualification.Rk, semifinal.T, semifinal.Ta, semifinal.B, semifinal.Ba, semifinal.Rk, final.T, final.Ta, final.B, final.Ba
FROM qualification
LEFT OUTER JOIN semifinal ON qualification.climberID=semifinal.climberID
LEFT OUTER JOIN final ON semifinal.climberID=final.climberID

Triggers...
CREATE TRIGGER fcount update on results begin update settings set f_count = f_count + 1 where round = old.round and old.category = 'f'; end;
CREATE TRIGGER mcount update on results begin update settings set m_count = m_count + 1 where round = old.round and old.category = 'm'; end;


update results set Ta = tattempts1+tattempts2+tattempts3+tattempts4+tattempts5, Ba = battempts1+battempts2+battempts3+battempts4+battempts5  where name = 'Noguchi' and round='final'

