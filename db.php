<?php

function getInitializedPDO()
{
    static $pdo;
    if ($pdo) {
        return $pdo;
    }
    try {
        $config = parse_ini_file('db.ini');

        $pdo = new PDO($config['dsn'], $config['username'], $config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        return $pdo;
    } catch (PDOException $e) {
        die("连接失败: " . $e->getMessage());
    }
}

function executePreparedStmt($sql, $params)
{
    $pdo = getInitializedPDO();
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        die("查询执行失败: " . $e->getMessage());
    }
}
