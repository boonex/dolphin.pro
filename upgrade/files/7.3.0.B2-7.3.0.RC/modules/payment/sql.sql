
UPDATE `sys_page_compose_pages` SET `Title` = 'Payment Shopping Cart' WHERE `Name` = 'bx_pmt_cart';
UPDATE `sys_page_compose_pages` SET `Title` = 'Payment Cart History' WHERE `Name` = 'bx_pmt_history';
UPDATE `sys_page_compose_pages` SET `Title` = 'Payment Order Administration' WHERE `Name` = 'bx_pmt_orders';

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_pmt_cart' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_pmt_cart' AND `Column` != 0 AND @iFirstColumn = 0;

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_pmt_history' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_pmt_history' AND `Column` != 0 AND @iFirstColumn = 0;

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_pmt_orders' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_pmt_orders' AND `Column` != 0 AND @iFirstColumn = 0;

SET @iFirstColumn = (SELECT COUNT(*) FROM `sys_page_compose` WHERE `Page` = 'bx_pmt_details' AND `Column` = 1);
UPDATE `sys_page_compose` SET `Column` = `Column` - 1 WHERE `Page` = 'bx_pmt_details' AND `Column` != 0 AND @iFirstColumn = 0;

