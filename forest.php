<?php
// 获取指定conversation_id的所有conversation_records，并构建成为森林
function getConversationForest($conversationId)
{
    // $records = getConversationRecords($conversationId);
    $records = [
        ['id' => 1, 'prev' => null],
        ['id' => 2, 'prev' => 1],
        ['id' => 3, 'prev' => 1],
        ['id' => 4, 'prev' => 2],
        ['id' => 5, 'prev' => 4],
        ['id' => 6, 'prev' => null],
        ['id' => 7, 'prev' => 6],
        ['id' => 8, 'prev' => 7],
        ['id' => 9, 'prev' => 7],
        ['id' => 10, 'prev' => 9],
    ];

    // Build the forest by finding the roots (records with 'prev' as null)
    $forest = array();
    foreach ($records as $record) {
        if ($record['prev'] === null) {
            $tree = buildTreeFromRoot($record, $records);
            $forest[] = $tree;
        }
    }

    return $forest;
}

// 从给定的根节点构建树
function buildTreeFromRoot($root, $records)
{
    $tree = array(
        'id' => $root['id'],
        // Add other attributes from the root record as needed
    );

    // Find the children of the root and recursively build the tree
    $tree['children'] = array();
    foreach ($records as $record) {
        if ($record['prev'] === $root['id']) {
            $childTree = buildTreeFromRoot($record, $records);
            $tree['children'][] = $childTree;
        }
    }

    return $tree;
}

// Include the file that contains the functions to be tested

class ConversationForestTest
{
    public function runTests()
    {
        $this->testGetConversationForest();
    }

    public function testGetConversationForest()
    {
        // Prepare test data (example records with 'id' and 'prev' attributes)
        $records = [
            ['id' => 1, 'prev' => null],
            ['id' => 2, 'prev' => 1],
            ['id' => 3, 'prev' => 1],
            ['id' => 4, 'prev' => 2],
            ['id' => 5, 'prev' => 4],
            ['id' => 6, 'prev' => null],
            ['id' => 7, 'prev' => 6],
            ['id' => 8, 'prev' => 7],
            ['id' => 9, 'prev' => 7],
            ['id' => 10, 'prev' => 9],
        ];

        // Call the getConversationForest function with a conversation_id (e.g., 1)
        $conversationId = 1;
        $forest = getConversationForest($conversationId);
        // print_r($forest);
        foreach ($forest as $tree)
            echo prettyPrintTree($tree), PHP_EOL;

        // Assert the result contains the correct number of trees (branches)
        $this->assertCount(2, $forest);

        // Assert the structure of the first tree (the one with root 'id' 1)
        $expectedTree1 = [
            'id' => 1,
            'children' => [
                [
                    'id' => 2,
                    'children' => [
                        [
                            'id' => 4,
                            'children' => [
                                ['id' => 5, 'children' => []],
                            ],
                        ],
                    ],
                ],
                [
                    'id' => 3,
                    'children' => [],
                ],
            ],
        ];
        $this->assertArraysEqual($expectedTree1, $forest[0]);

        // Assert the structure of the second tree (the one with root 'id' 6)
        $expectedTree2 = [
            'id' => 6,
            'children' => [
                [
                    'id' => 7,
                    'children' => [
                        ['id' => 8, 'children' => []],
                        [
                            'id' => 9,
                            'children' => [
                                ['id' => 10, 'children' => []],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->assertArraysEqual($expectedTree2, $forest[1]);
    }

    public function assertArraysEqual($expected, $actual)
    {
        if ($expected !== $actual) {
            throw new Exception('Assertion failed: Arrays are not equal.');
        }
    }

    public function assertCount($expected, $actual)
    {
        if (count($actual) !== $expected) {
            throw new Exception('Assertion failed: Incorrect count.');
        }
    }
}

// Run the tests
$testRunner = new ConversationForestTest();
$testRunner->runTests();

function prettyPrintTree($tree, $indent = 0)
{
    $output = str_repeat('  ', $indent) . $tree['id'] . PHP_EOL;

    foreach ($tree['children'] as $child) {
        $output .= prettyPrintTree($child, $indent + 1);
    }

    return $output;
}
