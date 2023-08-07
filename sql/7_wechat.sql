CREATE TABLE wechat_users  (
    user_id INT NOT NULL AUTO_INCREMENT,
    open_id VARCHAR(255) NOT NULL,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id),
    INDEX idx_open_id (open_id)
);
