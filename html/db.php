<?php

function getInitializedPDO()
{
    static $pdo;
    if ($pdo) {
        return $pdo;
    }
    try {
        $config = parse_ini_file(dirname(__DIR__) . '/config/db.ini');

        $pdo = new PDO($config['dsn'], $config['username'], $config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        return $pdo;
    } catch (PDOException $e) {
        die("连接失败: " . $e->getMessage());
    }
}

/**
 * 执行带有给定SQL查询和参数的预处理语句。
 *
 * @param string $sql 要执行的SQL查询。
 * @param array $params 要绑定到预处理语句的参数数组。
 * @return PDOStatement 执行的PDOStatement对象。
 * @throws PDOException 如果查询执行失败。
 */
function executePreparedStmt($sql, $params)
{
    $pdo = getInitializedPDO();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * 在给定的表中插入数据。
 *
 * @param string $table 要插入数据的数据库表名称。
 * @param array $data 要插入的数据数组，其中键是列名，值是要插入的值。
 * @return boolean 插入操作是否成功。
 * @throws PDOException 如果查询执行失败。
 */
function insertIntoTable($table, $data) {
    // 构建插入语句
    $columns = implode(", ", array_keys($data));
    $placeholders = ":" . implode(", :", array_keys($data));
    $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";

    // 执行预处理语句
    return executePreparedStmt($sql, $data);
}
