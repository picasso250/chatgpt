# Jisp - 一个简单的Lisp方言，使用JavaScript实现

Jisp是一个轻量级的Lisp方言，旨在让任何人都能轻松学习和修改。它与JavaScript密切相关，因此您可以轻松地利用现有的JavaScript库。

## 特色

- 简单易学的语法，适合初学者和有经验的开发人员。
- 与JavaScript亲和，可以直接嵌入JavaScript代码和库。
- 支持自定义语法糖，使您可以更灵活地扩展语言。

## 快速入门

要开始使用Jisp，您需要遵循以下步骤：
???

## API

Jisp提供了一组简单的API函数，以便于解析和执行Jisp代码：

- `compileJispToAST(input)`：将Jisp代码编译成抽象语法树（AST）。

- `expandSyntaxSugar(ast)`：可选的语法糖处理器，用于扩展Jisp语法。

- `executeAST(ast, env)`：在指定的环境中执行AST。

## 示例

以下是一个简单的Jisp示例：

```lisp
; 计算阶乘
(define (factorial n)
  (if (< n 2)
      1
      (* n (factorial (- n 1)))))
      
(display (factorial 5))
```

## 贡献

欢迎贡献代码或提出问题。如果您有任何改进或新功能的想法，请打开一个问题或发送一个拉取请求。

## 许可证

该项目采用MIT许可证。有关详细信息，请参阅[LICENSE](LICENSE)文件。

---

随时探索Jisp并开始编写自己的Lisp方言，欢迎提出改进建议和反馈。祝您编码愉快！