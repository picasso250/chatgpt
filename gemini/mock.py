import random

class FakeGenerativeModel:
    def __init__(self,name):
        self.history = []
        

    def start_chat(self, history=[]):
        # 模拟start_chat，返回一个新的对话对象
        self.history = []
        return FakeChat(history=self.history)

    

class FakeChat:
    def __init__(self, history=[]):
        self.history = history
        self.responses = [
            "我是一个模拟模型，不具备实际智能。",
            "计算机就像魔法盒子一样，能够执行各种任务。",
            "小学生可以把计算机想象成一个超级聪明的朋友。",
            "计算机就像一个无所不知的助手，可以回答你的问题。",
            "在计算机的世界里，每一道指令都是它的语言。",
        ]

    def add_message(self, role, text):
        # 添加消息到对话历史
        self.history.append({"role": role, "text": text})

    def send_message(self, message):
        # 模拟send_message，返回随机的回复
        response = random.choice(self.responses)
        self.add_message("user", message)
        self.add_message("model", response)
        return response

# # 使用FakeGenerativeModel进行模拟
# fake_model = FakeGenerativeModel()
# fake_chat = fake_model.start_chat()

# user_message = "用中文解释计算机是如何运作的，用一句话，对象是小学生"
# response = fake_model.send_message(fake_chat, user_message)

# # 输出模拟的结果
# print(response)
# print(fake_chat.history)
