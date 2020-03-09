--
-- Add new tab
--
INSERT INTO `core_form_tab` (`Tab_ID`, `form`, `title`, `subtitle`, `icon`, `counter`, `sort_id`, `filter_check`, `filter_value`) VALUES
('event-ticket', 'event-single', 'Tickets', 'Event Tickets', 'fas fa-ticket-alt', '', '1', '', '');

--
-- Add new partial
--
INSERT INTO `core_form_field` (`Field_ID`, `type`, `label`, `fieldkey`, `tab`, `form`, `class`, `url_view`, `url_list`, `show_widget_left`, `allow_clear`, `readonly`, `tbl_cached_name`, `tbl_class`, `tbl_permission`) VALUES
(NULL, 'partial', 'Tickets', 'event_ticket', 'event-ticket', 'event-single', 'col-md-12', '', '', '0', '1', '0', '', '', '');

--
-- add button
--
INSERT INTO `core_form_button` (`Button_ID`, `label`, `icon`, `title`, `href`, `class`, `append`, `form`, `mode`, `filter_check`, `filter_value`) VALUES
(NULL, 'Add Ticket', 'fas fa-ticket-alt', 'Add Ticket', '/event/ticket/add/##ID##', 'primary', '', 'event-view', 'link', '', ''),
(NULL, 'Save Ticket', 'fas fa-save', 'Save Ticket', '#', 'primary saveForm', '', 'eventticket-single', 'link', '', '');

--
-- create event ticket
--
CREATE TABLE `event_ticket` (
    `Ticket_ID` int(11) NOT NULL,
    `event_idfs` int(11) NOT NULL,
    `article_idfs` int(11) NOT NULL,
    `slots` int(10) NOT NULL,
    `fully_booked` tinyint(1) NOT NULL DEFAULT 0,
    `created_by` int(11) NOT NULL,
    `created_date` datetime NOT NULL,
    `modified_by` int(11) NOT NULL,
    `modified_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `event_ticket`
    ADD PRIMARY KEY (`Ticket_ID`);

ALTER TABLE `event_ticket`
    MODIFY `Ticket_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- add history form
--
INSERT INTO `core_form` (`form_key`, `label`, `entity_class`, `entity_tbl_class`) VALUES
('eventticket-single', 'Event Ticket', 'OnePlace\\Event\\Ticket\\Model\\Ticket', 'OnePlace\\Event\\Ticket\\Model\\TicketTable');

--
-- add form tab
--
INSERT INTO `core_form_tab` (`Tab_ID`, `form`, `title`, `subtitle`, `icon`, `counter`, `sort_id`, `filter_check`, `filter_value`) VALUES
('ticket-base', 'eventticket-single', 'Ticket', 'Ticket Data', 'fas fa-ticket-alt', '', '1', '', '');

--
-- add address fields
--
INSERT INTO `core_form_field` (`Field_ID`, `type`, `label`, `fieldkey`, `tab`, `form`, `class`, `url_view`, `url_list`, `show_widget_left`, `allow_clear`, `readonly`, `tbl_cached_name`, `tbl_class`, `tbl_permission`) VALUES
(NULL, 'text', 'Ticket Name', 'label', 'ticket-base', 'eventticket-single', 'col-md-4', '', '', '0', '1', '0', '', '', ''),
(NULL, 'currency', 'Ticket Price', 'ticket_price', 'ticket-base', 'eventticket-single', 'col-md-2', '', '', '0', '1', '0', '', '', ''),
(NULL, 'number', 'Tickets available', 'slots_total', 'ticket-base', 'eventticket-single', 'col-md-2', '', '', '0', '1', '0', '', '', ''),
(NULL, 'hidden', 'Event', 'event_idfs', 'ticket-base', 'eventticket-single', 'col-md-3', '', '/', '0', '1', '0', '', '', '');

--
-- permission add history
--
INSERT INTO `permission` (`permission_key`, `module`, `label`, `nav_label`, `nav_href`, `show_in_menu`) VALUES
('add', 'OnePlace\\Event\\Ticket\\Controller\\TicketController', 'Add Ticket', '', '', '0');