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
            $class = 'class="table-data-' . $fieldKey . '"';
            echo "<td $data $class>";

            if (isset($fieldData['func']) && is_callable($fieldData['func'])) {
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
    $pagination .= '<a href="?' . buildQueryString(['page_num' => 1, 'per_page' => $per_page]) . '" class="btn" role="button">第一页</a>';

    if ($page_num > 1) {
        $prevPageNum = $page_num - 1;
        $pagination .= '<a href="?' . buildQueryString(['page_num' => $prevPageNum, 'per_page' => $per_page]) . '" class="btn" role="button">上一页</a>';
    }

    // Calculate the page numbers to show between "First Page" and "Last Page"
    $startPage = max(1, $page_num - 2);
    $endPage = min($totalPages, $page_num + 2);

    for ($i = $startPage; $i <= $endPage; $i++) {
        if ($i == $page_num) {
            $pagination .= '<a class="btn current-page disabled" role="button">' . $i . '</a>';
        } else {
            $pagination .= '<a href="?' . buildQueryString(['page_num' => $i, 'per_page' => $per_page]) . '" class="btn" role="button">' . $i . '</a>';
        }
    }

    // Next page link
    if ($page_num < $totalPages) {
        $nextPageNum = $page_num + 1;
        $pagination .= '<a href="?' . buildQueryString(['page_num' => $nextPageNum, 'per_page' => $per_page]) . '" class="btn" role="button">下一页</a>';
    }

    $pagination .= '</div>';

    return $pagination;
}

// 函数：获取 page_num
function getPageNum()
{
    $page_num = isset($_GET['page_num']) ? intval($_GET['page_num']) : 1;

    if ($page_num <= 0) {
        $page_num = 1;
    }

    return $page_num;
}

// 函数：获取 per_page，并限制最大数量为 100
function getPerPage()
{
    $per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 10;

    if ($per_page <= 0) {
        $per_page = 10;
    } elseif ($per_page > 100) {
        $per_page = 100;
    }

    return $per_page;
}
