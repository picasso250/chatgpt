<?php
$directories = array(
    __DIR__ . "/../html",           // Original directory
    __DIR__ . "/../html/adminxx"    // Additional directory
);

foreach ($directories as $directory) {
    $files = scandir($directory);

    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) == "html") {
            $filePath = $directory . "/" . $file;
            $lines = file($filePath);

            $updatedContent = "";
            foreach ($lines as $line) {
                if (preg_match('/<(script|link).*?(href|src)="([^"]+)"/', $line, $matches)) {
                    $matchedHref = $matches[3];
                    $matchedHref = trim($matchedHref);
                    $filename = explode('?', $matchedHref)[0];
                    $filepath = $directory . "/" . $filename;

                    if (file_exists($filepath)) {
                        $md5 = md5_file($filepath);
                        $updatedLine = str_replace($matchedHref, $filename . "?v=" . $md5, $line);
                        $line = $updatedLine;
                    }
                }

                $updatedContent .= $line;
            }

            file_put_contents($filePath, $updatedContent);
        }
    }
}
