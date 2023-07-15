CREATE TABLE user_conversations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    conversation_id INT UNSIGNED NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX user_id_index (user_id),
    INDEX conversation_id_index (conversation_id)
);

CREATE TABLE conversations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type ENUM('chatgpt-3.5', 'chatgpt-4') NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE conversation_records (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT UNSIGNED NOT NULL,
    user_message TEXT NOT NULL,
    assistant_message TEXT NOT NULL,
    price INT UNSIGNED,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX conversation_id_index (conversation_id)
);

ALTER TABLE conversations
ADD COLUMN title VARCHAR(255) NOT NULL;