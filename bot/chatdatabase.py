import sqlite3

class ChatDatabase:
    def __init__(self):
        self.conn = sqlite3.connect('chat_records.db')
        self.c = self.conn.cursor()
        self.create_table()

    def create_table(self):
        self.c.execute('''
            CREATE TABLE IF NOT EXISTS chat_records (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                sender_name TEXT,
                sender_puid TEXT,
                message TEXT,
                reply TEXT
            )
        ''')
        self.conn.commit()

    def insert_message(self, sender_name, sender_puid, message):
        self.c.execute('INSERT INTO chat_records (sender_name, sender_puid, message) VALUES (?, ?, ?)', (sender_name, sender_puid, message))
        self.conn.commit()

    def update_reply(self, message_id, reply):
        self.c.execute('UPDATE chat_records SET reply = ? WHERE id = ?', (reply, message_id))
        self.conn.commit()

    def get_recent_messages(self, sender_puid=None, limit=10):
        if sender_puid:
            self.c.execute('SELECT sender_name, message, reply FROM chat_records WHERE sender_puid = ? ORDER BY id DESC LIMIT ?', (sender_puid, limit))
        else:
            self.c.execute('SELECT sender_name, message, reply FROM chat_records ORDER BY id DESC LIMIT ?', (limit,))
        rows = self.c.fetchall()

        # Create a list of message objects with role and content keys
        messages = []
        for row in rows[::-1]:
            sender_name, message, reply = row

            # Create user message object
            user_message = {
                "role": "user",
                "content": f"{sender_name}: {message}"
            }
            messages.append(user_message)

            # Create assistant message object if there's a reply
            if reply:
                assistant_message = {
                    "role": "assistant",
                    "content": f"{reply}"
                }
                messages.append(assistant_message)

        return messages

    def close(self):
        self.conn.close()
