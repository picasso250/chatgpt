import unittest
import os
from bot import ChatDatabase  # Replace 'your_script' with the actual name of your script containing the ChatDatabase class

class TestChatDatabase(unittest.TestCase):
    def setUp(self):
        # Create an instance of the ChatDatabase class for testing
        self.chat_db = ChatDatabase()
        self.db_file = 'chat_records.db'
        self.sample_message_id = None

        # Insert a sample message into the database for testing
        sender_name = 'John'
        sender_puid = '12345'
        message = 'Hello, how are you?'
        self.chat_db.insert_message(sender_name, sender_puid, message)
        self.sample_message_id = self.chat_db.c.lastrowid

    def tearDown(self):
        # Close the database connection and remove the database file after testing
        self.chat_db.close()
        if os.path.exists(self.db_file):
            os.remove(self.db_file)

    def test_insert_message(self):
        # Test inserting a message into the database
        sender_name = 'John'
        sender_puid = '12345'
        message = 'Hello, how are you?'
        self.chat_db.insert_message(sender_name, sender_puid, message)

        # Check if the message was inserted correctly
        self.chat_db.c.execute('SELECT * FROM chat_records WHERE id = ?', (self.chat_db.c.lastrowid,))
        row = self.chat_db.c.fetchone()
        self.assertEqual(row[1], sender_name)
        self.assertEqual(row[2], sender_puid)
        self.assertEqual(row[3], message)
        self.assertIsNone(row[4])  # The reply should be None at this point

    def test_update_reply(self):
        # Test updating the reply for a message in the database
        if self.sample_message_id is not None:
            reply = 'I am doing well, thank you!'
            self.chat_db.update_reply(self.sample_message_id, reply)

            # Check if the reply was updated correctly
            self.chat_db.c.execute('SELECT * FROM chat_records WHERE id = ?', (self.sample_message_id,))
            row = self.chat_db.c.fetchone()
            self.assertEqual(row[4], reply)
        else:
            self.fail("Sample message not found in the database. Unable to perform the test.")

    def test_get_recent_messages(self):
        # Test getting recent messages from the database
        limit = 3
        messages = [
            ('TestUser1', 'Message 1', 'Reply 1'),
            ('TestUser2', 'Message 2', None),
            ('TestUser3', 'Message 3', 'Reply 3')
        ]
        for sender_name, message, reply in messages:
            self.chat_db.insert_message(sender_name, '98765', message)
            if reply:
                self.chat_db.update_reply(self.chat_db.c.lastrowid, reply)

        # Get the recent messages
        recent_messages = self.chat_db.get_recent_messages(limit)

        # Expected formatted messages
        expected_messages = [
            f"{messages[0][0]}: {messages[0][1]}\nReply: {messages[0][2]}",
            f"{messages[1][0]}: {messages[1][1]}",
            f"{messages[2][0]}: {messages[2][1]}\nReply: {messages[2][2]}"
        ]
        expected_result = "\n\n".join(expected_messages)

        # Compare the expected and actual formatted messages
        self.assertMultiLineEqual(recent_messages, expected_result)

if __name__ == '__main__':
    unittest.main()
