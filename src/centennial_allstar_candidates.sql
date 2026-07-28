WITH
phases AS (
    SELECT
        e.no AS phase_no,
        e.server_id,
        g.winner_nation,
        e.l12name,
        e.l12pic,
        e.l11name,
        e.l11pic,
        e.l10name,
        e.l10pic,
        e.l9name,
        e.l9pic,
        e.l8name,
        e.l8pic,
        e.l7name,
        e.l7pic,
        e.l6name,
        e.l6pic,
        e.l5name,
        e.l5pic
    FROM emperior e
    LEFT JOIN ng_games g ON g.server_id = e.server_id
    WHERE e.no BETWEEN 1 AND 99
),
hall_eligible AS (
    SELECT
        p.phase_no,
        h.server_id,
        h.general_no,
        h.type,
        h.value,
        h.id
    FROM phases p
    JOIN hall h ON h.server_id = p.server_id
    JOIN ng_old_generals og
      ON og.server_id = h.server_id
     AND og.general_no = h.general_no
    WHERE h.general_no > 2
      AND CAST(COALESCE(JSON_VALUE(og.data, '$.npc'), 0) AS SIGNED) = 0
),
hall_ranked AS (
    SELECT
        phase_no,
        server_id,
        general_no,
        type,
        ROW_NUMBER() OVER (
            PARTITION BY server_id, type
            ORDER BY value DESC, id ASC
        ) AS hall_rank
    FROM hall_eligible
),
selection_reasons AS (
    SELECT
        phase_no,
        server_id,
        general_no,
        CONCAT('hall:', type) AS reason
    FROM hall_ranked
    WHERE hall_rank <= 10
),
chief_slots AS (
    SELECT phase_no, server_id, winner_nation, 12 AS officer_level, l12name AS name, l12pic AS picture FROM phases
    UNION ALL
    SELECT phase_no, server_id, winner_nation, 11, l11name, l11pic FROM phases
    UNION ALL
    SELECT phase_no, server_id, winner_nation, 10, l10name, l10pic FROM phases
    UNION ALL
    SELECT phase_no, server_id, winner_nation, 9, l9name, l9pic FROM phases
    UNION ALL
    SELECT phase_no, server_id, winner_nation, 8, l8name, l8pic FROM phases
    UNION ALL
    SELECT phase_no, server_id, winner_nation, 7, l7name, l7pic FROM phases
    UNION ALL
    SELECT phase_no, server_id, winner_nation, 6, l6name, l6pic FROM phases
    UNION ALL
    SELECT phase_no, server_id, winner_nation, 5, l5name, l5pic FROM phases
),
chief_reasons AS (
    SELECT
        c.phase_no,
        c.server_id,
        og.general_no,
        CONCAT('chief:', c.officer_level) AS reason
    FROM chief_slots c
    JOIN ng_old_generals og
      ON og.server_id = c.server_id
     AND og.name = c.name
     AND SUBSTRING_INDEX(
             COALESCE(JSON_VALUE(og.data, '$.picture'), ''),
             '?=',
             1
         ) = SUBSTRING_INDEX(COALESCE(c.picture, ''), '?=', 1)
     AND (
         CAST(COALESCE(JSON_VALUE(og.data, '$.officer_level'), -1) AS SIGNED) = c.officer_level
         OR (
             JSON_VALUE(og.data, '$.officer_level') IS NULL
             AND (
                 c.winner_nation IS NULL
                 OR CAST(JSON_VALUE(og.data, '$.nation') AS SIGNED) = c.winner_nation
             )
         )
     )
    WHERE og.general_no > 2
),
all_reasons AS (
    SELECT phase_no, server_id, general_no, reason FROM selection_reasons
    UNION ALL
    SELECT phase_no, server_id, general_no, reason FROM chief_reasons
)
SELECT
    r.phase_no,
    r.server_id,
    r.general_no,
    og.name,
    og.data,
    GROUP_CONCAT(DISTINCT r.reason ORDER BY r.reason SEPARATOR ',') AS reasons
FROM all_reasons r
JOIN ng_old_generals og
  ON og.server_id = r.server_id
 AND og.general_no = r.general_no
GROUP BY
    r.phase_no,
    r.server_id,
    r.general_no,
    og.name,
    og.data
ORDER BY r.phase_no, r.general_no;
