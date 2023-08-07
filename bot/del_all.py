import sqlite3

def delete_all_records():
    conn = sqlite3.connect('chat_records.db')
    c = conn.cursor()

    # Delete all records from the table
    c.execute('DELETE FROM chat_records')

    # Commit the changes and close the connection
    conn.commit()
    conn.close()

if __name__ == "__main__":
    delete_all_records()
