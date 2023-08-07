import requests
import configparser
import json

def get_config_data():
    # Read the configuration from config.ini
    config = configparser.ConfigParser()
    config.read('../config/config.ini')
    return config['REMOTE_CALL']

def remote_call(action, data=None):
    config_data = get_config_data()

    # Prepare the payload
    payload = {'action': action, 'secret': config_data['SECRET']}
    if data:
        payload.update(data)
    print(payload)

    # Make the request to the remote URL
    response = requests.post(config_data['REMOTE_URL'], data=payload)

    # Check the response status and return the content as a tuple (error, data)
    if response.status_code == 200:
        try:
            # Try to parse the response as JSON
            response_data = response.json()
            if 'error' in response_data:
                return (response_data['error'], None)
            elif 'data' in response_data:
                return (None, response_data['data'])
            else:
                return ('Unexpected response format from the server.', None)
        except json.JSONDecodeError:
            return ('Error: Unable to parse the response from the server as JSON.', None)
    else:
        return (f"Error: {response.status_code} - {response.reason}", None)

def recharge_user(username, yuan):
    action_name = 'Recharge'
    data = {'username': username, 'point': yuan*100}
    return remote_call(action_name, data)

def set_open_id(user_id, open_id):
    action_name = 'SetOpenID'
    data = {'user_id': user_id, 'open_id': open_id}
    return remote_call(action_name, data)

def get_user_info(open_id):
    action_name = 'GetUserInfo'
    data = {'open_id': open_id}
    return remote_call(action_name, data)

# Example usage:
if __name__ == '__main__':
    # Recharge user example
    user_id = '12345'
    point = 100
    error, result = recharge_user(user_id, point)
    if error:
        print("Error:", error)
    else:
        print("Result:", result)

    # Set OpenID example
    user_id = '12345'
    open_id = 'abc123'
    error, result = set_open_id(user_id, open_id)
    if error:
        print("Error:", error)
    else:
        print("Result:", result)

    # Get User ID example
    open_id = 'abc123'
    error, result = get_user_info(open_id)
    if error:
        print("Error:", error)
    else:
        print("Result:", result)
