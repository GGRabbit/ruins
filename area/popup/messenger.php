<?php
/**
 * Messenger Popup
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: messenger.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Page Content
 */
$popup->set("pagetitle", "Ruins Messenger");
$popup->set("headtitle", "Messenger");

$popup->nav->add(new Link("Verfassen", "popup=popup/messenger&op=create"));
$popup->nav->add(new Link("Posteingang", "popup=popup/messenger&op=inbox"));
$popup->nav->add(new Link("Postausgang", "popup=popup/messenger&op=outbox"));

switch ($_GET['op']) {

    case "send":
        $messageid = MessageSystem::write(	$user->char->id,
                                            $_POST['receivers'],
                                            $_POST['subject'],
                                            $_POST['text'] );
        if (isset($_GET['reply'])) {
            MessageSystem::updateMessageStatus($messageid, $user->char->id, MESSAGESYSTEM_STATUS_REPLIED);
            $popup->output("Antwort gesendet!");
        } else {
            $popup->output("Nachricht gesendet!");
        }
        break;

    default:
    case "create":
        $snippet = $popup->createTemplateSnippet();
        $snippet->assign("target", "popup=popup/messenger&op=send");
        $snippet->assign("receiver", "");
        $snippet->assign("subject", "");
        $snippet->assign("text", "");
        $output = $snippet->fetch("snippet_messenger_create.tpl");
        $popup->output($output, true);

        break;

    case "reply":
        $snippet = $popup->createTemplateSnippet();
        $snippet->assign("target", "popup=popup/messenger&op=send&reply=1");
        if (isset($_GET['replyto'])) {
            $message = MessageSystem::getMessage($_GET['replyto']);
            $snippet->assign("receiver", Manager\User::getCharacterName($message['sender'], false));
            if (substr($message['subject'], 0, 4) != "RE: ") {
                // Add 'RE: ' if there isn't already one
                $snippet->assign("subject", "RE: " . $message['subject']);
            } else {
                $snippet->assign("subject", $message['subject']);
            }
            $snippet->assign("text", "\r\n\n--- Original Message ---\r\n". $message['text']);
        }
        $output = $snippet->fetch("snippet_messenger_create.tpl");
        $popup->output($output, true);
        break;

    case "inbox":
        $popup->addForm("deleteform", true);
        $popup->deleteform->head("deleteform", "popup=popup/messenger&op=delete");

        //MessageSystem::write(rand(4,11), 3, "test", "blabla");
        $messagelist = MessageSystem::getInbox($user->char, array("status", "messages.id", "sender", "subject", "date"));
        foreach ($messagelist as &$message) {
            switch($message['status']) {
                case 0: $message['status'] = "<img src='".$popup->template['mytemplatedir']."/images/message_unread.gif' />"; break;
                case 1: $message['status'] = "<img src='".$popup->template['mytemplatedir']."/images/message_read.gif' />"; break;
                case 2: $message['status'] = "<img src='".$popup->template['mytemplatedir']."/images/message_replied.gif' />"; break;
            }
            $message['sender']		= "<a href='?popup=popup/messenger&op=read&messageid=".$message['_messages_id']."'>".Manager\User::getCharacterName($message['sender'])."</a>";
            $message['subject'] 	= "<a href='?popup=popup/messenger&op=read&messageid=".$message['_messages_id']."'>".$message['subject']."</a>";
            $message['date']		= date("H:i:s d.m.y", strtotime($message['date']));
            $message['action']		= "<input type='checkbox' name='chooser[]' value='".$message['_messages_id']."'>";
            unset ($message['_messages_id']);
        }

        $popup->addTable("messagelist", true);
        $popup->messagelist->setCSS("messagelist");
        $popup->messagelist->setTabAttributes(false);
        $popup->messagelist->addTabHeader(array("", "Absender", "Betreff", "Datum", ""), false, false, "head");
        $popup->messagelist->addListArray($messagelist, "firstrow", "firstrow");
        $popup->messagelist->setSecondRowCSS("secondrow");
        $popup->messagelist->load();

        $popup->output("<div id='messagetools'>", true);
        $popup->output("<input type='button' value='Alle' onclick='checkall(\"deleteform\")' class='button' />", true);
        $popup->deleteform->setCSS("delbutton");
        $popup->deleteform->submitButton("Löschen");
        $popup->output("</div>", true);
        $popup->deleteform->close();
        break;

    case "read":
        if (isset($_GET['messageid'])) {
            $message = MessageSystem::getMessage($_GET['messageid']);
            $snippet = $popup->createTemplateSnippet();
            $snippet->assign("target", "popup=popup/messenger&op=reply&replyto=".$message['id']);
            $snippet->assign("sender", Manager\User::getCharacterName($message['sender']));
            $snippet->assign("date", date("H:i:s d.m.y", strtotime($message['date'])));
            $snippet->assign("subject", $message['subject']);
            $snippet->assign("text", $message['text']);

            MessageSystem::updateMessageStatus($message['id'], $user->char->id, MESSAGESYSTEM_STATUS_READ);
        }
        $output = $snippet->fetch("snippet_messenger_read.tpl");
        $popup->output($output, true);
        break;

    case "delete":
        if (isset($_POST['chooser'])) {
            $popup->output("Willst du wirklich " . count($_POST['chooser']) . " Nachrichten löschen?");
            $popup->addForm("deleteform", true);
            $popup->deleteform->head("deleteform", "popup=popup/messenger&op=delete&ask=yes");
            $popup->deleteform->hidden("ids", implode(",", $_POST['chooser']));
            $popup->deleteform->setCSS("button");
            $popup->deleteform->submitButton("Ja, Löschen");
            $popup->deleteform->close();
        } elseif (isset($_POST['ids'])) {
            $messages = explode(",", $_POST['ids']);

            foreach ($messages as $messageid) {
                MessageSystem::delete($messageid);
            }

            $popup->output(count($messages) . " Nachrichten gelöscht!");
        }
        break;

    case "outbox":
        $messagelist = MessageSystem::getOutbox($user->char, array("messages.id", "receiver", "subject", "date"));

        foreach ($messagelist as &$message) {
            $message['receiver']	= "<a href='?popup=popup/messenger&op=read&messageid=".$message['_messages_id']."'>".Manager\User::getCharacterName($message['receiver'])."</a>";
            $message['subject'] 	= "<a href='?popup=popup/messenger&op=read&messageid=".$message['_messages_id']."'>".$message['subject']."</a>";
            $message['date']		= date("H:i:s d.m.y", strtotime($message['date']));
            unset ($message['_messages_id']);
        }

        $popup->addTable("messagelist", true);
        $popup->messagelist->setCSS("messagelist");
        $popup->messagelist->setTabAttributes(false);
        $popup->messagelist->addTabHeader(array("Empfänger", "Betreff", "Datum"), false, false, "head");
        $popup->messagelist->addListArray($messagelist, "firstrow", "firstrow");
        $popup->messagelist->setSecondRowCSS("secondrow");
        $popup->messagelist->load();
        break;
}
?>
