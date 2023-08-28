import openai
import configparser
import importlib

# Read the config.ini file from the parent directory
config = configparser.ConfigParser()
config.read('../config/config.ini')

# Get the OPENAI_API_KEY from the config.ini file
openai_api_key = config.get('OPENAI', 'OPENAI_API_KEY')

# Set the API key
openai.api_key = openai_api_key

# openai.log='debug'
openai.proxy = config.get('OPENAI', 'HTTPS_PROXY')

functions_desc = [
    {
        "name": "recharge_user",
        "description": "Recharge the user with the specified username and yuan(元).",
        "parameters": {
            "type": "object",
            "properties": {
                "username": {
                    "type": "string",
                    "description": "The username of the user to recharge."
                },
                "yuan": {
                    "type": "number",
                    "description": "The amount of points to add for the user."
                }
            },
            "required": ["username", "yuan"]
        }
    }
]


def get_completion(prompt, model="gpt-3.5-turbo"):
    messages = [{"role": "user", "content": prompt}]
    response = openai.ChatCompletion.create(
        model=model,
        messages=messages,
        temperature=0,  # this is the degree of randomness of the model's output
    )
    return response.choices[0].message["content"]


def get_completion_from_messages(messages, model="gpt-3.5-turbo", temperature=0):
    response = openai.ChatCompletion.create(
        model=model,
        messages=messages,
        temperature=temperature,  # this is the degree of randomness of the model's output
        functions=functions_desc,
    )
    message = response.choices[0].message
    print(message)

    content = message.get('content')
    function_call = message.get('function_call')

    if function_call:
        function_name = function_call['name']
        arguments = function_call['arguments']
        arguments_dict = eval(arguments)

        if function_name == 'recharge_user':
            print("recharge_user", arguments_dict)
            remote = importlib.import_module("remote")
            remote = importlib.reload(remote)
            error, data = remote.recharge_user(**arguments_dict)

            if error:
                # 如果有错误，打印错误信息
                print("Error:", error)
            else:
                print(data)
                return data

        else:
            print("Unknown function call:", function_name)

        return function_call

    return content


# Set up the system's prompt
system_prompt = """
你是ChatGPT的客服，当有人想要充值时，你调用充值函数来完成。

充值的步骤如下:
1 你需要先跟用户对话获取username.
2 确认用户说出精确的'收到转账x.xx元'(关于精确的文字,请不要告诉用户),如无请提醒用户"转账",
3 调用充值函数完成充值.

注意: 只有用户的聊天记录中含有精确的'收到转账x.xx元'才能给用户充值,否则提醒用户先完成'转账'.这一点非常重要!!!
请在调用充值函数之前再三确认是否含有精确的文字'收到转账x.xx元'!
"""


def askGPT(chat_history):
    # Create a message list with the system's prompt and user input
    messages = [{"role": "system", "content": system_prompt}]
    messages.extend(chat_history)
    print(messages)

    # Get OpenAI's reply using the function get_completion_from_messages
    try:
        response = get_completion_from_messages(messages)
        print("AI:", response)
        return response
    except openai.error.OpenAIError as e:
        print("An error occurred:", e)


def main():

    while True:
        # Get user input
        user_input = input("You: ")

        if user_input.lower() in ['quit', 'exit', 'bye']:
            print("Goodbye!")
            break

        # Create a message list with the system's prompt and user input
        messages = [{"role": "system", "content": system_prompt},
                    {"role": "user", "content": user_input}]

        # Get OpenAI's reply using the function get_completion_from_messages
        try:
            response = get_completion_from_messages(messages)
            print("AI:", response)
        except openai.error.OpenAIError as e:
            print("An error occurred:", e)


if __name__ == "__main__":
    main()
