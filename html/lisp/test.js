// 使用fetch请求testcases.txt文件
fetch('testcases.txt')
    .then(response => response.text())
    .then(data => {
        // 以===分隔testcase
        const testCases = data.split('===');

        // 遍历每个testcase
        for (const testCase of testCases) {
            // 以---分隔每个case的输入和输出
            const [inputCode, expectedOutput] = testCase.split(/\s*---\s*/);

            // 执行AST
            const result = executeASTList(expandSyntaxSugar(compileJispToAST(inputCode)), env);

            // 输出最终结果
            console.log(inputCode.trim());

            // 比较输出
            if (result.toString() === expectedOutput.trim()) {
                console.log('%cTest Passed:', 'color: green');
            } else {
                console.log('%cTest Failed:', 'color: red');
                console.log('Expected Output:', expectedOutput.trim());
                console.log('Actual Output:', result);
            }

        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
