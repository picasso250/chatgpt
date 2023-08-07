# 导入ChatDatabase类
from chatdatabase import ChatDatabase

def print_all_records():
    # 创建ChatDatabase对象
    chat_db = ChatDatabase()

    # 获取所有聊天记录
    all_messages = chat_db.get_recent_messages(limit=11)

    # 打印聊天记录
    print(all_messages)

    # 关闭数据库连接
    chat_db.close()

if __name__ == "__main__":
    print_all_records()
