<?php
$directory = __DIR__ . "/../html"; // 指定要处理的目录
$files = scandir($directory); // 获取目录中的所有文件

foreach ($files as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) == "html") { // 只处理.html文件
        $filePath = $directory . "/" . $file;
        $lines = file($filePath); // 按行读取文件内容

        $updatedContent = ""; // 存储处理后的文件内容
        foreach ($lines as $line) {
            // print_r($line);
            if (preg_match('/<(script|link).*?(href|src)="([^"]+)"/', $line, $matches)) { // 匹配 <script> 或 <link> 标签的 href 属性值
                // print_r($line);
                $matchedHref = $matches[3];
                $matchedHref = trim($matchedHref);
                $filename = explode('?', $matchedHref)[0];
                $filepath = $directory . "/" . $filename;

                if (file_exists($filepath)) { // 检查文件是否存在
                    $md5 = md5_file($filepath); // 对文件进行 md5 哈希
                    $updatedLine = str_replace($matchedHref, $filename . "?v=" . $md5, $line); // 在原文件名后面加上 ?v=md5
                    $line = $updatedLine;
                }
            }

            $updatedContent .= $line;
        }

        file_put_contents($filePath, $updatedContent); // 将处理后的内容写入文件
    }
}
