<?php
function generateTable($rows, $config)
{
    echo '<thead>';
    echo '<tr>';

    foreach ($config['fields'] as $fieldKey => $fieldData) {
        echo '<th>' . htmlspecialchars($fieldData['name']) . '</th>';
    }

    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    foreach ($rows as $row) {
        echo '<tr>';

        foreach ($config['fields'] as $fieldKey => $fieldData) {
            $data = isset($fieldData['data']) ?
                ('data-' . $fieldData['data'] . '="' . htmlentities($row[$fieldKey])) . '"'
                : '';
            echo "<td $data>";

            if (isset($fieldData['data'])) {
                $dataKey = $fieldData['data'];
                echo htmlspecialchars($row[$dataKey]);
            } elseif (isset($fieldData['func']) && is_callable($fieldData['func'])) {
                $value = isset($row[$fieldKey]) ? $row[$fieldKey] : null;
                $id = isset($row['id']) ? $row['id'] : null;
                echo $fieldData['func']($value, $fieldKey, $row, $id);
            } else {
                echo htmlspecialchars($row[$fieldKey]);
            }

            echo '</td>';
        }

        echo '</tr>';
    }

    echo '</tbody>';
}

function generatePagination($totalRowCount, $page_num, $per_page)
{
    $totalPages = ceil($totalRowCount / $per_page);
    $pagination = '<div class="pagination">';

    // Button to go to the first page
    $pagination .= '<a href="?' . buildQueryString(['page_num' => 1, 'per_page' => $per_page]) . '">第一页</a>';

    if ($page_num > 1) {
        $prevPageNum = $page_num - 1;
        $pagination .= '<a href="?' . buildQueryString(['page_num' => $prevPageNum, 'per_page' => $per_page]) . '">上一页</a>';
    }

    // Calculate the page numbers to show between "First Page" and "Last Page"
    $startPage = max(1, $page_num - 2);
    $endPage = min($totalPages, $page_num + 2);

    for ($i = $startPage; $i <= $endPage; $i++) {
        if ($i == $page_num) {
            $pagination .= '<span class="current-page">' . $i . '</span>';
        } else {
            $pagination .= '<a href="?' . buildQueryString(['page_num' => $i, 'per_page' => $per_page]) . '">' . $i . '</a>';
        }
    }

    // Next page link
    if ($page_num < $totalPages) {
        $nextPageNum = $page_num + 1;
        $pagination .= '<a href="?' . buildQueryString(['page_num' => $nextPageNum, 'per_page' => $per_page]) . '">下一页</a>';
    }

    $pagination .= '</div>';

    return $pagination;
}
