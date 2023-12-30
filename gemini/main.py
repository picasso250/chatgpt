import platform
from fastapi import FastAPI, Form, Request, HTTPException
from fastapi.templating import Jinja2Templates
from fastapi.responses import HTMLResponse
from fastapi.staticfiles import StaticFiles

app = FastAPI()
app.mount("/gemini/js", StaticFiles(directory="js"), name="js")  # Updated path for static files
templates = Jinja2Templates(directory="templates")

# 检测操作系统类型
is_windows = platform.system().lower() == 'windows'

# 根据操作系统选择使用真实模型还是模拟模型
if is_windows:
    from mock import FakeGenerativeModel as GenerativeModel  # Assuming you have the mock library installed
else:
    import google.generativeai as genai
    GOOGLE_API_KEY = 'AIzaSyA5V7OQZfFUkZlSt6TBR1vVLnco8PFS6wc'
    genai.configure(api_key=GOOGLE_API_KEY)
    GenerativeModel = genai.GenerativeModel

@app.get("/gemini/", response_class=HTMLResponse)  # Updated path for the root endpoint
def read_root(request: Request):
    return templates.TemplateResponse("index.html", {"request": request})

@app.post("/gemini/send_message/")  # Updated path for the send_message endpoint
def send_message(data: dict):
    message = data.get("message")
    history = data.get("history", [])

    if not message:
        raise HTTPException(status_code=400, detail="Message is required")

    # 创建一个新的 chat 实例，将 history 参数传递给 start_chat
    model = GenerativeModel('gemini-pro')
    chat = model.start_chat(history=history)

    response = chat.send_message(message)
    chat_history=[]
    for h in chat.history:
        chat_history.append({'role':h.role,'text':h.parts[0].text})
    return {"response": response, "history": chat_history}

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="127.0.0.1", port=8000)
