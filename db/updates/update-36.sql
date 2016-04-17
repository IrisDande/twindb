BEGIN;
INSERT INTO `menu_item` (`menuitem_id`, `text`, `iconCls`, `className`, `show`) VALUES (13,'Alerts','bell','alerts-grid','Y');
INSERT INTO `menu_item` (`menuitem_id`, `text`, `iconCls`, `className`, `show`) VALUES (14,'Key Performance Indicators','chart_curve','backup-kpi','Y');

-- Add leaf node 14, a child of 1
INSERT INTO menu_item_tree (ancestor, descendant, depth)
SELECT t.ancestor, 14, depth+1
FROM menu_item_tree AS t
WHERE t.descendant = 1
UNION ALL
SELECT 14, 14, 0;

-- Add leaf node 13, a child of 1
INSERT INTO menu_item_tree (ancestor, descendant, depth)
SELECT t.ancestor, 13, depth+1
FROM menu_item_tree AS t
WHERE t.descendant = 1
UNION ALL
SELECT 13, 13, 0;

INSERT INTO db_version (version) VALUES(36);
COMMIT;
