select distinct m.stable_id from member as m inner join homology_member as h
on (m.member_id = h.member_id
and m.genome_db_id = 57)
inner join homology_member as h2
on h.homology_id = h2.homology_id
inner join member as m2
on m2.member_id = h2.member_id and m2.stable_id = 'ENSRNOG00000005665';
