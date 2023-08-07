from wxpy import *
# import atexit
# from chatdatabase import ChatDatabase
import importlib

if __name__ == '__main__':
    # 初始化机器人，扫码登录
    bot = Bot()
    bot.enable_puid('wxpy_puid.pkl')

    # Create an instance of the ChatDatabase class
    # chat_db = ChatDatabase()

    # 监听好友发送的消息
    @bot.register(Friend)
    def on_friend_msg(msg):
        reply_module=importlib.import_module("reply_module")
        reply_module=importlib.reload(reply_module)
        # 将消息传递给外部的 reply_msg 函数进行处理
        reply_module.reply_msg(msg)

    # Close the database connection when the program ends
    # atexit.register(chat_db.close)

    # 进入监听状态
    bot.join()
