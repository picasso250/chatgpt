import importlib

def reply_msg(msg):
    chatdatabase=importlib.import_module("chatdatabase")
    chatdatabase=importlib.reload(chatdatabase)
    chat_db = chatdatabase.ChatDatabase()

    # 收到的文本消息内容
    text = msg.text
    # 发送者的昵称
    sender_name = msg.sender.name
    # 发送者的PUID
    sender_puid = msg.sender.puid

    # 在控制台打印收到的消息
    print(sender_puid,msg)
    print(f"收到来自 {sender_name} {sender_puid} 的消息：{text}")

    # Insert the user's chat record into the database
    chat_db.insert_message(sender_name, sender_puid, text)

    # Get the recent 10 messages as a single string
    recent_messages = chat_db.get_recent_messages(sender_puid)
    print(recent_messages)

    # You can use GPT-3 or any other API to generate a reply
    # Assuming you have a function askGPT() that takes text and returns a response
    gpt=importlib.import_module("gpt")
    gpt=importlib.reload(gpt)
    reply = gpt.askGPT(recent_messages)
    # reply="message recieved"

    # Update the database with the bot's reply
    chat_db.update_reply(chat_db.c.lastrowid, reply)

    # Reply to the message
    msg.reply(reply)

    chat_db.close()