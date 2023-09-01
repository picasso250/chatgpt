<?php

require_once '../lib.php';

function getOrderString($config)
{
    $order_by = isset($_GET['order_by']) ? $_GET['order_by'] : null;
    $order_dir = isset($_GET['order_dir']) && strtolower($_GET['order_dir']) == 'desc' ? 'desc' : 'asc';

    if ($order_by && isset($config['fields'][$order_by])) {
        return "ORDER BY $order_by $order_dir";
    } else {
        return '';
    }
}

function generateTable($rows, $config)
{
    $order_by = isset($_GET['order_by']) ? $_GET['order_by'] : null;
    $order_dir = isset($_GET['order_dir']) && strtolower($_GET['order_dir']) == 'desc' ? 'desc' : 'asc';

    $order_dir_icon = $order_dir == 'desc' ? '▼' : '▲';

    echo '<thead>';
    echo '<tr>';

    foreach ($config['fields'] as $fieldKey => $fieldData) {
        $order_dir_param = $order_by == $fieldKey && $order_dir == 'asc' ? 'desc' : 'asc';
        $order_dir_icon_html = $order_by == $fieldKey ? " $order_dir_icon" : '';
        $query_params = ['order_by' => $fieldKey, 'order_dir' => $order_dir_param];
        $query_string = '?' . buildQueryString($query_params);
        echo '<th>';
        echo '<a href="' . htmlentities($query_string) . '">';
        echo htmlspecialchars($fieldData['name']) . $order_dir_icon_html;
        echo '</a>';
        echo '</th>';
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
