function executeASTList(astList, env) {
    let res;
    for (let i = 0; i < astList.length; i++) {
        if (Array.isArray(astList[i]) && astList[i].length === 0) continue;
        res = executeAST(astList[i], env);
    }
    return res;
}
function executeAST(ast, env) {
    if (typeof ast === 'string') { // 如果 ast 是字符串，就是变量名
        return env[ast];
    } else if (typeof ast === 'number') { // 如果 ast 是数字，就是字面量
        return ast;
    } else if (ast[0] === 'quote') { // 如果 ast 是 quote，返回其第二个元素
        return ast[1];
    } else if (ast[0] === 'if') { // 如果 ast 是 if，则根据条件判断递归求值
        const [_, condition, thenClause, elseClause] = ast;
        const conditionResult = executeAST(condition, env);
        return executeAST(conditionResult ? thenClause : elseClause, env);
    } else if (ast[0] === 'def') { // 如果 ast 是 define，将其第三个元素作为变量名添加到环境中
        const [_, name, value] = ast;
        env[name] = executeAST(value, env);
    } else if (ast[0] === 'lambda') { // 如果 ast 是 lambda，创建一个新的函数，并返回
        const [_, params, body] = ast;
        return function () {
            const args = arguments;
            const scope = Object.create(env);
            for (let i = 0; i < args.length; i++) {
                scope[params[i]] = args[i];
            }
            return executeAST(body, scope);
        }
    } else { // 如果 ast 是函数调用，递归求值并调用函数
        const [fn, ...args] = ast.map(item => executeAST(item, env));
        return fn.apply(null, args);
    }
}



const env = {
    '+': (a, b) => a + b,
    '-': (a, b) => a - b,
    '*': (a, b) => a * b,
    '/': (a, b) => a / b,
    '>': (a, b) => a > b,
    '<': (a, b) => a < b,
    '>=': (a, b) => a >= b,
    '<=': (a, b) => a <= b,
    '=': (a, b) => a === b,
    'null?': (a) => a === null,
    'eq?': (a, b) => a === b,
    'car': (a) => a[0],
    'cdr': (a) => a.slice(1),
    'cons': (a, b) => [a].concat(b),
};

const factorial = ['lambda', ['n'], ['if', ['=', 'n', 0], 1, ['*', 'n', ['factorial', ['-', 'n', 1]]]]];

env['factorial'] = executeAST(factorial, env); // 将函数添加到环境中

console.log(executeAST(['factorial', 0], env)); // 输出 1
console.log(executeAST(['factorial', 1], env)); // 输出 1
console.log(executeAST(['factorial', 5], env)); // 输出 120

console.log(executeAST(['+', 1, 2], env)); // 输出 3


function expandSyntaxSugar(ast) {
    if (ast[0] === 'let') {
        const [_, bindings, body] = ast;
        const params = [];
        const args = [];

        for (const binding of bindings) {
            const [name, value] = binding;
            params.push(name);
            args.push(value);
        }

        // Convert let to lambda call in-place
        return [['lambda', params, body], ...args];
    } else if (ast[0] === 'def') {
        const [_, name, value] = ast;
        if (Array.isArray(name)) {
            // Handle the (def (f x) y) syntax
            const functionName = name[0]; // Get the function name 'f'
            const args = name.slice(1);  // Get the arguments 'x'
            const lambdaExpression = ['lambda', args, value];
            return ['def', functionName, lambdaExpression];
        } else {
            // Not (def (f x) y), return as-is
            return ast;
        }
    } else if (Array.isArray(ast)) {
        // Recursively process nested expressions
        return ast.map(expandSyntaxSugar);
    } else {
        return ast; // Not an expression, return as-is
    }
}

// 示例用法
const inputAst = ['let', [['x', 10], ['y', 20]], ['+', 'x', 'y']];
const transformedAst = expandSyntaxSugar(inputAst);
console.log(transformedAst);
console.log(executeAST(transformedAst, env));

// Test the 'def' part
const inputExpression = ['def', ['f', 'x'], 'y'];
const expandedExpression = expandSyntaxSugar(inputExpression);

console.log('Original Expression:', inputExpression);
console.log('Expanded Expression:', expandedExpression);
function compileJispToAST(input) {
    let tokens = input.replace(/\(/g, ' ( ').replace(/\)/g, ' ) ').replace(/;/g, ' ; ').split(/\s+/).filter(token => token.length > 0);
    let stack = [];
    let output = [];

    // 新的处理分号的函数
    function processSemicolon(tokens) {

        if (tokens.includes(';')) {
            let newTokens = [];
            let foundSemicolon = false;

            for (let token of tokens) {
                if (token === ';') {
                    foundSemicolon = true;
                    newTokens.push(')');
                    newTokens.push('(');
                } else {
                    newTokens.push(token);
                }
            }

            if (foundSemicolon) {
                newTokens = ['('].concat(newTokens, [')']);
            }

            return newTokens;
        }
        return tokens;
    }

    // 处理分号
    tokens = processSemicolon(tokens);

    for (let i = 0; i < tokens.length; i++) {
        let token = tokens[i];
        if (token === '(') {
            stack.push(output);
            output = [];
        } else if (token === ')') {
            let lastOutput = output;
            output = stack.pop();
            output.push(lastOutput);
        } else {
            if (!isNaN(parseFloat(token))) {
                output.push(parseFloat(token));
            } else {
                output.push(token);
            }
        }
    }

    return output;
}

// Example usage:
const input = "if c a b;";
const ast = compileJispToAST(input);
console.log(ast);

const lispCode = `
(def (factorial n)
  (if (= n 0)
      1
      (* n (factorial (- n 1)))))
(factorial 1)
`;

const jsArray = compileJispToAST(lispCode);
console.log(jsArray);
const jsArrayAfterSugar = expandSyntaxSugar(jsArray);
console.log(jsArrayAfterSugar);
console.log(executeASTList(jsArrayAfterSugar, env));



